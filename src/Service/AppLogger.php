<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class AppLogger implements LoggerInterface
{
    private string $logFilePath;

    public function __construct(string $logFilePath)
    {
        $this->logFilePath = $logFilePath;
    }

    public function emergency($message, array $context = array()) { $this->log(LogLevel::EMERGENCY, $message, $context); }
    public function alert($message, array $context = array()) { $this->log(LogLevel::ALERT, $message, $context); }
    public function critical($message, array $context = array()) { $this->log(LogLevel::CRITICAL, $message, $context); }
    public function error($message, array $context = array()) { $this->log(LogLevel::ERROR, $message, $context); }
    public function warning($message, array $context = array()) { $this->log(LogLevel::WARNING, $message, $context); }
    public function notice($message, array $context = array()) { $this->log(LogLevel::NOTICE, $message, $context); }
    public function info($message, array $context = array()) { $this->log(LogLevel::INFO, $message, $context); }
    public function debug($message, array $context = array()) { $this->log(LogLevel::DEBUG, $message, $context); }

    public function log($level, $message, array $context = array())
    {
        $logMessage = sprintf("[%s] [%s] %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            $context ? json_encode($context) : ''
        );

        // Assurez-vous que le répertoire existe
        $logDir = dirname($this->logFilePath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        error_log($logMessage, 3, $this->logFilePath);
    }
}
