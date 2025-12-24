<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ----------------------------
// 1. Создание строк (heredoc / nowdoc)
// ----------------------------

/**
 * Генерирует HTML-шаблон письма с использованием heredoc и интерполяцией переменных.
 *
 * @param string $name    Имя отправителя
 * @param string $product Наименование продукта
 * @return string HTML-код письма
 */
function generateEmailTemplate(string $name, string $product): string
{
    return <<<HTML
<!DOCTYPE html>
<html>
<head><title>Письмо</title></head>
<body>
    <h1>Добрый день. Меня зовут $name</h1>
    <p>Я пишу вам по поводу $product</p>
</body>
</html>
HTML;
}

/**
 * Возвращает пример nowdoc-строки (для демонстрации — без интерполяции).
 *
 * @return string nowdoc-строка
 */
function getNowdocExample(): string
{
    return <<<'NOWDOC'
Пример nowdoc: переменные, такие как $name, не интерполируются.
NOWDOC;
}


// ----------------------------
// 2. Длина и доступ к символам (Unicode)
// ----------------------------

/**
 * Возвращает первый и последний символ строки с поддержкой Unicode.
 *
 * @param string $str Входная строка
 * @return array Ассоциативный массив ['first' => ..., 'last' => ...]
 */
function getFirstAndLastChar(string $str): array
{
    if ($str === '') {
        return ['first' => '', 'last' => ''];
    }
    $first = mb_substr($str, 0, 1, 'UTF-8');
    $last  = mb_substr($str, -1, 1, 'UTF-8');
    return ['first' => $first, 'last' => $last];
}


// ----------------------------
// 3. Конкатенация и очистка строк
// ----------------------------

/**
 * Объединяет имя и фамилию в полное имя, удаляя лишние пробелы.
 *
 * @param string $first Имя
 * @param string $last  Фамилия
 * @return string Полное имя вида "Имя Фамилия"
 */
function buildFullName(string $first, string $last): string
{
    return trim($first) . ' ' . trim($last);
}


// ----------------------------
// 4. Изменение регистра (Unicode)
// ----------------------------

/**
 * Приводит каждое слово в строке к заглавной первой букве (Unicode-совместимо).
 *
 * @param string $phrase Исходная фраза
 * @return string Фраза в стиле Title Case
 */
function toTitleCase(string $phrase): string
{
    $words = explode(' ', $phrase);
    $result = [];
    foreach ($words as $word) {
        if ($word === '') {
            $result[] = '';
            continue;
        }
        $firstChar = mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8');
        $rest = mb_strtolower(mb_substr($word, 1, null, 'UTF-8'), 'UTF-8');
        $result[] = $firstChar . $rest;
    }
    return implode(' ', $result);
}


// ----------------------------
// 5. Поиск и извлечение подстрок
// ----------------------------

/**
 * Извлекает имя файла из полного пути.
 *
 * @param string $path Путь к файлу (например, "/home/user/file.txt")
 * @return string Имя файла (например, "file.txt")
 */
function extractFileName(string $path): string
{
    $lastSlashPos = strrpos($path, '/');
    if ($lastSlashPos === false) {
        return $path;
    }
    return substr($path, $lastSlashPos + 1);
}


// ----------------------------
// 6. Разбиение и сборка строк
// ----------------------------

/**
 * Объединяет массив тегов в строку CSV (через запятую и пробел).
 *
 * @param array<string> $tags Массив тегов
 * @return string Строка вида "tag1, tag2, tag3"
 */
function tagListToCSV(array $tags): string
{
    return implode(', ', $tags);
}

/**
 * Преобразует CSV-строку в массив тегов, удаляя пробелы вокруг запятых.
 *
 * @param string $csv Строка вида "php, html , css"
 * @return array<string> Массив тегов
 */
function csvToTagList(string $csv): array
{
    if ($csv === '') {
        return [];
    }
    return array_map('trim', explode(',', $csv));
}


// ----------------------------
// 7. Экранирование для HTML
// ----------------------------

