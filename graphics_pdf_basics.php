<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');


header('Content-Type: text/html; charset=utf-8');

// ——————————————————————————————————————————————
// Проверка расширения GD
// ——————————————————————————————————————————————
if (!extension_loaded('gd')) {
    die('Ошибка: расширение GD не загружено');
}

// ——————————————————————————————————————————————
// Подключение FPDF (отложенно, только при необходимости)
// ——————————————————————————————————————————————
function loadFpdf(): void
{
    if (!class_exists('FPDF', false)) {
        $fpdfPath = __DIR__ . '/fpdf/fpdf.php';
        if (!is_readable($fpdfPath)) {
            http_response_code(500);
            exit('Ошибка: FPDF не найден. Поместите fpdf.php в папку fpdf/');
        }
        require_once $fpdfPath;
    }
}

if (!class_exists('FPDF', false)) {
    loadFpdf();
}

// ——————————————————————————————————————————————
// 1. Чёрный квадрат на белом фоне
// ——————————————————————————————————————————————
/**
 * Рисует чёрный квадрат 100×100 по центру белого холста 200×200 и выводит PNG.
 *
 * @return void
 */
function renderBlackSquare(): void
{
    $size = 200;
    $rectSize = 100;
    $offset = ($size - $rectSize) / 2;

    $image = imagecreatetruecolor($size, $size);
    if ($image === false) {
        http_response_code(500);
        exit('Не удалось создать изображение');
    }

    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);

    imagefilledrectangle($image, 0, 0, $size - 1, $size - 1, $white);
    imagefilledrectangle($image, $offset, $offset, $offset + $rectSize - 1, $offset + $rectSize - 1, $black);

    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
}

// ——————————————————————————————————————————————
// 2. Текст с встроенным шрифтом
// ——————————————————————————————————————————————
/**
 * Создаёт изображение 300×100 и выводит текст шрифтом №5 в левом верхнем углу.
 * Ограничивает длину текста 50 ASCII-символами.
 *
 * @param string $text Текст для отображения (только ASCII, ≤50 символов)
 * @return void
 */
function renderTextImage(string $text): void
{
    if (strlen($text) > 50) {
        http_response_code(400);
        exit('Текст слишком длинный (макс. 50 ASCII-символов)');
    }

    $image = imagecreatetruecolor(300, 100);
    if ($image === false) {
        http_response_code(500);
        exit('Не удалось создать изображение');
    }

    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);

    imagefilledrectangle($image, 0, 0, 299, 99, $white);
    imagestring($image, 5, 0, 0, $text, $black);

    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
}

// ——————————————————————————————————————————————
// 3. TrueType-шрифты
// ——————————————————————————————————————————————
/**
 * Рисует текст TrueType-шрифтом на изображении 400×100.
 * При отсутствии шрифта выводит PNG с ошибкой.
 *
 * @param string $text Текст для отображения
 * @param string $fontPath Путь к TTF-файлу
 * @return void
 */
function renderTtfText(string $text, string $fontPath): void
{
    if (!is_readable($fontPath)) {
        // Генерируем PNG с сообщением об ошибке
        $image = imagecreatetruecolor(400, 50);
        if ($image === false) {
            http_response_code(500);
            exit('Не удалось создать изображение ошибки');
        }
        $red = imagecolorallocate($image, 255, 0, 0);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, 399, 49, $white);
        imagestring($image, 2, 10, 15, "Ошибка: шрифт не найден", $red);
        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
        return;
    }

    $image = imagecreatetruecolor(400, 100);
    if ($image === false) {
        http_response_code(500);
        exit('Не удалось создать изображение');
    }

    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);

    imagefilledrectangle($image, 0, 0, 399, 99, $white);
    imagettftext($image, 20, 0, 10, 40, $black, $fontPath, $text);

    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
}

// ——————————————————————————————————————————————
// 4. Динамическая кнопка
// ——————————————————————————————————————————————
/**
 * Накладывает текст по центру на фоновое изображение.
 * Поддерживает TTF (arial.ttf) и fallback на встроенный шрифт.
 *
 * @param string $text Текст (латиница/кириллица, цифры, пробелы; ≤50)
 * @param string $bgImagePath Путь к фоновому PNG
 * @return void
 */
