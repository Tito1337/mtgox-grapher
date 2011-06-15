<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en-US" xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Currently on Mt. Gox</title>

<link href="img/favicon.ico" rel="icon" type="image/x-icon" />
<link rel="stylesheet" href="style.css" type="text/css" media="screen" /> 

<script language="javascript" type="text/javascript" src="jquery/jquery.js"></script> 
<script language="javascript" type="text/javascript" src="jquery/jquery.flot.js"></script>
<script language="javascript" type="text/javascript" src="jquery/jquery.flot.selection.js"></script>

<script language="javascript" type="text/javascript" src="websocket.js"></script>
<script language="javascript" type="text/javascript" src="plotter.js"></script>
</head>
<body>
<h2>Currently on Mt. Gox</h2>
<div id="trades" style="width: 100%; height:450px;">
    <img src="img/loading.gif" width="31" height="31" alt="Loading..." /><br /><br />
    Loading older trades...
</div>
<div id="overview" style="width: 100%; height: 120px;"></div>
<p style="text-align: right; margin-top: 15px; font-size: 0.8em;">By <a href="https://twitter.com/Tito1337">@Tito1337</a> and 
<a href="https://twitter.com/Tito1337">@Tycale</a> - Like our work? Buy us a beer! 1S7CbxYdfCayqfyyNsshDoqyKghbW3LAA</p>
<pre id="output"></div>
<script type="text/javascript">
/* CONFIG ********************/
var debugging = true; // true or false
var oldDataURL = 'http://bitcoin.tycale.be/getTrades.php'; // URL of old trades. Set to null to disable
var volumeDivisions = 40; // Number of volume bars. Set to null to disable
/* CONFIG ********************/

$(document).ready(function () {
    binds();
    fetchData();
});
</script>
</body>
</html>
