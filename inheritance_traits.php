<?php

// ----------------------------
// ЗАДАНИЕ 1: Простейшее наследование
// ----------------------------

/**
 * Базовый класс продукта с названием и ценой.
 */
class Product
{
    /**
     * Создаёт продукт.
     *
     * @param string $title Название продукта
     * @param float  $price Цена продукта
     */
    public function __construct(
        protected string $title,
        protected float $price
    ) {}

    /**
     * Возвращает название продукта.
     *
     * @return string Название
     */
    public function getTitle(): string
    {
        return $this->title;
    }
}

/**
 * Книга — продукт с автором.
 */
class Book extends Product
{
    /**
     * Создаёт книгу.
     *
     * @param string $title  Название книги
     * @param float  $price  Цена книги
     * @param string $author Автор книги
     */
    public function __construct(string $title, float $price, private string $author)
    {
        parent::__construct($title, $price);
    }

    /**
     * Возвращает автора книги.
     *
     * @return string Имя автора
     */
    public function getAuthor(): string
    {
        return $this->author;
    }
}

// ----------------------------
// ЗАДАНИЕ 2: Абстрактные классы
// ----------------------------

/**
 * Абстрактный класс урока с обязательными методами расчёта стоимости и типа оплаты.
 */
abstract class Lesson
{
    /**
     * Возвращает стоимость урока в рублях.
     *
     * @return int Стоимость
     */
    abstract public function cost(): int;

    /**
     * Возвращает тип оплаты урока.
     *
     * @return string Описание типа оплаты
     */
    abstract public function chargeType(): string;
}

/**
 * Лекция — фиксированная оплата.
 */
class Lecture extends Lesson
{
    public function cost(): int
    {
        return 30;
    }

    public function chargeType(): string
    {
        return 'Фиксированная ставка';
    }
}

/**
 * Семинар — повышенная, нефиксированная ставка.
 */
class Seminar extends Lesson
{
    public function cost(): int
    {
        return 90;
    }

    public function chargeType(): string
    {
        return 'Нефиксированная ставка';
    }
}

// ----------------------------
// ЗАДАНИЕ 3: Интерфейсы
// ----------------------------

/**
 * Интерфейс бронирования — должен уметь бронироваться.
 */
interface Bookable
{
    /**
     * Производит бронирование (например, записывает в лог или БД).
     */
    public function book(): void;
}

/**
 * Интерфейс расчёта дополнительной платы.
 */
interface Chargeable
{
    /**
     * Вычисляет дополнительную плату (например, за материалы).
     *
     * @return float Сумма доплаты
     */
    public function calculateFee(): float;
}

/**
 * Воркшоп — бронируется и имеет доплату.
 */
class Workshop implements Bookable, Chargeable
{
    public function book(): void
    {
        echo 'Workshop booked!';
    }

    public function calculateFee(): float
    {
        return 1.5;
    }
}

// ----------------------------
// ЗАДАНИЕ 4: Программирование на основе интерфейса
// ----------------------------

/**
 * Универсальная функция бронирования любого бронируемого объекта.
 *
 * @param Bookable $item Объект, реализующий интерфейс Bookable
 */
function processBooking(Bookable $item): void
{
    $item->book();
}

// ----------------------------
// ЗАДАНИЕ 5: Трейты — базовое использование
// ----------------------------

/**
 * Трейт для расчёта налогов (НДС 20%).
 */
trait PriceUtilities
{
    /**
     * Рассчитывает налог (20%) от переданной цены.
     *
     * @param float $price Цена до налога
     * @return float Сумма налога
     */
    public function calculateTax(float $price): float
    {
        return $price * 0.2;
    }
}

/**
 * Продукт магазина — поддерживает расчёт цены с налогом.
 */
class ShopProduct
{
    use PriceUtilities;

    /**
     * Создаёт продукт.
     *
     * @param float  $price Цена без НДС
     * @param string $name  Название продукта
     */
    public function __construct(
        private float $price,
        private string $name
    ) {}

