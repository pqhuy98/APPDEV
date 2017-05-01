//These constants are set by archive.php
var VALUES_PER_SEC;
var WINDOW_SIZE;
var SECS_PER_DAY;

/*Algorithm ===============================================================

Use stacks and deque is to ensure efficient scrolling when WINDOW_SIZE and VALUES_PER_SEC are so big.

| bottom oooooooooooooo | front xxxxxxxxxxxxxxxxxxx back | xxxxxxxxxxxxxxxxxxxx bottom |
|          Lstack       |              data              |           Rstack            |

Rstack is displayed as reversed. So Rstack[Rstack.length-1] is adjacent to data[data.length-1] (back).

*/
var data = [];   //A deque (double end queue) store data in current window (data displayed)
var Lstack = []; //A stack store data to the left of current window (invisible until user scroll down to move left)
var Rstack = []; //A stack store data to the left of current window (invisible until user scroll up to move right)
/*

When scroll up (right) :
    - The data[0] is pushed to Lstack, then erased from data[].
    - The Lstack[Lstack.length-1] is pushed to data[], then popped from Lstack[].
Scroll down (left) use the same menthod.

Example :

	Lstack : [1] [3] [5] [6] [1]
	data   : [3] [2] [4] [7] [0]
	Rstack : [8] [5] [7] [9] [3]

Scroll up (move right) =>

	Lstack : [1] [3] [5] [6] [1] [3] <- This [3] is from data[]
	data   : [2] [4] [7] [0] [8] <- This [8] is from Rstack[]
	Rstack : [5] [7] [9] [3]

When Lstack or Rstack reach a certain size, theirs bottom element is removed to save memory.

(Updated 01/05/2017)
	Because WINDOW_SIZE and VALUES_PER_SEC are now smaller,
	the implementation's performance is no different from that of the naive approach.	

*/
//===========================================================================

var chart; //CanvasJS chart

//Some scrolling constants which control speed
var default_seed_speed = 0.1;
var base_speed = 0;
var seed_speed = default_seed_speed;
var bonus_speed = 0.5

//Used to detect scrolling
var last_scroll = new Date();

//Main functions ==============================================================

function reload() {
	fillData(DATE,TIME);
	//chart.render();
}

//Get data[] from server. Call fillLeft, fillRight to get Lstack and Rstack.
function fillData(date,time) {
	var path = "ARCHIVE_getSeconds.php?date="+date+"&time="+time+"&length="+WINDOW_SIZE;
	$.ajax({ url: path, async: false, success: function(dat) {
		data = [];
		for(var i=0;i<dat.length;i++) {
			var y = dat[i].charCodeAt(0);
			var label = "";
			if (i%VALUES_PER_SEC==0) {
				label = s_to_hms(time+Math.floor(i/VALUES_PER_SEC));
			}
			data.push({
				y : y,
				x : time*VALUES_PER_SEC+i,
				label : label
			});
		}
		chart.options.data[0].dataPoints = data;
		chart.render();
	}});
	TIME = time;
	if (TIME>0)
		fillLeft(date,Math.max(0,TIME-WINDOW_SIZE));
	if (TIME+WINDOW_SIZE<SECS_PER_DAY)
		fillRight(date,TIME+WINDOW_SIZE);
}

//Get Lstack
function fillLeft(date,time) {
	time = Math.max(0,time);
	var path = "ARCHIVE_getSeconds.php?date="+date+"&time="+time+"&length="+WINDOW_SIZE;
	$.ajax({ url: path, async: false, success: function(dat) {
		Lstack = [];
		for(var i=0;i<dat.length;i++) {
			if (Math.floor((time+i)/VALUES_PER_SEC)==TIME)
				break;
			var y = dat[i].charCodeAt(0);
			var label = "";
			if (i%VALUES_PER_SEC==0) {
				label = s_to_hms(time+Math.floor(i/VALUES_PER_SEC));
			}
			Lstack.push({
				y : y,
				x : time*VALUES_PER_SEC+i,
				label : label
			});
		}
	}});
}

