<?php
session_start();
require_once("../php/common.php");
require_once("common.php");
require_once("connection.php");

global $TILES, $WORDSinPLAY, $LETTERSLEFT, $MYLETTERS, $MYWORDS, $PLAYS, $WILDS;

$uid = $_GET['uid'];
$debug = $_GET['debug'];
$action = $_GET['action'];
$IMAGE = 1;

//echo $debug;

$PLAYTILES = array();

foreach($ALPHABET as $L)
{
	$PLAYTILES[$L] = NewMagickWand();
	MagickReadImage($PLAYTILES[$L],"wwfcron/ALPHA/RED/$L.jpg");
}



if($action == "debugimage")
{
  $PLAYTILES = array();

	foreach($ALPHABET as $L)
	{
	 $PLAYTILES[$L] = NewMagickWand();
	 MagickReadImage($PLAYTILES[$L],"wwfcron/ALPHA/RED/$L.jpg");
	}
	
  processImage();
  processBoard(1000);
  
  header("Location: wwf.php?uid=$uid");
  
}
else if($action == "findwords")
{
  $IMAGE = 0;
  foreach($_POST as $k=>$v)
  {
    $_SESSION[$k] = $v;
  }
  
  processSession();
  processBoard(1000);
  
  $d = ($debug)? "&debug=$debug":"";
  
  header("Location: wwf.php?uid=$uid".$d);
}
else if($action == "imageupload")
{
  	
	processImage();
	processBoard(9);
	
	header("Location: wwfupload.php?uid=$uid");
}




//**===========================================================

function processSession()
{
  global $BOARD, $ALPHABET, $WORDSinPLAY, $LETTERSLEFT, $MYLETTERS, $MYWORDS, $PLAYS, $WILDS, $TILES, $ROWTILECOUNT, $COLTILECOUNT, $uid;
  
  for($r = 1; $r <= 15; $r++)
	{
	  $TILES[$r] = array("");
	  for($c = 1; $c <= 15; $c++)
	  {
	    $TILES[$r][$c] = $_SESSION["$uid-$r-$c"];
	    if($_SESSION["$uid-$r-$c"])
	    {
	      $ROWTILECOUNT[$r]++;
	      $COLTILECOUNT[$c]++;
	    }
	  }
	}
	
	$MYLETTERS = "";
	for($i = 1; $i <= 7; $i++)
	{
	 $MYLETTERS .= $_SESSION["$uid-ml$i"]; 
	}

}

function processImage()
{
  global $BOARD, $ALPHABET, $WORDSinPLAY, $LETTERSLEFT, $MYLETTERS, $MYWORDS, $PLAYS, $WILDS, $TILES, $ROWTILECOUNT, $COLTILECOUNT, $uid;
  
  $dir = "wwfuploads";
  
    
  
  $mw=NewMagickWand();
  MagickReadImage($mw,"$dir/$uid.jpg");
  MagickResizeImage($mw, 640, 960, MW_GaussianFilter, .5);
  MagickWriteImage($mw, "$dir/$uid.jpg");
  
  //echo "<img src=\"$dir/$uid.jpg\" /><br /><br />";
  
  //echo "<br />RACK: ";
  for($i = 0; $i < 7; $i++)
  {
    ClearMagickWand($mw);
    MagickReadImage($mw,"$dir/$uid.jpg");
    $Y = 758;
    $X = $i * 91.429;
    MagickCropImage($mw, 58, 58, $X+14, $Y+21);
    //MagickWriteImage($mw, "rack/ml$i.jpg");
    //echo "<img src=\"rack/ml$i.jpg\" />&nbsp;";
    
     $l = findLetter($mw, "RACK");
     if($l == "BL") $l = "?";
     if($l == "MT") $l = "";
     
     //echo " = $l, ";
     //$_SESSION["$GID-ml$i"] = $l;
     $MYLETTERS .= $l;
     $_SESSION["$uid-ml".($i + 1)] = $l;

  }
  

  //echo "<br /><br />";
  for($y = 0; $y < 15; $y++)
  {
    for($x = 0; $x < 15; $x++)
    {	    
	    ClearMagickWand($mw);
	    MagickReadImage($mw,"$dir/$uid.jpg");
	    $Y = $y * 42.666 + 112.666;
	    $X = $x * 42.666;
	    MagickCropImage($mw, 28, 28, $X+7, $Y+11);
	    MagickWriteImage($mw, "tiles/spaces/$y-$x.jpg");
	    $r = $y+1;
      $c = $x+1;
	    $l = findLetter($mw, $space);
	    if($l && strlen($l) == 1)
	    {
	      //$_SESSION["$GID-$r-$c"] = $l;
	      $TILES[$r][$c] = $l;
	      $_SESSION["$uid-$r-$c"] = $l;
	      $ROWTILECOUNT[$r]++;
        $COLTILECOUNT[$c]++;
	            
	    }

	 
	    
	    //echo "<img src=\"rack/$y-$x.jpg\"/> = $l<br />";
	    
    }
    //echo "<br />";
  }
}