    /**
     * Возвращает цену с учётом НДС (20%).
     *
     * @return float Цена с НДС
     */
    public function getPriceWithTax(): float
    {
        return $this->price + $this->calculateTax($this->price);
    }
}

// ----------------------------
// ЗАДАНИЕ 6: Несколько трейтов
// ----------------------------

/**
 * Трейт для генерации уникального идентификатора.
 */
trait IdentityTrait
{
    /**
     * Генерирует уникальный строковый ID (на основе временной метки).
     *
     * @return string Уникальный ID
     */
    public function generateId(): string
    {
        return uniqid();
    }
}

// Модифицируем ShopProduct: добавляем IdentityTrait
class ShopProductWithId
{
    use PriceUtilities;
    use IdentityTrait;

    /**
     * Создаёт продукт с возможностью генерации ID.
     *
     * @param float  $price Цена без НДС
     * @param string $name  Название продукта
     */
    public function __construct(
        private float $price,
        private string $name
    ) {}

    /**
     * Возвращает цену с учётом НДС (20%).
     *
     * @return float Цена с НДС
     */
    public function getPriceWithTax(): float
    {
        return $this->price + $this->calculateTax($this->price);
    }
}

// ----------------------------
// ЗАДАНИЕ 7: Разрешение конфликтов трейтов
// ----------------------------

/**
 * Трейт имитирует печать.
 */
trait Printer
{
    /**
     * Выводит строку "Printer".
     */
    public function output(): void
    {
        echo 'Printer';
    }
}

/**
 * Трейт имитирует логирование.
 */
trait Logger
{
    /**
     * Выводит строку "Logger".
     */
    public function output(): void
    {
        echo 'Logger';
    }
}

/**
 * Отчёт — использует оба трейта с разрешённым конфликтом.
 */
class Report
{
    use Printer, Logger {
        Logger::output insteadOf Printer;
        Printer::output as printOutput;
    }
}

// ----------------------------
// ЗАДАНИЕ 8: Трейт с доступом к свойствам хост-класса
// ----------------------------

/**
 * Трейт, формирующий описание объекта на основе свойства `$name`.
 */
trait Describable
{
    /**
     * Возвращает строковое описание объекта.
     *
     * @return string Описание, например: "Объект: Товар А"
     */
    public function describe(): string
    {
        return "Объект: {$this->name}";
    }
}

/**
 * Простой товар с названием и возможностью описания.
 */
class Item
{
    use Describable;

    /**
     * Создаёт элемент.
     *
     * @param string $name Название элемента
     */
    public function __construct(public string $name)
    {
    }
}

// ----------------------------
// ЗАДАНИЕ 9: Абстрактные методы в трейтах
// ----------------------------

/**
 * Трейт, добавляющий базовую валидацию с требованием реализации правил.
 */
trait Validatable
{
    /**
     * Должен возвращать ассоциативный массив правил валидации.
     *
     * @return array<string, string> Правила вида ['поле' => 'правило']
     */
    abstract public function getRules(): array;

    /**
     * Заглушка валидации — всегда возвращает true.
     * В реальном приложении здесь будет логика проверки.
     *
     * @return bool Результат валидации
     */
    public function validate(): bool
    {
        return true;
    }
}

/**
 * Форма пользователя — реализует правила валидации для трейта Validatable.
 */
class UserForm
{
    use Validatable;

    public function getRules(): array
    {
        return ['email' => 'required'];
    }
}

// ----------------------------
// ЗАДАНИЕ 10: Комплексное — HasMedia + TaxCalculation
// ----------------------------

/**
 * Интерфейс для медианосителей — обязывает указывать длину.
 */
interface HasMedia
{
    /**
     * Возвращает длину медианосителя (в страницах или минутах).
     *
     * @return int Длина
     */
    public function getMediaLength(): int;
}

/**
 * Трейт расчёта налога — унифицированная логика НДС.
 */
trait TaxCalculation
{
    /**
     * Рассчитывает НДС (20%) от цены.
     *
     * @param float $price Цена до налога
     * @return float Сумма НДС
     */
    public function getTax(float $price): float
    {
        return $price * 0.2;
    }
}

