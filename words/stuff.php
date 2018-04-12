<?php
include("../php/common.php");

$POINTS = array('A'=>1,'B'=>4,'C'=>4,'D'=>2,'E'=>1,'F'=>4,'G'=>3,'H'=>3,'I'=>1,'J'=>10,'K'=>5,'L'=>2,'M'=>4,'N'=>2,'O'=>1,'P'=>4,'Q'=>10,'R'=>1,'S'=>1,'T'=>1,'U'=>2,'V'=>4,'W'=>4,'X'=>8,'Y'=>3,'Z'=>10);

$q = mysql_query("SELECT * FROM words WHERE 1 ORDER BY points DESC");

while($w = mysql_fetch_object($q))
{
  $word = $w->word;
  $points = $w->points;
  
  echo "$word = $points <br />";
  
  //mysql_query("UPDATE words SET points = '$points' WHERE word = '$word'"); 
 
}

function getPoints($word)
{
  global $POINTS;
  
  $points = 0;
  $letters = str_split($word);
  foreach($letters as $l)
    $points += $POINTS[$l];
    
  return $points;
}

?>