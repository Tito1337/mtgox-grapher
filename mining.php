<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Calcul de rentabilité du mining</title>
<link href="favicon.ico" rel="icon" type="image/x-icon" />
<script language="javascript" type="text/javascript" src="flot/jquery.js"></script> 
<script language="javascript" type="text/javascript" src="flot/jquery.flot.js"></script>
<style>
body {
    font-family: sans-serif;
}
td, th {
    padding: 3px;
}
</style>
</head>
<body>
<h2>Statistiques du réseau</h2>
<?php
$bcperblock = file_get_contents("http://blockexplorer.com/q/bcperblock");
$difficulty = file_get_contents("http://blockexplorer.com/q/getdifficulty");
$hashestowin = file_get_contents("http://blockexplorer.com/q/hashestowin");
$nextdifficulty = file_get_contents("http://blockexplorer.com/q/estimate");
?>
<ul>
<li><strong>Difficulté : </strong> <?php echo $difficulty; ?></li>
<li><strong>Hashes nécessaires pour gagner <?php echo $bcperblock; ?> BTC (en moyenne) : </strong> <?php echo $hashestowin; ?></li>
</ul>

<h2>Calculette</h2>
<script type="text/javascript">
function getHumanTime(sec) {
  var min = 0;
  var hour = 0;
  var day = 0;
  if(sec >= 60) {
    min = Math.floor(sec/60);
    sec -= min*60;
  }
  if(min >= 60) {
    hour = Math.floor(min/60);
    min -= hour * 60;
  }
  if(hour >= 24) {
    day = Math.floor(hour/24);
    hour -= day*24;
  }
  
  output = "";
  if(day > 0) {
    output += day + " day";
    if(day != 1)
      output += "s";
    output += ", ";
  }
 
  if(output || hour > 0) {
    output += hour + " hour";
    if(hour != 1)
      output += "s";
    output += ", ";
  }
 
  if(output || min > 0) {
    output += min + " minute";
    if(min != 1)
      output += "s";
    output += ", ";
  }
 
  output = output.replace(/,\ $/,'');
  return output;  
}

function calculate(id, difficulty) {
    var target = 0x00000000ffff0000000000000000000000000000000000000000000000000000 / difficulty;
    var average = Math.pow(2,256)/target;
    
    var hash = 1000*document.getElementById("khash").value;
    var time = average/hash;
    
    $("#averageTime"+id).html(getHumanTime(time)+" ("+Math.floor(time)+"s)");
    var perSecond = <?php echo $bcperblock; ?>/time;
    $("#perWeek"+id).html((Math.round(perSecond*3600*24*7*100000000)/100000000)+" BTC/sem");
    $("#perHour"+id).html((Math.round(perSecond*3600*100000000)/100000000)+" BTC/h");
}
</script>
<strong>Votre puissance : </strong> <input type="text" id="khash" />khash/s &nbsp;<input type="submit" value="Calculer" onclick="calculate(0, <?php echo $difficulty; ?>); calculate(1, <?php echo $nextdifficulty; ?>);" />

<h3>Aujourd'hui</h3>
<ul>
<li><strong>Temps moyen pour gagner <?php echo $bcperblock; ?>BTC : </strong> <span id="averageTime0"></span></li>
<li><strong>Gain par semaine : </strong> <span id="perWeek0"></span></li>
<li><strong>Gain par heure : </strong> <span id="perHour0"></span></li>
</ul>

<h3>Après la prochaine rectification de la difficulté</h3>
<ul>
<li><strong>Temps moyen pour gagner <?php echo $bcperblock; ?>BTC : </strong> <span id="averageTime1"></span></li>
<li><strong>Gain par semaine : </strong> <span id="perWeek1"></span></li>
<li><strong>Gain par heure : </strong> <span id="perHour1"></span></li>
</ul>
</body>
</html>
