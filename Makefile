.PHONY: run
run:
	PORT=8000 vendor/bin/php-watcher --signal SIGTERM bin/server
