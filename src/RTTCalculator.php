<?php

namespace App;

use DateTime;

use function count;
use function file_get_contents;
use function file_put_contents;
use function in_array;
use function json_decode;
use function json_encode;
use function touch;

use const JSON_PRETTY_PRINT;

class RTTCalculator
{
    private string $file;
    private string $yearReference;

    public function __construct(
        private float $byMonth = 1.5,
        ?string $file = null
    )
    {
        $this->file = $file ?: __DIR__ . '/../data.json';
        touch($this->file);
    }

    public function setYearReference(?string $yearReference): void
    {
        $this->yearReference = $yearReference ?: (new DateTime)->format('Y');

        touch($this->file);
    }

    public function computeBalance(DateTime $date): float
    {
        $this->setYearReference($date->format('Y'));

        $dateReference = new DateTime($this->yearReference.'-01-01');
        $interval = $dateReference->diff($date);

        $takenCount = $interval->m * $this->byMonth;

        $data = $this->load();

        $savedTakenCount = isset($data[$this->yearReference]['taken']) ? count($data[$this->yearReference]['taken']) : 0;

        return $takenCount - $savedTakenCount;
    }

    public function takeRTT(DateTime $date, int $days): void
    {
        $this->setYearReference($date->format('Y'));

        $data = $this->load();

        $takenDays = 0;

        while ($takenDays < $days) {
            // Ignore samedis et dimanches
            if (!in_array($date->format('N'), [6, 7])) {
                $data[$this->yearReference]['taken'][] = $date->format('Y-m-d');

                $takenDays++;
            }

            $date->modify('+1 day');
        }

        $this->save($data);
    }

    private function load(): mixed
    {
        $data = file_get_contents($this->file);

        return $data ? json_decode($data, true) : [];
    }

    private function save($data): void
    {
        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT));
    }
}
