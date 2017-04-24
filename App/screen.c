#include "screen.h"
#include <stdio.h>
#include <math.h>

//These things' names are kinda self-explained ...
//Basically they are functions that draw the graph.

int get_color_by_level(int level, int lim){
	//Question : what does this function do ?
	//Hint :
	//	int level - current height of the bar.
	//	int nb_color : number of color = 6 (red, yellow, green, cyan, blue, magneta).

	int colors[6] = {MAGENTA,BLUE,CYAN,GREEN,YELLOW,RED};
	int nb_color = sizeof(colors)/sizeof(int);
	
	if (level>=lim) level = lim-1;
	if (level<0) level = 0;

	return colors[level*nb_color/lim];
}

void set_color(int color){
	printf("\033[%d;1m", color);
	fflush(stdout);
}

void reset_color(){
	printf("\033[0m");
	fflush(stdout);
}

void goto_xy(int x, int y){
	printf("\033[%d;%dH",y,x);
	fflush(stdout);
}

void display_bar(int col, double rms, int lim, int reso){
	int i, j, color;
	j = trunc(rms/reso+1);
	if (j>lim) j=lim;
	for (i=0; i<j; i++){
		color = get_color_by_level(i,lim);
		set_color(color);
		goto_xy(col,lim-i+2);
#ifdef UNICODE
		printf("%s", UBAR);
#else
		printf("*");
#endif
	}
	reset_color();
	fflush(stdout);
}

void clrscr(){
	printf("\033[2J");
	fflush(stdout);
}