//Get Rstack
function fillRight(date,time) {
	var path = "ARCHIVE_getSeconds.php?date="+date+"&time="+time+"&length="+WINDOW_SIZE;
	$.ajax({ url: path, async: false, success: function(dat) {
		Rstack = [];
		for(var i=dat.length-1;i>=0;i--) {
			var y = dat[i].charCodeAt(0);
			var label = "";
			if (i%VALUES_PER_SEC==0) {
				label = s_to_hms(time+Math.floor(i/VALUES_PER_SEC));
			}
			Rstack.push({
				y : y,
				x : time*VALUES_PER_SEC+i,
				label : label
			});
		}
	}});
}

//Go to the left cnt step, according to the algorithm above.
function goLeft(cnt) {
	for(var i=0;i<cnt;i++) {
		if (TIME==0) return;
		for(var _=0;_<VALUES_PER_SEC;_++) {
			data.splice(0,0,Lstack[Lstack.length-1]);
			Lstack.pop();
			Rstack.push(data[data.length-1]);
			data.pop();
		}
		TIME--;
		if (Lstack.length==0 && TIME>0)
			fillLeft(DATE, TIME-WINDOW_SIZE);
		while (Rstack.length>5*WINDOW_SIZE*VALUES_PER_SEC)
			Rstack.shift();
	}
	chart.render();
	gebi("h").value = Math.floor(TIME/60/60);
	gebi("m").value = Math.floor(TIME/60)%60;
	gebi("s").value = TIME%60;
}

//Go to the right cnt step, according to the algorithm above.
function goRight(cnt) {
	for(var i=0;i<cnt;i++) {
		if (data[data.length-1].x==SECS_PER_DAY*VALUES_PER_SEC-1)
			return;
		for(var _=0;_<VALUES_PER_SEC;_++) {
			Lstack.push(data[0]);
			data.shift();
			data.push(Rstack[Rstack.length-1]);
			Rstack.pop();
		}
		TIME++;
		if (Rstack.length==0 && TIME+WINDOW_SIZE<SECS_PER_DAY)
			fillRight(DATE, TIME+WINDOW_SIZE);
		while (Lstack.length>5*WINDOW_SIZE*VALUES_PER_SEC)
			Lstack.shift();
	}
	chart.render();
	gebi("h").value = Math.floor(TIME/60/60);
	gebi("m").value = Math.floor(TIME/60)%60;
	gebi("s").value = TIME%60;
}

//Initializer function
function ARCHIVE_init() {
	console.log("asd");
	if (gebi("archive_chart")==null) {
		console.log("ARCHIVE : stop.");
		return;
	}
	chart = newChart("archive_chart");
	fillData(DATE,TIME);
	setInterval(function(){reload()},1000);
	gebi("go").onclick = function() {
		if (Number(gebi("h").value)<0 || Number(gebi("h").value)>23 ||
			Number(gebi("m").value)<0 || Number(gebi("m").value)>59 ||
			Number(gebi("s").value)<0 || Number(gebi("s").value)>59
		) {
			alert("Time is incorrect !");
			return;
		}
		var time = Number(gebi("h").value)*60*60;
		time+= Number(gebi("m").value)*60;
		time+= Number(gebi("s").value);
		fillData(DATE,time);
	}
}

//For scrolling
window.onload = ARCHIVE_init;
window.addEventListener('mousewheel',handler);
window.addEventListener('DOMMouseScroll',handler);

var predelta = 0;

//Scrolling handler
function handler(e){
	console.log("x");
    var e = window.event || e; // old IE support
    var delta = Math.max(-1, Math.min(1, (e.wheelDelta || -e.detail)));

	var this_scroll = new Date();

	if (this_scroll-last_scroll<500 && delta*predelta>0)
		seed_speed+= bonus_speed;
	else
		seed_speed = default_seed_speed;

	last_scroll = this_scroll;

	predelta = delta;
    if (delta < 0) {
	    	goLeft(base_speed+seed_speed);
    }
    else {
    		goRight(base_speed+seed_speed);
    }
};