function renderButton(string $text, string $bgImagePath): void
{
    if (!preg_match('/^[a-zA-Z0-9\sа-яА-ЯёЁ]{1,50}$/u', $text)) {
        http_response_code(400);
        exit('Текст содержит запрещённые символы');
    }

    if (!is_readable($bgImagePath)) {
        http_response_code(404);
        exit('Фоновое изображение не найдено');
    }

    $bg = @imagecreatefrompng($bgImagePath);
    if ($bg === false) {
        http_response_code(500);
        exit('Не удалось загрузить фоновое изображение');
    }

    $width = imagesx($bg);
    $height = imagesy($bg);
    $image = imagecreatetruecolor($width, $height);
    if ($image === false) {
        imagedestroy($bg);
        http_response_code(500);
        exit('Не удалось создать изображение кнопки');
    }

    imagecopy($image, $bg, 0, 0, 0, 0, $width, $height);
    imagedestroy($bg);

    $black = imagecolorallocate($image, 0, 0, 0);
    $fontPath = __DIR__ . '/arial.ttf';

    if (is_readable($fontPath)) {
        $bbox = imagettfbbox(16, 0, $fontPath, $text);
        if ($bbox === false) {
            http_response_code(500);
            imagedestroy($image);
            exit('Ошибка при измерении текста');
        }
        $textWidth = $bbox[2] - $bbox[0];
        $x = ($width - $textWidth) / 2;
        $y = $height / 2 + 6; // компенсация базовой линии
        imagettftext($image, 16, 0, $x, $y, $black, $fontPath, $text);
    } else {
        // fallback
        $x = ($width - strlen($text) * imagefontwidth(5)) / 2;
        $y = ($height - imagefontheight(5)) / 2;
        imagestring($image, 5, (int)$x, (int)$y, $text, $black);
    }

    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
}

// ——————————————————————————————————————————————
// 5. Кэширование изображений
// ——————————————————————————————————————————————
/**
 * Отдаёт кэшированное изображение или генерирует новое и сохраняет в кэш.
 * Ожидает, что $generator сохранит изображение в $cacheFile и вернёт bool успеха.
 *
 * @param string $cacheDir Директория кэша
 * @param string $key Уникальный ключ (используется в md5)
 * @param callable $generator callable(string $cacheFile): bool
 * @return void
 */
function getCachedImageOrGenerate(string $cacheDir, string $key, callable $generator): void
{
    $cacheFile = $cacheDir . '/' . md5($key) . '.png';

    if (file_exists($cacheFile)) {
        header('Content-Type: image/png');
        header('Cache-Control: max-age=86400');
        readfile($cacheFile);
        exit;
    }

    if (headers_sent()) {
        http_response_code(500);
        exit('Заголовки уже отправлены');
    }

    $errorReporting = error_reporting(0);
    ini_set('display_errors', '0');

    if (!is_dir($cacheDir) && !mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
        error_reporting($errorReporting);
        http_response_code(500);
        exit('Не удалось создать директорию кеша');
    }

    $success = $generator($cacheFile);

    error_reporting($errorReporting);

    if ($success && file_exists($cacheFile)) {
        header('Content-Type: image/png');
        readfile($cacheFile);
        exit;
    }

    http_response_code(500);
    exit('Не удалось сгенерировать и сохранить изображение');
}

// ——————————————————————————————————————————————
// 6. Простой PDF-документ
// ——————————————————————————————————————————————
/**
 * Генерирует PDF с одной страницей и центрированным сообщением.
 *
 * @param string $message Текст сообщения
 * @return void
 */
function renderSimplePdf(string $message): void
{
    loadFpdf();
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, $message, 0, 1, 'C');
    $pdf->Output();
    exit;
}

