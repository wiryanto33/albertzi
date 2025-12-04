<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$model = new App\Models\HeavyEquipment();
echo "Model table: " . $model->getTable() . PHP_EOL;
try {
    $count = $model->newQuery()->count();
    echo "Count result: " . $count . PHP_EOL;
} catch (\Exception $e) {
    echo "Query error: " . $e->getMessage() . PHP_EOL;
}