function processBoard($max)
{
  global $BOARD, $ALPHABET, $WORDSinPLAY, $LETTERSLEFT, $MYLETTERS, $MYWORDS, $PLAYS, $WILDS, $TILES, $ROWTILECOUNT, $COLTILECOUNT, $uid;  


$WORDSinPLAY = getWordsInPlay();
$LETTERSLEFT = "";

$letterList = str_split($MYLETTERS);

$MYWORDS = getWords($MYLETTERS, '');


$PLAYS = array();
$WILDS = array();

if(count($WORDSinPLAY))
{

  playRows();
  playCols();

  showScores($max, $from, $uid);  
  
}else
{
  playFirstWord();
  showScores($max, $from, $uid);
}




}

function playFirstWord()
{
  global $MYWORDS, $POINTS, $PLAYS;
  
  $words = array();
  foreach($MYWORDS as $word)
  {
    
    $len = strlen($word);
    $LL = 7 - $len;
	  $score = score($word, 8, 8, "H", $len, array());
	  $PLAYS[$score][] = "$word-8-8-H-$LL"; 
	  $PLAYS[$score] = array_unique($PLAYS[$score]); 
  }
}

function showScores($max, $from, $uid)
{
  global $PLAYS, $IMAGE, $test;
  
  
  
  $scores = array_keys($PLAYS);
  rsort($scores);
  
  $msg = "";
  $count = 0;
  $words = "";
  foreach($scores as $score)
  {
    if(!$IMAGE)
      $words .= "<p style=\"clear:both;\"><br />----------- $score POINTS -------------------</p>";
    
    $plays = $PLAYS[$score];

    foreach($plays as $play)
    {
      $p = explode("-", $play);
      $w = $p[0];
      $r = $p[1];
      $c = $p[2];
      $d = $p[3];
      $LL = $p[4];
          
      if($IMAGE)
      {
        drawWord($uid, $count, $w, $r, $c, $d, $score);
        $words .= "$w = $score\n"; 
      }
      else
      {
        $words .= "<p class=\"word\" id=\"$w-$r-$c-$d\" onclick=\"showWord('$w', '$r', '$c', '$d', '$LL');\">$w</p>";
      }
        
        
      
       
 
      
      $count++;
      
      if($count > $max)
        break;
      
    }
    
    if($count > $max)
        break;
  }
  
  file_put_contents("wwfwords/$uid.txt", $words);
  
  //echo $msg;
  
  //mail($from, "wwf@dwwd.me", "", $msg, "From: wwf@dwwd.me", "-fwwf@dwwd.me");
  //sendMyEmail("DWWDWWF", "wwf@dwwd.me", "$from", '', '', '', $msg, '', '');
}

function drawWord($uid, $count, $word, $r, $c, $dir, $score)
{
  global $PLAYTILES, $test;
  
  $mw=NewMagickWand();
  MagickReadImage($mw,"wwfuploads/$uid.jpg");
  
  $letters = str_split($word);
  $y = $r - 1;
  $x = $c - 1;
  
  $dw = NewDrawingWand();
  $pw = NewPixelWand("red");
  DrawSetFillColor($dw, $pw);
  DrawRectangle($dw, 0, 751, 640, 851);
  $pw = NewPixelWand("white");
  DrawSetFillColor($dw, $pw);
  DrawSetFontSize($dw, 75);
  DrawAnnotation($dw, 278, 830, $score);
	MagickDrawImage($mw, $dw);
	
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
  
	
  
  MagickWriteImage($mw, "wwfplays/$uid-$count.jpg");
  
  //echo "<p class=\"playrow\" onclick=\"showPlay($count);\">$word = $score</p>";
  //echo "<img class=\"playimg\" id=\"img$count\" src=\"wwfplays/$uid-$count.jpg\" />";
  
  
}



