<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Actuellement sur Mt. Gox</title>
<link href="favicon.ico" rel="icon" type="image/x-icon" />
<script language="javascript" type="text/javascript" src="js/jquery.js"></script> 
<script language="javascript" type="text/javascript" src="js/jquery.flot.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.flot.selection.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.flot.stack.js"></script> 
<style>
body {
    font-family: sans-serif;
    text-align: center;
    width: 800px;
    margin: 10px auto 10px auto;
}
td, th {
    padding: 3px;
}
div#tooltip {
    position: absolute;
    display: none;
    border: 1px solid #fdd;
    padding: 2px;
    background-color: #fee;
    opacity: 0.80;
}
</style>
</head>
<body>
<h2>Actuellement sur Mt. Gox</h2>
<h2>Derniers trades</h2>
<div id="trades" style="width: 100%; height:450px;">
    <img src="img/loading.gif" width="31" height="31" alt="Loading..." /><br /><br />
    Chargement des anciennes données...<br />
    <small>(Peut prendre une dizaine de secondes)</small>
</div>
<div id="overview" style="width: 100%; height: 120px;"></div>

<pre id="output"></div>

<script type="text/javascript"> 
/* Global vars ****************************************************************/
var time = new Date().getTime();
var decalage = new Date().getTimezoneOffset()*60000;
var trades = [];
var websocket;
var overview;
var maximum = 9999999999999999;
var minimum = time-decalage-5400000;
var isRedrawing = false;

/* DEBUG **********************************************************************/
function debug(message) {
    var pre = document.createElement("p");
    pre.style.wordWrap = "break-word";
    pre.innerHTML = message;
    document.getElementById("output").appendChild(pre);
}

/* PLOT ***********************************************************************/
function getTrades(minimum, maximum) {
    var trading = [];
    for(var i=0; i<trades.length; i++) {
        date = (trades[i].date*1000)-decalage;
        if((date >= minimum) && (date <= maximum)) {
            point = [(date), (trades[i].price)];
            trading.push(point);
        }
    }
    return trading;
}

function cumulAmounts(trades) {
    var cumul = 0.0;
    if(trades.length != 0) {
        for(var i=0; i<trades.length; i++)
            cumul += trades[i][1];
    }
    return cumul;
}

function getVolumes(minimum, maximum) {    
    volumes = [];
    
    num = 40.0;
    maximum = realMax(maximum);
    minimum = minimum; //Obviously
    
    width = (maximum-minimum)/num;
    
    for(toTime = maximum; toTime>=minimum; toTime-=width) {
        point = [(toTime), (cumulAmounts(getTrades(toTime-width, toTime)))];
        if(point[1] > 0.0) volumes.push(point);
    }
    return volumes;
}

function realMax(maximum) {
    if(maximum >= 9999999999999999) {
        return time-decalage;
    } else {
        return maximum;
    }
}

function plot() {
    time = new Date().getTime();

    showTrades = getTrades(minimum, maximum);
    showVolumes = getVolumes(minimum, maximum);
    overview = getTrades(0, 9999999999999999);
    
    tradeOptions = {
        xaxis: {
            mode: "time",
            show: true,
            position: "top",
            max: realMax(maximum),
        },
        
        yaxes: [{
            position: "right",
            labelWidth: 40,
        },{
            position: "left",
            labelWidth: 40,
        }],
        
        grid: {
            hoverable: true,
            clickable: true
        },
    }
    
    overviewOptions = {
        series: {
            lines: { show: true, lineWidth: 1 },
            shadowSize: 1,
        },
        
        xaxis: {
            show: true,
            mode: "time",
            max: time-decalage,
        },
        
        yaxes: [{
            show: false,
            position: "right",
            reserveSpace: true,
            labelWidth: 40,
        },{
            show: false,
            position: "left",
            reserveSpace: true,
            labelWidth: 40,
        }],
        
        selection: {
            mode: "x",
        }
    }
    
    isRedrawing = true;
    $.plot($("#trades"), [{data:showVolumes, color:1, bars:{show: true, barWidth:((minimum-realMax(maximum))/40)}, yaxis:2},
                          {data:showTrades, color:0, lines:{show:true}, points:{show:true}, yaxis:1}], 
                          tradeOptions);
    overview = $.plot($("#overview"), [{data: overview}], overviewOptions);
    overview.setSelection({ xaxis: { from: minimum, to: realMax(maximum) } });
    isRedrawing = false;
}

$("#overview").bind("plotselected", function (event, ranges) {
    if(isRedrawing) return;
    minimum = ranges.xaxis.from;
    maximum = ranges.xaxis.to;
    all = getTrades(0, 9999999999999999);
    if(maximum >= all[all.length-1][0]) maximum = 9999999999999999;
    plot();
});

/* ToolTip ********************************************************************/
function showTooltip(x, y, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({top: y, left: x + 10}).appendTo("body").show();
}

$("#trades").bind("plothover", function(event, pos, item) {
    $("#x").text(pos.x.toFixed(2));
    $("#y").text(pos.y.toFixed(2));

    if(item) {
        if (previousPoint != item.datapoint) {
       		previousPoint = item.datapoint;
            $("#tooltip").remove();
            var reward = item.datapoint[1];

	        d = new Date(item.datapoint[0]);
       		showTooltip(item.pageX, item.pageY, '' + d.getUTCHours() + ':' + (d.getUTCMinutes()) + ':' + d.getSeconds() + ': <strong>' + reward + ' USD</strong>');
        }
    } else {
        $("#tooltip").remove();
        previousPoint = null;            
    }
}
);


/* WebSocket ******************************************************************/
function onOpen(evt) {
    websocket.send('{"op":"unsubscribe","channel":"d5f06780-30a8-4a48-a2f8-7ed181b4a13f"}');
    websocket.send('{"op":"unsubscribe","channel":"24e67e0d-1cad-4cc0-9e7a-f8523ef460fe"}');
}

function onMessage(message) {
	var m = JSON.parse(message.data);
	if((m.op=="private") && (m.private == "trade")) {
		trades.push(m.trade);
		plot();
	}

    //debug(message.data);
}

function startWebsocket() {
    websocket = new WebSocket("ws://websocket.mtgox.com/mtgox");
    websocket.onopen = function(evt) { onOpen(evt); };
    websocket.onclose = function(evt) { alert("Connexion perdue"); };
    websocket.onmessage = function(evt) { onMessage(evt); };
}

/* START **********************************************************************/
function refresh() {
    if(maximum >= 9999999999999999) plot();
    setTimeout(refresh, 10000);
}

$(function () {
    if ("WebSocket" in window) {
        $.get('getTrades.php', function(data) {
            trades = eval('(' + data + ')');
            plot();
            startWebsocket();
            refresh();
        });
    } else {
        alert("Votre navigateur ne supporte pas les WebSockets. Je vous conseille Google Chrome / Chromium.\n\nCette page NE FONCTIONNERA PAS");
    }
});
</script>
</body>
</html>
