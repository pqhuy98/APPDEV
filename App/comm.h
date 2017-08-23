#include <stdlib.h>

#define SERVER_URL "http:/pqhuy98.hopto.org/APPDEV/APP_save.php"
#define DEBUG_URL "localhost/APPDEV/APP_save.php"

//Send file wav and a string of decibel values to url by HTTP POST
void* send_post(char *filename, char *data, char *url);
