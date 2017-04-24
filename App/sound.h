#include <pthread.h>

typedef struct{		//Store WAV file's header.
	char ChunkID[4];
	int	ChunkSize;
	char Format[4];
	char Subchunk1ID[4];
	int Subchunk1Size;
	short int AudioFormat;
	short int NumChannels;
	int SampleRate;
	int ByteRate;
	short int BlockAlign;
	short int BitsPerSample;
	char Subchunk2ID[4];
	int Subchunk2Size;
} WAVHDR;

typedef struct{	
	//Store function dispWAVdata's arguments, for multi-threading.
	char * filename;
	char *url;
} thread_args;

#define SAMPLE_RATE 16000
// #define WEB_ONLY 1	//Do not print to screen, use web only.

void* disp_wav_data(void* args);
