files = valid.inv skipped_lines.inv skipped_lines_lf.inv skipped_lines_crlf.inv no_objects.inv name_whitespace.inv invalid_object.inv

ifeq (, $(shell which pigz 2> /dev/null))
	ZLIB_COMPRESS = ./zlib-compress
else
	ZLIB_COMPRESS = pigz --zlib
endif

all: $(files)

%.inv: %.header %.data.zz
	cat $^ > $@

.INTERMEDIATE: $(files:.inv=.data.zz)
%.zz: %
	$(ZLIB_COMPRESS) < $< > $@

clean:
	rm -f *.zz $(files)

.PHONY: all clean
