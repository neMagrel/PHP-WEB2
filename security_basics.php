<?php

declare(strict_types=1);

/**
 * Запускает сессию с безопасными параметрами.
 *
 * @return void
 */
function secureSessionStart(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    $useSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', $useSecure ? '1' : '0');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_lifetime', '0');

    session_start();

    // Защита от session fixation
    if (!isset($_SESSION['_authenticated']) || !$_SESSION['_authenticated']) {
        session_regenerate_id(true);
    }
}

secureSessionStart();

/**
 * Проверяет email на корректность.
 *
 * @param string $email Строка email
 * @return string|null Возвращает email при успехе, иначе null
 */
function validateEmail(string $email): ?string
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
}

/**
 * Валидирует имя: кириллица/латиница/пробелы, длина 2–50.
 *
 * @param string $name Имя
 * @return string|null Возвращает имя при успехе, иначе null
 */
function validateName(string $name): ?string
{
    return preg_match('/^[a-zA-Zа-яА-ЯёЁ\s]{2,50}$/u', $name) ? $name : null;
}

/**
 * Валидирует возраст: целое от 1 до 120.
 *
 * @param int $age Возраст
 * @return int|null Возвращает возраст при успехе, иначе null
 */
function validateAge(int $age): ?int
{
    $opts = ['options' => ['min_range' => 1, 'max_range' => 120]];
    return filter_var($age, FILTER_VALIDATE_INT, $opts) !== false ? $age : null;
}

/**
 * Экранирует строку для HTML.
 *
 * @param string $text Исходный текст
 * @return string Экранированная строка
 */
function escapeHtml(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Генерирует и сохраняет CSRF-токен в сессии.
 *
 * @return string Новый токен
 */
function generateCsrfToken(): string
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

/**
 * Проверяет CSRF-токен безопасным сравнением.
 *
 * @param string $token Переданный токен
 * @return bool true при совпадении
 */
function validateCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Проверяет тип файла по MIME (только image/jpeg и image/png).
 *
 * @param string $tmpPath Путь ко временному файлу
 * @return bool true если тип разрешён
 */
function isValidImageFile(string $tmpPath): bool
{
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) {
        return false;
    }
    $type = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);
    return in_array($type, ['image/jpeg', 'image/png'], true);
}

/**
 * Генерирует безопасное имя файла с расширением .jpg/.png.
 *
 * @param string $originalName Исходное имя
 * @return string Имя вида xxx.jpg или пустая строка при ошибке
 */
function generateSafeFileName(string $originalName): string
{
    $lower = strtolower($originalName);
    $ext = '';
    if (str_ends_with($lower, '.jpg') || str_ends_with($lower, '.jpeg')) {
        $ext = '.jpg';
    } elseif (str_ends_with($lower, '.png')) {
        $ext = '.png';
    } else {
        return '';
    }
    return bin2hex(random_bytes(16)) . $ext;
}

/**
 * Проверяет размер файла (по умолчанию ≤1 МБ).
 *
 * @param int $size Размер в байтах
 * @param int $maxBytes Максимальный размер (по умолчанию 1048576)
 * @return bool true если в пределах
 */
function isFileSizeValid(int $size, int $maxBytes = 1048576): bool
{
    return $size > 0 && $size <= $maxBytes;
}

/**
 * Сохраняет загруженный файл вне корня веба.
 *
 * @param array $file Элемент из $_FILES
 * @param string $uploadDir Директория (например: __DIR__ . '/../uploads')
 * @return string|null Имя файла при успехе, иначе null
 */
function saveUploadedFile(array $file, string $uploadDir): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $tmp = $file['tmp_name'];
    if (!is_uploaded_file($tmp)) {
        return null;
    }
    if (!isFileSizeValid($file['size'])) {
        return null;
    }
    if (!isValidImageFile($tmp)) {
        return null;
    }
    $safeName = generateSafeFileName($file['name']);
    if ($safeName === '') {
        return null;
    }
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $target = $uploadDir . '/' . $safeName;
    return move_uploaded_file($tmp, $target) ? $safeName : null;
}

