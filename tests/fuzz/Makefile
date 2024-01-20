FUZZER ?= ../../vendor/bin/php-fuzzer
RUNS   ?= 1000000
TESTS   = parseHeader parseObjectsV2
RUNTESTS= $(TESTS:%=run-%)

all: $(RUNTESTS)

$(RUNTESTS): run-%: %
	$(FUZZER) fuzz --max-runs $(RUNS) $*/target.php $*/corpus

$(TESTS): %: %/corpus
	$(MAKE) -C $@

%/corpus:
	mkdir $@

clean:
	rm -rf $(TESTS:%=%/corpus)
	rm -f crash-*.txt
	rm -f minimized-*.txt

.PHONY: all $(RUNTESTS) $(TESTS) clean