//************* FIND LETTER ********************  
function findLetter($mw, $space)
{

  global $ALPHABET, $BLANKS;
  if(!$space) $space = "GN";
  $scores = array();
  $lowN = 1;
  $lowL = "";
  //$mw=NewMagickWand();
  //MagickReadImage($mw,$file);
 
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
  MagickReadImage($alpha,"wwfcron/ALPHA/$space$letter.jpg");
  $comp = MagickCompareImages($mw, $alpha, MW_MeanAbsoluteErrorMetric);
  $score = $comp[1];
  
  //print_r($comp);
  
  //echo "<br />Compare: $space$letter = $score";
  //echo "tiles/ALPHA/$space$letter.jpg";
     
  return $score;
}





function playRows()
{
  global $ROWTILECOUNT, $debug, $br;


  for($r = 1; $r <= 15; $r++)
  {
    //echo $br."row $r: ";
    if($ROWTILECOUNT[$r] || $ROWTILECOUNT[$r - 1] || $ROWTILECOUNT[$r + 1])
     play("row", $r);

     
         
  }
}

function playCols()
{
  global $COLTILECOUNT, $debug, $br;
  

  for($c = 1; $c <= 15; $c++)
  {
    //echo $br."col $c: ";
    if($COLTILECOUNT[$c] || $COLTILECOUNT[$c - 1] || $COLTILECOUNT[$c + 1])
     play("col", $c);

  
  }
}

function play($RorC, $rc)
{
   global $PLAYS, $LETTERSLEFT, $TIMES, $br, $MYWORDS;
   
   $letters = getLetters($RorC, $rc);
   $dir = ($RorC == "row")? "H":"V";
   
   
   $words = getWords($letters, getLikes($RorC, $rc));
   //need to merge the words with MYWORDS because MYWORDS can be played in the open spaces that are eliminated by the getLikes section
   $words = array_merge($words, $MYWORDS);

   $wc = count($words);
   $ll = strlen($letters);
   
   foreach($words as $w)
   {
    
    //only look to where the word can fit in the row
    $end = 16 - strlen($w);
    for($i = 1; $i <= $end; $i++)
    {
	    
	    if($RorC == "row")
	      $play = checkOverlay($w, $rc, $i, $dir);
	    else
	      $play = checkOverlay($w, $i, $rc, $dir);
	    
	    
	    
	    if($play)
	    {
	      
	      //the r,c where the word starts
	      $pr = $play[0];  
	      $pc = $play[1];
	      $score = $play[2]; //the score of the word
	      
	      $LL = implode($LETTERSLEFT);
	      
	      $PLAYS[$score][] = "$w-$pr-$pc-$dir-$LL"; 
	      $PLAYS[$score] = array_unique($PLAYS[$score]);       
	    }
    }
 
   } 
   
   $TIMES[] = round(microtime(true)*1000);
   
   //$time = ($TIMES[$rc] - $TIMES[$rc-1]);
   //$per = round($time/$wc, 2);
   //echo "letters: $ll words: $wc time: $time per: $per";
     
   
}

