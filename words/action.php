<?php
session_start();
include("../php/common.php");
include("common.php");

$action = $_GET['action'];

if($action == "findwords")
{
  foreach($_POST as $k=>$v)
  {
    $_SESSION[$k] = $v;
  }
  
  $gid = $_GET['gid'];
  
  header("Location: wwf.php?gid=$gid");
}

if($action == "assignletters")
{
 
  //save the images as the letter names
  foreach($_POST as $spot=>$letter)
  {
    if($letter)
    {
      copy("tiles/spaces/$spot.jpg", "tiles/ALPHA/$letter.jpg");
    }
  }
  
  header("Location: test.php");
}

?>