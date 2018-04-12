<?php
session_start();
$GID = uniqid();
include("../php/common.php");
include("../PHPMailer/class.phpmailer.php");
$show = $_GET['show'];
$BLANKS = array("BL", "TW", "DW", "TL", "DL");
$BOARD = array();
for($i = 1; $i <= 15; $i++)
  $BOARD[$i] = array("");
  
$BOARD[1][4] = $BOARD[1][12] = $BOARD[4][1] = $BOARD[4][15] = $BOARD[12][1] = $BOARD[12][15] = $BOARD[15][4] = $BOARD[15][12] = "TW"; 

$BOARD[1][7] = $BOARD[1][9] = $BOARD[4][4] = $BOARD[4][12] = $BOARD[6][6] = $BOARD[6][10] = $BOARD[7][1] = $BOARD[7][15] = $BOARD[9][1] = $BOARD[9][15] = $BOARD[10][6] = $BOARD[10][10] = $BOARD[12][4] = $BOARD[12][12] = $BOARD[15][7] = $BOARD[15][9] = "TL";

$BOARD[2][6] = $BOARD[2][10] = $BOARD[4][8] = $BOARD[6][2] = $BOARD[6][14] = $BOARD[8][4] = $BOARD[8][12] = $BOARD[10][2] = $BOARD[10][14] = $BOARD[12][8] = $BOARD[14][6] = $BOARD[14][10] = "DW";

$BOARD[2][3] = $BOARD[2][13] = $BOARD[3][2] = $BOARD[3][5] = $BOARD[3][11] = $BOARD[3][14] = $BOARD[5][3] = $BOARD[5][7] = $BOARD[5][9] = $BOARD[5][13] = $BOARD[7][5] = $BOARD[7][11] = $BOARD[9][5] = $BOARD[9][11] = $BOARD[11][3] = $BOARD[11][7] = $BOARD[11][9] = $BOARD[11][13] = $BOARD[13][2] = $BOARD[13][5] = $BOARD[13][11] = $BOARD[13][14] = $BOARD[14][3] = $BOARD[14][13] = "DL";

$BOARD[8][8] = "MD";

$PLAYTILES = array();
 
 foreach($ALPHABET as $L)
 {
   $PLAYTILES[$L] = NewMagickWand();
   MagickReadImage($PLAYTILES[$L],"tiles/ALPHA/RED/$L.jpg");
 }
?>
<div>
<button onclick="window.location='?show=assign'">Assign</button>
<button onclick="window.location='?show=alpha'">Alpha</button>
<button onclick="window.location='?show=jpgboard'">JPGBoard</button>
<button onclick="window.location='?show=findletters'">Find Letters</button>
<button onclick="window.location='?show=draw'">Draw</button>
<button onclick="window.open('wwf.php?gid=<?php echo $GID; ?>');">Open in WWF</button>
</div>
<pre>
<?php


if($show == "jpgboard")
{

  $mw=NewMagickWand();
  MagickReadImage($mw,'tiles/board7.png');
  //MagickModulateImage($mw, 100, 0, 0);
  //MagickThresholdImage($mw, 5500);
  MagickWriteImage($mw, "tiles/board7.jpg");
  
  echo "<img src=\"tiles/board7.jpg\"/>&nbsp;";
}


//************* SHOW ASSIGN ********************
if($show == "assign")
{
  echo '<form action="action.php?action=assignletters" method="post"  />';
  echo "<button>Assign Letters</button><br />";
  
  
  for($i = 0; $i < 7; $i++)
  {
    $mw=NewMagickWand();
    MagickReadImage($mw,'tiles/board.jpg');
    $Y = 758;
    $X = $i * 91.429;
    MagickCropImage($mw, 58, 58, $X+14, $Y+21);
    MagickWriteImage($mw, "tiles/rack/ml$i.jpg");
    echo "<img src=\"tiles/rack/ml$i.jpg\" />&nbsp;";
  }

  
  for($y = 0; $y < 15; $y++)
  {
    for($x = 0; $x < 15; $x++)
    {	    
	    $mw=NewMagickWand();
	    MagickReadImage($mw,'tiles/board.jpg');
	    $Y = $y * 42.666 + 112.666;
	    $X = $x * 42.666;
	    MagickCropImage($mw, 28, 28, $X+7, $Y+11);
	    unlink("tiles/spaces/$y-$x.jpg");
	    MagickWriteImage($mw, "tiles/spaces/$y-$x.jpg");
	 
	    
	    echo "$y-$x<br /><img style=\"\" src=\"tiles/spaces/$y-$x.jpg\"/><input name=\"$y-$x\" style=\"width:30px;\" /><br />";
	    
    }
    echo "<br /><br />";
  }
  echo '</form>';
  
  
  
} 

