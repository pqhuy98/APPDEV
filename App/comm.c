//This file contains the function which send data to the server.

#include <stdio.h>
#include <curl/curl.h>
#include "comm.h"

void* send_post(char *filename, char *data, char *url) {
    CURL *curl;
    CURLcode res;

    /* In windows, this will init the winsock stuff */
    curl_global_init(CURL_GLOBAL_ALL);

    struct curl_httppost* post = NULL;
    struct curl_httppost* last = NULL;

    curl = curl_easy_init();
    if(curl) {

        //Similar to this html code : <input type="file" name="file">.
        //Chosen file is "filename" (i.e. data0.wav / data1.wav) at current directory.
        curl_formadd(&post, &last, 
            CURLFORM_COPYNAME, "file",
            CURLFORM_FILE, filename,
            CURLFORM_END);

        //Similar to : <input type="text" name="data"> Its value is in the variable "data".
        curl_formadd(&post, &last,
               CURLFORM_COPYNAME, "data",
               CURLFORM_COPYCONTENTS, data,
               CURLFORM_END);

        //Send to "url"
        curl_easy_setopt(curl, CURLOPT_URL, url);

        curl_easy_setopt(curl, CURLOPT_HTTPPOST, post);

        printf("Hostname : ");
        res = curl_easy_perform(curl);
        if(res)
            printf("curl_easy_perform failed: %d\n", res);

        //Clean things
        curl_formfree(post);
        curl_easy_cleanup(curl);
    }
    else
        printf("curl_easy_init failed\n");
    //Clean all things
    curl_global_cleanup();
    return (void *)0;
}