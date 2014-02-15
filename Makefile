PHP=$(shell which php)
CURL=$(shell which curl)
TESTRUNNER=vendor/bin/testrunner

setup:
	$(CURL) -s https://getcomposer.org/installer | php

install: setup
	$(PHP) composer.phar install
	$(PHP) vendor/bin/testrunner compile -p vendor/autoload.php

test:
	$(PHP) ./vendor/bin/phpunit --bootstrap ./vendor/autoload.php -c ./phpunit.xml.dist ./tests

testrunner:
	$(TESTRUNNER) "phpunit"  --preload-script ./vendor/autoload.php  --phpunit-config ./phpunit.xml.dist --autotest ./tests ./src
