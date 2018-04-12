<?php
session_start();
include("connection.php");
include("../php/common.php");
include("common.php");
if($_GET['action'] == "reset")
{
  session_destroy();
  header("Location: /words/wwf.php");
}

$UID = $_GET['uid'];
if(!$UID)
{
  $UID = uniqid();
}

$debug = $_GET['debug'];
$br = "<br />";


for($r = 1; $r <= 15; $r++)
{
  $TILES[$r] = array("");
  for($c = 1; $c <= 15; $c++)
  {
    $TILES[$r][$c] = $_SESSION["$UID-$r-$c"];

  }
}


$MYLETTERS = "";
for($i = 1; $i <= 7; $i++)
{
 $MYLETTERS .= $_SESSION["$UID-ml$i"]; 
}

$letterList = str_split($MYLETTERS);
?>
<!DOCTYPE html>
<head>
<title>My Words With Friends Solver</title>
<meta content="en-us" http-equiv="Content-Language" />
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"> 
<style type="text/css">
body{font-family:"Courier New";}
.word{float:left;padding:2px;margin:3px;font-size:10pt;cursor:pointer;}
.grid{}
.grid td{width:20px;}
.grid input{width:20px;text-align: center;font-size:12pt;}
.ml{width:20px;text-align: center;font-size:12pt;margin:1px;}
.highlight{background-color: yellow;}
.TW{background-color:#FFCC00;}
.TL{background-color:#33CC33;}
.DW{background-color:#FF8080;}
.DL{background-color:#5CD6FF;}
.MD{background-color:#853385;}
.PL{background-color:#FFE0A3;}
#demodesc{background-color:white;border:2px black solid;width:60%;position:fixed;top:200px;left:20%;text-align:left;padding-top:5px;font-size:11pt;z-index: 2;}
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script type="text/javascript">
var rows = 14;
var cols = 14;
var BOARD = new Array();
for(var i = 0; i < 16; i++)
  BOARD[i] = new Array();

<?php
for($r = 1; $r <= 15; $r++)
 for($c = 1; $c <= 15; $c++)
   echo "BOARD[$r][$c] = '".$TILES[$r][$c]."'; ";
?>
 
var RACK = new Array('<?php echo implode("','", $letterList); ?>'); 
function drawBoard()
{
  for(var r = 1; r <= 15; r++)
    for(var c = 1; c <= 15; c++)
      $('#'+r+'-'+c).val(BOARD[r][c]);
}
  
function checkKey(e)
{
  e = e || window.event;
  var target = e.target || e.srcElement;
  var key = e.keyCode;
  
  var id = target.id.split('-');
  var r = parseInt(id[0]);
  var c = parseInt(id[1]);
  var rc = Math.min(c + 1, 15);
  var lc = Math.max(c - 1, 1);
  var ur = Math.max(r - 1, 1);
  var dr = Math.min(r + 1, 15);
  //alert(r+" - "+c+"\n"+ur+","+dr+" - "+rc+","+lc);
  
  if(key == 37) //left
  {
    $('#'+r+'-'+lc).focus();
  }
  if(key == 38) //up
  {
    $('#'+ur+'-'+c).focus();
  }
   if(key == 39) //right
  {
    $('#'+r+'-'+rc).focus();
  }
  if(key == 40) //down
  {
    $('#'+dr+'-'+c).focus();
  }
  if(key >= 65 && key <= 90)
  {
    var v = String.fromCharCode(key);
    if(v)  $('#'+target.id).val(v);
  }
  
  //if(nc <= cols && nr <= rows)
    //$('#'+nr+'-'+nc).focus();
}

function rackKeyUp(i)
{
  var v = $('#ml'+i).val();
  if(v)  $('#ml'+i).val(v.toUpperCase());
  
  var j = i + 1;
  $('#ml'+j).focus();
}

var R, C, D, LL, WORD;
function showWord(w, r, c, d, ll)
{
  WORD = w; R = r; C = c; D = d, LL = ll;
  $(".highlight").removeClass('highlight');
  drawBoard();
  
  $('#'+w+'-'+r+'-'+c+'-'+d).addClass("highlight");

  var s = w.split('');
  for(i in s)
  {
    var L = s[i];
    $('#'+r+'-'+c).val(L);
    $('#'+r+'-'+c).addClass("highlight");
    
    if(d == "H") c++;
    if(d == "V") r++; 
      
  }
}

function clearBoard()
{
  $(".highlight").removeClass('highlight');
  drawBoard();
}

function playWord()
{
   $(".highlight").removeClass('highlight');
   var form = document.forms['wordfinder'];
   var ll = LL.split('');
   for(var i = 1; i <= 7; i++)
   {
     var L = ll[i-1];
     if(L)
       $('#ml'+i).val(L);
     else
       $('#ml'+i).val('');
   }
}

function findWords()
{
  document.forms['wordfinder'].submit();
}



</script>
</head>
<body>
<div id="demodesc">
 <div style="padding:10px;">
 <h2>What you are looking at...</h2>
 <p>This is a Words With Friends solver program I made for fun</p>
 <p>You can enter letters onto the board to simulate a board with words already on it, or leave it blank.</p>
 <p>Then enter the letters you have into the row at the bottom.</p>
 <p>Click "Find Words" and it will list playable words with the highest points at the top.</p>
 <p>Click on any word to see where on the board it plays.</p>
 <p>I also made this program able to receive pictures of the board from a phone and return a set of pictures of the top words to play.</p>
 <p style="text-align:center"><button onclick="$('#demodesc').hide();">Hide Description</button></p>
 </div>
 </div>

<div style="position:fixed;top:10px;right:10px;background-color:white;">
<a href="http://www.dwwd.me"><img style="width:200px;" src="/images/DWWDlogoHorz.png" /></a>
</div>
<div style="position:fixed;top:0px;left:0px;background-color:white;">
<center>
<form id="wordfinder" action="/words/wwfprocess.php?action=findwords&uid=<?php echo $UID; ?>&debug=<?php echo $debug; ?>" method="post">
<table class="grid">
<?php

  $grid = array();
  $letters = '';
  for($r = 0; $r <= 15; $r++)
  {
    echo '<tr>';
    $grid[$r] = array();
    for($c = 0; $c <= 15; $c++)
    {
      if($r == 0)
      {
        echo "<td style=\"font-size:8pt;text-align:center;\">$c</td>";
        continue;
      }
      if($c == 0)
      {
        echo "<td style=\"font-size:8pt;text-align:center;\">$r</td>";
        continue;
      }
      $val = strtoupper($_SESSION["$UID-$r-$c"]);
      
      $space = $BOARD[$r][$c];
      if(!$space) $space = "PL";
      
      echo "<td class=\"$space\"><input class=\"grid\" id=\"$r-$c\" name=\"$UID-$r-$c\" onkeyup=\"checkKey(event)\" onfocus=\"this.select();\" value=\"$val\"/></td>";
      $grid[$r][$c] = $val;
      $letters .= $val;
    }
     
    echo '</tr>';
  }
  
  echo '</table>';
  echo 'Your Letters:<br />';
  for($i = 1; $i <= 7; $i++)
  {
   echo "<input class=\"ml\" id=\"ml$i\" name=\"$UID-ml$i\" value=\"".$_SESSION["$UID-ml$i"]."\" onkeyup=\"rackKeyUp($i);\" />";
  }
?>
</form>
<button onclick="findWords();">Find Words</button><br />
<button onclick="playWord();">Play Selected Word</button><br />
<button onclick="window.location='/words/wwf.php?action=reset'">Reset Board</button>&nbsp;<button onclick="clearBoard();">Clear Board</button><br />
<button onclick="window.open('?action=reset');">Start New Game in New Window</button>

</center>
</div>
<div id="output" style="margin-left:500px;">
<b>Click a word to see where it plays.</b>
<pre style="margin:0px;">


<?php

if(file_exists("wwfwords/$UID.txt"))
  echo file_get_contents("wwfwords/$UID.txt");


?>
</pre>
</div>

</body>

</html>