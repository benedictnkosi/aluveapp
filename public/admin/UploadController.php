<?php
require_once(__DIR__ . '/Uploader.php');
require_once(__DIR__ . '/application.php');

$uploader   =   new Uploader();
$uploader->setDir(__DIR__ . '/../assets/images/rooms/');
$uploader->setExtensions(array('jpg','jpeg','png','gif'));  //allowed extensions list//
$uploader->setMaxSize(5);                          //set max file size to be allowed in MB//

if($uploader->uploadFile('files')){   //txtFile is the filebrowse element name //
    $image  =   $uploader->getUploadName(); //get uploaded file name, renames on upload//

}else{//upload failed
    header("HTTP/1.1 500 Internal Server Error");
    print_r($uploader->getMessage()); //get upload error message
}

?>