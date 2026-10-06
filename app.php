<?php
declare(strict_types=1);

function topics(): array
{
    return match ((require __DIR__ . '/config.php')['variant']) {
        'A' => ['php' => 'PHP', 'http' => 'HTTP'],
        'B' => ['git' => 'Git', 'testing' => 'Тестирование'],
        'C' => ['sql' => 'SQL', 'design' => 'Проектирование'],
        default => throw new InvalidArgumentException('Вариант A/B/C'),
    };
}

function textField(array $input, string $name, array &$errors): string
{
    // P02-SCALAR: Отличить строку от массива, проверить UTF-8 и убрать крайние пробелы.
    $value = $input[$name] ?? '';
    if (!is_string($value)) {
        $errors[$name] = 'Ожидается одно текстовое значение';
        return '';
    }
    if (preg_match('//u', $value) !== 1) {
        $errors[$name] = 'Некорректная кодировка UTF-8';
        return '';
    }
    return trim($value);
}

function validateRegistration(array $input): array
{
    // P02-VALIDATE: Проверить имя, email, тему, места и согласие; собрать ошибки по полям.
    $values = [];
    $errors = [];
    foreach (['fullName', 'email', 'topic', 'seats', 'agree'] as $name) {
        $values[$name] = textField($input, $name, $errors);
    }

    if (!isset($errors['fullName'])) {
        $length = mb_strlen($values['fullName'], 'UTF-8');
        if ($length < 2 || $length > 60) {
            $errors['fullName'] = 'Имя должно содержать от 2 до 60 символов';
        }
    }

    if (!isset($errors['email'])) {
        if ($values['email'] === '') {
            $errors['email'] = 'Укажите email';
        } elseif (strlen($values['email']) > 120) {
            $errors['email'] = 'Email не должен превышать 120 байт';
        } elseif (filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Некорректный email';
        }
    }

    if (!isset($errors['topic']) && !array_key_exists($values['topic'], topics())) {
        $errors['topic'] = 'Выберите тему из списка';
    }

    if (!isset($errors['seats']) && preg_match('/^[1-5]$/D', $values['seats']) !== 1) {
        $errors['seats'] = 'Количество мест — одна цифра от 1 до 5';
    }

    if (!isset($errors['agree']) && $values['agree'] !== '1') {
        $errors['agree'] = 'Необходимо согласие на обработку заявки';
    }

    return [
        'values' => $values,
        'errors' => $errors,
    ];
}

function h(string $value): string
{
    // P02-ESCAPE: Реализовать htmlspecialchars для HTML-текста и атрибута.
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function form(array $values, array $errors): string
{
    $body = '<form action="/register" method="post"><p>Предпросмотр заявки; сохранения пока нет.</p>';
    foreach (['fullName' => 'Имя', 'email' => 'Email', 'seats' => 'Количество мест (1–5)'] as $name => $label) {
        $body .= '<label for="' . $name . '">' . $label . '</label><input id="' . $name . '" name="' . $name . '" value="' . h($values[$name] ?? '') . '">';
        if (isset($errors[$name])) $body .= '<p class="error">' . h($errors[$name]) . '</p>';
    }
    $body .= '<label for="topic">Тема</label><select id="topic" name="topic"><option value="">Выберите</option>';
    foreach (topics() as $key => $label) {
        $body .= '<option value="' . h($key) . '"' . (($values['topic'] ?? '') === $key ? ' selected' : '') . '>' . h($label) . '</option>';
    }
    $body .= '</select>';
    if (isset($errors['topic'])) $body .= '<p class="error">' . h($errors['topic']) . '</p>';
    $body .= '<label><input type="checkbox" name="agree" value="1"' . (($values['agree'] ?? '') === '1' ? ' checked' : '') . '> Согласен с обработкой учебной заявки</label>';
    if (isset($errors['agree'])) $body .= '<p class="error">' . h($errors['agree']) . '</p>';
    return $body . '<button>Проверить заявку</button></form>';
}
