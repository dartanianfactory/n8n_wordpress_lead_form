# Fullstack Integration: WordPress + n8n + Mock CRM

## Описание проекта

Интеграционная система для обработки заявок с сайта WordPress через n8n в CRM систему.

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

WordPress: http://localhost:8000
n8n: http://localhost:5678
Mock API: http://localhost:3000