# Библиотека (PHP + MySQL) — сборка для Beget (public_html)
Эта сборка рассчитана на то, что **всё лежит внутри `public_html/`**.

## Что загружать
Загрузите **всё содержимое архива** в папку `public_html` на Beget:
- `index.php`, `search.php`, `my.php`, `admin.php`, `login.php`, `register.php`, `logout.php`, `_header.php`
- папка `assets/`
- папка `src/`
- папка `tools/`
- `schema.sql`

## База данных
1) Создайте БД `revengoc_1`
2) Импортируйте `schema.sql`

## Администратор
Откройте 1 раз: `https://ваш-домен/tools/make_admin.php`
Затем **удалите** файл `tools/make_admin.php`.

Данные:
- email: admin@site.ru
- пароль: Admin123!

## Подключение к БД
`src/config.php`
