# Acoustic Sensor

Github : https://github.com/pqhuy98/APPDEV

This application records sound by the microphone and send them to a web server via the Internet. The web server supports visualizing and playing recorded sound lively with few seconds latency. Currently support Linux OS.  
  
This application was firstly intented to be used to turn a Raspberry Pi with USB sound card and microphone into an acoustic sensor. However, the application can be used in most Linux system with a microphone, for many other purposes.

Visualization is done by CanvasJS trial version.

## 1. Configuration Instruction

**If you are going to use this application on a Raspberry Pi with USB sound card and microphone, this section is for you ! It will tell you how to configurate the USB sound card. Otherwise skip to section 2.**  
  
Raspberry Pi onboard sound card doesn’t have microphone interface. We have to change the default audio device to be USB sound card. To do this, follow these instructions :

  1. Make sure the soundcard and micro phone is plugged in.
  2. Run the terminal command `lsusb` to check if your USB sound card is mounted.
  3. Run `sudo nano /etc/asound.conf` and put following content to the file :
  ```
  pcm.!default {
   type plug
   slave {
    pcm "hw:1,0"
   }
  }
  ctl.!default {
   type hw
   card 1
  }
  ```
  4. Run `alsamixer` you should be able to see that USB sound card is the default audio device.

**Note :** The newest version of Raspbian (a.k.a. Jessie) comes with a new version of alsa-utils (1.0.28), which has a bug while recording: it doesn’t stop even a ‘—duration' switch is given, and generates lots of useless audio files. To fix this problem, we have to downgrade alsa-util to an earlier version (1.0.25) :

  1. Run `sudo nano /etc/apt/sources.list` command and add these lines to last of the file:
```
deb http://mirrordirector.raspbian.org/raspbian/ jessie main contrib non-free rpi
deb http://mirrordirector.raspbian.org/raspbian/ wheezy main contrib non-free rpi
```
  2. Run `sudo apt-get update`.
  3. Run `sudo aptitude versions alsa-utils` to check if version 1.0.25 of alsa-util is available:
```
pi@raspberrypi:~ $ sudo aptitude versions alsa-utils
Package alsa-utils:
i   1.0.25-4                                                     oldstable                                 500
p   1.0.28-1                                                     stable                                    500
```
  4. Run `sudo apt-get install alsa-utils=1.0.25-4` to downgrade.
  5. Reboot (if necessary).
  6. Run `arecord -r44100 -c1 -f S16_LE -d5 test.wav` to test that your microphone is working. You should see a _“test.wav”_ file in the current folder.
  7. Put your earphone on. Run “aplay test.wav” to check that your recording is okay.

## 2. Installation Instruction
This application use _libcurl_ to communicate with the server. Make sure that libcurl is install :

  1. Run `sudo apt-get update`.
  2. Run `sudo apt-get install libcurl3`.
  3. Run `sudo apt-get install libcurl4-openssl-dev`.
  
Compile the application from source code :

  1. Run `git clone https://github.com/pqhuy98/APPDEV.git` to clone the source code to current directory.  
  2. Run `cd APPDEV/App`.
  3. Run `make` to compile the files.
  4. The application is compiled into executable file **wave.a** in this folder. Type `./wave.a` to run the program.
  5. Open [this page](http://www.cc.puv.fi/~e1601124/APPDEV/) and enjoy !

## 3. Application Modification
The application use [VAMK server](http://www.cc.puv.fi/~e1601124/APPDEV/) as default. To modify the destination server, follow these instructions :  
  1. Create a folder in your webserver's `/var/www/html/`. For example : `/var/www/html/MyServer`.
  2. Copy all files from the folder `APPDEV/Web/` to your `/var/www/html/MyServer/` folder.
  3. Change to URL inside `comm.h` to your server's "APP_save.php" URL. For example, if your server domain name is `http://www.yourdomain.com`, change the line into :
```
#define SERVER_URL "http://www.yourdomain.com/APPDEV/APP_save.php
```
You can modify anything in the source code to fit your purpose. I hope that my code comments is readable for you.  
## 4. Possible Improvements
The application is not completed yet. A lot of improvements can be done to increase security and usability. Here are what I can think of :
  1. The application can be scaled to support multiple device. Each device can be treated as a seperate user with their own _ID and password_.
  
  2. The application currently is not secured ! Hacker can exploit it by sending fake data in _HTTP POST requests_. Making the server ask for _ID/password_ in each _HTTP POST_ request is a good way to protect the server.
  
  3. The UI sucks ! Decorating it with background images and CSS is needed.
  
  4. If there are **thousands** of people listenning at the same time, the server would be overload. Changing from **HTTP polling** to **HTTP long polling** can improve performance a lot. 
  
  5. Another big performance boost is re-implementing the function `reload()` in `chart.js` so that it don't have to re-ask the server about already known data.
  
  6. The application records exactly `one` second each iterator and send the previous data to the server. How about recording `t` seconds instead of `one`, where `t` is the amount of time need to send previous data ? That would remove gaps seen in visualization. No lag, everyone's happy !
  
  7. In the previous improvement, it's obvious that we cannot predict the time taken to deliver data to the server. So it would be practical to set `t` to be `2 * T`, where `T` is the average time of previous data deliveries. It can be x2, x3, x4 or x10... whatever does best.

Are those what I can think of ? If you have any new ideas, I would like to hear it ! Just send me an email `huypham060398@gmail.com`, and we can talk all day.
