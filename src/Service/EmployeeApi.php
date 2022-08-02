<?php

namespace App\Service;

use App\Entity\Employee;
use App\Entity\Property;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

class EmployeeApi
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        if (session_id() === '') {
            $logger->info("Session id is empty" . __METHOD__);
            session_start();
        }
    }

    public function getEmployees(): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $propertyApi = new PropertyApi($this->em, $this->logger);
            $propertyId =   $_SESSION['PROPERTY_ID'];
            return $this->em->getRepository(Employee::class)->findBy(array('property' => $propertyId));
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() .' - '. __METHOD__ . ':' . $ex->getLine() . ' ' .  $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }
        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function updateEmployeeName($employeeId, $newValue)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $employee = $this->em->getRepository(Employee::class)->findOneBy(array("id" => $employeeId));
            if ($employee === null) {
                $responseArray[] = array(
                    'result_message' => "Employee not found",
                    'result_code' => 1
                );
                $this->logger->debug(print_r($responseArray, true));
            } else {
                $employee->setName($newValue);
                $this->em->persist($employee);
                $this->em->flush($employee);

                $responseArray[] = array(
                    'result_message' => "Successfully updated employee",
                    'result_code' => 0
                );
            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() .' - '. __METHOD__ . ':' . $ex->getLine() . ' ' .  $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function deleteEmployee($employeeId)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $employee = $this->em->getRepository(Employee::class)->findOneBy(array("id" => $employeeId));
            if ($employee === null) {
                $responseArray[] = array(
                    'result_message' => "employee not found",
                    'result_code' => 1
                );
                $this->logger->debug(print_r($responseArray, true));
            } else {
                $this->em->remove($employee);
                $this->em->flush();
            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() .' - '. __METHOD__ . ':' . $ex->getLine() . ' ' .  $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    public function createEmployee($employeeName)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            //check if employee with the same name does not exist
            $propertyApi = new PropertyApi($this->em, $this->logger);
            $propertyId =   $_SESSION['PROPERTY_ID'];
            $existingEmployees = $this->em->getRepository(Employee::class)->findBy(array('name' => $employeeName, 'property'=>$_SESSION['PROPERTY_ID']));

            if ($existingEmployees != null) {
                $responseArray[] = array(
                    'result_message' => "Employee with the same name already exists",
                    'result_code' => 1
                );
            } else {
                $property = $this->em->getRepository(Property::class)->findOneBy(array('id' => $_SESSION['PROPERTY_ID']));
                $employee = new Employee();
                $employee->setName($employeeName);
                $employee->setProperty($property);
                $this->em->persist($employee);
                $this->em->flush($employee);
                $responseArray[] = array(
                    'result_message' => "Successfully created employee",
                    'result_code' => 0,
                    'employee_id' => $employee->getId(),
                    'employee_name' => $employee->getName()
                );
            }


        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() .' - '. __METHOD__ . ':' . $ex->getLine() . ' ' .  $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->debug(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }


}