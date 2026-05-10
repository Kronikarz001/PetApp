
UNAME_S := $(shell uname -s)
ifeq ($(UNAME_S),Darwin)
    SUDO =
else
    SUDO = sudo
endif

include .env

renew:
	$(SUDO) docker compose down
	$(SUDO) docker compose up -d --build
	$(SUDO) docker exec petapp_app php artisan key:generate
	$(SUDO) docker exec petapp_app php artisan config:clear
	$(SUDO) docker exec petapp_app php artisan route:clear
	$(SUDO) docker exec petapp_app php artisan cache:clear

up:
	$(SUDO) docker compose up -d

down:
	$(SUDO) docker compose down

up-build:
	$(SUDO) docker compose up -d --build

down-remove:
	$(SUDO) docker compose down -v

optimize:
	$(SUDO) docker compose exec petapp_app php artisan optimize
	$(SUDO) docker compose exec petapp_app php artisan config:clear
	$(SUDO) docker compose exec petapp_app php artisan route:clear
	$(SUDO) docker compose exec petapp_app php artisan cache:clear

test:
	$(SUDO) docker compose exec petapp_app php artisan config:clear
	$(SUDO) docker compose exec petapp_app php artisan cache:clear
	$(SUDO) docker compose exec petapp_app php artisan test

logs:
	$(SUDO) docker compose exec petapp_app tail -f storage/logs/laravel.log

route-clear:
	$(SUDO) docker compose exec petapp_app php artisan route:clear

route-list:
	$(SUDO) docker compose exec petapp_app php artisan route:list

artisan:
	$(SUDO) docker compose exec petapp_app php artisan $(filter-out $@,$(MAKECMDGOALS))
