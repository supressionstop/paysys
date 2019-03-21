# paysys
Доброго утра, API готово, вот код на Гите:
https://github.com/supressionstop/paysys

Для развертывания быстрее всего предоставить локально работающую папку:
https://drive.google.com/open?id=1h_St7Amxz7Oa5TiHKtAh4LNeILqNXjuO

## Для запуска
1. Распаковать
2. В папке запустить docker-compose up

## Будут развернуты следующие контейнеры
1. PHP-FPM
2. Nginx
3. MySQL
4. Phpmyadmin

## Для работы используются следующие порты
1. 80 - для работы API
2. 8080 - для работы PHPMyAdmin

## Структура папок

```
├── data
│   └── db                            
│       ├── dumps                     - Бекапы
│       ├── migrations                - Файлы миграций Phinx
│       ├── mysql                     - Файлы СУБД
│       └── seeds                     - Файлы заполнения данными БД Phinx
├── dockerfiles                       - Dockerfile для сборки контейнеров
├── nginx                             - Файлы настройки Nginx
└── web                               - Папка проекта
    ├── config                        - Конфигурационные файлы
    ├── logs                          - Логи
    ├── public                        - api.php
    ├── src                           - Код
        ├── entities                  - Сущности
        └── providers                 - Провайдеры для связи с БД и Depkasa        
```
## Используемые библиотеки
1. Slim framework - для работы API
2. Phinx - для миграций

## Настройки
Хранятся в web/config/config.ini
Для работы Callback-ов нужно поменять callback_url на адрес, доступный для запросов через Интернет, например:
```
callback_url = "http://121.11.12.13/api/callback/payment_system"
```

## Endpoints
Доступны три эндпойнта API
1. /api/payment - создание запроса на платеж
2. /api/callback/payment_system - для callback-ов
3. /api/payment - проверка статуса платежа

Для проверки работы можно использовать Postman, подключив коллекцию по ссылке:
https://www.getpostman.com/collections/e7b916e6ed98b4c5e2e0
