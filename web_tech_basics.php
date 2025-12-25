<?php
// 🔒 ВСЕГДА: сессия и куки — ДО ЛЮБОГО ВЫВОДА!
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------- Функции из заданий ----------

function dumpRequestInfo(): void {
    echo '<pre>';
    echo 'Метод запроса: ' . htmlspecialchars($_SERVER['REQUEST_METHOD']) . "\n";
    echo 'URI: ' . htmlspecialchars($_SERVER['REQUEST_URI']) . "\n";
    if (!empty($_GET)) {
        echo "GET-параметры:\n";
        echo htmlspecialchars(print_r($_GET, true)) . "\n";
    }
    if (!empty($_POST)) {
        echo "POST-параметры:\n";
        echo htmlspecialchars(print_r($_POST, true)) . "\n";
    }
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        echo 'Браузер: ' . htmlspecialchars($_SERVER['HTTP_USER_AGENT']) . "\n";
    }
    echo '</pre>';
}

function getRequestData(): array {
    $serverInfo = [
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? '',
        'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? '',
    ];
    if (isset($_SERVER['HTTPS'])) {
        $serverInfo['HTTPS'] = $_SERVER['HTTPS'];
    }
    return [
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        'get' => $_GET,
        'post' => $_POST,
        'server_info' => $serverInfo,
    ];
}

function setThemeCookie(string $theme): void {
    setcookie(
        'theme',                   // имя
        $theme,                    // значение
        [
            'expires' => time() + 3600,
            'secure' => false,     // ← для localhost ставим false
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
}

function getTheme(): string {
    return $_COOKIE['theme'] ?? "light";
}

function initSession(): void {
    // Уже запущена в начале
}

class SessionBag {
    public function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }
    public function get(string $key, mixed $default = null): mixed {
        return $_SESSION[$key] ?? $default;
    }
    public function has(string $key): bool {
        return isset($_SESSION[$key]);
    }
    public function remove(string $key): void {
        unset($_SESSION[$key]);
    }
}

function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function safeOutput(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function generateCsrfToken(): string {
    $token = bin2hex(random_bytes(32));
    $_SESSION['token'] = $token;
    return $token;
}

function validateCsrfToken(string $token): bool {
    return isset($_SESSION['token']) && hash_equals($token, $_SESSION['token']);
}

function rotateSessionID(): void {
    session_regenerate_id(true); // true = удалить старую сессию
}

class ShoppingCart {
    public function addItem(array $item): void {
        if (!isset($item['id']) || !isset($item['name']) || !isset($item['price'])) {
            throw new \RuntimeException('Недостаточно параметров (нужны id, name, price)');
        }
        $_SESSION['cart'][] = $item;
    }
    public function getItems(): array {
        return $_SESSION['cart'] ?? [];
    }
    public function clear(): void {
        unset($_SESSION['cart']);
    }
}

// ---------- НОВОЕ ЗАДАНИЕ: Форма входа ----------
$loginError = '';

if ($_POST['action'] ?? null === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!validateEmail($email)) {
        $loginError = 'Неверный email';
    } elseif ($password !== 'secret') {
        $loginError = 'Неверный пароль';
    } else {
        rotateSessionID();
        $_SESSION['user_id'] = 123;
        $_SESSION['email'] = $email;
        // Перенаправляем, чтобы избежать повторной отправки
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

if ($_POST['action'] ?? null === 'logout') {
    session_destroy();
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// ---------- Гостевая книга ----------
if (!isset($_SESSION['comments'])) {
    $_SESSION['comments'] = [];
}
$comment = '';
if ($_POST['comment'] ?? false) {
    $comment = trim($_POST['comment']);
    if ($comment !== '') {
        $_SESSION['comments'][] = $comment;
    }
}

// ---------- Примеры использования ----------
$bag = new SessionBag();
$bag->set('lang', 'ru');
$cart = new ShoppingCart();
$cart->clear();
$cart->addItem(['id' => 1, 'name' => 'Тест', 'price' => 99]);

// ---------- Куки: установим тему ----------
setThemeCookie('dark');

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Демо-страница</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background: #f9f9f9; }
        pre { background: #eee; padding: 10px; border-radius: 4px; }
        .error { color: red; }
        .success { color: green; }
        form { margin: 15px 0; padding: 15px; background: white; border-radius: 6px; }
        input, button { padding: 6px; margin: 4px; }
    </style>
</head>
<body>

<h1>🛠️ Демонстрация всех функций</h1>

<!-- 1. dumpRequestInfo -->
<h2>1. Информация о запросе</h2>
<?php dumpRequestInfo(); ?>

<?php initSession(); ?>

<!-- 2. getRequestData -->
<h2>2. Данные запроса через getRequestData()</h2>
<pre><?= htmlspecialchars(print_r(getRequestData(), true)) ?></pre>

<!-- 3. Формы -->
<h2>3. Формы (GET и POST)</h2>
<?php
$search = $_GET['search'] ?? '';
$message = $_POST['message'] ?? '';
if ($search !== '') echo '<p>Вы искали: <strong>' . safeOutput($search) . '</strong></p>';
if ($message !== '') echo '<p>Ваше сообщение: <strong>' . safeOutput($message) . '</strong></p>';
?>

<form method="GET">
    <label>Поиск: <input type="text" name="search" value="<?= safeOutput($search) ?>"></label>
    <button>Найти</button>
</form>

<form method="POST">
    <label>Сообщение: <input type="text" name="message" value="<?= safeOutput($message) ?>"></label>
    <button>Отправить</button>
</form>

<!-- 4. Гостевая книга -->
<h2>4. Гостевая книга</h2>
<form method="POST">
    <label>Комментарий: <input type="text" name="comment" value="<?= safeOutput($comment) ?>"></label>
    <button>Отправить</button>
</form>
<?php if (!empty($_SESSION['comments'])): ?>
    <ul>
        <?php foreach ($_SESSION['comments'] as $cmt): ?>
            <li><?= safeOutput($cmt) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- 5. Форма входа (НОВОЕ ЗАДАНИЕ) -->
<h2>5. Форма входа</h2>
<?php if (!empty($_SESSION['email'])): ?>
    <p class="success">Здравствуйте, <?= safeOutput($_SESSION['email']) ?>!</p>
    <form method="POST">
        <input type="hidden" name="action" value="logout">
        <button type="submit">Выход</button>
    </form>
<?php else: ?>
    <?php if ($loginError): ?>
        <p class="error"><?= safeOutput($loginError) ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="hidden" name="action" value="login">
        <label>Email: <input type="email" name="email" required></label><br>
        <label>Пароль: <input type="password" name="password" required></label><br>
        <button type="submit">Войти</button>
    </form>
    <p><small>Правильный пароль: <code>secret</code></small></p>
<?php endif; ?>

<!-- 6. Примеры использования классов -->
<h2>6. Примеры использования</h2>
<ul>
    <li>SessionBag: lang = <?= safeOutput($bag->get('lang')) ?></li>
    <li>Тема из куки: <?= safeOutput(getTheme()) ?></li>
    <li>Товаров в корзине: <?= count($cart->getItems()) ?></li>
    <li>CSRF-токен сгенерирован: <?= validateCsrfToken(generateCsrfToken()) ? 'да' : 'нет' ?></li>
</ul>

</body>
</html>