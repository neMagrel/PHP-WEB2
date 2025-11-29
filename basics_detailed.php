<?php
echo "<h2>Page by Magrel</h2>\n";

//Задание 1: Функция классификации возраста
function classifyAge(int $age): string {
    if ($age < 12){
        return "Ребёнок";
    }
    else if (12 < $age && $age <= 17){
        return "Подросток";
    }
    else{ return "Взрослый";}
}

// Задание 2: вывод списка городов
function printCitiesAsList(array $cities): void {
    echo "<ul>\n";
    foreach ($cities as $city) {
        // Обязательно — экранирование (ТЗ: безопасность, XSS)
        echo "  <li>" . htmlspecialchars($city, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "</li>\n";
    }
    echo "</ul>\n";
}
// Задание 3: Алгоритм FizzBuzz
function fizzBuzz (int $finalNumber): array{
    //тута
    $lines = [];
    for ($i = 1; $i <= $finalNumber; $i++) {
            if ($i % 3 === 0 && $i % 5 === 0) {
                $lines[] = 'FizzBuzz';
            } elseif ($i % 3 === 0) {
                $lines[] = 'Fizz';
            } elseif ($i % 5 === 0) {
                $lines[] = 'Buzz';
            } else {
                $lines[] = (string)$i;
            }
        }
    return $lines;
}
// Задание 4. Конвертер температур
function convertCelsiusToFahrenheit(float $celsius): int {
    return (int)($celsius * 9/5 + 32);
}

// Задание 5: Работа с union types
function getUserName(int|string $id): string|false {
    if ($id === 1) {
        return "Администратор";
    } else if ($id === "guest") {
        return "Гость";
    } else {
        return false;
    }
}

// Задание 6: Использование конструкции match
function classifyAgeMatch(int $age): string {
    return match (true) {
        ($age < 12) => "Ребёнок",
        ($age > 12 && $age <= 17 ) => "Подросток",
        ($age > 18)=> "Взрослый",
    };
}

//--------------------------------------------------------------

// Тестирование
echo "<h3>1. classifyAge</h3>\n";
foreach ([8, 15, 25] as $a) echo "$a → ", classifyAge($a), "<br>\n";

echo "<h3>2. Города</h3>\n";
$cities = ['Москва', 'СПб', 'Новосиб', 'Екб', 'Казань'];
printCitiesAsList($cities);

echo "<h3>3. FizzBuzz (1–15)</h3>\n";
$fizz = fizzBuzz(15);
foreach ($fizz as $i => $v) echo ($i+1), " → $v<br>\n";

echo "<h3>4. °C → °F</h3>\n";
foreach ([0, 25, -10, 100] as $c) {
    $f = convertCelsiusToFahrenheit($c);
    echo "{$c}°C = {$f}°F<br>\n";
}

echo "<h3>5. getUserName</h3>\n";
foreach ([1, 'guest', 42] as $id) {
    $res = getUserName($id);
    echo var_export($id, true), " → ", var_export($res, true), "<br>\n";
}

echo "<h3>6. match: classifyAgeMatch</h3>\n";
foreach ([8, 15, 25] as $a) echo "$a → ", classifyAgeMatch($a), "<br>\n";