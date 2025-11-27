# Fullstack Integration: WordPress + n8n + Mock CRM

## Описание проекта

Интеграционная система для обработки заявок с сайта WordPress через n8n в CRM систему.

Плагин разворачивает себя и WP автоматически. Просто запустить.

## Технологии

- **WordPress 6.0+** - CMS с кастомным плагином
- **n8n** - платформа автоматизации workflows
- **Node.js/Express** - Mock CRM API
- **Docker** - контейнеризация
- **MySQL** - база данных WordPress

## Быстрый старт

### 1. Запуск

```bash
docker-compose up -d --build
```

### 2. Доступ к сервисам

## WordPress

URL: http://localhost:8000
Админка: http://localhost:8000/wp-admin

Логин: admin / password

### WordPress REST API эндпоинты:

GET  /wp-json/wp/v2/posts
GET  /wp-json/wp/v2/pages
GET  /wp-json/wp/v2/applications
POST /wp-json/wp/v2/applications

##### Кастомные эндпоинты плагина Lead Form:

```
POST /wp-admin/admin-ajax.php?action=submit_lead_form
- Принимает данные формы лида
- Параметры: name, email, phone, message, nonce
```

## n8n

URL: http://localhost:5678

Логин: admin / password

n8n Webhook эндпоинты:

POST /webhook/wordpress-lead
- Принимает данные из WordPress при создании новой заявки
- Формат JSON: {name, email, phone, message, wordpress_id, source, timestamp}

n8n REST API эндпоинты:

```
GET  /rest/workflows
GET  /rest/workflows/:id
POST /rest/workflows/:id/activate
POST /rest/workflows/:id/deactivate
```

## Mock API

URL: http://localhost:3000

Mock API эндпоинты:

```
GET  /api/healthcheck
- Проверка работоспособности API

POST /api/crm/leads
- Имитация создания лида в CRM системе
- Формат JSON: {name, email, phone, message, source}

GET  /api/crm/leads
- Получение списка созданных лидов

GET  /api/crm/leads/:id
- Получение конкретного лида по ID

PUT  /api/crm/leads/:id/status
- Обновление статуса лида
```