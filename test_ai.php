<?php

require_once '/var/www/html/bootstrap/app.php';

$app = app();

try {
    $service = $app->make('FireflyIII\Services\Internal\AIService');
    echo 'AIService resolved successfully: ' . get_class($service) . PHP_EOL;
} catch (Exception $e) {
    echo 'Error resolving AIService: ' . $e->getMessage() . PHP_EOL;
}

try {
    $controller = $app->make('FireflyIII\Http\Controllers\AI\DashboardController');
    echo 'DashboardController resolved successfully: ' . get_class($controller) . PHP_EOL;
} catch (Exception $e) {
    echo 'Error resolving DashboardController: ' . $e->getMessage() . PHP_EOL;
}
