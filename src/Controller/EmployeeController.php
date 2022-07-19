<?php

namespace App\Controller;

use App\Helpers\FormatHtml\ConfigEmployeesHTML;
use App\Service\AddOnsApi;
use App\Service\EmployeeApi;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class EmployeeController extends AbstractController
{

    /**
     * @Route("api/config/employees/{propertyUid}")
     */
    public function getConfigEmployees($propertyUid, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, EmployeeApi $employeeApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $employees = $employeeApi->getEmployees($propertyUid);
        $configEmployeesHTML = new ConfigEmployeesHTML( $entityManager, $logger);
        $response = $configEmployeesHTML->formatHtml($employees);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/createemployee/{name}/{propertyUid}")
     */
    public function createEmployee($name, $propertyUid, LoggerInterface $logger, Request $request,EmployeeApi $employeeApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $employeeApi->createEmployee($name, $propertyUid);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }


    /**
     * @Route("api/employee/delete/{employeeId}")
     */
    public function deleteEmployee($employeeId, LoggerInterface $logger, Request $request,EmployeeApi $employeeApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $employeeApi->deleteEmployee($employeeId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/employee/update/{employeeId}/{newValue}")
     */
    public function updateEmployees($employeeId, $newValue, LoggerInterface $logger,Request $request, EntityManagerInterface $entityManager, EmployeeApi $employeeApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $employeeApi->updateEmployeeName($employeeId, $newValue);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }
}