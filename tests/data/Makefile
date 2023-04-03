files = valid.inv skipped_lines.inv invalid_object.inv no_objects.inv

all: $(files)

%.inv: %.header %.data.zz
	cat $^ > $@

.INTERMEDIATE: $(files:.inv=.data.zz)
%.zz: %
	pigz --keep --zlib $<
	@touch $@


clean:
	rm -f *.zz $(files)

.PHONY: all clean
