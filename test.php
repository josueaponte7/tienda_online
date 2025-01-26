<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()->build();
echo "Elasticsearch está funcionando correctamente.\n";