function getLikes($RorC, $rc)
{
  global $TILES, $br;
  
  $BLOCKS = array();
  $block = "";
  $lastL = "";
  for($i = 1; $i <= 15; $i++)
  {
    if($RorC == "row")
      $L = ($TILES[$rc][$i])? $TILES[$rc][$i]:"_";
    else
      $L = ($TILES[$i][$rc])? $TILES[$i][$rc]:"_";
      
    
    if($L == "_" && $lastL && $lastL != "_")
    { 
      $BLOCKS[] = $block;
      $block = $L;
    }
    else if($lastL && $lastL == "_" && $L != "_")
    {
      $BLOCKS[] = $block;
      $block = $L;
    }
    else
      $block .= $L;
      
    $lastL = $L;
  }
  
  if($block) $BLOCKS[] = $block;
  
  $codes = array();
  $lens = array();
  
  
  
  $bc = count($BLOCKS);
  for($i = 0; $i < $bc; $i++)
  {
    $code = "";
    $block = $BLOCKS[$i];
    $len = strlen($block);
    $prev = $BLOCKS[$i - 1];
    $next = $BLOCKS[$i + 1];

    
    //look at the block - is it blanks or letters?
    if(strpos($block, "_") === 0)
    {
      if($len > 1)
      { 
        $P = "%";       
        if($prev) { $codes[] = $prev.$P; $lens[] = $len + strlen($prev); }
        if($next) { $codes[] = $P.$next; $lens[] = $len + strlen($next); }
        if($prev && $next) { $codes[] = $prev.$P.$next; $lens[] = $len + strlen($prev) + strlen($next); }
                 
      }
      else
      {     
       $codes[] = $prev.$block.$next; 
       $lens[] = $len + strlen($prev) + strlen($next);
       if($i == 2)
       {
         $codes[] = "%".$prev.$block.$next;
         $lens[] = $len + strlen($prev) + strlen($next) + strlen($BLOCKS[0]);
       }
       if($i == ($bc - 3))
       {
         $codes[] = $prev.$block.$next."%";
         $lens[] = $len + strlen($prev) + strlen($next) + strlen($BLOCKS[$bc - 1]);
       }
      }
      
    }
    else
    {
      $codes[] = "%".$block."%";
      $lens[] = $len + strlen($prev) + strlen($next);
    }
  
    
    //if blanks and more than one - change to %:
    	//code1 = prev% (if prev)
    	//code2 = %next (if next)
    	//code3 = prev%next (with prev OR next if on the end)
    	
    //if one blank
    	//code = prev*next
    	
    //make a code of the whole row?
    
    
     
  }
  
  $likes = array();
  
  $i = 0;
  foreach($codes as $code)
  {
    $len = $lens[$i++];
    
    if(strpos($code, "%") ===  0 || strpos($code, "%") > 0)
      $likes[] = "(word LIKE '$code' AND len <= '$len')";
    else
      $likes[] = "word LIKE '$code'";
  }
  
   
  
  //echo "$RorC $rc: ";
  //print_r($likes);
  return $likes;


  
}

function getLetters($RorC, $rc)
{
  global $TILES, $MYLETTERS;
  //decho("----PlayMyLetters---$bl, $r, $c, $dir <br />");
  $LETTERS = $MYLETTERS;
  
  //add the row or column board letters to the collection of letters
  for($i = 1; $i <= 15; $i++)
  {
    if($RorC == "row")
      $LETTERS .= $TILES[$rc][$i];
    else
      $LETTERS .= $TILES[$i][$rc];
  } 
  
  return $LETTERS;
}





//verify the word can actually be played here
function checkOverlay($word, $R, $C, $dir)
{
  global $TILES, $MYLETTERS, $LETTERSLEFT, $br;
  
  decho("checkOverlay: $word, $R, $C, $dir");
  $sidescore = 0;
  $r = $R; $c = $C;
  $myLetters = str_split($MYLETTERS);
  $wilds = array();
  $split = str_split($word);
  $len = count($split);
  foreach($split as $L)
  {
    decho("L = $L");
    //decho("T = ".$TILES[$r][$c].$br.$br);
    
    //if a tile is already played at this spot and it is not equal to the letter I am trying to play, then fail
    if($TILES[$r][$c] && $TILES[$r][$c] != $L) 
    {
      decho("FAIL AT $r - $c");
      return 0;
    }
    
    //if there is no tile at this place on the board, then I am playing a letter out of my stack
    //need to remove it so I know
    if(!$TILES[$r][$c])
    {
      if(in_array($L, $myLetters))
       array_splice($myLetters, array_search($L, $myLetters), 1);
      else if(in_array("?", $myLetters))
      {
        array_splice($myLetters, array_search("?", $myLetters), 1);
        $wilds[$r][$c] = 1;
      }
      else
        return 0;  //if this is a blank spot and I don't have the letter in my rack, then I can't play it
    }
      
    //verify any sideways words generated are valid
    $side = checkSideways($L, $r, $c, $dir, $wilds);
    decho("SIDE = $side");
    if(!$side) return 0;
    if($side > 1) $sidescore += $side;
    
    if($dir == "V") $r++;
    if($dir == "H") $c++;
  }
  
  $start = ($dir == "V")? $R:$C;
  $end = $start + $len - 1;
  
  //if there is a letter in the space just before the beginning of this word, then it's a no go
  $adj = ($dir == "V")? $TILES[$start - 1][$c]:$TILES[$r][$start - 1];
  if(($start - 1) >= 1 && $adj) return 0;

  
  //if there is a letter just past the end of this word, then it's a no go
  $adj = ($dir == "V")? $TILES[$end + 1][$c]:$TILES[$r][$end + 1];
  if(($end + 1) <= 15 && $adj) return 0;

  
  $LETTERSLEFT = $myLetters;
  $used = strlen($MYLETTERS) - count($myLetters);
  
  if($used == 0) return 0; //if I didn't use any of my own letters, then it's not a play

  
  //if the word is made of only my letters and there is no side word, then it's not a play
  if($used == strlen($word) && $sidescore <= 1)  return 0;  
  
  
  
  //decho("SUCCESS!");
  //checkExtension($word, $R, $C, $dir, $wilds);
  $score = score($word, $R, $C, $dir, $used, $wilds);
  $score += $sidescore;
  
  if($score)  decho("$word, $R, $C, $dir, $score");
  
  return array($R, $C, $score);


}

