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
    public function __construct(
        public float $byMonth = 1.5,
        public ?string $file = null,
        public ?string $yearReference = null,
    )
    {
        $this->yearReference = $this->yearReference ?: (new DateTime)->format('Y');

        if ($this->file === null) {


            $this->file = __DIR__ . '/../data_' . $this->yearReference . '.json';
        }

        touch($this->file);
    }

    public function computeBalance(DateTime $date): float
    {
        $dateReference = new DateTime($this->yearReference.'-01-01');
        $interval = $dateReference->diff($date);

        $takenCount = $interval->m * $this->byMonth;

        $data = $this->load();

        $savedTakenCount = isset($data['taken']) ? count($data['taken']) : 0;

        return $takenCount - $savedTakenCount;
    }

    public function takeRTT(DateTime $date, int $days): void
    {
        $data = $this->load();

        $takenDays = 0;

        while ($takenDays < $days) {
            // Ignore samedis et dimanches
            if (!in_array($date->format('N'), [6, 7])) {
                $data['taken'][] = $date->format('Y-m-d');

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
