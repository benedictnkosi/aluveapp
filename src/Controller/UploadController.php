<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\FileUploader;
use Psr\Log\LoggerInterface;

class UploadController extends AbstractController
{
    /**
     * @Route("/api/doUpload", name="do-upload")
     * @param Request $request
     * @param FileUploader $uploader
     * @param LoggerInterface $logger
     * @return Response
     */
    public function index(Request $request,
                          FileUploader $uploader, LoggerInterface $logger): Response
    {
        try {
            $responseArray = array();

            $file = $request->files->get('files');
            if (empty($file)) {
                return new Response("No file specified",
                    Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
            }

            $filename = $file->getClientOriginalName();
            $logger->info("file name is $filename");
            if (isset($_COOKIE['room_id'])) {
                $responseJson = $uploader->upload($file, $_COOKIE['room_id']);
                if ($responseJson[0]['result_code'] === 1) {

                    $responseArray[] = array(
                        'result_message' => "File uploaded for room id " . $_COOKIE['room_id'],
                        'result_code'=> 1
                    );
                } else {
                    $responseArray[] = array(
                        'result_message' => "Success",
                        'result_code'=> 0
                    );
                }


            } else {
                $responseArray[] = array(
                    'result_message' => "Room not selected",
                    'result_code'=> 1
                );
            }

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage(),
                'result_code'=> 1
            );

        }

        $callback = $request->get('callback');
        $response = new JsonResponse($responseArray , 200, array());
        $response->setCallback($callback);
        return $response;
    }
}