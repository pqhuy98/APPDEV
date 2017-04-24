#include <stdio.h>
#include <math.h>
#include "sound.h"
#include "screen.h"
#include "comm.h"
#include <string.h>

#define n_block 80	//Number of small block which would be display on-screen.
					//Should be divisible by N_BLOCK.

#define N_BLOCK 8	//Number of big block which would be sent to the server.

void* disp_wav_data(void* args){ 	//This function will be put on a thread to handle lagging issues in live-stream.
	//Functions arguments :
	thread_args* a  = (thread_args*)args;  //Arguments casted from void*.
	char* filename  = (*a).filename;       //The file which will be sent.
	char* url       = (*a).url;            //Server's url.

	//Variables :
	int i,j;
	double sum, rms;      //sum, rms, of a small block.

	double RMS[N_BLOCK];  //RMS values of big blocks.
	memset(RMS,0,sizeof RMS);


	int size        = SAMPLE_RATE/n_block; //size of a small block.
	int SIZE        = n_block/N_BLOCK;     //Each big block contains (SIZE) small blocks.

	char tmp[10];           //Used to convert double to string.
	char data[N_BLOCK*10] = "";    //Data sent to the server.

	//Read file =========================================================================
	WAVHDR hdr;
	short int sa[SAMPLE_RATE];

	FILE* f = fopen(filename, "rb");
	fread(&hdr,sizeof(hdr),1,f);	//Read wav file's header.
	fread(&sa, sizeof(short int), SAMPLE_RATE, f);	//Read wave data.
	fclose(f);

	printf("%s\n",filename);

	//Calculate amplitude graph =========================================================
	#ifndef WEB_ONLY
		clrscr();
		goto_xy(1,1);
		printf("Press Enter to move to current graph.");
	#endif


	short int* s = sa;

	for (i=0; i<n_block; i++){
		sum = 0.0;              //Sum of square of a small block.
		for (j=0; j<size; j++){
			sum += (int)(*s)*(*s);
			s++;
		}
		rms = sqrt(sum/size);   //RMS of a small block.
		RMS[i/SIZE] += sum;     //RMS[i/SIZE] currently stores sum of square of big block i/SIZE-th

		//------------------------------------------
		#ifndef WEB_ONLY
			//Draw a bar at column i+3, with value rms.
			display_bar(i+3,rms,30,60);
		#endif
		//------------------------------------------
	}
	#ifndef WEB_ONLY
		goto_xy(1,2);
	#endif

	for (i=0; i<N_BLOCK; i++) {
		//Calculate RMS of big block by its sum of square.
		//Each big block has SAMPLE_RATE/N_BLOCK elements.
		RMS[i] = sqrt(RMS[i]/(SAMPLE_RATE/N_BLOCK));
		//Decibel value  = 20*log10(RMS[i])
		sprintf(tmp,"%.2lf ", 20*log10(RMS[i]));
		strcat(data,tmp);
	}

	//Send data to the server. =========================================================
	send_post(filename, data, url);
}
