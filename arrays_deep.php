<?php

/**
 * Извлекает названия книг из массива.
 *
 * @param array<int, array<string, mixed>> $books Массив книг, каждая — ассоциативный массив с ключом 'title'
 * @return array<int, string> Массив названий
 */
function getBookTitles(array $books): array
{
    $titles = [];
    foreach ($books as $book) {
        if (isset($book['title']) && is_string($book['title'])) {
            $titles[] = $book['title'];
        }
    }
    return $titles;
}

/**
 * Проверяет наличие книги по автору (без учёта регистра).
 *
 * @param array<int, array<string, mixed>> $books Массив книг
 * @param string $author Имя автора для поиска
 * @return bool true, если хотя бы одна книга принадлежит указанному автору
 */
function hasBookByAuthor(array $books, string $author): bool
{
    foreach ($books as $book) {
        if (isset($book['author']) && is_string($book['author'])) {
            if (strcasecmp($book['author'], $author) === 0) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Добавляет год по умолчанию для книг, у которых он отсутствует или некорректен.
 *
 * @param array<int, array<string, mixed>> $books Массив книг
 * @param int $defaultYear Год по умолчанию
 * @return array<int, array<string, mixed>> Новый массив книг с установленными годами
 */
function addDefaultYear(array $books, int $defaultYear = 2025): array
{
    $result = [];
    foreach ($books as $book) {
        $book = $book; // копируем, чтобы не мутировать оригинал
        if (!isset($book['year']) || !is_int($book['year'])) {
            $book['year'] = $defaultYear;
        }
        $result[] = $book;
    }
    return $result;
}

/**
 * Фильтрует книги, оставляя только те, что выпущены строго после указанного года.
 *
 * @param array<int, array<string, mixed>> $books Массив книг
 * @param int $minYear Минимальный год (не включительно)
 * @return array<int, array<string, mixed>> Отфильтрованный массив
 */
function filterBooksByYear(array $books, int $minYear): array
{
    $result = [];
    foreach ($books as $book) {
        if (isset($book['year']) && is_int($book['year']) && $book['year'] > $minYear) {
            $result[] = $book;
        }
    }
    return $result;
}

/**
 * Преобразует книги в строки формата "Название (Автор, Год)".
 * Если год отсутствует — подставляется "неизвестен".
 *
 * @param array<int, array<string, mixed>> $books Массив книг
 * @return array<int, string> Массив строк-описаний
 */
function mapBooksToPairs(array $books): array
{
    $result = [];
    foreach ($books as $book) {
        $title   = $book['title']   ?? 'Без названия';
        $author  = $book['author']  ?? 'Неизвестен';
        $year    = isset($book['year']) && is_int($book['year'])
                   ? (string)$book['year']
                   : 'неизвестен';

        $result[] = "{$title} ({$author}, {$year})";
    }
    return $result;
}

/**
 * Сортирует книги по году по возрастанию, при равенстве — по названию (алфавитно).
 *
 * @param array<int, array<string, mixed>> $books Массив книг
 * @return array<int, array<string, mixed>> Отсортированный массив
 */
function sortBooks(array $books): array
{
    usort($books, static function ($a, $b): int {
        $yearA = $a['year'] ?? 0;
        $yearB = $b['year'] ?? 0;

        if ($yearA !== $yearB) {
            return $yearA <=> $yearB;
        }

        $titleA = $a['title'] ?? '';
        $titleB = $b['title'] ?? '';

        return strcmp($titleA, $titleB);
    });
    return $books;
}

/**
 * Группирует элементы по указанному ключу.
 * Элементы без указанного ключа — пропускаются.
 *
 * @param array<int, array<string, mixed>> $items Массив элементов
 * @param string $key Ключ для группировки
 * @return array<string|int, array<int, array<string, mixed>>> Ассоциативный массив групп
 */
function groupBy(array $items, string $key): array
{
    $groups = [];
    foreach ($items as $item) {
        if (array_key_exists($key, $item)) {
            $value = $item[$key];
            // Приводим ключ к строке или целому — как есть
            $groups[$value][] = $item;
        }
    }
    return $groups;
}

/**
 * Добавляет элемент в стек (конец массива).
 *
 * @param array $stack Стек (модифицируется по ссылке)
 * @param mixed $value Значение для добавления
 * @return void
 */
function stackPush(array &$stack, mixed $value): void
{
    $stack[] = $value;
}

/**
 * Извлекает верхний элемент из стека.
 *
 * @param array $stack Стек (модифицируется по ссылке)
 * @return mixed Значение элемента или null, если стек пуст
 */
function stackPop(array &$stack): mixed
{
    return empty($stack) ? null : array_pop($stack);
}

/**
 * Добавляет элемент в очередь (в конец).
 *
 * @param array $queue Очередь (модифицируется по ссылке)
 * @param mixed $value Значение для добавления
 * @return void
 */
function queueEnqueue(array &$queue, mixed $value): void
{
    $queue[] = $value;
}

/**
 * Извлекает первый элемент из очереди (FIFO).
 *
 * @param array $queue Очередь (модифицируется по ссылке)
 * @return mixed Значение элемента или null, если очередь пуста
 */
function queueDequeue(array &$queue): mixed
{
    return empty($queue) ? null : array_shift($queue);
}

/**
 * Безопасно извлекает значение из массива по ключу.
 *
 * @param array $array Исходный массив
 * @param string|int $key Ключ
 * @param mixed $default Значение по умолчанию
 * @return mixed Значение элемента или $default
 */
function safeGet(array $array, string|int $key, mixed $default = null): mixed
{
    return array_key_exists($key, $array) ? $array[$key] : $default;
}

/**
 * Проверяет, является ли массив ассоциативным.
 * Считается ассоциативным, если:
 *   - есть хотя бы один нечисловой ключ, ИЛИ
 *   - числовые ключи не образуют непрерывную последовательность от 0.
 *
 * @param array $array Проверяемый массив
 * @return bool true — если ассоциативный
 */
function isAssociative(array $array): bool
{
    if (empty($array)) {
        return false;
    }

    $keys = array_keys($array);

    // Проверяем наличие нечислового ключа
    foreach ($keys as $key) {
        if (!is_int($key)) {
            return true;
        }
    }

    // Если все ключи int — проверяем, последовательны ли они от 0
    $expected = range(0, count($array) - 1);
    return $keys !== $expected;
}

$books = [
    ['title' => '1984', 'author' => 'Джордж Оруэлл', 'year' => 1949],
    ['title' => 'Мастер и Маргарита', 'author' => 'Михаил Булгаков'], // без года
    ['title' => 'Гарри Поттер и философский камень', 'author' => 'Дж. К. Роулинг', 'year' => 1997],
    ['title' => 'Война и мир', 'author' => 'Лев Толстой', 'year' => 1869],
    ['title' => 'Dune', 'author' => 'Фрэнк Герберт', 'year' => 1965]
];

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>1. getBookTitles</h3>\n";
$titles = getBookTitles($books);
echo "Названия: [" . implode(', ', array_map(fn($t) => "'" . htmlspecialchars($t, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "'", $titles)) . "]<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>2. hasBookByAuthor (регистронезависимо)</h3>\n";
echo "Оруэлл (точно): " . (hasBookByAuthor($books, 'Джордж Оруэлл') ? '✅' : '❌') . "<br>\n";
echo "оруэлл (нижний регистр): " . (hasBookByAuthor($books, 'джордж оруэлл') ? '✅' : '❌') . "<br>\n";
echo "Пушкин (отсутствует): " . (hasBookByAuthor($books, 'А. С. Пушкин') ? '✅' : '❌') . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>3. addDefaultYear</h3>\n";
$booksWithYear = addDefaultYear($books, 2025);
echo "Книга без года → после addDefaultYear(2025):<br>\n";
$mm = $booksWithYear[1];
echo "'" . htmlspecialchars($mm['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') . "' → год = " . $mm['year'] . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>4. filterBooksByYear</h3>\n";
$after1950 = filterBooksByYear($books, 1950);
echo "Книги после 1950 г.:<br>\n";
foreach ($after1950 as $book) {
    echo "– " . htmlspecialchars($book['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') . " (" . $book['year'] . ")<br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>5. mapBooksToPairs</h3>\n";
$pairs = mapBooksToPairs($books);
foreach ($pairs as $pair) {
    echo htmlspecialchars($pair, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>6. sortBooks</h3>\n";
$sorted = sortBooks($books);
echo "После сортировки (по году ↑, затем по названию ↑):<br>\n";
foreach ($sorted as $book) {
    $year = $book['year'] ?? 'неизвестен';
    echo htmlspecialchars($book['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') . " (" . $year . ")<br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>7. groupBy (по автору)</h3>\n";
$byAuthor = groupBy($books, 'author');
echo "Группы по авторам (количество):<br>\n";
foreach ($byAuthor as $author => $group) {
    echo "'" . htmlspecialchars($author, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "' → " . count($group) . " книга(и)<br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>8. safeGet</h3>\n";
$firstBook = $books[0];
$title = safeGet($firstBook, 'title', '—');
$genre = safeGet($firstBook, 'genre', 'Не указан');
echo "title → '" . htmlspecialchars($title, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "'<br>\n";
echo "genre (отсутствует) → '" . htmlspecialchars($genre, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "'<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>9. isAssociative</h3>\n";
echo "['a','b','c'] → " . (isAssociative(['a','b','c']) ? '✅ ассоц.' : '❌ индекс.') . "<br>\n";
echo "[1=>'a', 2=>'b'] → " . (isAssociative([1=>'a', 2=>'b']) ? '✅ ассоц.' : '❌ индекс.') . "<br>\n";
echo "['key'=>'value'] → " . (isAssociative(['key'=>'value']) ? '✅ ассоц.' : '❌ индекс.') . "<br>\n";
echo "[] (пустой) → " . (isAssociative([]) ? '✅ ассоц.' : '❌ индекс.') . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>10. stackPush / stackPop</h3>\n";
$stack = [];
stackPush($stack, "первый");
stackPush($stack, "второй");
echo "stackPop() → '" . htmlspecialchars(stackPop($stack), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "' (должно быть: второй)<br>\n";
echo "stackPop() → '" . htmlspecialchars(stackPop($stack), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "' (должно быть: первый)<br>\n";
echo "stackPop() (пусто) → " . json_encode(stackPop($stack)) . " (должно быть: null)<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>11. queueEnqueue / queueDequeue</h3>\n";
$queue = [];
queueEnqueue($queue, "первый");
queueEnqueue($queue, "второй");
echo "queueDequeue() → '" . htmlspecialchars(queueDequeue($queue), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "' (первый)<br>\n";
echo "queueDequeue() → '" . htmlspecialchars(queueDequeue($queue), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "' (второй)<br>\n";
echo "queueDequeue() (пусто) → " . json_encode(queueDequeue($queue)) . " (null)<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>12. groupBy (по году, книги без года — пропущены)</h3>\n";
$byYear = groupBy($books, 'year');
echo "Количество групп по году: " . count($byYear) . " (из 5 книг, 1 без года → 4 группы)<br>\n";
foreach ($byYear as $year => $group) {
    echo "Год " . $year . ": " . count($group) . " книга<br>\n";
}

// ----------------------------
// Примеры вызовов (можно раскомментировать)
// ----------------------------
/*
$titles = getBookTitles($books);
var_dump(hasBookByAuthor($books, 'джордж оруэлл'));
$booksWithYear = addDefaultYear($books, 2025);
$after2000 = filterBooksByYear($books, 2000);
$pairs = mapBooksToPairs($books);
$sorted = sortBooks($books);
$byAuthor = groupBy($books, 'author');

// Стек и очередь
$stack = []; stackPush($stack, 'A'); stackPush($stack, 'B'); var_dump(stackPop($stack));
$queue = []; queueEnqueue($queue, 'X'); queueEnqueue($queue, 'Y'); var_dump(queueDequeue($queue));

// safeGet и isAssociative
var_dump(safeGet(['a' => 1], 'a', '—'), safeGet(['a' => 1], 'b', '—'));
var_dump(isAssociative([0 => 'a', 1 => 'b']), isAssociative(['x' => 1]));
*/