DIRS=includes images 
DIRS=
FTP_FILES=admin_css.php admin_lst_pages.php  admin_or.php  admin_users.php index.php admin_edt_page.php  admin_nouvelles.php  admin.php .htaccess
DEL=*~ .*.sw* erreur.txt
FTP_REP_DEST=/

clean: $(patsubst %, _clean_%, $(DIRS))
	rm -f $(DEL)

$(patsubst %, _clean_%, $(DIRS)):
	cd $(patsubst _clean_%, %, $@) && make clean

$(patsubst %, _ftp_%, $(DIRS)):
	cd $(patsubst _ftp_%, %, $@) && make ftp

include ../../Makefile.ftp
