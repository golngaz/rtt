<?php

use App\RTTCalculator;

include __DIR__ . '/vendor/autoload.php';

if ($argc < 2) {
    exit('Usage:
  php rtt.php balance [YYYY-MM-DD]
  php rtt.php take YYYY-MM-DD [X (how many) = 1]
  php rtt.php save
');
}

$calculator = new RTTCalculator();

$action = $argv[1];
if ($action === 'balance') {
    $dateString = $argv[2] ?? date('Y-m-d');

    $date = new DateTime($dateString);
    echo "Solde RTT restant au $dateString : " . $calculator->computeBalance($date) . " jours (solde total: " . $calculator->computeIgnoreTaken($date) . ") (pris : " . $calculator->taken() . ")\n";

} elseif ($action === 'take') {
    if ($argc === 4) {
        $days = (int) $argv[3];
    } else {
        $days = 1;
    }

    $calculator->takeRTT(new DateTime($argv[2]), $days);

    echo "RTT posés à partir du $argv[2] pour $days jours\n";

} elseif ($action === 'save') {
    echo 'saving..', PHP_EOL;

    $filename = __DIR__ . '/saves/data-' . date('Y-m-d') . '.json';
    touch($filename);
    echo 'file : ' . $filename;

    $calculator->save($filename);
} else {
    exit("Commande invalide.\n");
}
