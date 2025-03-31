<?php

use App\RTTCalculator;

include __DIR__ . '/vendor/autoload.php';

if ($argc < 2) {
    exit("Usage:\n  php rtt.php balance [YYYY-MM-DD]\n  php rtt.php take YYYY-MM-DD [X (how many) = 1]\n");
}

$calculator = new RTTCalculator();

$action = $argv[1];
if ($action === 'balance') {
    $date = $argv[2] ?? date('Y-m-d');

    echo "Solde RTT au $date : " . $calculator->computeBalance(new DateTime($date)) . " jours\n";

} elseif ($action === 'take') {
    if ($argc === 4) {
        $days = (int) $argv[3];
    } else {
        $days = 1;
    }

    $calculator->takeRTT(new DateTime($argv[2]), $days);

    echo "RTT posés à partir du $argv[2] pour $days jours\n";

} else {
    exit("Commande invalide.\n");
}
