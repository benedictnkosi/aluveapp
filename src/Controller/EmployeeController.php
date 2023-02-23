<?php

namespace App\Controller;

use App\Helpers\FormatHtml\ConfigEmployeesHTML;
use App\Service\AddOnsApi;
use App\Service\EmployeeApi;
use App\Service\ReservationApi;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class EmployeeController extends AbstractController
{

    /**
     * @Route("api/config/employees")
     */
    public function getConfigEmployees( LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, EmployeeApi $employeeApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $employees = $employeeApi->getEmployees();
        $configEmployeesHTML = new ConfigEmployeesHTML( $entityManager, $logger);
        $html = $configEmployeesHTML->formatHtml($employees);
        $response = array(
            'html' => $html,
        );
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("admin_api/createemployee")
     */
    public function createEmployee(LoggerInterface $logger, Request $request,EmployeeApi $employeeApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('post')) {
            return new JsonResponse("Internal server error" , 500, array());
        }

        $response = $employeeApi->createEmployee($request->get('name'));
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }


    /**
     * @Route("admin_api/employee/delete/{employeeId}")
     */
    public function deleteEmployee($employeeId, LoggerInterface $logger, Request $request,EmployeeApi $employeeApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('remove')) {
            return new JsonResponse("Internal server error" , 500, array());
        }
        $response = $employeeApi->deleteEmployee($employeeId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("admin_api/employee/update/{employeeId}/{newValue}")
     */
    public function updateEmployees($employeeId, $newValue, LoggerInterface $logger,Request $request, EntityManagerInterface $entityManager, EmployeeApi $employeeApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('put')) {
            return new JsonResponse("Internal server error" , 500, array());
        }
        $response = $employeeApi->updateEmployeeName($employeeId, $newValue);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/json/employee/{id}")
     */
    public function getPaymentJson( $id, LoggerInterface $logger, EmployeeApi $employeeApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $employee = $employeeApi->getEmployee($id);

        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($employee, 'json');

        $logger->info($jsonContent);
        return new JsonResponse($jsonContent , 200, array(), true);
    }
}