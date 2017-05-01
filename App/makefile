OBJ = main.o sound.o screen.o comm.o
APPNAME = wave.a

$(APPNAME) : $(OBJ)
	gcc -o $(APPNAME) $(OBJ) -lm -lcurl -pthread

%.o : %.c
	gcc -c -o $@ $< -std=c99

clean :
	rm $(OBJ) $(APPNAME) *.wav