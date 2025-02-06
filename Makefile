DC=docker-compose
PHP_CONTAINER=app
EXEC_PHP=$(DC) exec $(PHP_CONTAINER) php
KAFKA_SERVERS=kafka:9092
KAFKA_CONTAINER=kafka
EXEC_KAFKA=$(DC) exec $(KAFKA_CONTAINER)

.PHONY: help
help : Makefile # Print commands help.
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

##
## Project commands
##----------------------------------------------------------------------------------------------------------------------
.PHONY: logs shell kafka prune install-local perf-test

logs: ## View containers logs.
	$(DC) logs -f $(filter-out $@,$(MAKECMDGOALS))

shell: ## Run bash shell in php container.
	$(DC) exec $(PHP_CONTAINER) sh

kafka: ## Run bash shell in kafka container.
	$(DC) exec $(KAFKA_CONTAINER) sh

prune:
	$(DC) down -v

install-local: ## Install project
	@echo "[Docker] Down project if exist"
	$(MAKE) prune
	@echo "[Docker] Build & Run container"
	$(DC) up -d --build
	@echo "[Composer] Install dependencies"
	$(MAKE) composer install
	@echo "[Kafka] Create order_topic_test topic"
	$(MAKE) topic-create order_topic_test
	@echo "[Kafka] Create invoice_topic_test topic"
	$(MAKE) topic-create invoice_topic_test

##
## Performance Test commands
##----------------------------------------------------------------------------------------------------------------------
.PHONY: perf-test

perf-test: ## Run performance test for RabbitMQ
	docker run --rm pivotalrabbitmq/perf-test \
		--uri amqp://guest:guest@host.docker.internal:5672 \
		--producers 5 \
		--consumers 1 \
		--time 15 \
		--autoack \
		--heartbeat 10

