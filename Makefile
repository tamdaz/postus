.PHONY: tsc test install-pkgs mercure

# test selected class test (recommended for debugging the classes)
tsc:
	@bin/console doctrine:database:drop --force --env=test > /dev/null 2>&1
	@bin/console doctrine:schema:create --env=test > /dev/null 2>&1
	@bin/console doctrine:fixtures:load --append --env=test > /dev/null 2>&1
	@env PANTHER_CHROME_BINARY=/bin/google-chrome ./bin/phpunit --filter PostTest --debug

# Test all classes located in the tests/ folder
test:
	@bin/console doctrine:database:drop --force --env=test > /dev/null 2>&1
	@bin/console doctrine:schema:create --env=test > /dev/null 2>&1
	@bin/console doctrine:fixtures:load --append --env=test > /dev/null 2>&1
	@env PANTHER_CHROME_BINARY=/bin/google-chrome ./bin/phpunit

# Install all necessary packages
install-pkgs:
	@composer update
	@npm update

# Start Symfony server
start:
	@symfony server:start --no-tls