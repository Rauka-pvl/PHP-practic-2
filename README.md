# Практическая работа 02

**Разработка форм с валидацией данных и безопасным выводом**

Группа: CS-302(С)

| Параметр | Значение |
|---|---|
| Вариант | A (topic: `php`, `http`) |
| Версия PHP | 8.3–8.5 (проверено также на 8.2), расширение mbstring |
| Адрес запуска | http://localhost:8088/ |
| Веб-корень | `C:/WebLabs/pr02/public` |

## Запуск

1. Скопировать проект в `C:\WebLabs\pr02`.
2. Вариант задаётся в `config.php`.
3. Подключить `apache-course.conf` в Uniform Server (VirtualHost на `public`, порт 8088) и перезапустить Apache.
4. Открыть `http://localhost:8088/health.php` — `stage: 2`, `mbstring: true`.

Альтернатива без Apache:

```
php -S localhost:8088 -t public router.php
```

## Выполненные TODO (`app.php`)

- `P02-SCALAR` — `textField()`: отсутствующее поле считается пустой строкой; до любых строковых операций проверяется `is_string`, затем корректность UTF-8 через `preg_match('//u', ...)`; при ошибке сообщение пишется в `$errors[$name]` и возвращается `''`, иначе — результат `trim`.
- `P02-VALIDATE` — `validateRegistration()`: читает все пять полей через `textField()` и проверяет каждое, не останавливаясь на первой ошибке. Возвращает `['values' => ..., 'errors' => ...]`.
- `P02-ESCAPE` — `h()`: `htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`.

Где что происходит:

- **нормализация** — `trim` в `textField()`;
- **валидация** — `validateRegistration()`: имя 2–60 символов (`mb_strlen`), email не более 120 байт (`strlen`) и `FILTER_VALIDATE_EMAIL`, topic — ключ из `topics()` текущего варианта, seats — `/^[1-5]$/D`, agree — строго `'1'`;
- **экранирование** — `h()` вызывается только при выводе в `form()` и `public/index.php`; в `values` хранится неэкранированный текст.

## Результаты самопроверки

```
curl.exe -i -d 'email=student@example.test&topic=php&seats=2&agree=1' --data-urlencode 'fullName=Anna Example' http://localhost:8088/register

HTTP/1.1 200 OK
Имя: Anna Example / Email: student@example.test / Тема: PHP / Мест: 2
```

| Проверка | Результат |
|---|---|
| Все пять полей корректны | 200, предпросмотр заявки |
| Имя из пробелов / один символ | 422, ошибка fullName |
| Имя 60 / 61 символ (кириллица) | 200 / 422 |
| Неверный email | 422, имя, тема, места и согласие сохранены в форме |
| topic `git` (вариант B) | 422, ошибка topic |
| seats: 0, 6, 2.5, 02, +2, массив | 422, ошибка seats |
| Нет agree | 422, ошибка agree |
| Пустой запрос с неверными полями | 422, ошибки всех пяти полей одновременно |
| `fullName=<b>Анна & Илья</b>` | 200, в исходнике `&lt;b&gt;Анна &amp; Илья&lt;/b&gt;`, в браузере видны сами теги |
| `fullName[]=Анна` | 422 без предупреждений и TypeError, в поле не появляется `Array` |
| Неправильный UTF-8 в имени | 422 |
| GET к /register | 405, `Allow: POST` |

Успешный ответ — только предпросмотр: заявка не сохраняется, повторная отправка лишь повторяет проверку.
