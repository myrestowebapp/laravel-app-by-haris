<?php
http_response_code(403);
die('Forbidden: Migration scripts should only be run via artisan migrate in CLI.');