// ============== Тесты в стиле Magrel ==============

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>1. validateEmail</h3>\n";
foreach (['alice@example.com', 'bad@', 'user@domain', 'ВАЖНО@ДОМЕН.РФ'] as $email) {
    $res = validateEmail($email);
    echo "'" . escapeHtml($email) . "' → " . ($res !== null ? "'" . escapeHtml($res) . "'" : 'null') . "<br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>2. validateName</h3>\n";
foreach (['', 'A', 'Иван', 'John Doe', '123 Иван', 'Кириллица и English', str_repeat('x', 51)] as $name) {
    $res = validateName($name);
    echo "'" . escapeHtml($name) . "' → " . ($res !== null ? "'" . escapeHtml($res) . "'" : 'null') . "<br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>3. validateAge</h3>\n";
foreach ([0, 1, 25, 120, 121, -5] as $age) {
    $res = validateAge($age);
    echo "$age → " . ($res !== null ? "$res" : 'null') . "<br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>4. escapeHtml</h3>\n";
$danger = '<script>alert("XSS & \'quotes\'")</script>';
echo "Вход: " . escapeHtml($danger) . "<br>\n";
echo "Выход: " . escapeHtml(escapeHtml($danger)) . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>5. CSRF — generate + validate</h3>\n";
$token1 = generateCsrfToken();
echo "Токен 1: " . escapeHtml($token1) . "<br>\n";
echo "Валидация токена 1: " . (validateCsrfToken($token1) ? '✅' : '❌') . "<br>\n";
$token2 = generateCsrfToken();
echo "Токен 2 (новый): " . escapeHtml($token2) . "<br>\n";
echo "Валидация старого после регенерации: " . (validateCsrfToken($token1) ? '✅' : '❌') . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>6. isValidImageFile (имитация)</h3>\n";
$tmpPng = tempnam(sys_get_temp_dir(), 'test');
file_put_contents($tmpPng, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));
$tmpBad = tempnam(sys_get_temp_dir(), 'bad');
file_put_contents($tmpBad, 'not an image');
echo "PNG имитация → " . (isValidImageFile($tmpPng) ? '✅' : '❌') . "<br>\n";
echo "Некорректный файл → " . (isValidImageFile($tmpBad) ? '✅' : '❌') . "<br>\n";
unlink($tmpPng); unlink($tmpBad);

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>7. generateSafeFileName</h3>\n";
foreach (['photo.jpg', 'Фото.JPEG', 'image.PNG', 'bad.exe', 'noext', 'scan.jpg.png'] as $name) {
    $res = generateSafeFileName($name);
    echo "'" . escapeHtml($name) . "' → '" . escapeHtml($res) . "'<br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>8. isFileSizeValid</h3>\n";
foreach ([0, 1, 512000, 1048576, 1048577] as $sz) {
    $res = isFileSizeValid($sz);
    echo "$sz байт → " . ($res ? '✅' : '❌') . "<br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>9. saveUploadedFile — демонстрация (требует $_FILES)</h3>\n";
echo "⚠️ Тест через форму невозможен без HTML. См. пример ниже.<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>10. secureSessionStart</h3>\n";
echo "Сессия запущена: " . (session_status() === PHP_SESSION_ACTIVE ? '✅' : '❌') . "<br>\n";
echo "ID: " . escapeHtml(session_id()) . "<br>\n";
echo "cookie_httponly: " . escapeHtml(ini_get('session.cookie_httponly')) . "<br>\n";
echo "cookie_samesite: " . escapeHtml(ini_get('session.cookie_samesite')) . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>11. Полный сценарий — формально</h3>\n";
echo "Для реализации: используй POST с полями email, name, age, avatar + hidden csrf_token.<br>\n";
echo "Логика обработки — как в предыдущих лабах: валидация → saveUploadedFile → вывод через escapeHtml().<br>\n";

/*
// ----------------------------
// Примеры вызова (для ручной проверки)
// ----------------------------

secureSessionStart();
$token = generateCsrfToken();
var_dump(validateCsrfToken($token)); // true

// Валидации
var_dump(validateEmail('user@example.com')); // string
var_dump(validateName('Анна')); // string
var_dump(validateAge(30)); // int

// Экранирование
echo escapeHtml('<b>"XSS"</b>'); // &lt;b&gt;&quot;XSS&quot;&lt;/b&gt;

// Файлы (требуется реальный $_FILES['avatar'])
// $safeName = saveUploadedFile($_FILES['avatar'], __DIR__ . '/../uploads');
// echo $safeName; // 'a1b2c3...jpg' или null
*/