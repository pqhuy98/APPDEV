enum COLORS {BLACK = 30, RED, GREEN, YELLOW, BLUE, MAGENTA, CYAN, WHITE};

#define UNICODE 1
#define UBAR "\u2590"

// Function prototypes

//main
void display_bar(int col, double rms, int lim, int reso);
void goto_xy(int x, int y);
void clrscr();

//helpers
int get_color_by_level(int level, int lim);
void set_color(int color);
void reset_color();


