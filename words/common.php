<?php

$BOARD = array();
for($i = 1; $i <= 15; $i++)
  $BOARD[$i] = array("");
  
$BOARD[1][4] = $BOARD[1][12] = $BOARD[4][1] = $BOARD[4][15] = $BOARD[12][1] = $BOARD[12][15] = $BOARD[15][4] = $BOARD[15][12] = "TW"; 

$BOARD[1][7] = $BOARD[1][9] = $BOARD[4][4] = $BOARD[4][12] = $BOARD[6][6] = $BOARD[6][10] = $BOARD[7][1] = $BOARD[7][15] = $BOARD[9][1] = $BOARD[9][15] = $BOARD[10][6] = $BOARD[10][10] = $BOARD[12][4] = $BOARD[12][12] = $BOARD[15][7] = $BOARD[15][9] = "TL";

$BOARD[2][6] = $BOARD[2][10] = $BOARD[4][8] = $BOARD[6][2] = $BOARD[6][14] = $BOARD[8][4] = $BOARD[8][12] = $BOARD[10][2] = $BOARD[10][14] = $BOARD[12][8] = $BOARD[14][6] = $BOARD[14][10] = "DW";

$BOARD[2][3] = $BOARD[2][13] = $BOARD[3][2] = $BOARD[3][5] = $BOARD[3][11] = $BOARD[3][14] = $BOARD[5][3] = $BOARD[5][7] = $BOARD[5][9] = $BOARD[5][13] = $BOARD[7][5] = $BOARD[7][11] = $BOARD[9][5] = $BOARD[9][11] = $BOARD[11][3] = $BOARD[11][7] = $BOARD[11][9] = $BOARD[11][13] = $BOARD[13][2] = $BOARD[13][5] = $BOARD[13][11] = $BOARD[13][14] = $BOARD[14][3] = $BOARD[14][13] = "DL";

$BOARD[8][8] = "MD";

$POINTS = array('A'=>1,'B'=>4,'C'=>4,'D'=>2,'E'=>1,'F'=>4,'G'=>3,'H'=>3,'I'=>1,'J'=>10,'K'=>5,'L'=>2,'M'=>4,'N'=>2,'O'=>1,'P'=>4,'Q'=>10,'R'=>1,'S'=>1,'T'=>1,'U'=>2,'V'=>5,'W'=>4,'X'=>8,'Y'=>3,'Z'=>10);


$TILES = array();
$ROWTILECOUNT = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
$COLTILECOUNT = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

$BLANKS = array("BL", "TW", "DW", "TL", "DL");

function countdown($a, $i)
{	
	global $combos;
	global $length;
	if($i == count($a)) 
	{
	  if(!$length || array_sum($a) == $length)
	    $combos[] = $a;
	  return;
	}

	for($d = $a[$i]; $d >= 0; $d--)
	{
	  $a[$i] = $d;
	  
	  countdown($a, $i+1);
	  	  
	}


}

function printWords($WORDS, $len)
{

	$min = 3; 
	$max = 11;
	if($len)
	{
	 $min = $_GET['len'];
	 $max = $min + 1;
	}
	 
	for($i = $min; $i < $max; $i++)
	{
	  sort($WORDS[$i]);
	  if(count($WORDS[$i])) echo "<p style=\"clear:both;\">----- $i -----</p>";
	  for($j = 0; $j < count($WORDS[$i]); $j++)
	    echo '<p class="word">'.$WORDS[$i][$j].'</p>';
	}
}


function getAlphaCombos($combos, $keys)
{
  global $ALPHACOUNT;
	
	$aCombos = array();	
	foreach($combos as $c)
	{	  
	  //echo implode($c).$br;
	  //$code = empty alphabet array
	  $code = $ALPHACOUNT;
		$i = 0;
		foreach($keys as $k)
		{
		  $code[$k] = $c[$i++]; 
		}
		
		$aCombos[] = implode($code);
		
		

		
		
	}
	
	return $aCombos;
}

$combos = array();


?>
