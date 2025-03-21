<?php

use App\RTTCalculator;

include __DIR__ . '/vendor/autoload.php';

if ($argc < 2) {
    exit("Usage:\n  php rtt.php balance [YYYY-MM-DD]\n  php rtt.php take YYYY-MM-DD X\n");
}

$calculator = new RTTCalculator();

$action = $argv[1];
if ($action === 'balance') {
    $date = $argv[2] ?? date('Y-m-d');

    echo "Solde RTT au $date : " . $calculator->computeBalance(new DateTime($date)) . " jours\n";

} elseif ($action === 'take' && $argc === 4) {
    $calculator->takeRTT(new DateTime($argv[2]), (int) $argv[3]);

    echo "RTT posés à partir du $argv[2] pour $argv[3] jours\n";

} else {
    exit("Commande invalide.\n");
}
