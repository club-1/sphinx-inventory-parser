VERSION = 0.0.10
PHARURL = https://github.com/nikic/PHP-Fuzzer/releases/download/v$(VERSION)/php-fuzzer.phar
PHAR    = php-fuzzer_$(VERSION).phar
FUZZER ?= ./$(PHAR)
RUNS   ?= 1000000
TESTS   = parseHeader parseObjectsV2
RUNTESTS= $(TESTS:%=run-%)

all: $(RUNTESTS)

$(RUNTESTS): run-%: % $(FUZZER)
	$(FUZZER) fuzz --max-runs $(RUNS) $*/target.php $*/corpus

$(TESTS): %: %/corpus
	$(MAKE) -C $@

%/corpus:
	mkdir $@

.DELETE_ON_ERROR: $(PHAR)
$(PHAR):
	wget -q $(PHARURL) -O $@
	sha256sum --check php-fuzzer.phar.sha256
	chmod +x $@

clean:
	rm -rf $(TESTS:%=%/corpus)
	rm -f crash-*.txt
	rm -f minimized-*.txt
	rm -f php-fuzzer_*.phar

.PHONY: all $(RUNTESTS) $(TESTS) clean
