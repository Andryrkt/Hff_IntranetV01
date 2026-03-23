<?php

namespace App\Utils;

class PerfLogger
{
    private static ?self $instance = null;

    private float $startTime;
    private float $previous;
    private int $counter = 0;
    private array $logs = [];

    /**
     * Private constructor pour singleton
     */
    private function __construct()
    {
        $this->startTime = microtime(true);
        $this->previous = $this->startTime;
    }

    /**
     * Retourne l'instance singleton
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Log avec label et option category
     */
    public function log(string $category = 'default', string $label = '...'): self
    {
        $now = microtime(true);
        $this->counter++;

        $elapsed = $now - $this->previous;
        $total = $now - $this->startTime;

        $this->logs[] = [
            'index' => sprintf("%03d", $this->counter),
            'category' => $category,
            'label' => $label,
            'temps' => sprintf("%.5fs", $elapsed),
            'total' => sprintf("%.5fs", $total),
        ];

        $this->previous = $now;

        return $this; // chainable
    }

    /**
     * Sauvegarde logs dans un fichier JSON
     */
    public function save(): void
    {
        $filename = "logPerfLogger/perf_logs_" . date('Y-m-d_H-i-s') . ".json";
        $dossier = dirname($filename);
        if (!is_dir($dossier)) @mkdir($dossier);
        file_put_contents($filename, json_encode($this->logs, JSON_PRETTY_PRINT));
    }

    /**
     * Récupérer les logs en PHP
     */
    public function getLogs(): array
    {
        return $this->logs;
    }
}
