<?php
http_response_code(403);
die('Forbidden: Database setup should only be executed via php artisan migrate.');
