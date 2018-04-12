<?php
include("../php/common.php");
include("common.php");
include("connection.php");

$uid = $_GET['uid'];
$delete = $_GET['delete'];
if($delete)
{
  unlink("wwfuploads/$uid.jpg");
  unlink("wwfwords/$uid.txt");
  for($i = 0; $i < 10; $i++)
    unlink("wwfplays/$uid-$i.jpg");
    
  header("Location: ?");
}



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>WWF Uploader</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"> 
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		<style type="text/css">
		img{max-width:100%;}
		.playimg{display:none;}
		.playrow{padding:5px;background-color:#eee;margin:2px;font-weight:bold;}
		</style>
		<script type="text/javascript">
		function showPlay(c)
		{
		  //$('.playimg').hide();
		  $('#img'+c).toggle();
		}
		
		function upload()
		{
		  $('#uploader, #uploading').toggle();
		  
		  var form = document.forms['upload'];
		  form.submit();
		  
		}
		</script>
	</head>
	<body>
	<center>
	<a href="http://www.dwwd.me"><img src="/images/DWWDlogoHorz.png" style="width:400px;max-width:100%;" /></a><br /><br />
<?php if(!$uid): ?>	
<div id="uploader">
	<form id="upload" action="upload.php?src=wwfupload.php" method="post" enctype="multipart/form-data">
	<h3>Upload your screenshot:</h3><input name="filename" type="file" /> <br /><br />	
	</form>
	<button onclick="upload();" style="font-size:20pt;">Upload</button>
</div>
<div id="uploading" style="display:none">
<h2>Processing, please wait...</h>
</div>
<?php else: ?>
<a href="wwfupload.php?uid=<?php echo $uid; ?>&delete=1">Upload Another</a><br />
<?php 

if(file_exists("wwfwords/$uid.txt"))
{
  $file = fopen("wwfwords/$uid.txt", "r");
  
  for($i = 0; $i < 10; $i++)
  {
    $word = fgets($file);
    echo "<p class=\"playrow\" onclick=\"showPlay($i);\">$word</p>";
    echo "<img class=\"playimg\" id=\"img$i\" src=\"wwfplays/$uid-$i.jpg\" />";
  }
  
  fclose($file);
}
?>
<a href="wwfprocess.php?action=debugimage&uid=<?php echo $uid; ?>">debug image</a>	
<?php endif; ?>

	
	</center>
	</body>
</html>

<?php


?>