if($show == "draw")
{

 //echo '<img src="tiles/board.jpg" />';    

 
 $mw = NewMagickWand();
 MagickReadImage($mw,'wwfcron/boards/board.jpg');
 
 drawWord($mw, "HEX", 1,12,"V");
 
 $dw = NewDrawingWand();
 $pw = NewPixelWand("red");
 DrawSetFillColor($dw, $pw);
 DrawRectangle($dw, 0, 751, 640, 851);
 $pw = NewPixelWand("white");
 DrawSetFillColor($dw, $pw);
 DrawSetFontSize($dw, 75);
 DrawAnnotation($dw, 278, 830, "19");
 
 
 MagickDrawImage($mw, $dw);
 MagickWriteImage($mw, "tiles/comp.jpg");
 
 echo '<img src="tiles/comp.jpg" />';
 /*
   try{
  $email = new PHPMailer();
	$email->From      = 'wwf@dwwd.me';
	//$email->Subject   = "";
	$email->Body      = "PLEASE = 35 PTS";
	$email->AddAddress('7572823111@vzwpix.com');
	$email->setFrom("wwf@dwwd.me");
	
	
	$file_to_attach = "tiles/comp.jpg";

	
	$att = $email->AddAttachment($file_to_attach);
	
	return $email->send();
	
	}catch(phpmailerException $e){
	  echo sprintf('<p>A phpmailer error occurred: <code>%s</code></p>',
	      htmlspecialchars($e->getMessage()));
	}*/
 
 //MagickEchoImageBlob($mw);
 
  
  
}

function drawWord($mw, $word, $r, $c, $dir)
{
  global $PLAYTILES;
  
  $letters = str_split($word);
  $y = $r - 1;
  $x = $c - 1;
  
  
  foreach($letters as $L)
  {
    $Y = $y * 42.666 + 112.666;
    $X = $x * 42.666;
    
    MagickCompositeImage($mw, $PLAYTILES[$L], MW_OverCompositeOp, $X, $Y);
    
    if($dir == "H")
      $x++;
    else
      $y++;    
  }
  
}

//************* SHOW ALPHA ********************

if($show == "alpha")
{
  foreach($BLANKS as $b)
   echo "<img src=\"tiles/ALPHA/$b.jpg\"/><br />";
  
  foreach($ALPHABET as $letter)
   echo "<img src=\"tiles/ALPHA/$letter.jpg\"/><img src=\"tiles/ALPHA/WHITE/$letter.jpg\"/><img src=\"tiles/ALPHA/DL/$letter.jpg\"/><img src=\"tiles/ALPHA/TL/$letter.jpg\"/><img src=\"tiles/ALPHA/DW/$letter.jpg\"/><img src=\"tiles/ALPHA/TW/$letter.jpg\"/><img src=\"tiles/ALPHA/MD/$letter.jpg\"/><br />"; 
   
   
  
}

if($show == "findletters")
{
  
  for($i = 0; $i < 7; $i++)
  {
    $l = findLetter("tiles/rack/ml$i.jpg", "RACK");
    if($l == "BL") $l = "?";
    if($l == "MT") $l = "";
    echo "<img src=\"tiles/rack/ml$i.jpg\" /> = $l<br />";
   
    $_SESSION["$GID-ml$i"] = $l;
  }
  
 
  for($y = 0; $y < 15; $y++)
  {
    for($x = 0; $x < 15; $x++)
    {	    	    
      $r = $y+1;
      $c = $x+1;
	    $space = $BOARD[$r][$c];
	    $l = findLetter("tiles/spaces/$y-$x.jpg", $space);
	    if($l && strlen($l) == 1)
	    {
	      $_SESSION["$GID-$r-$c"] = $l;
	      echo "$y-$x<br /><img src=\"tiles/spaces/$y-$x.jpg\"/><span style=\"font-size:30pt;\"> = $l</span><br /><br />";    
	    }
	    
    }

  }
  


}

if($show == "findlettersx")
{
  $x = 7; $y = 9;
  $space = $BOARD[$x+1][$y+1];
  findLetter("tiles/spaces/$x-$y.jpg", $space);
} 
 
//************* FIND LETTER ********************  
function findLetter($file, $space)
{

  global $ALPHABET, $BLANKS;
  if(!$space) $space = "GN";
  $scores = array();
  $lowN = 1;
  $lowL = "";
  $mw=NewMagickWand();
  MagickReadImage($mw,$file);
 
  if($space != "RACK")
	  foreach($BLANKS as $bl)
	  {
	    $score = compareLetter($mw, $bl, "BL/");
	    //echo "$bl = $score<br />";
	    if($score < .1)
	      return $bl;
	  }  
  
  $ALPHA = $ALPHABET;
  if($space == "RACK")
  {
    $ALPHA[] = "BL";
    $ALPHA[] = "MT";
  }
	foreach($ALPHA as $letter)
  {
     if($space == "RACK")
       $wscore = 1;
     else 
       $wscore = compareLetter($mw, $letter, "WHITE/");
     $oscore = compareLetter($mw, $letter, "$space/");     
     
     $score = min($wscore, $oscore);
     
     //echo "$letter = $score </br>";
     
       
     if($score == 0)
       return $letter;
       
     if($score < $lowN)
     {
       $lowN = $score;
       $lowL = $letter;
     }  
  }
  
  //if($lowN > .17)
    //return;
    
  //echo "<img src=\"$file\"/> = $lowL = $lowN<br />";
  
  return $lowL;
 
  //$COMPS[$lowN * 1000] = "<img src=\"$file\"/> $lowL = $lowN<br />";
  
  //asort($scores);
  //print_r($scores);

}

function compareLetter($mw, $letter, $space)
{
  
  $alpha = NewMagickWand();
  MagickReadImage($alpha,"tiles/ALPHA/$space$letter.jpg");
  $comp = MagickCompareImages($mw, $alpha, MW_MeanAbsoluteErrorMetric);
  $score = $comp[1];
  
  //print_r($comp);
  
  //echo "<br />Compare: $space$letter = $score";
  //echo "tiles/ALPHA/$space$letter.jpg";
     
  return $score;
}
 
?>
</pre>