//check the validity of the sideways words created by playing a word, $dir = direction of the word
function checkSideways($L, $R, $C, $dir, $wilds)
{
  global $TILES, $br; 
  $w = "";
  $l = $L;
  $r = $R;
  $c = $C;
  //look right/down
  while($l)
  {
    $w .= $l;
    if($dir == "V") $c++;
    if($dir == "H") $r++;
    $l = $TILES[$r][$c];
  }
  
  $r = ($dir == "V")? $R:$R-1;
  $c = ($dir == "H")? $C:$C-1;
  $l = $TILES[$r][$c]; 
  //look left/up
  while($l)
  {
    $w = $l.$w;
    if($dir == "V") $c--;
    if($dir == "H") $r--;
    $l = $TILES[$r][$c];
  }
  if($dir == "V") $c++;
  if($dir == "H") $r++;
  //echo "R = $R $br C = $C $br L = $L $br W = $w $br";

  
  //if the word is more than just the letter, then verify it is a word
  if($w == $L) 
    return 1; //later add score
  
  if($dir == "H") $dir = "V"; else $dir = "H";  
  if(mysqlFetchObject("words", "word = '$w'"))
  {
    if($TILES[$R][$C] == $L) //if this space was already played, then it is not creating a new word
      return 1;
    //if creatinga new word, calculate score of new word  
    return score($w, $r, $c, $dir, 1, $wilds);
  }
    
  return 0;
  	
  
}

function score($word, $r, $c, $dir, $used, $wilds)
{
  decho("----SCORE----- $word, $r, $c, $dir ---------", "score");

  global $TILES, $BOARD, $POINTS, $MYLETTERS;
  $score = 0;
  $split = str_split($word);
  $mult = 1;
  foreach($split as $L)
  {
    decho("---------------------------------------------------", "score");
    $lp = $POINTS[$L];
    decho("$L = $lp pts", "score");
    decho("BOARD[$r][$c] = ".$BOARD[$r][$c], "score");
    decho("WILDS[$r][$c] = ".$wilds[$r][$c], "score");
    decho("TILES[$r][$c] = ".$TILES[$r][$c], "score");
    
    
    if($TILES[$r][$c]) //if the space is a tile already played, just count it's points
      $score += $lp;
    else 
    {
      if($wilds[$r][$c])
        $score += 0;
      else if($BOARD[$r][$c] == "DL")
        $score += ($lp * 2);
      else if($BOARD[$r][$c] == "TL")
        $score += ($lp * 3);
      else
        $score += $lp;
        
      if($BOARD[$r][$c] == "DW")
        $mult *= 2;
      if($BOARD[$r][$c] == "TW")
        $mult *= 3;
    }
    
    decho("SCORE = $score", "score");    
    
    if($dir == "V") $r++;
    if($dir == "H") $c++;
  }
  
  decho("mult = $mult", "score");
  
  $score *= $mult;
  if($used == 7) $score += 35;
  
  decho("used = $used", "score");
  
  decho("$word = $score pts", "score");
  

  return $score;

  
  
}


