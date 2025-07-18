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
    private string $reference;

    private array $data = [];

    public function __construct(
        private float $byMonth = 1.5,
        ?string $file = null,
        private bool $computeFromEndMonth = true
    )
    {
        $this->file = $file ?: __DIR__ . '/../data.json';
        touch($this->file);
    }

    private function setReference(?string $year): void
    {
        $this->reference = $year ? $year.'-01-01' : (new DateTime)->format('Y').('-01-01');
    }

    public function computeBalance(DateTime $date): float
    {
        return $this->computeBalanceAbsolute($date) - $this->taken();
    }

    public function computeBalanceAbsolute(DateTime $date): float
    {
        $this->setReference($date->format('Y'));

        $dateReference = new DateTime($this->reference);
        $interval = $dateReference->diff($date);

        $monthCount = $this->computeFromEndMonth ? $interval->m : $interval->m + 1;

        return $monthCount * $this->byMonth;
    }

    public function takeRTT(DateTime $date, int $days): void
    {
        $this->setReference($date->format('Y'));

        $data = $this->load();

        $takenDays = 0;

        while ($takenDays < $days) {
            // Ignore samedis et dimanches
            if (!in_array($date->format('N'), [6, 7])) {
                $data[$this->reference]['taken'][] = $date->format('Y-m-d');

                $takenDays++;
            }

            $date->modify('+1 day');
        }

        $this->data = $data;
        $this->save();
    }

    private function load(): mixed
    {
        $data = file_get_contents($this->file);

        return $data ? json_decode($data, true) : [];
    }

    public function save(?string $file = null): void
    {
        $data = $this->data ?: $this->load();

        file_put_contents($file ?: $this->file, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function taken(): int
    {
        $data = $this->load();

        return isset($data[$this->reference]['taken']) ? count($data[$this->reference]['taken']) : 0;
    }
}
