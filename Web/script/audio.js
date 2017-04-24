var context;

try {
	window.AudioContext = window.AudioContext||window.webkitAudioContext;
	context = new AudioContext();
}
catch(e) {
	alert('Web Audio API is not supported in this browser');
}


function get_new_sound(url) {
  var request = new XMLHttpRequest();
  request.open('GET', url, true);
  request.responseType = 'arraybuffer';

  // Decode asynchronously
  request.onload = function() {
    context.decodeAudioData(request.response, function(buffer) {
    	audio.push(buffer);
    }, function(){});
  }
  request.send();
}

function play_lastest() {
	var source = context.createBufferSource();
	source.buffer = audio[0];
	source.playbackRate.value = 1;
	source.connect(context.destination);
	source.start(0);
	setTimeout(function(){Controller()},source.buffer.duration*1000/source.playbackRate.value);
}