function getWordsInPlay()
{
	global $TILES;
	
	$WORDSinPLAY = array();
	for($r = 1; $r <= 15; $r++)
	{
	  $row = "";
	  for($c = 1; $c <= 15; $c++)
	  {
	    $L = $TILES[$r][$c];
	    $row .= ($L)? $L:" ";
	  }
	  
	  $words = explode(" ", $row);
	  
	  //echo "$br ROW $r = $row ";
	  foreach($words as $w)
	  {
	    if($w && $w != " " && strlen($w) > 1)
	    {
	      $C = strpos($row, $w) + 1;
	      //echo "COL = $C,";
	      $WORDSinPLAY["H"][$r][$C] = $w;
	    }
	  }
	  
	}
	
	for($c = 1; $c <= 15; $c++)
	{
	  $col = "";
	  for($r = 1; $r <= 15; $r++)
	  {
	    $L = $TILES[$r][$c];
	    $col .= ($L)? $L:" ";
	  }
	  
	  $words = explode(" ", $col);
	  
	  //echo "$br COL $c = $col ";
	  foreach($words as $w)
	  {
	    if($w && $w != " " && strlen($w) > 1)
	    {
	      $R = strpos($col, $w) + 1;
	      //echo "ROW = $R,";
	      $WORDSinPLAY["V"][$R][$c] = $w;
	    }
	  }
	  
	}
	
	return $WORDSinPLAY; 

}

function getWords($letters, $LIKES)
{
	global $ALPHACOUNT, $combos;
	$br = "<br />";
	//echo $letters.$br;
	
	$split = str_split($letters);
	
	
	//sort the array alphabetically
	sort($split);
	if(in_array("?", $split))
	{
	  $wild = 1;
	  //take the ? out
	  array_shift($split);
	}
	//do it again in case of two wilds
	if(in_array("?", $split))
	  array_shift($split);

	//if(in_array("?", $split))
	//create array that shows the count of each letter represented, ex: A=>2, B=>1, etc.
	$lc = array_count_values($split);
	//create array of the letters (each letter only listed once)
	$keys = array_keys($lc);
	//create array of just the values
	$counts = array_values($lc);
	//copy the zeroed out whole alphabet
	$acount = $ALPHACOUNT;
	//apply the values to the appropriate letters in the whole alphabet array
	foreach($lc as $l=>$c)
	{
	  $acount[$l]=$c;
	}
	
	//print_r($acount);
	
	//decho("WILD = $wild");
	
	
	$qcount = $acount;
	foreach($qcount as $a=>$q)
	{
	   if($q) 
	   	$qcount[$a] = "_";	
	}
	//print_r($qcount);
	$like = "'".implode($qcount)."'";
	
	if($wild)
	{
	  $orlikes = array();
	  $extracombos = array();
	  
	  foreach($qcount as $a=>$q)
	  {
	    $orcount = $qcount;
	    if(!$q)
	    {
	      $orcount[$a] = "_";
	      $extracombos[] = $orcount;
	      $orlikes[] = implode($orcount);
	    }
	  }
	  
	  //print_r($extracombos);
	  
	}
	
	
	
	//print_r($orlikes);
	  
	if($wild)
	{
	  $like .= " OR code LIKE '".implode("' OR code LIKE '", $orlikes)."'"; 

	}
	
	if($LIKES && count($LIKES))
	  $wlikes = "AND (".implode(" OR ", $LIKES).")";
	
	
	
	//decho("QCOUNT = $like");
	//echo "<br /><br />SELECT * FROM words WHERE (code LIKE $like) $wlikes";
	$q = mysql_query("SELECT * FROM words WHERE (code LIKE $like) $wlikes");
	decho("Number of Words: ".mysql_num_rows($q));
	
	$words = array();
	while($w = mysql_fetch_object($q))
	{
	  $ws = str_split($w->word);
	  
	  if(wordInLetters($w->word, $letters) && !in_array($w->word, $words))
	  {
	    $words[] = $w->word;
	  }
	   
	}

	
	
	return $words;
	
}

function wordInLetters($word, $letters)
{
  $WORD = str_split($word);
  $LETTERS = str_split($letters);
  
  foreach($WORD as $wl)
  {
    if(in_array($wl, $LETTERS))
       array_splice($LETTERS, array_search($wl, $LETTERS), 1);
    else if(in_array("?", $LETTERS))
       array_splice($LETTERS, array_search("?", $LETTERS), 1);
    else
      return 0;
  }
  
  return 1;
}


?>