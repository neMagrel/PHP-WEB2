<?php


// Лабораторная работа: углублённое использование функций в PHP
// @author Magrel

// 1. Проверка на простое число
/**
 * Проверяет, является ли число простым.
 *
 * @param int $n Число для проверки
 * @return bool true, если $n — простое, иначе false
 */
function isPrime(int $n): bool {
    if ($n < 2) {
        return false;
    }
    for ($i = 2; $i * $i <= $n; $i++) {
        if ($n % $i === 0) {
            return false;
        }
    }
    return true;
}

// 2. Числа Фибоначчи (рекурсивная реализация)
/**
 * Возвращает n-е число последовательности Фибоначчи.
 *
 * @param int $n Порядковый номер (начиная с 0)
 * @return int Значение F(n)
 */
function fibonacci(int $n): int {
    if ($n === 0) {
        return 0;
    } elseif ($n === 1) {
        return 1;
    } else {
        return fibonacci($n - 1) + fibonacci($n - 2);
    }
}

// 3. Форматирование номера телефона
/**
 * Форматирует 11-значный номер телефона в формат +7 (XXX) XXX-XX-XX.
 *
 * @param string $phone Номер в формате '89123456789'
 * @return string Отформатированный номер или 'Неверный формат', если проверка не пройдена
 */
function formatPhone(string $phone): string {
    if (strlen($phone) !== 11 || !ctype_digit($phone)) {
        return 'Неверный формат';
    }
    // Замена первой '8' на '7' (стандартный префикс РФ)
    $digits = '7' . substr($phone, 1);
    return "+{$digits[0]} ({$digits[1]}{$digits[2]}{$digits[3]}) {$digits[4]}{$digits[5]}{$digits[6]}-{$digits[7]}{$digits[8]}-{$digits[9]}{$digits[10]}";
}

// 4. Фильтрация чётных чисел (анонимная функция + array_filter)
// Реализация — в тестовом блоке, т.к. задача — применить, а не объявить отдельную функцию

// 5. Кэшированный факториал
/**
 * Вычисляет факториал числа с кэшированием уже посчитанных значений.
 *
 * @param int $n Неотрицательное целое число
 * @return int Факториал n!
 */
function memoizedFactorial(int $n): int {
    static $cache = [];

    if ($n < 0) {
        throw new InvalidArgumentException('n не может быть отрицательным');
    }

    if (isset($cache[$n])) {
        return $cache[$n];
    }

    if ($n === 0 || $n === 1) {
        $cache[$n] = 1;
    } else {
        $cache[$n] = $n * memoizedFactorial($n - 1);
    }

    return $cache[$n];
}

// 6. Создание пользователя с именованными аргументами
/**
 * Создаёт ассоциативный массив с данными пользователя.
 *
 * @param string $name Имя пользователя
 * @param string $email Электронная почта
 * @param int $age Возраст
 * @param bool $isActive Активен ли (по умолчанию true)
 * @return array Ассоциативный массив с данными
 */
function createUser(string $name, string $email, int $age, bool $isActive = true): array {
    return [
        'name' => $name,
        'email' => $email,
        'age' => $age,
        'isActive' => $isActive,
    ];
}

// 7. Генератор счётчика через замыкание
/**
 * Возвращает функцию-счётчик, инкрементирующую своё значение при каждом вызове.
 *
 * @return callable Функция без параметров, возвращающая int
 */
function makeCounter(): callable {
    $count = 0;
    return function () use (&$count): int {
        return ++$count;
    };
}

//--------------------------------------------------------------
// Тестирование

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>1. isPrime</h3>\n";
foreach ([1, 2, 3, 4, 17, 25, 97] as $n) {
    echo "$n → ", isPrime($n) ? 'простое' : 'не простое', "<br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>2. fibonacci</h3>\n";
foreach ([0, 1, 2, 5, 10] as $n) {
    echo "F($n) = ", fibonacci($n), "<br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>3. formatPhone</h3>\n";
$testPhones = ['89123456789', '89991234567', '123', '8abcdefghij'];
foreach ($testPhones as $p) {
    echo htmlspecialchars($p, ENT_QUOTES | ENT_HTML5, 'UTF-8'), " → ", formatPhone($p), "<br>\n";
}

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>4. Фильтрация чётных (array_filter + анонимка)</h3>\n";
$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
$evens = array_filter($numbers, fn($x) => $x % 2 === 0);
echo "Исходный: [" . implode(', ', $numbers) . "]<br>\n";
echo "Чётные: [" . implode(', ', $evens) . "]<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>5. memoizedFactorial (с кэшированием)</h3>\n";
foreach ([0, 1, 5, 7] as $n) {
    echo "$n! = ", memoizedFactorial($n), "<br>\n";
}
// Второй проход — уже из кэша

echo "<h2>Page by Magrel</h2>\n";
echo "<em>Повторный вызов 5! = ", memoizedFactorial(5), "</em><br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>6. createUser с именованными аргументами</h3>\n";
// Вызов с изменённым порядком: $isActive — первым
$user = createUser(
    isActive: false,
    name: "Алиса",
    email: "alice@example.com",
    age: 30
);

echo "<h2>Page by Magrel</h2>\n";
echo "Пользователь: ", htmlspecialchars(json_encode($user, JSON_UNESCAPED_UNICODE), ENT_QUOTES | ENT_HTML5, 'UTF-8'), "<br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>7. makeCounter (замыкание)</h3>\n";
$counter = makeCounter();
echo "1-й вызов: ", $counter(), "<br>\n";
echo "2-й вызов: ", $counter(), "<br>\n";
echo "3-й вызов: ", $counter(), "<br>\n";