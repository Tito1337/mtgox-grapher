var websocket;
var trades = [];

function debug(message) {
    if(!debugging) return;
    var pre = document.createElement("p");
    pre.style.wordWrap = "break-word";
    pre.innerHTML = message;
    document.getElementById("output").appendChild(pre);
}

function onOpen(evt) {
    // Unsubscribe from ticker & depth
    websocket.send('{"op":"unsubscribe","channel":"d5f06780-30a8-4a48-a2f8-7ed181b4a13f"}');
    websocket.send('{"op":"unsubscribe","channel":"24e67e0d-1cad-4cc0-9e7a-f8523ef460fe"}');
}

function onMessage(message) {
	var m = JSON.parse(message.data);
	if((m.op=="private") && (m.private == "trade")) {
		trades.push(m.trade);
		plot();
	}
    debug(message.data);
}

function fetchData() {   
    // Old Data
    if(oldDataURL) {
        $.get(oldDataURL, function(data) {
            oldData = eval(data);
            trades = trades.concat(oldData);
            plot();
        });
    }

    // WebSocket
    if ("WebSocket" in window) {
        websocket = new WebSocket("ws://websocket.mtgox.com/mtgox");
        websocket.onopen = function(evt) { onOpen(evt); };
        websocket.onclose = function(evt) { alert("Connexion perdue"); };
        websocket.onmessage = function(evt) { onMessage(evt); };
    } else {
        alert("Your browser doesn't support websockets so this page will not refresh or show live data.\n\nWe recommand Chromium (Google Chrome).");
    }
}
