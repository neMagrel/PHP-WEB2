<?php
// 🔒 ВСЕГДА: сессия и куки — ДО ЛЮБОГО ВЫВОДА!
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --------------------------------------------------------------
// Задание 1. Анализ HTTP-запроса
// --------------------------------------------------------------

/**
 * Выводит информацию о текущем HTTP-запросе в HTML-формате.
 *
 * Выводит:
 * - метод запроса (GET/POST и т.д.)
 * - URI
 * - GET/POST-параметры (если есть)
 * - User-Agent браузера
 *
 * Все данные экранируются через htmlspecialchars().
 *
 * @return void
 */
function dumpRequestInfo(): void
{
    echo '<pre>';
    echo 'Метод запроса: ' . htmlspecialchars($_SERVER['REQUEST_METHOD'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\n";
    echo 'URI: ' . htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\n";

    if (!empty($_GET)) {
        echo "GET-параметры:\n";
        echo htmlspecialchars(print_r($_GET, true), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\n";
    }

    if (!empty($_POST)) {
        echo "POST-параметры:\n";
        echo htmlspecialchars(print_r($_POST, true), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\n";
    }

    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        echo 'Браузер: ' . htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\n";
    }
    echo '</pre>';
}

// --------------------------------------------------------------
// Задание 2. Работа с суперглобальными массивами
// --------------------------------------------------------------

/**
 * Возвращает структурированные данные о текущем HTTP-запросе.
 *
 * @return array Ассоциативный массив с ключами:
 *   - 'method': string — метод запроса
 *   - 'get': array — копия $_GET
 *   - 'post': array — копия $_POST
 *   - 'server_info': array — данные сервера (HTTP_HOST, SERVER_NAME, HTTPS)
 */
function getRequestData(): array
{
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

// --------------------------------------------------------------
// Задание 4. Cookies: установка и чтение
// --------------------------------------------------------------

/**
 * Устанавливает cookie 'theme' на 1 час.
 *
 * Параметры cookie:
 * - secure = false (для локального тестирования)
 * - httponly = true
 * - samesite = 'Lax'
 *
 * @param string $theme Название темы (например, 'light', 'dark')
 * @return void
 */
function setThemeCookie(string $theme): void
{
    setcookie(
        'theme',
        $theme,
        [
            'expires' => time() + 3600,
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

/**
 * Возвращает текущую тему из cookie или 'light' по умолчанию.
 *
 * @return string Тема (например, 'light', 'dark')
 */
function getTheme(): string
{
    return $_COOKIE['theme'] ?? 'light';
}

// --------------------------------------------------------------
// Задание 5. Сессии: инициализация и использование
// --------------------------------------------------------------

/**
 * Инициализирует сессию, если она ещё не запущена.
 *
 * Безопасно вызывать многократно.
 *
 * @return void
 */
function initSession(): void
{
    // Уже инициализировано в начале файла, оставлено для соответствия ТЗ
}

/**
 * Обёртка над $_SESSION для удобной работы с данными сессии.
 */
class SessionBag
{
    /**
     * Сохраняет значение в сессию по ключу.
     *
     * @param string $key Ключ
     * @param mixed $value Значение
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Получает значение из сессии по ключу.
     *
     * @param string $key Ключ
     * @param mixed $default Значение по умолчанию, если ключа нет
     * @return mixed Значение или $default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Проверяет наличие ключа в сессии.
     *
     * @param string $key Ключ
     * @return bool true, если ключ существует
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Удаляет ключ из сессии.
     *
     * @param string $key Ключ
     * @return void
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }
}

// --------------------------------------------------------------
// Задание 6. Безопасная валидация входных данных
// --------------------------------------------------------------

/**
 * Валидирует email-адрес с помощью filter_var().
 *
 * @param string $email Проверяемый email
 * @return bool true, если email валиден
 */
function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Экранирует строку для безопасного HTML-вывода.
 *
 * Использует ENT_QUOTES | ENT_SUBSTITUTE и UTF-8.
 *
 * @param string $text Исходная строка
 * @return string Экранированная строка
 */
function safeOutput(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// --------------------------------------------------------------
// Задание 8. Защита от CSRF
// --------------------------------------------------------------

/**
 * Генерирует и сохраняет в сессии CSRF-токен длиной 64 hex-символа.
 *
 * @return string Новый токен
 */
function generateCsrfToken(): string
{
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    return $token;
}

/**
 * Проверяет переданный CSRF-токен на соответствие сохранённому в сессии.
 *
 * Использует безопасное сравнение через hash_equals().
 *
 * @param string $token Переданный токен
 * @return bool true, если токен валиден
 */
function validateCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($token, $_SESSION['csrf_token']);
}

// --------------------------------------------------------------
// Задание 9. Регенерация ID сессии
// --------------------------------------------------------------

/**
 * Регенерирует ID сессии и удаляет старый файл сессии.
 *
 * Рекомендуется вызывать при входе в систему.
 *
 * @return void
 */
function rotateSessionId(): void
{
    session_regenerate_id(true);
}

// --------------------------------------------------------------
// Задание 10. Корзина товаров на сессиях
// --------------------------------------------------------------

/**
 * Корзина товаров, хранящая элементы в сессии.
 */
class ShoppingCart
{
    /**
     * Добавляет товар в корзину.
     *
     * Требуемые поля: id (int|string), name (string), price (float|int)
     *
     * @param array $item Товар с ключами 'id', 'name', 'price'
     * @return void
     * @throws \InvalidArgumentException если не хватает обязательных полей
     */
    public function addItem(array $item): void
    {
        if (!isset($item['id']) || !isset($item['name']) || !isset($item['price'])) {
            throw new \InvalidArgumentException('Товар должен содержать id, name и price');
        }
        $_SESSION['cart'][] = $item;
    }

    /**
     * Возвращает все товары в корзине.
     *
     * @return array Массив товаров (пустой, если корзина пуста)
     */
    public function getItems(): array
    {
        return $_SESSION['cart'] ?? [];
    }

    /**
     * Очищает корзину.
     *
     * @return void
     */
    public function clear(): void
    {
        unset($_SESSION['cart']);
    }
}

// --------------------------------------------------------------
// Задание 7 и 11: Гостевая книга + Авторизация + CSRF
// --------------------------------------------------------------
// Обработка POST-запросов (все формы — здесь)

$loginError = '';
$commentError = '';

// ——— Форма входа (Задание 11) ———
if (($_POST['action'] ?? null) === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!validateCsrfToken($csrfToken)) {
        $loginError = 'Недействительный CSRF-токен';
    } elseif (!validateEmail($email)) {
        $loginError = 'Неверный email';
    } elseif ($password !== 'secret') {
        $loginError = 'Неверный пароль';
    } else {
        rotateSessionId();
        $_SESSION['user_id'] = 123;
        $_SESSION['email'] = $email;
        // Перенаправление для избежания повторной отправки формы
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// ——— Форма выхода ———
if (($_POST['action'] ?? null) === 'logout') {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// ——— Гостевая книга (Задание 7 + CSRF) ———
if (($_POST['action'] ?? null) === 'comment') {
    $comment = trim($_POST['comment'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!validateCsrfToken($csrfToken)) {
        $commentError = 'Недействительный CSRF-токен';
    } elseif ($comment === '') {
        $commentError = 'Комментарий не может быть пустым';
    } else {
        $_SESSION['comments'][] = $comment;
        // Перенаправление после успешной отправки
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Инициализация сессий, если ещё не созданы
if (!isset($_SESSION['comments'])) {
    $_SESSION['comments'] = [];
}

// ——— Тестовые данные (для демонстрации) ———
$bag = new SessionBag();
$bag->set('lang', 'ru');

$cart = new ShoppingCart();
$cart->clear();
$cart->addItem(['id' => 1, 'name' => 'PHP в действии', 'price' => 99.99]);
$cart->addItem(['id' => 2, 'name' => 'Безопасность', 'price' => 149]);

// Устанавливаем тему по умолчанию как 'dark' (для демонстрации)
setThemeCookie('dark');

// Генерируем токен один раз для всех форм
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Web Tech Basics — Magrel</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; margin: 20px; background: #fafafa; }
        pre { background: #f4f4f4; padding: 12px; border-radius: 6px; overflow-x: auto; }
        .error { color: #d32f2f; font-weight: bold; }
        .success { color: #388e3c; }
        form { margin: 15px 0; padding: 15px; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        input, textarea, button { padding: 8px 12px; margin: 4px 2px; border: 1px solid #ccc; border-radius: 4px; }
        ul { padding-left: 20px; }
        li { margin-bottom: 4px; }
    </style>
</head>
<body>
    <h1>🛠️ Лабораторная работа: Web Tech Basics</h1>

    <!-- 1. dumpRequestInfo -->
    <h2>1. Информация о запросе (dumpRequestInfo)</h2>
    <?php dumpRequestInfo(); ?>

    <!-- 2. getRequestData -->
    <h2>2. Структурированные данные запроса (getRequestData)</h2>
    <pre><?= safeOutput(print_r(getRequestData(), true)) ?></pre>

    <!-- 3. GET и POST формы -->
    <h2>3. Формы: поиск (GET) и сообщение (POST)</h2>
    <?php
    $search = $_GET['search'] ?? '';
    $message = $_POST['message'] ?? '';
    if ($search !== '') {
        echo '<p>Поиск: <strong>' . safeOutput($search) . '</strong></p>';
    }
    if ($message !== '') {
        echo '<p>Сообщение: <strong>' . safeOutput($message) . '</strong></p>';
    }
    ?>
    <form method="GET">
        <label>Поиск: <input type="text" name="search" value="<?= safeOutput($search) ?>" placeholder="Введите запрос"></label>
        <button type="submit">Найти</button>
    </form>

    <form method="POST">
        <input type="hidden" name="message_action" value="send">
        <label>Сообщение: <input type="text" name="message" value="<?= safeOutput($message) ?>" placeholder="Ваше сообщение"></label>
        <button type="submit">Отправить</button>
    </form>

    <!-- 4. Гостевая книга -->
    <h2>4. Гостевая книга (с CSRF-защитой)</h2>
    <?php if ($commentError): ?>
        <p class="error"><?= safeOutput($commentError) ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="hidden" name="action" value="comment">
        <input type="hidden" name="csrf_token" value="<?= safeOutput($csrfToken) ?>">
        <label>Комментарий: <input type="text" name="comment" placeholder="Оставьте комментарий"></label>
        <button type="submit">Добавить</button>
    </form>
    <?php if (!empty($_SESSION['comments'])): ?>
        <ul>
            <?php foreach ($_SESSION['comments'] as $cmt): ?>
                <li><?= safeOutput($cmt) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p><em>Комментариев пока нет.</em></p>
    <?php endif; ?>

    <!-- 5. Форма входа -->
    <h2>5. Авторизация (с CSRF, rotateSessionId, secure logout)</h2>
    <?php if (!empty($_SESSION['email'])): ?>
        <p class="success">Здравствуйте, <strong><?= safeOutput($_SESSION['email']) ?></strong>!</p>
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
            <input type="hidden" name="csrf_token" value="<?= safeOutput($csrfToken) ?>">
            <label>Email: <input type="email" name="email" required></label><br>
            <label>Пароль: <input type="password" name="password" required></label><br>
            <button type="submit">Войти</button>
        </form>
        <p><small>Правильный пароль: <code>secret</code></small></p>
    <?php endif; ?>

    <!-- 6. Демонстрация классов и функций -->
    <h2>6. Примеры использования классов и вспомогательных функций</h2>
    <ul>
        <li>SessionBag (lang): <code><?= safeOutput($bag->get('lang')) ?></code></li>
        <li>Текущая тема (cookie): <code><?= safeOutput(getTheme()) ?></code></li>
        <li>Товаров в корзине: <code><?= count($cart->getItems()) ?></code></li>
        <li>CSRF-токен сгенерирован и валиден: <code><?= validateCsrfToken($csrfToken) ? '✅' : '❌' ?></code></li>
        <li>Валидный email (<code>user@example.com</code>): <code><?= validateEmail('user@example.com') ? '✅' : '❌' ?></code></li>
        <li>Безопасный вывод: <code><?= safeOutput('<script>alert(1)</script>') ?></code></li>
    </ul>
</body>
</html>

<?php
// ----------------------------
// Тестирование (в стиле Magrel)
// ----------------------------
echo "<h2>Page by Magrel</h2>\n";
echo "<h3>1. dumpRequestInfo</h3>\n";
// (вызов dumpRequestInfo() уже был выше — чтобы не дублировать вывод, пропускаем)

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>2. getRequestData</h3>\n";
$data = getRequestData();
echo "Метод: " . htmlspecialchars($data['method'], ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
echo "GET: " . htmlspecialchars(json_encode($data['get'], JSON_UNESCAPED_UNICODE), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
echo "POST: " . htmlspecialchars(json_encode($data['post'], JSON_UNESCAPED_UNICODE), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>3. safeOutput + validateEmail</h3>\n";
$testEmail = "user@site.com";
$danger = '<img src=x onerror=alert("XSS")>';
echo "Email '$testEmail' → " . (validateEmail($testEmail) ? '✅ валиден' : '❌ не валиден') . "<br>\n";
echo "Опасный ввод: " . htmlspecialchars($danger, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
echo "После safeOutput: " . safeOutput($danger) . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>4. SessionBag</h3>\n";
$bagTest = new SessionBag();
$bagTest->set('test_key', 'тестовое значение ✅');
echo "has('test_key'): " . ($bagTest->has('test_key') ? '✅' : '❌') . "<br>\n";
echo "get('test_key'): '" . htmlspecialchars($bagTest->get('test_key'), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "'<br>\n";
$bagTest->remove('test_key');
echo "has('test_key') после remove: " . ($bagTest->has('test_key') ? '✅' : '❌') . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>5. ShoppingCart</h3>\n";
$cartTest = new ShoppingCart();
$cartTest->clear();
$cartTest->addItem(['id' => 99, 'name' => 'Тест 📦', 'price' => 42.99]);
$items = $cartTest->getItems();
echo "Корзина содержит " . count($items) . " товар(ов)<br>\n";
echo "Первый товар: " . htmlspecialchars(json_encode($items[0] ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
$cartTest->clear();
echo "После clear(): " . count($cartTest->getItems()) . " товаров<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>6. CSRF защита</h3>\n";
$token1 = generateCsrfToken();
echo "Длина токена: " . (strlen($token1) === 64 ? '✅ 64 hex' : '❌') . "<br>\n";
echo "Валидация токена: " . (validateCsrfToken($token1) ? '✅ корректен' : '❌ не прошёл проверку') . "<br>\n";
echo "Подделка: " . (validateCsrfToken('fake123') ? '❌ прошла (!)' : '✅ отклонена') . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>7. rotateSessionId</h3>\n";
$oldId = session_id();
rotateSessionId();
$newId = session_id();
echo "ID изменился: " . ($oldId !== $newId ? '✅' : '❌') . " (было: " . htmlspecialchars($oldId, ENT_QUOTES | ENT_HTML5, 'UTF-8')
    . " → стало: " . htmlspecialchars($newId, ENT_QUOTES | ENT_HTML5, 'UTF-8') . ")<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>8. setThemeCookie / getTheme</h3>\n";
setThemeCookie('light');
// Примечание: getTheme() вернёт старое значение, т.к. cookie ещё не применён в этом запросе
echo "getTheme() сейчас: <code>" . htmlspecialchars(getTheme(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "</code><br>\n";
echo "<small>После обновления страницы будет «light»</small><br>\n";