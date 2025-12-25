<?php

declare(strict_types=1);

namespace App\Service;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use InvalidArgumentException;

/**
 * Сервис логирования на основе Monolog.
 * Обеспечивает запись сообщений в файл с уровнем INFO по умолчанию.
 */
class LoggerService
{
    /**
     * Экземпляр Monolog\Logger.
     *
     * @var Logger
     */
    private Logger $logger;

    /**
     * Конструктор LoggerService.
     *
     * Создаёт логгер с именем 'app' и добавляет StreamHandler.
     *
     * @param string $logFile Путь к файлу лога (по умолчанию — 'app.log')
     * @throws InvalidArgumentException Если путь не является строкой или пуст
     */
    public function __construct(string $logFile = 'app.log')
    {
        if ($logFile === '') {
            throw new InvalidArgumentException('Путь к файлу лога не может быть пустым');
        }

        $this->logger = new Logger('app');
        $this->logger->pushHandler(new StreamHandler($logFile, Logger::INFO));
    }

    /**
     * Записывает информационное сообщение в лог.
     *
     * @param string $message Текст сообщения
     * @return void
     */
    public function log(string $message): void
    {
        $this->logger->info($message);
    }

    /**
     * Возвращает внутренний Monolog\Logger (для расширенного использования).
     *
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }
}