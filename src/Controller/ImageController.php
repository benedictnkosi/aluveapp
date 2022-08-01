<?php

namespace App\Controller;

use App\Service\FileUploaderApi;
use App\Service\RoomApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ImageController extends AbstractController
{

    /**
     * @Route("api/configuration/removeimage/{imageId}")
     */
    public function removeImage($imageId, LoggerInterface $logger,Request $request, EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->removeImage($imageId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/configuration/markdefault/{imageId}")
     */
    public function markDefault($imageId, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->markDefault($imageId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/configuration/room/images/{roomId}")
     */
    public function getRoomImagesJson($roomId, LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response = $roomApi->getRoomImagesJson($roomId);
        $callback = $request->get('callback');
        $response = new JsonResponse($response , 200, array());
        $response->setCallback($callback);
        return $response;
    }

    /**
     * @Route("api/configuration/image/upload")
     */
    public function uploadImage(LoggerInterface $logger, Request $request,EntityManagerInterface $entityManager, FileUploaderApi $fileUploaderApi, RoomApi $roomApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $file = $request->files->get('file');
        if (empty($file))
        {
            $logger->info("No file specified");
            return new Response("No file specified",
                Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
        }

        $uploadDir = __DIR__ . '/../../public/rooms_images/';
        $uploader   =   new FileUploaderApi($logger);
        $uploader->setDir($uploadDir);
        $uploader->setExtensions(array('jpg','jpeg','png','gif'));  //allowed extensions list//
        $uploader->setMaxSize(5);                          //set max file size to be allowed in MB//

        if($uploader->uploadFile('file')){   //txtFile is the filebrowse element name //
            $imageName  =   $uploader->getUploadName(); //get uploaded file name, renames on upload//
            //update database
            if(isset($_SESSION['ROOM_ID'])){
                $roomApi->addImageToRoom($imageName, $_SESSION['ROOM_ID']);
            }else{
                $logger->info("Room id not set, refresh page");
                return new Response("Room id not set, refresh page",
                    Response::HTTP_INTERNAL_SERVER_ERROR, ['content-type' => 'text/plain']);
            }
        }else{//upload failed
            header("HTTP/1.1 500 Internal Server Error");
            print_r($uploader->getMessage()); //get upload error message
            return new Response("500 Internal Server Error",
                Response::HTTP_INTERNAL_SERVER_ERROR, ['content-type' => 'text/plain']);
        }
        return new Response("File uploaded",  Response::HTTP_OK,
            ['content-type' => 'text/plain']);
    }


}