FUZZER  = ../../vendor/bin/php-fuzzer
TARGETS = $(wildcard *.php)
TESTS   = $(TARGETS:.php=)

all: $(TESTS)

$(TESTS):
	$(FUZZER) fuzz $@.php corpus/$@

clean:
	rm crash-*.txt

.PHONY: all $(TESTS) clean
