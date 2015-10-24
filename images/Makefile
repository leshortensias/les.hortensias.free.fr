DEL=*~ .*~ .*.sw* 
FTP_FILES= *.jpg *.gif gites.png vim_created.png traveaux.png cheque_vacances.png acc.png partenaires.png
FTP_REP_DEST=/images/

clean: 
	rm -f $(DEL)

recode:
	recode -d l1..h *.php

img: 
	
include ../../../Makefile.ftp
