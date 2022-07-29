<?php

class Uploader
{
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
        $this->destinationPath = $path;
        $this->allowAll = false;
    }

    function setMaxSize($sizeMB)
    {
        $this->maxSize = $sizeMB * (1024 * 1024);
    }

    function setExtensions($options)
    {
        $this->extensions = $options;
    }

    function getExtension($string)
    {
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
        $this->errorMessage = $message;
    }

    function getMessage()
    {
        return $this->errorMessage;
    }

    function getUploadName()
    {
        return $this->uploadName;
    }

    function getRandom()
    {
        return strtotime(date('Y-m-d H:i:s')) . rand(1111, 9999) . rand(11, 99) . rand(111, 999);
    }

    function uploadFile($fileBrowse)
    {
        $result = false;
        $size = $_FILES[$fileBrowse]["size"];
        $name = $_FILES[$fileBrowse]["name"];
        $ext = $this->getExtension($name);

        if (!is_dir($this->destinationPath)) {
            $this->setMessage("Destination folder is not a directory ");
        } else if (!is_writable($this->destinationPath)) {
            $this->setMessage("Destination is not writable !");
        } else if (empty($name)) {
            $this->setMessage("File not selected ");
        } else if ($size > $this->maxSize) {
            $this->setMessage("Too large file !");
        } else if ($this->allowAll || (in_array($ext, $this->extensions))) {
            $this->uploadName = $this->imageSeq . "-" . substr(md5(rand(1111, 9999)), 0, 8) . $this->getRandom() . rand(1111, 1000) . rand(99, 9999) . "." . $ext;

            //set new dimensions
            $maxDim = 5000;
            $minDim = 320;
            $file_name = $_FILES[$fileBrowse]['tmp_name'];
            list($width, $height, $type, $attr) = getimagesize($file_name);
            echo 'image width ' . $width;
            if ($width < $minDim || $height < $minDim) {
                $this->setMessage('Image is too small. Please upload an image with a better quality');
                echo $this->getMessage();
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
                if (isset($_COOKIE['room_id'])) {
                    if(!$this->curl(API_SERVER."/api/rooms/addimage/" . $_COOKIE['room_id'] . "/" . $this->uploadName)){
                        $result = false;
                    }
                } else {
                    $this->setMessage('Room ID cookie value not set');
                }
            } else {
                if (move_uploaded_file($_FILES[$fileBrowse]["tmp_name"], $this->destinationPath . $this->uploadName)) {
                    $result = true;
                    if (isset($_COOKIE['room_id'])) {
                        if(!$this->curl(API_SERVER."/api/rooms/addimage/" . $_COOKIE['room_id'] . "/" . $this->uploadName)){
                            $result = false;
                        }
                    } else {
                        $this->setMessage('Room ID cookie value not set');
                    }
                } else {
                    $this->setMessage("Upload failed , try later !");
                }
            }
        } else {
            $this->setMessage("Invalid file format !");
        }
        echo $this->getMessage();
        return $result;
    }

    function curl($url)
    {
        try{
            // create curl resource
            $ch = curl_init();
            // set url
            echo $url;
            curl_setopt($ch, CURLOPT_URL, $url);

            //return the transfer as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // $output contains the output string

            $output = curl_exec($ch);

            curl_close($ch);

            if(str_contains("Successfully linked image to the room", $output)){
                return true;
            }else{
                return false;
            }
        }catch(Exception $ex){
            echo $ex->getMessage();
            return false;
        }
    }

}

?>