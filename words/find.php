<?php
include("../php/common.php");
include("common.php");
include("connection.php");
$word = strtoupper($_GET['word']);
if($_GET['insert'])
{
  $code = $_GET['code'];
  $val = $_GET['val'];
  $len = strlen($word);
  
  mysql_query("INSERT INTO words (word, code, points, len)VALUES('$word', '$code', '$val', '$len')");
}

if($word)
{
  $found = mysqlFetchObject("words", "word = '$word'");
  $code = $found->code;
  $val = $found->points;
  $len = $found->len;
}

?>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"> 
		<title></title>
	</head>
	<body>
	<form action="find.php" method="get">
	Find Word: <input name="word" value="<?php echo $word; ?>"/> <button>Find</button>
	</form>
	<?php 
	if($found)
	  echo "FOUND $word<br />CODE: $code<br />SCORE: $val<br />LEN: $len";
	else if($word)
	{
	  echo "NOT FOUND";
?>
<form action="find.php" method="get">
<input type="hidden" name="insert" value="1" />
<input name="word" value="<?php echo $word; ?>"/><input style="width:200px;" name="code" value="<?php echo getCode($word); ?>" /><input name="val" value="<?php echo getScore($word); ?>" /><button>INSERT</button>
</form>

<?php

	  
	}
	
	?>
	
	
	</body>
</html>

<?php 

function getScore($word)
{
  global $POINTS;
  
  $split = str_split($word);	
  
  $p = 0;
  foreach($split as $a)
   $p += $POINTS[$a];
   
  return $p;
}

function getCode($word)
{
  global $ALPHACOUNT;
  
  $split = str_split($word);	
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
	
	
	return implode($acount);
}
