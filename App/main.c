/**
This application records the sound from our machine's micro to .wav file every 1 second,
sent the files and decibel values to the server. The server will visualize the decibel values
and play the audio in real time.
*/

//Some standard libraries...
#include <stdio.h>
#include <stdlib.h>
#include <signal.h>
#include <sys/wait.h>
//Our libraries !
#include "sound.h"
#include "screen.h"

int main(int argc, char *argv[]){
	/**
	There are time gaps between recording the sound and sending them to the server.
	These time gaps produce audio gaps which create annoying listening experience.
	
	Multithreading eliminates these gaps. We record the first second to data(X).wav with X=0.
	Then repeat :
		1) Record one second to the file data(X).wav.
		2) Send the previous file data(X xor 1).wav to the server.
		3) Flip X, i.e. X xor 1 (0 to 1, 1 to 0).
	(1) and (2) are executed at the same time by creating 2 thread processes.

	We record data to 2 different files (data0.wav and data1.wav) to avoid race conditions.

	So the processes would look like :
		Record data0.wav (first step)
		Record data1.wav -- send data0.wav
		Record data0.wav -- send data1.wav
		Record data1.wav -- send data0.wav
		Record data0.wav -- send data1.wav
		...
	*/

	/*  Flow chart :
	          /Record data0.wav \            /Record data1.wav \                           / ...
	         /                   \          /                   \                         /  ...
	  A --> <                    Join -->  <                    Join --> A (repeat)  --> <   ...
	         \                   /          \                   /                         \  ...
	          \Send data1.wav   /            \Send data0.wav   /                           \ ...
	*/

	char* cmd[2];
	cmd[0] = "arecord -q -r16000 -c1 -d1 -f S16_LE data0.wav";
	cmd[1] = "arecord -q -r16000 -c1 -d1 -f S16_LE data1.wav";

	char* name[2];
	name[0] = "data0.wav";
	name[1] = "data1.wav";

	/*
	Where is our server ?
	$ ./wave.a      (no argument)                   --> VAMK server
	$ ./wave.a kfdg (any random string as argument) --> local server (debug mode).
	*/
	char* server_url;
	if (argc<=1)
		server_url = SERVER_URL;
	else
		server_url = DEBUG_URL;

	//Stuffs...
	int i;
	pthread_t thr;	//Thread for processing...
	thread_args args;	//Function dispWAVdata's arguments for multithreading.
	args.url = server_url;

	int idx = 0;	//Record to data0.wav or data1.wav ? Format : data($idx).wav
	int first_run = 1; 

	//========================= MAIN LOOP ========================================
	//Because the system command is a blocking process, so the
	//program structure is written a bit different than described above.
	//But the main idea should be the same.

	while (1) {
		int exitcode = system(cmd[idx]);

		//User want the halt the program ?
		if (WIFSIGNALED(exitcode) && WTERMSIG(exitcode)==SIGINT) {
			clrscr();
			break;
		}

		//Join previous data sending thread.
		if (!first_run) {
			pthread_join(thr,NULL);
		}

		//Start new data sending thread
		args.filename = name[idx];
		pthread_create(&thr, NULL, disp_wav_data, &args);

		idx^=1;	//Flip : 0->1 and 1->0
		first_run = 0;	//No longer first-run.
	}
 
	return 0;
}