# Censorship Bot

Telegram бот на Symfony

- Удаляет нецензурные сообщения из чата
- Возможность блокировать пользователей, отправляющих нецензурные сообщения

## Инструкция
- Загрузить репозиторий
- Обновить зависимости `composer install`
- Поднять окружение докер `docker-compose up -d`
- Прописать настройки в файле `.env` (пример `.env.dist`)
- Поменять при необходимости сертификаты в папке docker/nginx/certs

## Веб хук
- Установить веб хук для бота (домен заменить на свой)
```shell script
php bin/console telegram-bot:censorship:set-webhook https://domain.ru/censorship-bot/update
```
