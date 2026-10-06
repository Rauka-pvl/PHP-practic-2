<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app.php';
header('Content-Type: text/html; charset=UTF-8');
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];
try {
    if (!in_array($path, ['/', '/register'], true)) {
        http_response_code(404);
        $body = '<p>Ресурс не найден</p>';
    } elseif (!in_array($method, $path === '/' ? ['GET', 'HEAD'] : ['POST'], true)) {
        http_response_code(405);
        header('Allow: ' . ($path === '/' ? 'GET, HEAD' : 'POST'));
        $body = '<p>Метод не разрешён</p>';
    } elseif ($path === '/') {
        $body = form([], []);
    } else {
        $result = validateRegistration($_POST);
        if ($result['errors'] !== []) {
            http_response_code(422);
            $body = '<p class="error">Исправьте поля формы</p>' . form($result['values'], $result['errors']);
        } else {
            $v = $result['values'];
            $body = '<h2>Предпросмотр подтверждён</h2><p>Имя: ' . h($v['fullName']) . '</p><p>Email: '
                . h($v['email']) . '</p><p>Тема: ' . h(topics()[$v['topic']]) . '</p><p>Мест: ' . h($v['seats'])
                . '</p><p>Заявка не сохранена; повторная отправка только повторяет проверку.</p>';
        }
    }
} catch (Throwable $error) {
    $pending = str_starts_with($error->getMessage(), 'TODO ');
    http_response_code($pending ? 501 : 500);
    $body = '<p>' . htmlspecialchars($pending ? $error->getMessage() : 'Ошибка приложения', ENT_QUOTES, 'UTF-8') . '</p>';
    if (!$pending) error_log((string) $error);
}
if ($method !== 'HEAD') {
    echo '<!doctype html><html lang="ru"><meta charset="utf-8"><title>Форма заявки</title><link rel="stylesheet" href="/style.css">';
    echo '<h1>Форма заявки</h1>' . $body . '<p><a href="/">Новая заявка</a></p></html>';
}
