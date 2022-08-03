<?php

namespace App\Service;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileUploaderApi
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    private $destinationPath;
    private $errorMessage;
    private $extensions;
    private $allowAll;
    private $maxSize;
    private $uploadName;
    private $imageSeq = "room";
    private $thumbImageSeq = "thumb";

    function setDir($path)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $this->destinationPath = $path;
        $this->allowAll = false;
    }

    function setMaxSize($sizeMB)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $this->maxSize = $sizeMB * (1024 * 1024);
    }

    function setExtensions($options)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $this->extensions = $options;
    }

    function getExtension($string)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            $parts = explode(".", $string);
            $ext = strtolower($parts[count($parts) - 1]);
        } catch (Exception $c) {
            $ext = "";
        }
        return $ext;
    }

    function setMessage($message)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $this->errorMessage = $message;
    }

    function getMessage()
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        return $this->errorMessage;
    }

    function getUploadName()
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        return $this->uploadName;
    }

    function getRandom()
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        return strtotime(date('Y-m-d H:i:s')) . rand(1111, 9999) . rand(11, 99) . rand(111, 999);
    }

    function uploadFile($fileBrowse)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $result = false;
        $size = $_FILES[$fileBrowse]["size"];
        $name = $_FILES[$fileBrowse]["name"];
        $ext = $this->getExtension($name);
        if (!is_dir($this->destinationPath)) {
            $this->setMessage("Destination folder is not a directory ");
            $this->logger->debug(print_r($this->getMessage()));
        } else if (!is_writable($this->destinationPath)) {
            $this->setMessage("Destination is not writable !");
            $this->logger->debug(print_r($this->getMessage()));
        } else if (empty($name)) {
            $this->setMessage("File not selected ");
            $this->logger->debug(print_r($this->getMessage()));
        } else if ($size > $this->maxSize) {
            $this->setMessage("Too large file !");
            $this->logger->debug(print_r($this->getMessage()));
        } else if ($this->allowAll || (in_array($ext, $this->extensions))) {
            $this->logger->debug("Starting upload....");
            $this->uploadName = $this->imageSeq . "-" . substr(md5(rand(1111, 9999)), 0, 8) . $this->getRandom() . rand(1111, 1000) . rand(99, 9999) . "." . $ext;

            //set new dimensions
            $maxDim = 800;
            $minDim = 320;
            $file_name = $_FILES[$fileBrowse]['tmp_name'];
            list($width, $height, $type, $attr) = getimagesize($file_name);
            echo 'image width ' . $width;
            if ($width < $minDim || $height < $minDim) {
                $this->setMessage('Image is too small. Please upload an image with a better quality');
                $this->logger->debug(print_r($this->getMessage()));
                return false;
            }

            //save thumbnail
            $thumbnailMax = 320;
            $ratio = $width / $height;
            if ($ratio > 1) {
                $new_width = $thumbnailMax;
                $new_height = $thumbnailMax / $ratio;
            } else {
                $new_width = $thumbnailMax * $ratio;
                $new_height = $thumbnailMax;
            }
            $src = imagecreatefromstring(file_get_contents($file_name));
            $dst = imagecreatetruecolor($new_width, $new_height);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagedestroy($src);
            imagepng($dst, $this->destinationPath . $this->thumbImageSeq . $this->uploadName); // adjust format as needed
            imagedestroy($dst);

            if ($width > $maxDim || $height > $maxDim) {
                if ($ratio > 1) {
                    $new_width = $maxDim;
                    $new_height = $maxDim / $ratio;
                } else {
                    $new_width = $maxDim * $ratio;
                    $new_height = $maxDim;
                }
                $src = imagecreatefromstring(file_get_contents($file_name));
                $dst = imagecreatetruecolor($new_width, $new_height);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagepng($dst, $this->destinationPath . $this->uploadName); // adjust format as needed
                imagedestroy($dst);

                $result = true;
            } else {
                if (move_uploaded_file($_FILES[$fileBrowse]["tmp_name"], $this->destinationPath . $this->uploadName)) {
                    $result = true;
                } else {
                    $this->setMessage("Upload failed , try later !");
                    $this->logger->debug(print_r($this->getMessage()));
                }
            }
        } else {
            $this->setMessage("Invalid file format !");
            $this->logger->debug(print_r($this->getMessage()));
        }
        $this->logger->debug(print_r($this->getMessage()));
        return $result;
    }
}