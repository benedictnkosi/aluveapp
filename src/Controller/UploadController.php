<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function index(Request      $request,
                          FileUploader $uploader, LoggerInterface $logger): Response
    {
        try {
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
                    return new Response($responseJson[0]['result_message'], Response::HTTP_BAD_REQUEST,
                        ['content-type' => 'text/plain']);
                } else {
                    return new Response("File uploaded for room id " . $_COOKIE['room_id'], Response::HTTP_OK,
                        ['content-type' => 'text/plain']);
                }


            } else {
                return new Response("Room not selected", Response::HTTP_BAD_REQUEST,
                    ['content-type' => 'text/plain']);
            }

        } catch (Exception $ex) {
            return new Response($ex->getMessage(), Response::HTTP_BAD_REQUEST,
                ['content-type' => 'text/plain']);
        }
    }
}