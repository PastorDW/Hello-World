<?php
session_start();


$targetDir = "wwfuploads";
// Create target dir
if (!file_exists($targetDir))
{	
  mkdir($targetDir);	
}

$tmp_name =  $_FILES['filename'] ['tmp_name'];
$fileName = $_FILES['filename'] ['name'];
$size = $_FILES['filename'] ['size'];
$error = $_FILES['filename'] ['error'];
if($error != UPLOAD_ERR_OK)
{
  echo $error;
  exit();
}

$ext = substr($fileName, strpos($fileName, "."));

$uid = uniqid();
$fileName = "$uid.jpg";

move_uploaded_file ($tmp_name, "$targetDir/$fileName");

header("location: wwfprocess.php?uid=$uid&action=imageupload");




?>