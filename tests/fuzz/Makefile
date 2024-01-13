FUZZER ?= ../../vendor/bin/php-fuzzer
RUNS   ?= 1000000
TARGETS = $(wildcard *.php)
TESTS   = $(TARGETS:.php=)

all: $(TESTS)

$(TESTS):
	$(FUZZER) fuzz --max-runs $(RUNS) $@.php corpus/$@

clean:
	rm crash-*.txt
	rm minimized-*.txt

.PHONY: all $(TESTS) clean
