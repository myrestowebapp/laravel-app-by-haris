<?php
// Category Seeder - Handled via standard Laravel seeders (database/seeders)
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('Forbidden');
}
echo "Please use php artisan db:seed instead.\n";
