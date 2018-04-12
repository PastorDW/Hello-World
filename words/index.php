<?php
include("../php/common.php");
include("connection.php");
include("common.php");
$letters = strtoupper($_GET['letters']);
$length = $_GET['len'];

?>
<!DOCTYPE html>
<head>
<title>WORDS</title>
<meta content="en-us" http-equiv="Content-Language" />
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"> 
<style type="text/css">
body{font-family:"Courier New";}
.word{float:left;padding:2px;margin:3px;font-size:10pt;}
#demodesc{background-color:white;border:2px black solid;width:60%;position:fixed;top:200px;left:20%;text-align:left;padding-top:5px;font-size:11pt;z-index: 2;}
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script type="text/javascript">

</script>
</head>
<body>
<div id="demodesc">
 <div style="padding:10px;">
 <h2>What you are looking at...</h2>
 <p>This is a Words With Friends solver porgram I made for fun</p>
 <p>You can enter letters onto the board to simulate a board with words already on it, or leave it blank.</p>
 <p>Then enter the letters you have into the row at the bottom.</p>
 <p>Click "Find Words" and it will list playable words with the highest points at the top.</p>
 <p>Click on any word to see where on the board it plays.</p>
 <p>I also made this program able to receive pictures of the board from a phone and return a set of pictures of the top words to play.</p>
 <p style="text-align:center"><button onclick="$('#demodesc').hide();">Hide Description</button></p>
 </div>
 </div>

<div>
<center>
<a href="/words/grid.php">grid</a>
<form action="/words" method="get">
Letters: <input name="letters" value="<?php echo $letters; ?>" /><br />
Length:&nbsp; <input name="len" value="<?php echo $length; ?>" /><br />
<button>Submit</button>
</form>
</center>
</div>
<?php
$br = "<br />";
$time0 = strtotime("now");
//split the letters into an array
$split = str_split($letters);
//sort the array alphabetically
sort($split);
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

$qcount = $acount;
foreach($qcount as $a=>$q)
  if($q) $qcount[$a] = "_";
  
$like = implode($qcount);

echo "Qcount: ".$like.$br;

$combos = array();
//recursively find all the combinations of the letters
countdown($counts, 0);

$combos = getAlphaCombos($combos, $keys);
echo "Number of Combos: ".count($combos).$br.$br;

//$WORDS = getWords($combos, $keys);
$q = mysql_query("SELECT * FROM words WHERE len = '$length' AND code LIKE '$like'");
echo $br."Number of Words: ".mysql_num_rows($q).$br.$br;


while($w = mysql_fetch_object($q))
{
  if(in_array($w->code, $combos))
    echo '<p class="word">'.$w->word.'</p>';
}


$time4 = strtotime("now");
echo '<p style="clear:both"><br /><br />Total Time: '.($time4 - $time0).'</p>';

//printWords($WORDS, $length);

  











/*

	$file = fopen("words.txt", "r");
	
	while(!feof($file))
	{
	    $word = trim(strtoupper(fgets($file)));
	    $letters = array_count_values(str_split($word));
	    //print_r($letters);
	    //echo $br;
	    //for each letter in the alphabet
      $count = $ALPHACOUNT;
      foreach($letters as $l=>$c)
        $count[$l]=$c;
	    
	    $code = implode($count);
	    
	    $len = strlen($word);
	    echo "$code = $word ($len) $br";
	    mysql_query("INSERT INTO words (code, word, len)VALUES('$code', '$word', '$len')");
	    
	    
	}
  fclose($file);


*/






?>


</body>

</html>