/**
 * Безопасно экранирует строку для вывода в HTML.
 *
 * @param string $userInput Входная строка от пользователя
 * @return string Экранированная строка
 */
function safeEcho(string $userInput): string
{
    return htmlspecialchars($userInput, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}


// ----------------------------
// 8. Кодирование для URL
// ----------------------------

/**
 * Формирует корректный URL поиска с закодированным параметром запроса.
 *
 * @param string $query Поисковый запрос
 * @return string URL вида "https://example.com/search?q=..."
 */
function buildSearchUrl(string $query): string
{
    return 'https://example.com/search?q=' . rawurlencode($query);
}


// ----------------------------
// 9. Регулярные выражения: валидация пароля
// ----------------------------

/**
 * Проверяет, соответствует ли пароль требованиям:
 * – не менее 8 символов,
 * – содержит хотя бы одну заглавную букву и одну цифру.
 *
 * @param string $pass Пароль
 * @return bool true, если пароль валиден
 */
function validatePassword(string $pass): bool
{
    if (strlen($pass) < 8) {
        return false;
    }
    // Используем упреждающие проверки: (?=.*[A-Z]) и (?=.*\d)
    // Флаг /u не нужен — работаем с ASCII-ограничениями.
    return (bool) preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/u', $pass);
}


// ----------------------------
// 10. Регулярные выражения: извлечение данных
// ----------------------------

/**
 * Извлекает все email-адреса из текста.
 *
 * @param string $text Текст, возможно содержащий email-адреса
 * @return array<string> Массив найденных email-адресов
 */
function extractEmails(string $text): array
{
    // Базовый шаблон: \b[\w._%+-]+@[\w.-]+\.[a-zA-Z]{2,}\b
    // Флаг /i — без учёта регистра, /u — для Unicode (имена могут содержать кириллицу)
    preg_match_all('/\b[\w._%+-]+@[\w.-]+\.[a-zA-Z]{2,}\b/iu', $text, $matches);
    return $matches[0];
}


// ----------------------------
// 11. Регулярные выражения: замена
// ----------------------------

/**
 * Оборачивает целые и десятичные числа в <span class="number ...">...</span>.
 * Обработка: сначала десятичные (иначе 3.14 → 3 + .14), затем целые.
 *
 * @param string $text Исходный текст
 * @return string Текст с подсвеченными числами
 */
function highlightNumbers(string $text): string
{
    // Десятичные: могут быть с +/–, с точкой, без цифр до/после точки (но не одни точка)
    $text = preg_replace('/[-+]?(?:\d*\.\d+|\d+\.\d*)/', '<span class="number decimal">$0</span>', $text);
    // Целые: только если не часть десятичного (т.е. не окружены точкой)
    $text = preg_replace('/(?<!\.)\b[-+]?\d+\b(?!\.)/', '<span class="number integer">$0</span>', $text);
    return $text;
}


// ----------------------------
// Тестирование (в стиле Magrel)
// ----------------------------

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>1. generateEmailTemplate / getNowdocExample</h3>\n";
echo "HTML-шаблон:<br>\n";
echo htmlspecialchars(generateEmailTemplate("Анна", "курс по регулярным выражениям"), ENT_QUOTES | ENT_HTML5, 'UTF-8'), "<br>\n";
echo "Nowdoc-пример:<br>\n";
echo htmlspecialchars(getNowdocExample(), ENT_QUOTES | ENT_HTML5, 'UTF-8'), "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>2. getFirstAndLastChar</h3>\n";
foreach (["", "A", "Привет", "😊🚀", "Кириллица и emoji 😊"] as $test) {
    $res = getFirstAndLastChar($test);
    echo htmlspecialchars($test, ENT_QUOTES | ENT_HTML5, 'UTF-8') . " → "
        . htmlspecialchars(json_encode($res, JSON_UNESCAPED_UNICODE), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>3. buildFullName</h3>\n";
echo buildFullName("   Иван   ", "   Петров   "), "<br>\n";
echo buildFullName("  ", "  "), "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>4. toTitleCase</h3>\n";
foreach ([
    "просто текст",
    "ЭТО ПОЛНОСТЬЮ В ВЕРХНЕМ РЕГИСТРЕ",
    "смЕшАнНыЙ РеГиСтР",
    "кириллица и ENGLISH",
    "😊  привет  мир  🚀"
] as $test) {
    echo htmlspecialchars($test, ENT_QUOTES | ENT_HTML5, 'UTF-8') . " → "
        . htmlspecialchars(toTitleCase($test), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>5. extractFileName</h3>\n";
foreach ([
    "/var/www/index.php",
    "file.txt",
    "/",
    "",
    "C:\\Users\\file.txt" // не обрабатываем \ — по условию только /
] as $path) {
    echo htmlspecialchars($path, ENT_QUOTES | ENT_HTML5, 'UTF-8') . " → "
        . htmlspecialchars(extractFileName($path), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>6. tagListToCSV / csvToTagList</h3>\n";
$tags = ["php", "html", "css"];
$csv = tagListToCSV($tags);
echo "Теги → CSV: [" . implode(', ', array_map(fn($s) => "'$s'", $tags)) . "] → '$csv'<br>\n";
$restored = csvToTagList("php,  html , css  , ");
echo "CSV → теги: '$csv' → [" . implode(', ', array_map(fn($s) => "'$s'", $restored)) . "]<br>\n";
echo "Пустая строка → " . json_encode(csvToTagList("")) . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>7. safeEcho</h3>\n";
$danger = '<script>alert("XSS")</script>';
echo "Опасный ввод: " . htmlspecialchars($danger, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
echo "Экранированный вывод: " . safeEcho($danger) . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>8. buildSearchUrl</h3>\n";
$query = "hello & привет!";
echo "Запрос: " . htmlspecialchars($query, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
echo "URL: " . htmlspecialchars(buildSearchUrl($query), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>9. validatePassword</h3>\n";
foreach ([
    "Short1" => false,
    "LongEnough1" => true,
    "longenough1" => false,
    "LONGENOUGH" => false,
    "ValidPass1" => true,
    "ВалидныйПароль1" => false, // нет латинской заглавной
] as $pass => $expected) {
    $result = validatePassword($pass);
    echo "'" . htmlspecialchars($pass, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "' → "
        . ($result ? '✅ валиден' : '❌ не валиден') . " (ожидалось: " . ($expected ? '✅' : '❌') . ")<br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>10. extractEmails</h3>\n";
$text = "Контакты: user@example.com, admin@site.org, а также invalid.email, test@.ru и ВАЖНО@ДОМЕН.РФ";
$emails = extractEmails($text);
echo "Текст: " . htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
echo "Email-адреса: [" . implode(', ', array_map(fn($e) => "'" . htmlspecialchars($e, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "'", $emails)) . "]<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>11. highlightNumbers</h3>\n";
$text = "Цены: -5, +3.14, 0, .5, 10., и текст 2025 год.";
$highlighted = highlightNumbers($text);
echo "Исходный текст: " . htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
echo "С подсветкой: $highlighted<br>\n";

// ----------------------------
// Примеры вызовов (можно раскомментировать для быстрой проверки)
// ----------------------------
/*
echo generateEmailTemplate("Имя", "продукт");
var_dump(getFirstAndLastChar("😊"));
echo buildFullName("  Иван  ", "  Иванов  ");
echo toTitleCase("это ПрИмер");
echo extractFileName("/a/b/c.txt");
echo tagListToCSV(["php", "web"]);
var_dump(csvToTagList("php, web , test "));
echo safeEcho("<b>bold</b>");
echo buildSearchUrl("query с пробелом");
var_dump(validatePassword("Valid123"));
var_dump(extractEmails("user@site.com, another@test.org"));
echo highlightNumbers("Цена: 99.95 руб.");
*/