/**
 * Книга как медианоситель.
 */
class BookProduct implements HasMedia
{
    use TaxCalculation;

    /**
     * Длина книги в страницах.
     */
    private int $length = 300;

    /**
     * Создаёт книгу.
     *
     * @param float  $price Цена без НДС
     * @param string $name  Название книги
     */
    public function __construct(
        private float $price,
        private string $name
    ) {}

    /**
     * Возвращает цену с НДС.
     *
     * @return float Цена с НДС
     */
    public function getPriceWithTax(): float
    {
        return $this->price + $this->getTax($this->price);
    }

    public function getMediaLength(): int
    {
        return $this->length;
    }
}

/**
 * Аудио-CD как медианоситель.
 */
class CDProduct implements HasMedia
{
    use TaxCalculation;

    /**
     * Длина CD в минутах.
     */
    private int $length = 74;

    /**
     * Создаёт CD-продукт.
     *
     * @param float  $price Цена без НДС
     * @param string $name  Название
     */
    public function __construct(
        private float $price,
        private string $name
    ) {}

    /**
     * Возвращает цену с НДС.
     *
     * @return float Цена с НДС
     */
    public function getPriceWithTax(): float
    {
        return $this->price + $this->getTax($this->price);
    }

    public function getMediaLength(): int
    {
        return $this->length;
    }
}

// ----------------------------
// ЗАДАНИЕ 11: Итоговое — Service + Schedulable + Logger
// ----------------------------

/**
 * Абстрактный сервис (услуга) с базовыми требованиями.
 */
abstract class Service
{
    /**
     * Возвращает длительность услуги в минутах.
     *
     * @return int Длительность
     */
    abstract public function getDuration(): int;

    /**
     * Возвращает текстовое описание услуги.
     *
     * @return string Описание
     */
    abstract public function getDescription(): string;
}

/**
 * Интерфейс планирования — услуга должна уметь запланироваться.
 */
interface Schedulable
{
    /**
     * Формирует строку с подтверждением расписания.
     *
     * @return string Сообщение
     */
    public function schedule(): string;
}

/**
 * Трейт простого логирования в stdout.
 */
trait Logger2
{
    /**
     * Выводит сообщение в поток вывода (например, для отладки).
     *
     * @param string $msg Текст сообщения
     */
    public function log(string $msg): void
    {
        echo $msg;
    }
}

/**
 * Консультация — 60-минутная услуга.
 */
class Consulting extends Service implements Schedulable
{
    use Logger2;

    public function getDuration(): int
    {
        return 60;
    }

    public function getDescription(): string
    {
        return 'This is a description of consulting';
    }

    public function schedule(): string
    {
        return 'Scheduled consulting session';
    }
}

/**
 * Тренинг — 120-минутная услуга.
 */
class Training extends Service implements Schedulable
{
    use Logger2;

    public function getDuration(): int
    {
        return 120;
    }

    public function getDescription(): string
    {
        return 'This is a description of training';
    }

    public function schedule(): string
    {
        return 'Scheduled training session';
    }
}

