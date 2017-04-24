//Small functions

function gebi(x) {return document.getElementById(x);}

function newChart(id) {
	return new CanvasJS.Chart(id,{
    	backgroundColor: "transparent",
		axisX: {
  			interval: 10,
			labelAngle: -45,
			valueFormatString: "HH-mm-ss"
		},
		data: [{
			type: "area",
			dataPoints: data
		}]
	});

}