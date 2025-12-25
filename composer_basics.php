<?php

declare(strict_types=1);

// Подключаем автозагрузчик Composer
require_once __DIR__ . '/vendor/autoload.php';

use App\Service\LoggerService;

// ----------------------------
// Тестирование (в стиле Magrel)
// ----------------------------

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>1. LoggerService — запись сообщения</h3>\n";

// Создаём экземпляр логгера
$logger = new LoggerService('test_app.log');

// Генерируем уникальное сообщение с временем
$message = 'Тестовое сообщение от ' . date('Y-m-d H:i:s') . ' (UTC)';

try {
    $logger->log($message);
    echo "✅ Сообщение успешно записано в файл <code>test_app.log</code><br>\n";
    echo "Текст: <code>" . htmlspecialchars($message, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "</code><br>\n";
} catch (Throwable $e) {
    echo "❌ Ошибка: " . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
}

// Проверим, что файл создан
$fileExists = file_exists('test_app.log');
echo "Файл <code>test_app.log</code> существует: " . ($fileExists ? '✅ да' : '❌ нет') . "<br>\n";

if ($fileExists) {
    $lines = file('test_app.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lastLine = end($lines) ?: '';
    echo "Последняя строка лога:<br>\n";
    echo "<pre>" . htmlspecialchars($lastLine, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "</pre><br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>2. LoggerService — обработка исключений</h3>\n";

try {
    $faultyLogger = new LoggerService(''); // пустой путь → исключение
    echo "❌ Не должно было дойти сюда<br>\n";
} catch (InvalidArgumentException $e) {
    echo "✅ Поймано исключение при пустом пути:<br>\n";
    echo "<code>" . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "</code><br>\n";
} catch (Throwable $e) {
    echo "❌ Неожиданное исключение: " . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>3. Composer info (демонстрация среды)</h3>\n";

// Дополнительно: покажем, что Monolog загружен
if (class_exists(\Monolog\Logger::class)) {
    echo "✅ Monolog\\Logger загружен (версия: ";
    // Определим версию через Composer (если composer.json доступен)
    if (file_exists('composer.lock')) {
        $lock = json_decode(file_get_contents('composer.lock'), true, 512, JSON_THROW_ON_ERROR);
        $monologVersion = null;
        foreach ($lock['packages'] as $pkg) {
            if ($pkg['name'] === 'monolog/monolog') {
                $monologVersion = $pkg['version'];
                break;
            }
        }
        echo htmlspecialchars($monologVersion ?? 'неизвестно', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    } else {
        echo 'composer.lock отсутствует';
    }
    echo ")<br>\n";
} else {
    echo "❌ Monolog не найден — проверьте composer install<br>\n";
}

// Очистим test-файл после тестов (опционально, но чисто)
if (file_exists('test_app.log')) {
    // Не удаляем полностью — пусть остаётся для проверки, но можно:
    // unlink('test_app.log');
}

// ----------------------------
// Примеры вызовов (можно раскомментировать для быстрой проверки)
// ----------------------------
/*
$logger = new \App\Service\LoggerService('my_custom.log');
$logger->log('Привет из лабы 13!');
// В логе будет: [2025-12-26T...+00:00] app.INFO: Привет из лабы 13! []
*/