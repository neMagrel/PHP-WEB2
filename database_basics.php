<?php

/*
-- Создание базы данных и таблицы books
CREATE DATABASE IF NOT EXISTS library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library;
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255),
    isbn VARCHAR(20),
    pub_year INT,
    available TINYINT DEFAULT 1
);
*/

/**
 * Создаёт и возвращает подключение к БД с настройками безопасности.
 *
 * @param string $env Режим окружения: 'dev' или 'prod'
 * @return PDO Объект подключения
 * @throws PDOException При ошибке подключения
 */
function getPdoConnection(string $env = 'dev'): PDO
{
    $username = 'magrel';      
    $password = 'user123';
    $host = 'localhost';
    $dbname = 'library';

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        if ($env === 'dev') {
            die("<pre style='color:red;background:#ffecec;padding:10px;'>
❌ Ошибка подключения (DEV):
" . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "
</pre>");
        } else {
            error_log("[PROD DB ERROR] " . $e->getMessage());
            http_response_code(500);
            die("Внутренняя ошибка сервера.");
        }
    }
}

/**
 * Добавляет новую книгу в базу данных.
 *
 * @param string $title Название книги (обязательное)
 * @param string $author Автор книги
 * @param string $isbn ISBN книги (до 20 символов)
 * @param int $year Год издания
 * @return int ID вставленной книги
 */
function addBook(string $title, string $author, string $isbn, int $year): int
{
    $pdo = getPdoConnection('dev');
    $sql = "INSERT INTO books (title, author, isbn, pub_year) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $author, $isbn, $year]);
    return (int)$pdo->lastInsertId();
}

/**
 * Находит все книги по имени автора.
 *
 * @param string $author Имя автора (точное совпадение)
 * @return array Массив записей книг в формате ассоциативных массивов
 */
