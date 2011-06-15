/* Plots options **************************************************************/
tradeOptions = {
    xaxis: {
        mode: "time",
        show: true,
        position: "top",
    },
    
    yaxes: [{
        show: true,
        position: "right",
        labelWidth: 40,
    },{
        position: "left",
        labelWidth: 40,
        reserveSpace: true,
    }],
    
    grid: {
        hoverable: true,
        clickable: true
    },
};

overviewOptions = {
    series: {
        lines: { show: true, lineWidth: 1 },
        shadowSize: 1,
    },
    
    xaxis: {
        show: true,
        mode: "time",
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
};
/* Plots options **************************************************************/


/* Global vars */
var timeOffset = new Date().getTimezoneOffset()*60000;
var maximum = 9999999999999999;
var minimum = 5400000;
var isRedrawing = false;
var loopLaunched = false;

function getTrades(minimum, maximum) {
    var output = [];
    for(var i=0; i<trades.length; i++) {
        date = (trades[i].date*1000)-timeOffset;
        if((date >= minimum) && (date <= maximum)) {
            point = [(date), (trades[i].price)];
            output.push(point);
        }
    }
    return output;
}

function cumulAmounts(trades) {
    var cumul = 0.0;
    for(var i=0; i<trades.length; i++) cumul += trades[i][1];
    return cumul;
}

function getVolumes(minimum, maximum) {    
    output = [];
    width = (maximum-minimum)/(volumeDivisions*1.0);
    
    for(base = minimum; base<=maximum; base+=width) {
        point = [(base), (cumulAmounts(getTrades(base, base+width)))];
        if(point[1] > 0.0) output.push(point);
    }
    
    return output;
}

function plot() {
    if(!loopLaunched) {
        loopLaunched=true;
        plotLoop();
        return;
    }
    localTime = new Date().getTime()-timeOffset;

    if(maximum >= 9999999999999999) {
        showMaximum = localTime;
        showMinimum = showMaximum - minimum;
    }
    
    plotData = [];
    
    if(volumeDivisions) {
        showVolumes = getVolumes(showMinimum, showMaximum);
        plotData.push({data:showVolumes, color:1, bars:{show: true, barWidth:((showMaximum-showMinimum)/volumeDivisions)}, yaxis:2});
    }
    
    showTrades = getTrades(showMinimum, showMaximum);
    plotData.push({data:showTrades, color:0, lines:{show:true}, points:{show:true}, yaxis:1});
    
    allTrades = getTrades(0, 9999999999999999);
    
    tradeOptions.xaxis.max = overviewOptions.xaxis.max = showMaximum;
    tradeOptions.xaxis.min = showMinimum;
    overviewOptions.xaxis.min = localTime-(48*60*60*1000);
    $.plot($("#trades"), plotData, tradeOptions);
    overview = $.plot($("#overview"), [{data: allTrades}], overviewOptions);
    isRedrawing = true;
    overview.setSelection({ xaxis: { from: showMinimum, to: showMaximum } });
    isRedrawing = false;
}

function showTooltip(x, y, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({top: y, left: x + 10}).appendTo("body").show();
}

function binds() {
    $("#trades").bind("plothover", function(event, pos, item) {
        $("#x").text(pos.x.toFixed(2));
        $("#y").text(pos.y.toFixed(2));

        if(item) {
            if (previousPoint != item.datapoint) {
           		previousPoint = item.datapoint;
                $("#tooltip").remove();
                var reward = item.datapoint[1];

	            d = new Date(item.datapoint[0]);
           		showTooltip(item.pageX, item.pageY, '' + d.getUTCHours() + ':' + d.getUTCMinutes() + ':' + d.getSeconds() + ': <strong>' + reward + '</strong>');
            }
        } else {
            $("#tooltip").remove();
            previousPoint = null;            
        }
    });
    
    $("#overview").bind("plotselected", function (event, ranges) {
        if(isRedrawing) return;
        minimum = ranges.xaxis.from;
        maximum = ranges.xaxis.to;
        all = getTrades(0, 9999999999999999);
        if(maximum >= all[all.length-1][0]) {
            minimum = maximum-minimum;
            maximum = 9999999999999999;
        }
        plot();
    });

}

function plotLoop() {
    if(maximum >= 9999999999999999) plot();
    setTimeout(plotLoop, 10000);
}
