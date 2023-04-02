files = objects.inv.valid objects.inv.skipped_lines objects.inv.invalid_object objects.inv.no_objects

all: $(files)

$(files): %: %.header %.data.zz
	cat $^ > $@

.INTERMEDIATE: $(files:=.data.zz)
%.zz: %
	pigz --keep --zlib $<
	@touch $@


clean:
	rm -f *.zz $(files)

.PHONY: all clean