function findBooksByAuthor(string $author): array
{
    $pdo = getPdoConnection('dev');
    $stmt = $pdo->prepare("SELECT * FROM books WHERE author = ?");
    $stmt->execute([$author]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Возвращает все доступные книги (available = 1).
 *
 * @return array Массив записей книг
 */
function getAllAvailableBooks(): array
{
    $pdo = getPdoConnection('dev');
    $stmt = $pdo->query("SELECT * FROM books WHERE available = 1");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Обновляет статус доступности книги.
 *
 * @param int $bookId ID книги
 * @param bool $available true — доступна, false — недоступна
 * @return void
 */
function setBookAvailability(int $bookId, bool $available): void
{
    $pdo = getPdoConnection('dev');
    $stmt = $pdo->prepare("UPDATE books SET available = :available WHERE id = :bookId");
    $stmt->execute([
        ':available' => (int)$available,
        ':bookId'    => $bookId,
    ]);
}

/**
 * Перемещает указанное количество экземпляров между двумя книгами в рамках транзакции.
 *
 * ⚠️ Предполагается, что available может быть отрицательным (логика склада).
 *
 * @param int $fromId ID книги-источника
 * @param int $toId ID книги-приёмника
 * @param int $amount Количество для перемещения (должно быть > 0)
 * @return void
 * @throws Exception При ошибке выполнения запроса (транзакция откатывается)
 */
function transferStock(int $fromId, int $toId, int $amount): void
{
    if ($amount <= 0) {
        throw new InvalidArgumentException('Количество должно быть положительным');
    }

    $pdo = getPdoConnection('dev');
    $pdo->beginTransaction();

    try {
        $stmt1 = $pdo->prepare("UPDATE books SET available = available - :amount WHERE id = :fromId");
        $stmt1->execute([':amount' => $amount, ':fromId' => $fromId]);

        $stmt2 = $pdo->prepare("UPDATE books SET available = available + :amount WHERE id = :toId");
        $stmt2->execute([':amount' => $amount, ':toId' => $toId]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}

// === ПРОВЕРКА ПОДКЛЮЧЕНИЯ И СУЩЕСТВОВАНИЯ ТАБЛИЦЫ ===
$pdo = getPdoConnection('dev');

try {
    $stmt = $pdo->query("SELECT 1 AS test");
    $result = $stmt->fetch();
    if ($result && $result['test'] === 1) {
        echo "<p style='color: green; font-family: monospace;'>✅ Подключение к базе данных успешно!</p>";
    }
} catch (PDOException $e) {
    die("<p style='color: red;'>❌ Ошибка при тестовом запросе: " . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "</p>");
}

try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'books'");
    $tableExists = $stmt->fetch();
    if ($tableExists) {
        echo "<p style='color: green; font-family: monospace;'>✅ Таблица `books` найдена.</p>";
    } else {
        echo "<p style='color: orange; font-family: monospace;'>⚠️ Таблица `books` не найдена (её нужно создать).</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Ошибка при проверке таблицы: " . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "</p>";
}

// ----------------------------
// Тестирование (в стиле Magrel)
// ----------------------------
echo "<h2>Page by Magrel</h2>";

echo "<h3>1. Подключение и проверка таблицы</h3>";
// (уже выведено выше)

echo "<h2>Page by Magrel</h2>";

echo "<h3>2. Проверка устойчивости к SQL-инъекциям</h3>";
$maliciousAuthor = "' OR '1'='1";
$books = findBooksByAuthor($maliciousAuthor);
echo "<p>Поиск по автору: <code>" . htmlspecialchars($maliciousAuthor, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "</code></p>";
if (empty($books)) {
    echo "<p>✅ Нет результатов — инъекция заблокирована корректно.</p>";
} else {
    echo "<p>⚠️ Обнаружены книги (возможно, есть автор с таким именем):</p><ul>";
    foreach ($books as $book) {
        echo "<li>📖 ID {$book['id']}: <strong>" .
            htmlspecialchars($book['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') .
            "</strong> — " .
            htmlspecialchars($book['author'] ?? '—', ENT_QUOTES | ENT_HTML5, 'UTF-8') .
            "</li>";
    }
    echo "</ul>";
}

echo "<h2>Page by Magrel</h2>";

echo "<h3>3. Доступные книги (getAllAvailableBooks)</h3>";
$available = getAllAvailableBooks();
if (empty($available)) {
    echo "<p>📭 Нет доступных книг.</p>";
} else {
    echo "<ul>";
    foreach ($available as $book) {
        echo "<li>📚 ID {$book['id']}: <strong>" .
            htmlspecialchars($book['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') .
            "</strong> (" .
            htmlspecialchars($book['author'] ?? 'автор не указан', ENT_QUOTES | ENT_HTML5, 'UTF-8') .
            ") — " .
            ($book['available'] ? '✅ в наличии' : '❌ недоступна') .
            "</li>";
    }
    echo "</ul>";
}

echo "<h2>Page by Magrel</h2>";

echo "<h3>4. Демонстрация использования функций (закомментировано)</h3>";
echo "<pre style='background:#f4f4f4;padding:10px;'>";
echo "// \$id = addBook('Мастер и Маргарита', 'Михаил Булгаков', '978-5-17-114701-0', 1967);\n";
echo "// echo \"Добавлена книга с ID: \$id\";\n\n";
echo "// setBookAvailability(\$id, false); // Сделать недоступной\n\n";
echo "// \$books = findBooksByAuthor('Михаил Булгаков');\n";
echo "// print_r(\$books);\n\n";
echo "// transferStock(\$id, 1, 2); // Переместить 2 экз. с книги \$id на книгу с ID=1 (осторожно!)\n";
echo "</pre>";

// === Примеры вызовов (для быстрой проверки — раскомментируй при необходимости) ===

$id = addBook('Мастер и Маргарита', 'Михаил Булгаков', '978-5-17-114701-0', 1967);
echo "<p>Добавлена книга с ID: $id</p>";

setBookAvailability($id, false);
echo "<p>Книга ID=$id сделана недоступной</p>";

$books = findBooksByAuthor('Михаил Булгаков');
echo "<pre>" . htmlspecialchars(print_r($books, true), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "</pre>";

// Проверь transferStock — только если в таблице есть минимум 2 записи с available ≥ amount!
// transferStock($id, 1, 1);
// echo "<p>1 экз. перемещён с книги $id на книгу 1</p>";

?>