<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

/**
 * xml_json_basics.php — лабораторная по работе с XML и JSON.
 *
 * Реализует:
 * - создание/чтение books.xml
 * - парсинг SimpleXML → массив
 * - HTML-вывод таблицы
 * - REST API /api/books.json
 * - приём JSON/XML от клиента
 * - рекурсивное преобразование XML → массив
 * - защиту от XXE
 *
 * @author Magrel
 * @version 1.0
 */

// ——————————————————————————————
// Защита от XXE (актуально для PHP < 8.0)
// ——————————————————————————————
if (PHP_VERSION_ID < 80000) {
    libxml_disable_entity_loader(true);
}
libxml_use_internal_errors(true);

// ——————————————————————————————
// Задание 1: Создание books.xml (если не существует)
// ——————————————————————————————
$booksXmlPath = __DIR__ . '/books.xml';
if (!file_exists($booksXmlPath)) {
    $xmlContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<catalog>
  <book isbn="978-5-4461-1488-7">
    <title>Создаем динамические веб-сайты на PHP</title>
    <authors>
      <author>Кевин Татро</author>
      <author>Питер Макинтайр</author>
    </authors>
  </book>
  <book isbn="978-5-97060-569-1">
    <title>PHP и MySQL. Искусство программирования</title>
    <authors>
      <author>Люк Веллинг</author>
      <author>Лора Томсон</author>
    </authors>
  </book>
  <book isbn="978-5-4461-1972-1">
    <title>Изучаем PHP 8</title>
    <authors>
      <author>Робин Никсон</author>
    </authors>
  </book>
</catalog>
XML;
    file_put_contents($booksXmlPath, $xmlContent);
}

// ——————————————————————————————
// Задание 4: Класс Book с JsonSerializable
// ——————————————————————————————
/**
 * Представление книги с поддержкой сериализации в JSON.
 */
class Book implements JsonSerializable
{
    /**
     * @param string $isbn ISBN книги
     * @param string $title Название книги
     * @param string[] $authors Список авторов
     */
    public function __construct(
        public string $isbn,
        public string $title,
        public array $authors
    ) {}