// ——————————————————————————————————————————————
// 7–9. InvoicePdf: класс для генерации счёта
// ——————————————————————————————————————————————
/**
 * Расширенный генератор PDF-счёта с логотипом, таблицей, колонтитулами и гиперссылкой.
 */
class InvoicePdf extends FPDF
{
    /**
     * Верхний колонтитул: логотип слева, заголовок по центру.
     *
     * @return void
     */
    public function Header(): void
    {
        $logoPath = __DIR__ . '/logo.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 10, 30);
        }
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Счёт', 0, 1, 'C');
        $this->Ln(5);
    }

    /**
     * Нижний колонтитул: номер страницы по центру.
     *
     * @return void
     */
    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Страница ' . $this->PageNo(), 0, 0, 'C');
    }

    /**
     * Рисует таблицу с рамками.
     *
     * @param array $header Массив заголовков (строки)
     * @param array $data Массив строк (каждая — массив ячеек)
     * @return void
     */
    public function buildTable(array $header, array $data): void
    {
        $this->SetFont('Arial', 'B', 10);
        $colWidths = [80, 40, 40, 30];

        foreach ($header as $i => $col) {
            $w = $colWidths[$i] ?? 40;
            $this->Cell($w, 7, $col, 1, 0, 'C');
        }
        $this->Ln();

        $this->SetFont('Arial', '', 10);
        foreach ($data as $row) {
            foreach ($row as $i => $cell) {
                $w = $colWidths[$i] ?? 40;
                $this->Cell($w, 6, $cell, 1, 0, 'L');
            }
            $this->Ln();
        }
    }

    /**
     * Генерирует и отправляет PDF-счёт с таблицей и гиперссылкой.
     *
     * @param array $items Массив строк [['Товар', 'Кол-во', 'Цена', 'Сумма'], ...]
     * @return void
     */
    public function renderInvoice(array $items): void
    {
        $this->AddPage();
        $header = ['Товар', 'Кол-во', 'Цена', 'Сумма'];
        $this->buildTable($header, $items);

        $this->Ln(10);

        $this->SetFont('Arial', 'U', 10);
        $this->SetTextColor(0, 0, 255);
        $linkText = 'Посетить сайт';
        $this->Write(5, $linkText);
        $this->Link(
            $this->GetX() - $this->GetStringWidth($linkText),
            $this->GetY() - 5,
            $this->GetStringWidth($linkText),
            5,
            'https://example.com'
        );

        $this->Output();
        exit;
    }
}

// ——————————————————————————————————————————————
// 10. Итоговое домашнее задание: badge и invoice
// ——————————————————————————————————————————————
/**
 * Генерирует значок участника с именем на фоне badge-bg.png.
 *
 * @param string $name Имя (только буквы и пробелы, 2–50 символов)
 * @return void
 */
function renderBadge(string $name): void
{
    $name = trim($name);
    if (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s]{2,50}$/u', $name)) {
        http_response_code(400);
        exit('Имя должно содержать только буквы и пробелы, длина 2–50');
    }

    $cacheDir = __DIR__ . '/cache';
    getCachedImageOrGenerate($cacheDir, 'badge_' . $name, function (string $cacheFile) use ($name): bool {
        $bgPath = __DIR__ . '/badge-bg.png';
        $fontPath = __DIR__ . '/arial.ttf';

        if (!is_readable($bgPath)) {
            // fallback: plain image
            $image = imagecreatetruecolor(300, 100);
            if ($image === false) return false;
            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            imagefilledrectangle($image, 0, 0, 299, 99, $white);
            imagestring($image, 5, 50, 40, "BADGE: $name", $black);
        } else {
            $bg = @imagecreatefrompng($bgPath);
            if ($bg === false) return false;
            $width = imagesx($bg);
            $height = imagesy($bg);
            $image = imagecreatetruecolor($width, $height);
            if ($image === false) {
                imagedestroy($bg);
                return false;
            }
            imagecopy($image, $bg, 0, 0, 0, 0, $width, $height);
            imagedestroy($bg);

            $black = imagecolorallocate($image, 0, 0, 0);
            if (is_readable($fontPath)) {
                imagettftext($image, 18, 0, 30, 60, $black, $fontPath, $name);
            } else {
                imagestring($image, 5, 30, 50, $name, $black);
            }
        }

        $success = imagepng($image, $cacheFile);
        imagedestroy($image);
        return $success;
    });
}

