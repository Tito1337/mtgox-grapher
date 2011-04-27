<?php
header("Access-Control-Allow-Origin: *");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Actuellement sur Mt. Gox</title>
<script language="javascript" type="text/javascript" src="flot/jquery.js"></script> 
<script language="javascript" type="text/javascript" src="flot/jquery.flot.js"></script>
<style>
body {
    font-family: sans-serif;
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
<?php
/* DEPTH **********************************************************************/
/*$depth = json_decode(file_get_contents("http://mtgox.com/code/data/getDepth.php"), true);

function cmp($a, $b) {
	if($a[0] == $b[0]) return 0;
	return ($a[0] < $b[0]) ? -1 : 1;
}
usort($depth[asks], "cmp");
usort($depth[bids], "cmp");
rsort($depth[bids]);
*/
?>
<h2>Actuellement sur Mt. Gox</h2>
<ul>
    <li>Le bitcoin le moins cher se vend <span id="ask" style="font-weight: bold;"></span></li>
    <li>L'acheteur le plus généreux propose <span id="bid" style="font-weight: bold;"></span></li>
</ul>
<div id="depth" style="width:800px;height:350px;"></div>
<div style="font-weight: bold; width: 800px; text-align: center;">10 propositions d'achat et 10 propositions de vente</div>

<h2>Derniers trades</h2>
<div id="trades" style="width:800px;height:350px;"></div> 

<script type="text/javascript"> 
function showTooltip(x, y, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({top: y, left: x + 10}).appendTo("body").show();
}

function plot() {
    /* OPTIONS FLOT */
    var time = new Date().getTime();
    tradeOptions = {
        series: {
            lines: { show: true },
            points: { show: true },
        },

        xaxis: {
            mode: "time",
            show: true,
            max: time,
        },
        
        yaxis: {
            position: "right",
        },
        
        selection: {
            mode: "x"
        },
        
        grid: {
            hoverable: true,
            clickable: true
        },
    }
    
    depthOptions = {
        series: {
            lines: { show: true, fill: true },
            points: { show: true },
        },
        
        yaxis: {
            transform: function (v) {
                if(v<1) return v;
                return Math.log(v);
            },
            inverseTransform: function (v) {
                if(v<1) return v;
                return Math.exp(v);
            }
        },    
    
        selection: {
            mode: "x"
        },
        
        grid: {
            hoverable: true,
            clickable: true
        },
        
        legend: {
            position: 'sw',
        }
    }

    /* DONNÉES */
    var trades = [];
    $.get('getTrades.php', function(data) {
        var data = eval(data);
        var timeLimit = (time/1000)-7200;
        var minimum = 10000;
        var maximum = 0;
        for(var id in data) {
            if(data[id].date > timeLimit) {
                if(data[id].price < minimum) minimum = data[id].price;
                if(data[id].price > maximum) maximum = data[id].price;
                var point = [(data[id].date*1000), (data[id].price)];
                trades.push(point);
            }
        }
        $.plot($("#trades"), [{data: trades}], tradeOptions);
        $("#minmax").html("Plot des deux dernières heures (maximum: "+maximum+", minimum: "+minimum+")");
    });
    
    var asks = [];
    var bids = [];
    $.get('getDepth.php', function(data) {
        data = eval('(' + data + ')');
        var i = 0;
        for(i=0; i<10; i++) {
            asks.push(data.asks[i]);
            bids.push(data.bids[i]); 
        }
        $.plot($("#depth"), [{data: asks, label: "Vendeurs"}, {data: bids, label: "Acheteurs"}], depthOptions);
        $("#ask").html(data.asks[0][0]+" USD");
        $("#bid").html(data.bids[0][0]+" USD");
    });

    /* INFOBULLE */
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
    
    $("#depth").bind("plothover", function(event, pos, item) {
        $("#x").text(pos.x.toFixed(2));
        $("#y").text(pos.y.toFixed(2));

        if(item) {
	        if (previousPoint != item.datapoint) {
           		previousPoint = item.datapoint;
                $("#tooltip").remove();
	            var prix = item.datapoint[0];
                var volume = item.datapoint[1];
           		showTooltip(item.pageX, item.pageY, volume+" BTC à <strong>"+prix+" USD</strong>");
	        }
        } else {
	        $("#tooltip").remove();
	        previousPoint = null;            
        }
    }
    );
}

function refresh() {
    plot();
    setTimeout(refresh, 10000);
}

$(function () {
    refresh();
});
</script>

<div style="font-weight: bold; width: 800px; text-align: center;" id="minmax"></div>
</body>
</html>