// ============================================================================
// === ТЕСТИРОВАНИЕ (в стиле Magrel)
// ============================================================================

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>1. Простейшее наследование (Product / Book)</h3>\n";
$book = new Book('Clean Code', 29.99, 'Robert Martin');
echo "Книга: " . htmlspecialchars($book->getTitle(), ENT_QUOTES | ENT_HTML5, 'UTF-8')
    . " — Автор: " . htmlspecialchars($book->getAuthor(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>2. Абстрактные классы (Lecture / Seminar)</h3>\n";
$lecture = new Lecture();
$seminar = new Seminar();
echo "Лекция: " . $lecture->cost() . " руб, "
    . htmlspecialchars($lecture->chargeType(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
echo "Семинар: " . $seminar->cost() . " руб, "
    . htmlspecialchars($seminar->chargeType(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>3–4. Интерфейсы и интерфейсное программирование</h3>\n";
$workshop = new Workshop();
echo "Доплата за Workshop: " . $workshop->calculateFee() . " руб<br>\n";
echo "Бронирование: ";
processBooking($workshop);
echo "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>5–6. Трейты: PriceUtilities + IdentityTrait</h3>\n";
$product = new ShopProductWithId(100.0, 'Notebook');
echo "Цена с НДС (20%): " . number_format($product->getPriceWithTax(), 2, '.', '') . " руб<br>\n";
echo "Уникальный ID: " . htmlspecialchars($product->generateId(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>7. Разрешение конфликтов трейтов</h3>\n";
$report = new Report();
echo "output() → ";
$report->output(); // вызов из Logger
echo "<br>\n";
echo "printOutput() → ";
$report->printOutput(); // псевдоним Printer::output
echo "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>8. Трейт с доступом к свойству хоста (Describable)</h3>\n";
$item = new Item('Товар А');
echo htmlspecialchars($item->describe(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>9. Трейт с абстрактным методом (Validatable)</h3>\n";
$form = new UserForm();
$rulesJson = json_encode($form->getRules(), JSON_UNESCAPED_UNICODE);
echo "Правила валидации: " . htmlspecialchars($rulesJson, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
echo "Валидация успешна: " . ($form->validate() ? '✅ Да' : '❌ Нет') . "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>10. HasMedia + TaxCalculation (BookProduct / CDProduct)</h3>\n";
$bookProd = new BookProduct(50.0, 'PHP Guide');
$cdProd = new CDProduct(30.0, 'Music Album');
echo "Книга — длина: " . $bookProd->getMediaLength() . " стр., цена с НДС: "
    . number_format($bookProd->getPriceWithTax(), 2, '.', '') . " руб<br>\n";
echo "CD — длина: " . $cdProd->getMediaLength() . " мин, цена с НДС: "
    . number_format($cdProd->getPriceWithTax(), 2, '.', '') . " руб<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>11. Service + Schedulable + Logger2</h3>\n";
$consulting = new Consulting();
$training = new Training();

echo "Консультация: " . htmlspecialchars($consulting->getDescription(), ENT_QUOTES | ENT_HTML5, 'UTF-8')
    . " (" . $consulting->getDuration() . " мин)<br>\n";
echo "Расписание: " . htmlspecialchars($consulting->schedule(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
echo "Лог: ";
$consulting->log("✅ Консультация запланирована\n");
echo "<br>\n";

echo "Тренинг: " . htmlspecialchars($training->getDescription(), ENT_QUOTES | ENT_HTML5, 'UTF-8')
    . " (" . $training->getDuration() . " мин)<br>\n";
echo "Расписание: " . htmlspecialchars($training->schedule(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . "<br>\n";
echo "Лог: ";
$training->log("✅ Тренинг подтверждён\n");
echo "<br>\n";

// ----------------------------
// Примеры вызовов (можно раскомментировать для быстрой проверки)
// ----------------------------

// 1.
$book = new Book('Title', 19.99, 'Author');
echo $book->getTitle(), ' / ', $book->getAuthor();

// 2.
var_dump((new Lecture())->cost(), (new Seminar())->chargeType());

// 3.
$w = new Workshop();
$w->book(); echo '; fee = ', $w->calculateFee();

// 4.
processBooking($w);

// 5–6.
$p = new ShopProductWithId(99.9, 'Mouse');
echo $p->getPriceWithTax(), ' / ', $p->generateId();

// 7.
$r = new Report();
$r->output();         // → Logger
$r->printOutput();    // → Printer

// 8.
$i = new Item('Test');
echo $i->describe();

// 9.
$f = new UserForm();
var_dump($f->getRules(), $f->validate());

// 10.
$b = new BookProduct(100, 'Book');
$c = new CDProduct(20, 'CD');
echo $b->getMediaLength(), ' / ', $b->getPriceWithTax();
echo $c->getMediaLength(), ' / ', $c->getPriceWithTax();

// 11.
$con = new Consulting();
echo $con->getDescription(), ' / ', $con->getDuration(), ' / ', $con->schedule();
$con->log("Test log");