/**
 * Роутинг для домашнего задания: badge и invoice.
 *
 * @return void
 */
function runHomework(): void
{
    $type = $_GET['type'] ?? '';

    if ($type === 'badge') {
        renderBadge($_GET['name'] ?? '');
        return;
    }

    if ($type === 'invoice') {
        $items = [
            ['Наушники', '2', '1500', '3000'],
            ['Мышь', '1', '800', '800'],
            ['Клавиатура', '1', '2500', '2500'],
            ['Монитор', '1', '12000', '12000'],
            ['Коврик', '3', '200', '600'],
        ];
        loadFpdf();
        $pdf = new InvoicePdf();
        $pdf->renderInvoice($items);
        return;
    }

    if ($type === 'simple-pdf') {
        renderSimplePdf('Простой PDF-документ');
        return;
    }

    if ($type === 'black-square') {
        renderBlackSquare();
        return;
    }

    if ($type === 'text') {
        renderTextImage($_GET['value'] ?? 'Hello');
        return;
    }

    if ($type === 'ttf') {
        renderTtfText($_GET['value'] ?? 'Привет', __DIR__ . '/arial.ttf');
        return;
    }

    if ($type === 'button') {
        renderButton($_GET['value'] ?? 'Кнопка', __DIR__ . '/badge-bg.png');
        return;
    }
}

// ——————————————————————————————————————————————
// Тестирование (в стиле Magrel)
// ——————————————————————————————————————————————
echo "<h2>Page by Magrel</h2>\n";
echo "<h3>1. renderBlackSquare()</h3>\n";
echo "Прямой вызов: <a href='?type=black-square' target='_blank'>?type=black-square</a><br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>2. renderTextImage()</h3>\n";
echo "Пример: <a href='?type=text&value=Hello+World' target='_blank'>?type=text&value=Hello+World</a><br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>3. renderTtfText()</h3>\n";
echo "Пример (требуется arial.ttf): <a href='?type=ttf&value=Тест+TTF' target='_blank'>?type=ttf&value=Тест+TTF</a><br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>4. renderButton()</h3>\n";
echo "Пример (требуется badge-bg.png): <a href='?type=button&value=Купи+меня' target='_blank'>?type=button&value=Купи+меня</a><br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>5. getCachedImageOrGenerate() + renderBadge()</h3>\n";
echo "Значок: <a href='?type=badge&name=Алексей' target='_blank'>?type=badge&name=Алексей</a><br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>6. renderSimplePdf()</h3>\n";
echo "PDF: <a href='?type=simple-pdf' target='_blank'>?type=simple-pdf</a><br>\n";

echo "<h2>Page by Magrel</h2>\n";
echo "<h3>7–9. InvoicePdf::renderInvoice()</h3>\n";
echo "Счёт: <a href='?type=invoice' target='_blank'>?type=invoice</a><br>\n";

// ——————————————————————————————————————————————
// Примеры вызовов (раскомментируйте для быстрой проверки)
// ——————————————————————————————————————————————

/*
// 1. Чёрный квадрат
renderBlackSquare();

// 2. Текст
renderTextImage("Тест");

// 3. TTF
renderTtfText("Привет, мир!", __DIR__ . '/arial.ttf');

// 4. Кнопка
renderButton("Старт", __DIR__ . '/badge-bg.png');

// 5. Кэширование — в renderBadge() уже используется

// 6. Простой PDF
renderSimplePdf("Тестовое сообщение");

// 7–9. Счёт
loadFpdf();
$pdf = new InvoicePdf();
$items = [
    ['Товар', '1', '1000', '1000']
];
$pdf->renderInvoice($items);

// 10. Бейдж
renderBadge("Иван");
*/

// Запуск роутинга
runHomework();