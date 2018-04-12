<?php
include("../php/common.php");
include("common.php");
$cols = $_GET['cols'];
$rows = $_GET['rows'];
?>
<!DOCTYPE html>
<head>
<title>GRID</title>
<meta content="en-us" http-equiv="Content-Language" />
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"> 
<style type="text/css">
body{font-family:"Courier New";}
.word{float:left;padding:2px;margin:3px;font-size:10pt;cursor:pointer;}
.grid{}
.grid td{width:20px;}
.grid input{width:20px;text-align: center;font-size:12pt;}
.highlight{background-color: yellow;}
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script type="text/javascript">
 var rows = <?php echo ($rows)? $rows:0; ?>;
  var cols = <?php echo ($cols)? $cols:0; ?>;
  
function gotoNext(r, c)
{
  var nc = c + 1;
  var nr = r;
  if(nc > cols)
  {
    nc = 1;
    nr = r + 1;
  }
  
  if(nc <= cols && nr <= rows)
    $('#'+nr+'-'+nc).focus();
}

function highlightWord(word, p)
{
  $('.highlight').removeClass('highlight');
  $('#'+word).addClass('highlight');
  
  var posits = p.split("|");
  for(i in posits)
  {
    rc = posits[i];
    $('#'+rc).addClass('highlight');
  }
}


</script>
</head>
<body>
<div>
<center>
<a href="/words/">words</a>
<form action="/words/grid.php" method="get">
<?php
if($cols && $rows)
{
  $grid = array();
  $letters = '';
  echo "<input type=\"hidden\" name=\"cols\" value=\"$cols\" />";
  echo "<input type=\"hidden\" name=\"rows\" value=\"$rows\" />";
  echo '<table class="grid">';
  for($r = 1; $r <= $rows; $r++)
  {
    echo '<tr>';
    $grid[$r] = array();
    for($c = 1; $c <= $cols; $c++)
    {
      $val = strtoupper($_GET["$r-$c"]);
      echo "<td><input class=\"grid\" id=\"$r-$c\" name=\"$r-$c\" onkeyup=\"gotoNext($r, $c);\" onfocus=\"this.select();\" value=\"$val\"/></td>";
      $grid[$r][$c] = $val;
      $letters .= $val;
    }
     
    echo '</tr>';
  }
  
  echo '</table>';
  echo 'Length: <input style="width:20px" name="len" value="'.$_GET['len'].'" /><br />';
  echo '<button>Find Words</button>';
}else
{
?>
Cols: <input name="cols" value="<?php echo $cols; ?>" /><br />
Rows: <input name="rows" value="<?php echo $rows; ?>" /><br />
<button>Draw Grid</button>
<?php
}

?>

</form>
<button onclick="window.location='/words/grid.php'">Reset</button>
</center>
</div>
<?php
$br = "<br />";
$length = $_GET['len'];
$time0 = strtotime("now");
$split = str_split($letters);
sort($split);
$lc = array_count_values($split);
$keys = array_keys($lc);
$counts = array_values($lc);
$acount = $ALPHACOUNT;
foreach($lc as $l=>$c)
{
  $acount[$l]=$c;
}

$qcount = $acount;
foreach($qcount as $a=>$q)
  if($q) $qcount[$a] = "_";
  
$like = implode($qcount);

echo "Qcount: ".$like.$br;

$combos = array();

countdown($counts, 0);

$combos = getAlphaCombos($combos, $keys);
echo "Number of Combos: ".count($combos).$br.$br;

//$WORDS = getWords($combos, $keys);


$q = mysql_query("SELECT * FROM words WHERE len = '$length' AND code LIKE '$like'");
echo $br."Number of Words: ".mysql_num_rows($q).$br.$br;

$count = 0;
while($w = mysql_fetch_object($q))
{
  //echo "##".$w->word."##".$br;
  if(in_array($w->code, $combos) && checkGrid($w->word))
  {
    $posits = implode("|", $WORD_POSITS);
    $word = $w->word;
    echo "<p class=\"word\" id=\"$word\" onclick=\"highlightWord('$word', '$posits');\">$word</p>";
    $count++;
  }
  //else
    //echo '<p class="word" style="color:red;">'.$w->word.'</p>';
}


$time4 = strtotime("now");
echo '<p style="clear:both"><br /><br />Grid Words: '.$count.'<br />Total Time: '.($time4 - $time0).'</p>';


//echo checkGrid('FOUND').$br.$br;
//print_r($WORD_POSITS);
 

function checkGrid($W)
{
  global $grid, $cols, $rows, $used;
  
  $split = str_split($W);
  //print_r($split);
  $fl = findLetter($split[0]);
  
  if($fl)
	  foreach($fl as $l)
	  {
	    $used = array();
	    if(findNextLetter($l, $split, 1, $used)) return 1;
	  }
  
  return 0;
  
} 

$WORD_POSITS = array(); 
 
function findNextLetter($start, $WORD, $i, $used)
{
   global $WORD_POSITS;
   //echo "<br /><br />$i. $start ";
   
   //if looking at a position that has already been looked at, then we are reusing letters and thus the word is not found  
   if($i > 1 && in_array($start, $used)) 
   {
     //echo "<br />$start is USED";
     return 0;
   }

   
  //if looking past the end of the word, return true
   if($i >= count($WORD))
   {
     //echo "<br />ENDOFWORD";
     $used[] = $start;
     $WORD_POSITS = $used;
     return 1;
   }   
   
   //echo "<br />LETTER: ".$WORD[$i];
   $adj = checkAdjacents($start, $WORD[$i]);
   //echo "<br />ADJ: ";
   //print_r($adj);
 
   //if the letter is not found adjacent, return false
   if(!count($adj))
   { 
     //echo "<br />NO ADJ";
     return 0; 
   }
   
   //if made it to this point, then using this letter position
   if($start)
     $used[] = $start;
     
   //echo "<br />USED: "; print_r($used);
  
   
  
   foreach($adj as $a)
   {
     if(findNextLetter($a, $WORD, $i + 1, $used))
       return 1;
   }
   
   //if it didn't get returned, then it didn't find the next letter - return false  
   return 0;
   
   
 }

//print_r(checkAdjacents("0-0", "A"));
//check the grids adjacent to $x for the letter $L
//return an array of the adjacent squares that contain the letter
function checkAdjacents($x, $L)
{
  global $grid, $rows, $cols;
  
  $p = explode("-", $x);
  $R = $p[0];
  $C = $p[1];
  //echo "$R - $C<br />";
  $ret = array();
  for($r = 1; $r <= $rows; $r++)
    for($c = 1; $c <= $cols; $c++)
    {
      $HD = abs($C - $c);
      $VD = abs($R - $r);
      //echo "$r - $c, $HD - $VD<br />";
      if($HD <= 1 && $VD <= 1 && $grid[$r][$c] == $L)
        $ret[] = "$r-$c";
    }

  return $ret;
  
}


function findLetter($L)
{
  global $grid, $cols, $rows;
  $ret = array();
  
  for($r = 1; $r <= $rows; $r++)
    for($c = 1; $c <= $cols; $c++)
    {
      if($grid[$r][$c] == $L)
        $ret[] = "$r-$c";
    }
    
  if(count($ret))
    return $ret;
  return 0;
}



?>


</body>

</html>