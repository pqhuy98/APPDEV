//Return value for updateChart() ====================================
var OK = 0;
var OFFLINE = 1
var NO_DATA = 2;
var NO_AUDIO = 3;

//Controller() variables ==============================================
var updateInterval = 1000;	//Normal interval for getLastest() is every 1 seconds. 
var trueInterval = 1000;	//True length of an audio chunk : 1 seconds.
var smallInterval = 500;

var dataLength = 350;	//Chart X-axis size
var req = 5;

//===================================================================
var data = [];		//data linked with chart
var chart;
var play = false;	//Play or muted ?
var online = false;	//Device online ?

//Data queues
var audio = [];
var xNew = [];
var yNew = [];
var idx = [];

var last_idx;

//==================================================================
var waitMoreData = true;	//Wait to load more data ?
var LagMode = false;// LagMode = Realtime mode


//==================================================================
function STREAM_init() {
	if (gebi("stream_chart")==null) {
		console.log("STREAM : stop.");
		return;
	} else chart = newChart("stream_chart");
	get_past_data();
	setInterval(function() {checkin()},1500); //Check-in and get # of listeners
}

function get_past_data() {
	$.get("STREAM_getpastdata.php",
		function(data) {
			lines = data.split("\n");
			for (i in lines)
				add_data(lines[i],false);//false : do not call getLastest() 
			play = false;
			var tmp = online;
			online = true;
			while (yNew.length>0)
				Update();
			online = tmp;
			//---------------------
			getLastest();
			Controller();
		}
	);
}

//Play audio and draw chart ========================================
function Controller() {		//Control the process.
	var status = Update();
	if (status==OK) {
		Online();
		//If muted, call itself in next 1 seconds.
		if (!play) setTimeout(function() {Controller()},trueInterval);
		//Else play_lastest() will call Controller().
		return;
	} else
	if (status==OFFLINE) {
		Offline();
		setTimeout(function() {Controller()},trueInterval);
	} else
	if (status==NO_DATA || status==NO_AUDIO) {
		gebi("status").innerHTML = "LOADING...";
		gebi("status").style.color = "yellow";
		setTimeout(function(){Controller()},smallInterval);
	}
}

var xtmp = 0;

function Update() {	//Re-draw chart and play sound. Return status.
	if (!online) return OFFLINE;
	if (play) {
		if (audio.length<1 || (!LagMode && waitMoreData && audio.length<req)) {
			waitMoreData = true;
			return NO_AUDIO;
		} else {
			waitMoreData = false;
			play_lastest();
		}
	}
	if (yNew.length<=0) return NO_DATA;
	var x;
	for (var i = 0; i < yNew[0].length; i++) {
		var l;
		if (i==yNew[0].length-1)
			l = xNew[0];
		else
			l = "";
		x = xtmp++;
		data.push({
			y: yNew[0][i],
			x: xtmp,
			label: l
		});
		if (data.length > dataLength)
			data.shift();
	};
	//Pop from queue
	xNew.shift();
	yNew.shift();
	audio.shift();
	idx.shift();
	chart.render();
	return OK;
};

//Get data from server ===============================================
function getLastest() {		//Get lastest data from server.
	$.get('STREAM_getlastest.php',
		function(data) {
			add_data(data);
		}
	);
}

function add_data(data, callGetLastest = true) {	//Validate and push data to the queue.
													//Also call the next getLastest().
    if (data=="offline") {
    	Offline();
		if (callGetLastest)	setTimeout(function(){getLastest()},updateInterval);
    	return;
    } else {
    	Online()
    }
    var y = (data+"").split(" ");
    var x = y[0]; 				y.shift();
    var ID = y[y.length-1];		y.pop();y.pop();
    if (typeof ID == "undefined" || ID.length<1 || ID==last_idx) {
		if (callGetLastest)	setTimeout(function(){getLastest()},smallInterval);
    	return;
    }
    for (i in y) y[i] = Number(y[i]);
    if (callGetLastest)
		get_new_sound('wavdata/data_'+ID);
    xNew.push(x);
	yNew.push(y);
	idx.push(ID);
	last_idx = ID;
	if (callGetLastest)	setTimeout(function(){getLastest()},updateInterval);
}

//Stuffs ============================================================

function Online() {
	online = true;
	if (!play || !waitMoreData) {
		gebi("status").innerHTML = "ONLINE";
		gebi("status").style.color = "green";
	}
}
function Offline() {
	online = false;
	gebi("status").innerHTML = "OFFLINE";
	gebi("status").style.color = "red";	
}

function toggleLagMode(x) {
	LagMode = !LagMode;
	if (LagMode) {
		x.value = "Realtime is ON : realtime but maybe lagging.";
		var tmp = play;
		play = false;
		while (yNew.length>0)
			updateChart();
		play = tmp;
	}
	else
		x.value = "Smooth mode is ON : smooth but high latency.";
}

function toggle_play(x) {
	play = !play;
	if (play)
		x.value = "Mute";
	else
		x.value = "Play"
}

function checkin() {	//Get listeners, also checkin.
	$.get("STREAM_checkin.php",
		function(data) {
			gebi("users").innerHTML = "Listeners : "+data;
		}
	);
}

window.onload = STREAM_init