<?php

namespace App\Controller;

use App\Helpers\FormatHtml\ConfigEmployeesHTML;
use App\Service\AddOnsApi;
use App\Service\EmployeeApi;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmployeeController extends AbstractController
{

    /**
     * @Route("api/config/employees")
     */
    public function getConfigEmployees(LoggerInterface $logger, EntityManagerInterface $entityManager, EmployeeApi $employeeApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $employees = $employeeApi->getEmployees();
        $configEmployeesHTML = new ConfigEmployeesHTML( $entityManager, $logger);
        $formattedHtml = $configEmployeesHTML->formatHtml($employees);
        return new Response(
            $formattedHtml
        );
    }

    /**
     * @Route("api/createemployee/{name}")
     */
    public function createEmployee($name, LoggerInterface $logger, EmployeeApi $employeeApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $employeeApi->createEmployee($name);
        return  $this->json($response);
    }


    /**
     * @Route("api/employee/delete/{employeeId}")
     */
    public function deleteEmployee($employeeId, LoggerInterface $logger, EmployeeApi $employeeApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $employeeApi->deleteEmployee($employeeId);
        return  $this->json($response);
    }

    /**
     * @Route("api/employee/update/{employeeId}/{newValue}")
     */
    public function updateEmployees($employeeId, $newValue, LoggerInterface $logger, EntityManagerInterface $entityManager, EmployeeApi $employeeApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $employeeApi->updateEmployeeName($employeeId, $newValue);
        return  $this->json($response);
    }
}