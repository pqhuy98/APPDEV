var data = [];
var chart;

var xNew = [];
var yNEW = [];

var ERRORS = [
				"Date missing.",
				"Date is wrong format.",
				"No record on this date.",
				"Start not found",
				"End not found."
			 ];

function displayChart() {
	
	
}

function Update(render=true) {
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
	xNew.shift();
	yNew.shift();
	if (render) chart.render();
};

function UpdateAll() {
	while (yNew.length>0) {
		Update(false);
	}
	chart.render();

}

function add_data(data) {
	if (data.length<1) return;
    var y = (data+"").split(" ");
    var x = y[0];
    y.shift();
	y.pop();
	y.pop();
    for (i in y) y[i] = Number(y[i]);
    xNew.push(x);
	yNew.push(y);
}

function get_data(date, from = "", to = "") {
	var path = "ARCHIVE_getdata.php?date="+date+"&from="+from+"&to="+to;
	$.get(path,
		function(data) {
			if ($.inArray(data,ERRORS)) {
				alert(data);
				return;
			}
			lines = data.split("\n");
			for (i in lines)
				add_data(lines[i]);
		}
	);
}

function ARCHIVE_init() {
	if (gebi("archive_chart")==null) {
		console.log("ARCHIVE : stop.");
		return;
	} else chart = newChart("archive_chart");
}