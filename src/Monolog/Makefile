libmonolog.so:
	./bpc-prepare.sh src.list
	$(MAKE) -C ./Monolog libmonolog

libmonolog:
	bpc -v \
		-c bpc.conf  \
		-l monolog \
		-u psr-log \
		--input-file src.list

install-libmonolog:
	cd Monolog && bpc -l monolog --install
