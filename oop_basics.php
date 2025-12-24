<?php

// Задание 1. Класс Person
/**
 * Простой класс для хранения имени и возраста человека.
 */
class Person
{
    public string $name = '';
    public int $age = 0;
}

// Задание 2. Класс Product
/**
 * Товар с названием, остатком и ценой.
 * Цена защищена: можно установить/получить только через методы.
 */
class Product
{
    public string $title = '';
    protected int $stock = 0;
    private float $price = 0.0;

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}

// Задание 3. Класс Greeter
/**
 * Генератор приветствий.
 */
class Greeter
{
    private string $greeting;

    public function __construct(string $greeting)
    {
        $this->greeting = $greeting;
    }

    public function greet(string $name): string
    {
        return "{$this->greeting}, {$name}!";
    }
}

// Задание 4. Класс Book (PHP 8 promoted properties)
/**
 * Книга с заголовком, автором и годом.
 */
class Book
{
    public function __construct(
        private string $title,
        private string $author,
        private int $year
    ) {}

    public function getInfo(): string
    {
        return "\"{$this->title}\" ({$this->author}, {$this->year})";
    }
}

// Задание 5. Класс BankAccount (инкапсуляция)
/**
 * Банковский счёт с защищённым балансом.
 */
class BankAccount
{
    private float $balance = 0.0;

    public function deposit(float $amount): void
    {
        if ($amount > 0) {
            $this->balance += $amount;
        }
    }

    public function withdraw(float $amount): bool
    {
        if ($this->balance >= $amount) {
            $this->balance -= $amount;
            return true;
        }
        return false;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }
}

// Задание 6. Класс ShopProduct (каталог товаров)
/**
 * Продукт из каталога.
 */
class ShopProduct
{
    public function __construct(
        private string $title,
        private string $producer,
        private int $price
    ) {}

    public function getSummaryLine(): string
    {
        return "{$this->title} ({$this->producer}): {$this->price}";
    }
}

// Задание 7. Класс Counter (статика)
/**
 * Счётчик созданных экземпляров.
 */
class Counter
{
    private static int $count = 0;

    public function __construct()
    {
        self::$count++;
    }

    public static function getCount(): int
    {
        return self::$count;
    }
}

// Задание 8. Класс User (итоговый)
/**
 * Пользователь системы.
 */
class User
{
    public function __construct(
        private string $email,
        private string $name,
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable()
    ) {}

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getInfo(): string
    {
        return "{$this->name} ({$this->email}), зарегистрирован: " . $this->createdAt->format('Y-m-d');
    }
}

// ========================================================
// === ДЕМОНСТРАЦИЯ (закомментирована по требованию) ===
// ========================================================

echo "<h2>Page by Magrel</h2>\n";

// 1. Person
echo "<h3>1. Person</h3>\n";
$p1 = new Person();
$p1->name = "Алексей";
$p1->age = 20;
$p2 = new Person();
$p2->name = "Мария";
$p2->age = 22;
echo "p1: {$p1->name}, {$p1->age} лет<br>\n";
echo "p2: {$p2->name}, {$p2->age} лет<br>\n";

// 2. Product
echo "<h2>Page by Magrel</h2>\n";
echo "<h3>2. Product</h3>\n";
$prod = new Product();
$prod->title = "Кофемашина";
$prod->setPrice(12500.0);
echo "Название: " . htmlspecialchars($prod->title, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
echo "Цена: " . number_format($prod->getPrice(), 2, ',', ' ') . " ₽<br>\n";

// 3. Greeter
echo "<h2>Page by Magrel</h2>\n";
echo "<h3>3. Greeter</h3>\n";
$g = new Greeter("Здравствуйте");
echo htmlspecialchars($g->greet("Иван"), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";

// 4. Book
echo "<h2>Page by Magrel</h2>\n";
echo "<h3>4. Book</h3>\n";
$book = new Book("Мастер и Маргарита", "М. Булгаков", 1967);
echo htmlspecialchars($book->getInfo(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";

// 5. BankAccount
echo "<h2>Page by Magrel</h2>\n";
echo "<h3>5. BankAccount</h3>\n";
$acc = new BankAccount();
$acc->deposit(1000.0);
$acc->withdraw(250.0);
echo "Баланс: " . number_format($acc->getBalance(), 2, ',', ' ') . " ₽<br>\n";
echo "Попытка снятия 900 ₽: " . ($acc->withdraw(900) ? '✅ Успешно' : '❌ Отказ') . "<br>\n";

// 6. ShopProduct
echo "<h2>Page by Magrel</h2>\n";
echo "<h3>6. ShopProduct</h3>\n";
$sp = new ShopProduct("PHP для профессионалов", "Котеров Д.", 990);
echo htmlspecialchars($sp->getSummaryLine(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";

// 7. Counter
echo "<h2>Page by Magrel</h2>\n";
echo "<h3>7. Counter</h3>\n";
new Counter();
new Counter();
echo "Создано объектов Counter: " . Counter::getCount() . "<br>\n";

// 8. User
echo "<h2>Page by Magrel</h2>\n";
echo "<h3>8. User</h3>\n";
$u1 = new User("ivan@example.com", "Иван");
$u2 = new User("maria@test.ru", "Мария");
echo htmlspecialchars($u1->getInfo(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
echo htmlspecialchars($u2->getInfo(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