    /**
     * Сериализует объект в массив для JSON.
     *
     * @return array{
     *     isbn: string,
     *     title: string,
     *     authors: string[]
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'isbn' => $this->isbn,
            'title' => $this->title,
            'authors' => $this->authors,
        ];
    }
}

// ——————————————————————————————
// Задание 2: Парсинг XML через SimpleXML
// ——————————————————————————————
/**
 * Загружает список книг из XML-файла.
 *
 * @param string $filename Путь к XML-файлу
 * @return array<int, array{isbn: string, title: string, authors: string[]}> Массив книг
 * @throws RuntimeException При отсутствии или некорректности файла
 */
function loadBooksFromXml(string $filename): array
{
    if (!file_exists($filename)) {
        throw new RuntimeException("File not found: $filename", 500);
    }

    $xml = simplexml_load_file($filename);
    if ($xml === false) {
        $errors = libxml_get_errors();
        libxml_clear_errors();
        $msg = "XML parse error in $filename";
        throw new RuntimeException($msg, 500);
    }

    $books = [];
    foreach ($xml->book as $book) {
        $authors = [];
        foreach ($book->authors->author as $author) {
            $authors[] = (string)$author;
        }
        $books[] = [
            'isbn' => (string)$book['isbn'],
            'title' => (string)$book->title,
            'authors' => $authors,
        ];
    }

    return $books;
}

// ——————————————————————————————
// Задание 3: Вывод книг в HTML-таблице
// ——————————————————————————————
/**
 * Выводит переданный список книг в виде HTML-таблицы.
 *
 * Все данные экранируются через htmlspecialchars().
 *
 * @param array<int, array{isbn: string, title: string, authors: string[]}> $books Список книг
 * @return void
 */
function renderBooksAsHtmlTable(array $books): void
{
    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse; margin:20px 0;'>";
    echo "<thead><tr><th>ISBN</th><th>Название</th><th>Авторы</th></tr></thead><tbody>";
    foreach ($books as $book) {
        $isbn = htmlspecialchars($book['isbn'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
        $title = htmlspecialchars($book['title'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
        $authors = htmlspecialchars(implode(', ', $book['authors']), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
        echo "<tr><td>$isbn</td><td>$title</td><td>$authors</td></tr>";
    }
    echo "</tbody></table>";
}

// ——————————————————————————————
// Задание 6: Приём JSON от клиента
// ——————————————————————————————
/**
 * Получает и парсит JSON из тела запроса.
 *
 * При ошибке парсинга отправляет HTTP 400 и возвращает null.
 *
 * @return array|null Раскодированный массив или null
 */
function getJsonInput(): ?array
{
    $input = file_get_contents('php://input');
    if ($input === false) {
        http_response_code(400);
        return null;
    }

    $data = json_decode($input, true, 512, JSON_THROW_ON_ERROR);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        return null;
    }

    return $data;
}

// ——————————————————————————————
// Задание 7: Приём XML от клиента
// ——————————————————————————————
/**
 * Получает и парсит XML из тела запроса.
 *
 * Использует libxml_disable_entity_loader при PHP < 8.0.
 * При ошибке парсинга отправляет HTTP 400 и возвращает null.
 *
 * @return SimpleXMLElement|null Распарсенный XML или null
 */
function getXmlInput(): ?SimpleXMLElement
{
    $input = file_get_contents('php://input');
    if ($input === false) {
        http_response_code(400);
        return null;
    }

    $xml = simplexml_load_string($input);
    if ($xml === false) {
        libxml_clear_errors();
        http_response_code(400);
        return null;
    }

    return $xml;
}

// ——————————————————————————————
// Задание 8: Преобразование XML в массив (рекурсивно)
// ——————————————————————————————
/**
 * Рекурсивно преобразует SimpleXMLElement в ассоциативный массив.
 *
 * Узлы без дочерних элементов возвращаются как строки.
 * Узлы с дочерними — как вложенные массивы.
 * Повторяющиеся имена → массив значений.
 *
 * @param SimpleXMLElement $xml Узел XML
 * @return array<string, mixed> Ассоциативный массив
 */
function xmlToArray(SimpleXMLElement $xml): array
{
    $result = [];

    foreach ($xml->children() as $name => $child) {
        $childArray = xmlToArray($child);
        $value = count($child->children()) === 0
            ? (string)$child
            : $childArray;

        if (isset($result[$name])) {
            if (!is_array($result[$name]) || !isset($result[$name][0])) {
                $result[$name] = [$result[$name]];
            }
            $result[$name][] = $value;
        } else {
            $result[$name] = $value;
        }
    }

    return $result;
}

// ——————————————————————————————
// Задание 5: API-эндпоинт /api/books.json
// ——————————————————————————————
if ($_SERVER['REQUEST_URI'] === '/api/books.json') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $books = loadBooksFromXml(__DIR__ . '/books.xml');
        $bookObjects = array_map(fn($b) => new Book($b['isbn'], $b['title'], $b['authors']), $books);
        echo json_encode($bookObjects, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    } catch (RuntimeException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal Server Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    } catch (JsonException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'JSON encode error'], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// ——————————————————————————————
// Вспомогательная функция для безопасного вывода
// ——————————————————————————————
/**
 * Экранирует строку для HTML-вывода.
 *
 * @param string $s Исходная строка
 * @return string Экранированная строка
 */
function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

// ——————————————————————————————
// Основной вывод (HTML-страница)
// ——————————————————————————————
try {
    $books = loadBooksFromXml(__DIR__ . '/books.xml');
} catch (RuntimeException $e) {
    http_response_code(500);
    exit('<h2>Page by Magrel</h2><p style="color:red;">Ошибка загрузки XML: ' . h($e->getMessage()) . '</p>');
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>XML ↔ JSON — Лабораторная Magrel</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; background: #fafafa; }
        h1, h2 { color: #2c3e50; }
        table { width: 100%; max-width: 900px; margin: 20px auto; border-collapse: collapse; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.08); }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        pre { background: #2d2d2d; color: #f8f8f2; padding: 16px; border-radius: 6px; overflow-x: auto; margin: 15px 0; }
        .note { background: #e8f4fc; padding: 14px; border-left: 4px solid #3498db; margin: 20px 0; }
    </style>
</head>
<body>
<h1>📚 Лабораторная: XML и JSON</h1>

<?php renderBooksAsHtmlTable($books); ?>

<div class="note">
    <strong>💡 API:</strong> Открой <a href="/api/books.json" target="_blank"><code>/api/books.json</code></a> для получения JSON.
</div>

<?php
// ----------------------------
// Тестирование (в стиле Magrel)
// ----------------------------
echo "<h2>Page by Magrel</h2>\n";
echo "<h3>1. loadBooksFromXml() → массив</h3>\n";
echo "<pre>" . h(print_r($books, true)) . "</pre>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>2. Book + jsonSerialize()</h3>\n";
$bookObjects = array_map(fn($b) => new Book($b['isbn'], $b['title'], $b['authors']), $books);
$jsonExample = json_encode($bookObjects[0], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
echo "<pre>" . h($jsonExample) . "</pre>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>3. xmlToArray() — рекурсивное преобразование</h3>\n";
$xmlRaw = simplexml_load_file(__DIR__ . '/books.xml');
$converted = xmlToArray($xmlRaw);
echo "<pre>" . h(print_r($converted, true)) . "</pre>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>4. getJsonInput() / getXmlInput() — заглушка для POST</h3>\n";
echo "Для теста используй:<br>\n";
echo "<code>curl -X POST -H 'Content-Type: application/json' -d '{\"test\":1}' http://yoursite/xml_json_basics.php</code><br>\n";
echo "<code>curl -X POST -H 'Content-Type: application/xml' -d '&lt;data&gt;&lt;val&gt;42&lt;/val&gt;&lt;/data&gt;' http://yoursite/xml_json_basics.php</code><br>\n";
echo "(в коде функции вернут null из-за отсутствия обработчиков — но безопасность и парсинг работают)\n";

// ----------------------------
// Примеры вызовов (закомментировано — как в ТЗ)
// ----------------------------
/*
// Пример 1: загрузка + вывод
$books = loadBooksFromXml('books.xml');
renderBooksAsHtmlTable($books);

// Пример 2: API — имитация вызова
$_SERVER['REQUEST_URI'] = '/api/books.json';
// → перейдёт в блок API и завершится через exit()

// Пример 3: приём JSON
$_POST = []; // важно: json идёт в php://input, не в $_POST
// Передать через curl:
// curl -X POST -H "Content-Type: application/json" -d '{"isbn":"123","title":"Test","authors":["Magrel"]}' http://localhost/xml_json_basics.php
$data = getJsonInput();
if ($data) {
    var_dump($data);
}

// Пример 4: приём XML
// curl -X POST -H "Content-Type: application/xml" -d '<book><title>Test</title></book>' http://localhost/xml_json_basics.php
$xml = getXmlInput();
if ($xml) {
    echo h($xml->title);
}

// Пример 5: рекурсивный xmlToArray
$xml = simplexml_load_string('<root><a><b>1</b><b>2</b></a><c>text</c></root>');
print_r(xmlToArray($xml));
// → ['a' => ['b' => ['1', '2']], 'c' => 'text']
*/
?>

</body>
</html>