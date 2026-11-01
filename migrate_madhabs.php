<?php
// Madhab Migration - Handled via standard Laravel migrations
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('Forbidden');
}
echo "Please use php artisan migrate instead.\n";
