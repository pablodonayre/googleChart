<?php

    $timespan= $_POST['timespan'];
    $url = 'https://api.blockchain.info/charts/market-price'. '?timespan='. $timespan;
// echo $url;
// die();
    $json = file_get_contents($url);

echo $json;
?>
