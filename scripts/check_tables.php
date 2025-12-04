<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = \DB::select("SHOW TABLES LIKE 'heavy_equipments'");
print_r($tables);
$tables2 = \DB::select("SHOW TABLES LIKE 'heavy_equipment'");
print_r($tables2);
