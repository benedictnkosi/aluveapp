<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileUploader
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        if(session_id() === ''){
            $logger->info("Session id is empty");
            session_start();
        }
    }

    public function upload($file, $roomId)
    {
        try {
            $this->logger->info("trying to move file");
            //check if room exists first
            $roomApi = new RoomApi($this->em, $this->logger);
            $room = $roomApi->getRoom($roomId);
            if($room === null){
                $responseArray[] = array(
                    'result_message' => "Failed to find room with Id $roomId",
                    'result_code' => 1
                );
                $this->logger->info(print_r($responseArray, true));
                return $responseArray;
            }

            $path = __DIR__ . '/../../public/assets/images/rooms';
            $imageName = uniqid() . ".jpg";
            $file->move($path, $imageName);
            //save to database
            $roomApi = new RoomApi($this->em, $this->logger);
            $responseArray = $roomApi->addImageToRoom($imageName, $room);

            $this->logger->info(print_r($responseArray, true));
            return $responseArray;
        } catch (FileException $e){
            $this->logger->error('failed to upload image: ' . $e->getMessage());
            $responseArray[] = array(
                'result_message' => $e->getMessage(),
                'result_code' => 1
            );
            return $responseArray;
        }
    }
}