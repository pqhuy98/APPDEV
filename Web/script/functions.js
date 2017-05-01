//Small functions

function gebi(x) {return document.getElementById(x);}

function newChart(id) {
	console.log(data);
	return new CanvasJS.Chart(id, {
    	backgroundColor: "transparent",
		axisX: {
  			interval: 8,
			labelAngle: -45,
			valueFormatString: "HH:mm:ss"
		},
		axisY: {
			maximum:140
		},
		data: [{
			type: "area",
			dataPoints: data
		}]
	});
}

function s_to_hms(time) {
	var date = new Date(null);
	date.setSeconds(time);
	return date.toISOString().substr(11, 8);
}

function hms_to_s(h,m,s) {
	return h*60*60+m*60+s;
}