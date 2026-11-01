<?php
/**
 * Standalone Old Questions Database Migrator & Importer
 * 
 * This script imports old questions from public/questions.sql, normalizes them,
 * sets up referential user/madhab integrity, and inserts them cleanly into the 
 * new Laravel database structure.
 * 
 * Features:
 * - Detects CLI / Browser access.
 * - Auto-loads database configuration from Laravel's .env file.
 * - Supports temporary schema virtualization to process SQL dumps.
 * - Auto-resolves madhab strings ('Shafi' -> 'Shafii', 'Hanafi' -> 'Hanafi', 'Common' -> 'General/Other').
 * - Creates dummy questioner user accounts automatically for any old ID that doesn't exist, preventing Foreign Key constraint violations.
 * - Beautiful premium modern HTML interface with detailed real-time execution logs!
 */

// Define absolute path helper
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Start Session for browser CSRF/Status tracking
if (php_sapi_name() !== 'cli') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// 1. Parse Database config from Laravel .env
function get_db_credentials() {
    $envPath = BASE_PATH . '/.env';
    
    // Credentials must be provided via .env file or environment variables only
    $config = [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_DATABASE') ?: '',
        'username' => getenv('DB_USERNAME') ?: '',
        'password' => getenv('DB_PASSWORD') ?: ''
    ];
    
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1], " \t\n\r\0\x0B\"'");
                
                if ($key === 'DB_HOST') $config['host'] = $value;
                if ($key === 'DB_PORT') $config['port'] = $value;
                if ($key === 'DB_DATABASE') $config['database'] = $value;
                if ($key === 'DB_USERNAME') $config['username'] = $value;
                if ($key === 'DB_PASSWORD') $config['password'] = $value;
            }
        }
    }
    return $config;
}

// Get credentials
$dbConfig = get_db_credentials();
$isCli = (php_sapi_name() === 'cli');

$status = [];
$error = null;
$pdo = null;

// Handle actual execution request
if ($isCli || (isset($_POST['action']) && $_POST['action'] === 'import')) {
    try {
        $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        
        $status[] = ["Connected to target database successfully.", "success"];
        
        // --- UPGRADE USERS TABLE WITH MISSING MADHAB_ID COLUMN ---
        $status[] = ["Verifying and upgrading active `users` table structure...", "info"];
        $hasMadhabId = $pdo->query("SHOW COLUMNS FROM users LIKE 'madhab_id'")->fetch();
        if (!$hasMadhabId) {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $pdo->exec("ALTER TABLE users ADD COLUMN madhab_id INT NULL AFTER district;");
            $pdo->exec("ALTER TABLE users ADD CONSTRAINT fk_users_madhab_id FOREIGN KEY (madhab_id) REFERENCES madhabs(id) ON DELETE SET NULL;");
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
            $status[] = ["Successfully added missing column `madhab_id` to the active `users` table.", "success"];
        } else {
            $status[] = ["Active `users` table already has the `madhab_id` column.", "success"];
        }
        
        // --- UPGRADE FATWA_ACTIVITY_LOGS TABLE ---
        $status[] = ["Upgrading active `fatwa_activity_logs` table structure to prevent exceptions...", "info"];
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec("DROP TABLE IF EXISTS fatwa_activity_logs;");
        $pdo->exec("CREATE TABLE IF NOT EXISTS fatwa_activity_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            question_id BIGINT UNSIGNED NOT NULL,
            fatwa_id BIGINT UNSIGNED NULL,
            user_id BIGINT UNSIGNED NULL,
            event VARCHAR(255) NOT NULL,
            event_label VARCHAR(255) NOT NULL,
            notes TEXT NULL,
            icon VARCHAR(255) DEFAULT 'bi-circle',
            color VARCHAR(50) DEFAULT 'secondary',
            event_at TIMESTAMP NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
            FOREIGN KEY (fatwa_id) REFERENCES fatwas(id) ON DELETE SET NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        $status[] = ["Upgraded active `fatwa_activity_logs` successfully.", "success"];
        
        // --- UPGRADE FATWA_FEATURED_RULES TABLE ---
        $status[] = ["Upgrading active `fatwa_featured_rules` table structure to prevent exceptions...", "info"];
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec("DROP TABLE IF EXISTS fatwa_featured_rules;");
        $pdo->exec("CREATE TABLE IF NOT EXISTS fatwa_featured_rules (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            hijri_month TINYINT NULL,
            hijri_day_start TINYINT NULL,
            hijri_day_end TINYINT NULL,
            country_codes JSON NULL,
            category_id BIGINT UNSIGNED NULL,
            madhab_id INT NULL,
            fatwa_ids JSON NULL,
            section_label VARCHAR(255) NOT NULL DEFAULT 'Most Useful & Readable Fatwas',
            section_sub VARCHAR(500) NULL,
            `limit` TINYINT NOT NULL DEFAULT 6,
            priority INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
            FOREIGN KEY (madhab_id) REFERENCES madhabs(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // Populate default rule if empty
        $pdo->exec("INSERT INTO `fatwa_featured_rules` (`name`, `section_label`, `section_sub`, `limit`, `priority`, `is_active`, `created_at`, `updated_at`)
            VALUES (
                'Default: Most Popular Fatwas',
                'Most Useful & Readable Fatwas',
                'The most read and appreciated Islamic rulings from our scholars.',
                6, 0, 1, NOW(), NOW()
            )");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        $status[] = ["Upgraded active `fatwa_featured_rules` and seeded default rule successfully.", "success"];
        
        // --- STEP 1: CLEAN PREVIOUS staging TABLES ---
        $status[] = ["Cleaning any previous staging tables `old_questions` and `old_users`...", "info"];
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec("DROP TABLE IF EXISTS old_questions;");
        $pdo->exec("DROP TABLE IF EXISTS old_users;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        // --- STEP 2: LOAD & PARSE INLINED DUMP DATA ---
        $status[] = ["Loading inlined database dump content...", "info"];
        $sqlContent = <<<SQL
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `question_id` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `madhhab` enum('Shafi','Hanafi','Common') NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `shortcode` varchar(50) DEFAULT NULL,
  `question` text,
  `attachment` text,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `questions` (`id`, `question_id`, `user_id`, `madhhab`, `category`, `shortcode`, `question`, `attachment`, `status`, `created_at`) VALUES
(7, 'Q0007', 6, 'Shafi', NULL, NULL, 'ഒരു വ്യക്തി മാസ്ബൂഖയി ഇമാമിന്റെ പിറകിൽ കൂടി ഇമാം സലാം വീട്ടിയപ്പോൾ ഇനി എത്ര രക്കാത് നിസ്കരിക്കണം ( ഉഉദാഹരണം 3 റക്കഅത്താണോ അതോ 4 ആണോ ) എന്ന് സംശയം വന്നാൽ ആ മാസ്ബൂഖ് എന്ത് ചെയ്യണം', '', 'Pending', '2025-05-20 11:46:18'),
(8, 'Q0008', 7, 'Shafi', NULL, NULL, 'ഒരു മരുന്ന് നിർമ്മാണ ശാലയിൽ ജോലി ചെയ്യുന്ന എന്നോട്, കഴിക്കുന്നവർക്ക് ഗുരുതര ആരോഗ്യ പ്രശ്നങ്ങൾക്ക് കാരണമാകുന്ന രാസപദാർത്ഥങ്ങൾ മരുന്നിൽ ചേർക്കാൻ എന്നോട് മരുന്ന് കമ്പനി ഉടമ നിർദേശിച്ചാൽ ഞാൻ എന്താണ് ചെയ്യേണ്ടത് ?', '', 'Pending', '2025-05-20 12:50:18'),
(9, 'Q0009', 8, 'Common', NULL, NULL, 'Can we eat pork if we are on the edge of death by hunger?', '', 'Pending', '2025-05-21 07:15:26'),
(10, 'Q0010', 9, 'Common', NULL, NULL, 'Digital gold invest  അഥവാ phonepay, google pay എന്നിവയിൽ gold വാങ്ങി ഇടുന്നതിн്റെ വിധി എന്താണ് ?', '', 'Pending', '2025-05-27 05:38:28'),
(11, 'Q0011', 10, 'Common', NULL, NULL, 'ഹെൽത്ത്‌ ഇൻഷുറൻസ് നാട്ടിൽ എടുക്കുന്നതിന്റെ വിധി', '', 'Pending', '2025-06-24 06:35:28'),
(12, 'Q0012', 11, 'Shafi', NULL, NULL, 'തഹജ്ജുദ് പോലെയുള്ള രാത്രിയിലെ നമസ്കാരങ്ങളിൽ ആഖിറം, കബർ ഇങ്ങനെയുള്ള കാര്യങ്ങൾ ഓർത്തു കരയുകയും, ആ കരച്ചിൽകാരണമായി ഉണ്ടാകുന്ന കണ്ണുനീർ വായിലേക്ക് ഇറങ്ങി അത്‌ ഉള്ളിൽപോയാൽ ആ നമസ്കാരം സ്വീകാര്യ മാകുമോ?', '', 'Pending', '2025-06-24 07:00:15'),
(13, 'Q0013', 11, 'Shafi', NULL, NULL, 'തഹജ്ജുദ് പോലെയുള്ള രാത്രിയിലെ നമസ്കാരങ്ങളിൽ ആഖിറം, കബർ ഇങ്ങനെയുള്ള കാര്യങ്ങൾ ഓർത്തു കരയുകയും, ആ കരച്ചിൽകാരണമായി ഉണ്ടാകുന്ന കണ്ണുനീർ വായിലേക്ക് ഇറങ്ങി അത്‌ ഉള്ളിൽപോയാൽ ആ നമസ്കാരം സ്വീകാര്യ മാകുമോ?', '', 'Pending', '2025-06-24 07:01:31'),
(14, 'Q0014', 12, 'Shafi', NULL, NULL, 'തോലിന്റെ മുസല്ല എന്നാൽ അവനറിയില്ല ഇത് ഊറക്കി ഇടപെട്ടതാണോ എന്ന് അതിൽ നമസ്കാരിക്കുന്നതിന്റെ  ഹുക്കും എന്ത്?അതുപോലെ തൊലിയുടെ  ഖുഫാ അവനറിയില്ല ഊരക്കിട്ടതാണോ എന്ന് അതിട്ട് നമസ്കരിക്കുന്നതിന്റെ ഹുക്കും', '', 'Pending', '2025-06-24 18:55:11'),
(15, 'Q0015', 13, 'Common', NULL, NULL, 'السلام عليكم \r\nമലബാർ ഗോൾഡ് പോലെയുള്ള സ്ഥാപനങ്ങൾ മാസം തോറും നിശ്ചിത തുക കസ്റ്റമേഴ്സിൽ നിന്ന് സ്വീകരിച്ച്, അവരുടെ അക്കൗണ്ടിൽ തത്തുല്യമായ സ്വർണ്ണം അപ്പോൾ തന്നെ വകയിരുത്തി, ഒരു വർഷം ആകുമ്പോൾ മുഴുവൻ സ്വർണ്ണവും ഒരുമിച്ച് നൽകുന്ന പദ്ധതികൾ നടത്തുന്നുണ്ട്. ഇതിൽ ചേരുന്നതിന് ഇസ്ലാമികമായി തെറ്റുണ്ടോ?\r\n(കസ്റ്റമേഴ്സിന്റെ കയ്യിൽ സ്വർണ്ണം physical ആയി എത്തിച്ചേരുന്നതിന് മുമ്പായി വിൽക്കാനുള്ള ഓപ്ഷൻ ഇല്ല)\r\nجزاكم الله خيرا', '', 'Pending', '2025-06-25 04:28:06'),
(16, 'Q0016', 14, 'Shafi', NULL, NULL, 'Is isthigasa and tawassul permitted in islam according to all madhab', '', 'Pending', '2025-06-25 04:37:39'),
(17, 'Q0017', 7, 'Shafi', NULL, NULL, 'അസ്സലാമു അലൈക്കും\r\nഒരു വ്യക്തിയുടെ മകന്റെ ഭാര്യയുടെ വിധവയായ മാതാവ്, അല്ലെങ്കിൽ മകളുടെ ഭർത്താവിന്റെ വിധവയായ മാതാവ് ഉണ്ട്.  ഇവരെ ഒരിക്കലും ആ വ്യക്തിക്ക് വിവാഹം ചെയ്യാൻ പാടില്ലാത്തതാണല്ലോ ? \r\n അപ്പോൾ ആ വ്യക്തിക്ക് ഈ മാതാവിനോട് സ്വന്തം സഹോദരിയോടെന്നപോലെ പെരുമാറാമോ ?', '', 'Pending', '2025-06-25 14:02:47'),
(18, 'Q0018', 15, 'Shafi', NULL, NULL, 'Photos edkal anuvadaneeyamano', '', 'Pending', '2025-06-29 14:31:15'),
(19, 'Q0019', 16, 'Shafi', NULL, NULL, 'ചോദ്യം: പ്രാർത്ഥനാ സമയങ്ങളിൽ കൂവുന്ന ഒരു കോഴി ആർക്കെങ്കിലും ഉണ്ടെങ്കിൽ, അത് ആവർത്തിച്ച് പരീക്ഷിക്കപ്പെട്ടിട്ടുണ്ടെങ്കിൽ, അത്തരമൊരു കോഴിയെ പ്രാർത്ഥനാ സമയം നിർണ്ണയിക്കുന്നതിൽ വിശ്വസിക്കാൻ കഴിയുമോ ഇല്ലയോ?', '', 'Pending', '2025-07-01 08:15:50'),
(20, 'Q0020', 11, 'Shafi', NULL, NULL, '10 ലക്ഷം രൂപ കൊടുത്ത് വീട് പണയത്തിന് വാങ്ങി.ഒരു വർഷത്തേക്ക് വീട്ടുടമയുടെ പൂർണ തൃപ്തിയോടെ മാസ വാടക കൊടുക്കാതെ താമസിക്കുന്നതിന്റെ ഇസ്ലാമിക വിധി എന്താണ്? വാടക കൊടുക്കാത്തത് പലിശ ഇനത്തിൽ പെടുമോ?', '', 'Pending', '2025-07-01 17:43:56'),
(21, 'Q0021', 11, 'Shafi', NULL, NULL, '10 ലക്ഷം രൂപ കൊടുത്ത് വീട് പണയത്തിന് വാങ്ങി.ഒരു വർഷത്തേക്ക് വീട്ടുടമയുടെ പൂർണ തൃപ്തിയോടെ മാസ വാടക കൊടുക്കാതെ താമസിക്കുന്നതിന്റെ ഇസ്ലാമിക വിധി എന്താണ്? വാടക കൊടുക്കാത്തത് പലിശ ഇനത്തിൽ പെടുമോ?', '', 'Pending', '2025-07-01 17:44:22'),
(22, 'Q0022', 17, 'Shafi', NULL, NULL, 'രണ്ട് റക്അത്ത് കൊണ്ട് സ്വലാത്തുതൗബ, സ്വലാത്തുൽഹാജത്ത്, സ്വലാത്തുൽഇസ്തിഖാറ, സ്വലാത്തു ശുക്ർ... എന്നീ നാല് നമസ്കാരങ്ങളും നമസ്കരിക്കാൻ പറ്റുമോ?', '', 'Pending', '2025-07-03 06:13:09'),
(23, 'Q0023', 18, 'Shafi', NULL, NULL, 'ഒരാൾ ഇസ്തിസ്ഖാ നമസ്കാരം നിർവഹിക്കുന്നതിനിടയിൽ മഴ പെയ്ത് തുടങ്ങുകയും, ആ മഴത്തുള്ളികൾ അവന്റെ വായിൽ കയറി അവൻ അത് വിഴുങ്ങുകയും ചെയ്താൽ, നമസ്കാരത്തിന്റെ വിധി എന്താണ്?', '', 'Pending', '2025-07-05 06:03:28'),
(24, 'Q0024', 19, 'Shafi', NULL, NULL, 'മുടിയിൽ ഏത് കളർ അടിക്കലും ജായിസ് ആണോ?', '', 'Pending', '2025-07-05 06:35:50'),
(25, 'Q0025', 18, 'Shafi', NULL, NULL, 'നോമ്പുകാരൻ കണ്ണുനീർ കുടിച്ചാൽ നോമ്പ് മുറിയുമോ? മുറിയുമെങ്കിൽ അത് ഫർള് നോമ്പിലും സുന്നത്ത് നോമ്പിലും മുറിയുമോ?', '', 'Pending', '2025-07-10 17:37:13'),
(26, 'Q0026', 18, 'Shafi', NULL, NULL, 'മൂത്ര വാർച്ചക്കാരൻ ഇഹ്റാം കെട്ടിയാൽ എന്ത് ചെയ്യണം? നമസ്ക്കാരക്കാരനെ പോലെ കെട്ടി വച്ച് കൊണ്ടാണോ ഇഹ്റാമിൻ്റെ അവസ്ഥയിൽ അമലുകൾ എല്ലാം ചെയ്യേണ്ടത്?', '', 'Pending', '2025-07-10 17:38:29'),
(27, 'Q0027', 20, 'Shafi', NULL, NULL, 'ചെവിയിൽ നിന്നും-രോഗത്തിന്റെ പേരിലോ,അല്ലാതെയോ- ഒലിക്കുന്ന  വെള്ളത്തിന്റെ (Ear wax) വിധി എന്താണ്?\r\nഅതിലൂടെ വുളൂഅ് മുറിയുമോ? നമസ്കാരത്തിന് ശരീരത്തിൽ ആയാൽ നമസ്കാരം ശരിയാകുമോ?', '', 'Pending', '2025-07-11 16:43:42'),
(28, 'Q0028', 21, 'Shafi', NULL, NULL, 'السلام عليكم ورحمة الله وبركاته✨\r\nസിഗരറ്റ് വിൽക്കൽ ഹലാലാണോ ഹറാമാണോ?\r\nനമുക്കൊരു സ്റ്റേഷനറി കടയുണ്ട് ഹോൾസെയിൽ കടയാണ് അതോടൊപ്പം ആ ചുറ്റുവട്ടത്ത് ഇതുപോലെ തന്നെ ഒരുപാട് സ്റ്റേഷനിൽ കടകളുമുണ്ട് പരസ്പരം കച്ചവടത്തിൽ മത്സരവും ആണ്.\r\nഅതേപോലെ ഒരുപാട് ഭായിമാർ ഉള്ളതുകൊണ്ട് സിഗരറ്റ്ഒരുപാട് ചെലവാകുന്ന സ്ഥലവുമാണ്.\r\nനമ്മൾ സിഗരറ്റ് ഒഴിവാക്കുകയാണെങ്കിൽ നമ്മുടെ പല കച്ചവടങ്ങളും മറ്റു കടകളിലേക്ക് പോകുകയും നമ്മുടെ മറ്റു സാധനങ്ങളും അടുത്തുള്ള കടകളിൽ നിന്നും വാങ്ങാൻ കാരണമാകുന്നതുമാണ്...\r\nഇതിനായി വ്യക്തമായ ഇസ്‌ലാമിക വിധിയെ പ്രതീക്ഷിക്കുന്നു', '', 'Pending', '2025-07-29 03:05:47'),
(29, 'Q0029', 22, 'Hanafi', NULL, NULL, 'Assalamualaikum, \r\nട്രെയിനിൽ നിന്ന് നിസ്കരിക്കാൻ പറ്റില്ലെങ്ക്തിൽ എന്തു ചെയ്യണം?', '', 'Pending', '2025-08-03 02:18:04'),
(30, 'Q0030', 23, 'Shafi', NULL, NULL, 'السلام عليكم \r\nശരീഅത്ത് നിയമപ്രകാരം ഒരു പുരുഷന് കാണൽ അനുവദനീയമായ സ്ത്രീകളും അതുപോലെ ഒരു സ്ത്രീക്ക് കാണൽ അനുവദനീയമായ പുരുഷന്മാരുടെയും ലിസ്റ്റ് അറിയിക്കാമോ', '', 'Pending', '2025-08-03 02:18:42'),
(31, 'Q0031', 15, 'Shafi', NULL, NULL, 'Shafi madhabil petteyaalk hanafi madhabil petteyaale vivaham cheyyan pattumo', '', 'Pending', '2025-08-04 04:09:58'),
(32, 'Q0032', 24, 'Hanafi', NULL, NULL, 'മൂക്ക് കുത്തുന്നതിന്റെ ഇസ്ലാമിക വിധി മദ്ഹബിന്റെ വീക്ഷണത്തിൽ', '', 'Pending', '2025-08-04 04:57:21'),
(33, 'Q0033', 23, 'Shafi', NULL, NULL, 'കാർഷിക വിളയായ അടക്ക കൃഷി ചെയ്യുന്നതിൻ്റെയും വിൽപ്പന നടത്തുന്നതിൻ്റെ യും ഇസ്ലാമിക വിധി എന്ത്', '', 'Pending', '2025-08-09 14:21:32'),
(34, 'Q0034', 25, 'Shafi', NULL, NULL, 'Islamika shareeath anusarich mudi vettenda reethi engane?', '', 'Pending', '2025-08-10 05:18:44'),
(35, 'Q0035', 17, 'Shafi', NULL, NULL, 'ഹദ്ദാദ്‌, മൗലൂദ്ഇൽ പങ്കെടുക്കുന്നതിന്റെ വിധി എന്ത്? തെറ്റാണെങ്കിൽ ഒരാൾ മൗലൂദിൽ പങ്കെടുത്തില്ലെങ്കിൽ കുടുംബക്കാർ/ഹദ്ദാദിൽ പങ്കടുത്തില്ലെങ്കിൽ നാട്ടുകാർ പ്രശ്നത്തിലാകും, അപ്പോൾ പങ്കെടുക്കാൻ പറ്റുമോ?', '', 'Pending', '2025-08-12 08:28:47'),
(36, 'Q0036', 17, 'Shafi', NULL, NULL, 'ഒരാൾക്ക് നിയന്ത്രണമില്ലാതെ ബേക്കിലൂടെ കൊട്ടുവായ(ريح)പോകുന്നു, നമസ്കാരത്തിലും പോകുന്നു, നിയന്ദ്രിക്കാൻ പറ്റുന്നില്ല, അവൻ എന്ത് ചെയ്യണം', '', 'Pending', '2025-08-12 08:33:15'),
(37, 'Q0037', 26, 'Shafi', NULL, NULL, 'ما حكم التنويم المغناطسي ؟', '', 'Pending', '2025-08-14 02:44:31'),
(38, 'Q0038', 7, 'Shafi', NULL, NULL, 'മയ്യത്ത് നമസ്കാരത്തിലെ സലാം വീട്ടുന്ന കാര്യത്തിൽ വ്യത്യസ്ത രീതികൾ കാണുന്നു.\r\n1.  \"അസ്സലാമു അലൈക്കും വ റഹ്‌മത്തുല്ലാഹ്\" എന്ന് ഒറ്റ സലാം മാത്രം ഇമാം ചൊല്ലി കേൾക്കുന്നു. (മക്കയിലും മദീനയിലും മറ്റും)\r\n2.  \"അസ്സലാമു അലൈക്കും വ റഹ്‌മത്തുല്ലാഹ്\" എന്ന് ഇരു വശത്തേക്കും സലാം ചൊല്ലുന്നു.\r\n3.  \"അസ്സലാമു അലൈക്കും വ റഹ്‌മത്തുല്ലാഹി വ ബറക്കാതുഹ്\"  എന്ന് ഇരു വശത്തേക്കും ഇമാം സലാം ചൊല്ലിയ ശേഷമേ മഅ്‌മൂമീങ്ങൾ സലാം ചൊല്ലാൻ പാടുള്ളു എന്ന് ചില ഇമാമീങ്ങൾ നിർദേശിക്കാറുണ്ട്.\r\n\r\nഇതിനെ കുറിച്ച് ഒരു വ്യക്തത വരുത്തിതന്നാൽ ഉപകാരമായിരിക്കും.', '', 'Pending', '2025-08-20 17:45:23'),
(39, 'Q0039', 27, 'Shafi', NULL, NULL, 'Tata Ethical Fund is this type of SIP are halal?', '', 'Pending', '2025-08-21 04:15:11'),
(40, 'Q0040', 28, 'Shafi', NULL, NULL, 'നഗ്നമായി കുളിക്കുന്നതിന്റെ വിധി എന്താണ്? അനുവദിനിയം ആണോ?', '', 'Pending', '2025-08-21 05:14:14'),
(41, 'Q0041', 16, 'Shafi', NULL, NULL, 'ഇന്ന് കാലത്ത് പലപ്പോഴും (മുസല്ലി)-യിൽ കഅ്ബയുടെ ചിത്രം വരച്ചിട്ടുണ്ടാകും.\r\n\r\n👉 അത്തരത്തിലുള്ള മുസല്ലയിൽ ഇരിക്കാമോ?\r\n👉 അതിന്മേൽ വസ്തു വച്ച് ഇരിക്കാമോ?', '', 'Pending', '2025-08-25 07:01:08'),
(42, 'Q0042', 29, 'Hanafi', NULL, NULL, 'ഓണാഘോഷ പരിപാടികളിൽ പങ്കെടുക്കുകയും ഓണ സദ്യ ദക്ഷിക്കുന്നതിൻ്റെയും ഇസ്ലാമിക വിധി ?', '', 'Pending', '2025-08-26 16:11:18'),
(43, 'Q0043', 30, 'Shafi', NULL, NULL, 'Is payment screenshot manipulation permissible in Islam?', '1756777289_VID_20250901_012217_293.mp4', 'Pending', '2025-09-02 01:41:29'),
(44, 'Q0044', 31, 'Shafi', NULL, NULL, 'സ്വർണം വാങ്ങാൻ കുറച്ചു പൈസ അഡ്വാൻസ് ആയി കൊടുക്കുന്ന ഇടപാട് ഇപ്പോൾ നാട്ടിൽ സജീവമാണ്. അങ്ങനെ അഡ്വാൻസ് ചെയ്യുന്ന കാരണത്താൽ സ്വർണം എടുക്കുമ്പോൾ അഡ്വാൻസ് ചെയ്ത സമയത്തെ വില അല്ലെങ്കിൽ എടുക്കുന്ന സമയത്തെ ഇവയിൽ ഏതാണോ കുറവ് അത് നൽകിയാൽ മതിയാകും. ഇങ്ങനെ ചെയ്യൽ അനുവദനീയമാണോ?', '', 'Pending', '2025-09-11 08:51:12'),
(45, 'Q0045', 23, 'Shafi', NULL, NULL, 'ഇതിനു മുമ്പ് രണ്ട് ചോദ്യം ചോദിച്ചിരുന്നു. മറുപടി പ്രതീക്ഷിക്കുന്നു.', '', 'Pending', '2025-09-11 08:56:43'),
(46, 'Q0046', 7, 'Shafi', NULL, NULL, 'നാല് റക്കഅത്ത് നമസ്കാരത്തിൽ മൂന്നാമത്തെ റക്കഅത്തിൽ ഇമാം സലാം വീട്ടി.  പിന്നിൽ നിന്നും ചിലർ ഓർമ്മപ്പെടുത്തിക്കൊടുത്തെങ്കിലും ഇമാം സ്വീകരിച്ചില്ല. ഇവിടെ നമസ്കരിച്ചവർ മൂന്ന് തരക്കാരായി.\r\n1.  മൂന്ന് റക്കഅത്തേ നിസ്കരിച്ചിട്ടുള്ളൂവെങ്കിലും നാലാണെന്ന് ഉറപ്പിച്ച ഇമാം.\r\n2.  മൂന്ന് റക്കഅത്തേ നിസ്കരിച്ചിട്ടുള്ളൂവെന്ന് ഉറപ്പുള്ള മഅ്‌മൂമീങ്ങൾ.\r\n3.  എത്ര റക്കഅത്ത് നിസ്കരിച്ചൂവെന്ന് ഉറപ്പില്ലാത്ത മഅ്‌മൂമീങ്ങൾ.\r\n\r\nഇത്തരം ഘട്ടങ്ങളിൽ എന്താണ് ചെയ്യേണ്ടത് ?', '', 'Pending', '2025-09-17 16:17:58'),
(47, 'Q0047', 32, 'Hanafi', NULL, NULL, 'السلام عليكم و رحمة الله و بركاته \r\nഷാഫി മദ്ഹബ് അനുസരിച്ച് ആദ്യത്തെ ഇരുത്തത്തിൽ അത്തഹിയ്യാത്തിന് ശേഷം നബി (സ) മേൽ സ്വലാത്ത് ചൊല്ലൽ ഉണ്ടല്ലോ അപ്പോൾ ഹനഫിയായ ഇമാമിനെ പിന്തുടരുമ്പോൾ ഇമാം സ്വലാത് ചൊല്ലാത്തതിൻ്റെ പേരിൽ മഅ്മൂം സഹ്വിൻ്റെ സുജൂദ് ചെയ്യണോ?', '', 'Pending', '2025-09-23 07:34:45'),
(48, 'Q0048', 33, 'Shafi', NULL, NULL, 'With humbly asking out of madhab (Q) ....\r\nI want the whole islamic politics pdf or whatever files that u can send me ......jazakallah khair', '', 'Pending', '2025-10-15 12:37:44'),
(49, 'Q0049', 34, 'Hanafi', NULL, NULL, 'Varsham തോറും സകാത്ത് കൊടുത്ത് വീട്ടിയിരുന്ന മുതലിൽ നിന്നും മറ്റൊരാൾ കച്ചവടത്തിൽ പങ്കാളിയാക്കാമെന്ന് തെറ്റിദ്ധരിപ്പിച്ചു പണം വാങ്ങി കബളിപ്പിച്ചു വർഷങ്ങൾക്ക് ശേഷം  അത് തിരികെ കിട്ടിയാൽ ഉടൻ തന്നെ കഴിഞ്ഞ വർഷങ്ങളിലെ സകാത്ത് കൊടുക്കണോ അതോ വീണ്ടും ഒരു വർഷം പൂർത്തിയാക്കിയിട്ടു കൊടുത്താൽ മതിയോ', '', 'Pending', '2025-10-17 00:38:22'),
(50, 'Q0050', 35, 'Hanafi', NULL, NULL, 'ഒരാൾ രണ്ട് വർഷമായി ഇഷാളന് ശേഷം വിത്ർ നമസ്കരിച്ചിട്ടില്ല. ആ  വ്യക്തി ഹനഫീ മദ്ഹബ്കാരനായിരുന്നു. ഇപ്പോൾ ശാഫിഈ മഹല്ലിൽ ഖിദ്മത്ത് ചെയ്യുന്നത് കൊണ്ട് ശാഫിഈ മദ്ഹബ്കാരനാണ് ഈ വ്യക്തിക്ക് മുമ്പ് ഹനഫിയായിരുന്നപ്പോൾ നഷ്ടമായ വിത്ർ നമസ്കാരം മടക്കേണ്ടതുണ്ടോ', '', 'Pending', '2025-10-19 08:02:30'),
(51, 'Q0051', 36, 'Hanafi', NULL, NULL, 'ഒരാൾ രണ്ട് വർഷമായി ഇഷാളന് ശേഷം വിത്ർ നമസ്കരിച്ചിട്ടില്ല. ആ  വ്യക്തി ഹനഫീ മദ്ഹബ്കാരനായിരുന്നു. ഇപ്പോൾ ശാഫിഈ മഹല്ലിൽ ഖിദ്മത്ത് ചെയ്യുന്നത് കൊണ്ട് ശാഫിഈ മദ്ഹബ്കാരനാണ് ഈ വ്യക്തിക്ക് മുമ്പ് ഹനഫിയായിരുന്നപ്പോൾ നഷ്ടമായ വിത്ർ നമസ്കാരം മടക്കേണ്ടതുണ്ടോ', '', 'Pending', '2025-10-19 09:16:59'),
(52, 'Q0052', 37, 'Shafi', NULL, NULL, 'Do the bonus provided by agencies like post office and others included in Interest and can be realized as Haram?', '', 'Pending', '2025-10-22 00:29:30'),
(53, 'Q0053', 38, 'Shafi', NULL, NULL, 'Can I use an alcohol-containing spray?', '', 'Pending', '2025-11-01 11:36:51'),
(54, 'Q0054', 38, 'Shafi', NULL, NULL, 'Can I use an alcohol-containing spray?', '', 'Pending', '2025-11-01 11:38:29'),
(55, 'Q0055', 39, 'Hanafi', NULL, NULL, 'ക്риസ്മസ് ഉത്പന്നങ്ങള്‍ രൂപങ്ങള്‍ അല്ലാത്ത decorative items kachavadam cheyyaamo Christmas ne lakshyamaaki', '', 'Pending', '2025-11-01 12:22:08'),
(56, 'Q0056', 23, 'Shafi', NULL, NULL, 'അസ്സലാമു അലൈക്കും വറഹ്മത്തുള്ള . നമ്മൾ സ്ഥിരമായി ഉപയോഗിക്കുന്ന പഞ്ചസാരയിലെ അതിനെ വെളുപ്പിക്കാൻ ആയിട്ട് കെമിക്കലും എല്ലുപൊടിയും ചേർക്കുന്നു എന്നൊരു റിപ്പോർട്ട് വായിക്കാൻ ഇടയായി അങ്ങനെയെങ്കിൽ നമ്മൾക്ക് ആ പ്രോഡക്റ്റ് ഉപയോഗിക്കൽ അനുവദനീയമാണോ', '', 'Pending', '2025-11-02 11:01:48'),
(57, 'Q0057', 40, 'Shafi', NULL, NULL, 'Magic is haram?', '', 'Pending', '2025-11-03 13:51:16'),
(60, 'Q0060', 41, 'Shafi', NULL, NULL, 'അശുദ്ധി ഉള്ളപ്പോൾ വുളൂഅ് ചെയ്യൽ അനുവദനീയമാണോ\r\nകോപം പോലെയുള്ള കാര്യങ്ങൾ ഉണ്ടാകുമ്പോൾ വുളൂഅ് ചെയ്യാമോ', '', 'Pending', '2025-11-04 01:32:09'),
(61, 'Q0061', 41, 'Shafi', NULL, NULL, 'അശുദ്ധി ഉള്ളപ്പോൾ വുളൂഅ് ചെയ്യൽ അനുവദനീയമാണോ\r\nകോപം പോലെയുള്ള കാര്യങ്ങൾ ഉണ്ടാകുമ്പോൾ വുളൂഅ് ചെയ്യാമോ', '', 'Pending', '2025-11-04 01:32:17'),
(62, 'Q0062', 42, 'Shafi', NULL, NULL, 'ചാണകം (cow dung) ബിസിനസ് ചെയ്യൽ അനുവാദം ഉണ്ടോ?\r\nഇന്ത്യയിൽ നിന്ന് ധാരാളം  പശു ചാണകം വിദേശരാജ്യങ്ങൾക്ക് എക്സ്പോർട്ട് ചെയ്യുന്നുണ്ട്. ഇങ്ങനെ ബിസിനസ് ചെയ്യാമോ?', '', 'Pending', '2025-11-06 06:52:16'),
(63, 'Q0063', 43, 'Shafi', NULL, NULL, 'ഇസ്തിഹാദ മൂത്ര വാർച്ച ഉള്ളവർക്ക് (തുടർച്ചയായിട്ട് അല്ല) , ഫർദിന് മുന്നേ ഉള്ള സുന്നത് നമസ്കരിച്ചാൽ ഫർദ് നമസ്കാരികാൻ പിന്നെയും വുദു എടുക്കണമോ?', '', 'Pending', '2025-11-18 16:17:43'),
(64, 'Q0064', 43, 'Shafi', NULL, NULL, 'ഇസ്തിഹാദ മൂത്ര വാർച്ച ഉള്ളവർക്ക് (തുടർച്ചയായിട്ട് അല്ല) , ഫർദിന് മുന്നേ ഉള്ള സുന്നത് നമസ്കരിച്ചാൽ ഫർദ് നമസ്കാരികാൻ പിന്നെയും വുദു എടുക്കണമോ?', '', 'Pending', '2025-11-18 16:17:43'),
(65, 'Q0065', 43, 'Shafi', NULL, NULL, 'ഏകദേശം 8–9 ആഴ്ച ഗർഭകാലത്ത് വളരെ ആദ്യം സംഭവിക്കുന്ന ഒരു miscarriage (ഗർഭപാതം) കഴിഞ്ഞാൽ, അതിന് ശേഷം വരുന്ന രക്തസ്രാവം നിഫാസ് ആണോ അതോ ഹൈദ് ആണോ എന്ന് അറിയാൻ ആഗ്രഹിക്കുന്നു.\r\nഅത്യാവശ്യം അറിയേണ്ട മറ്റൊരു കാര്യം: രക്തസ്രാവത്തിന്റെ ദിവസങ്ങൾ ആദ്യമായി രക്തം തുടങ്ങുന്ന ദിവസത്തിൽ നിന്ന് ആണോ എണ്ണേണ്ടത്, അല്ലെങ്കിൽ ടിഷ്യൂ/ക്ലോട്ട് പുറത്തിറങ്ങിയ ദിവസത്തിൽ നിന്ന് ആണോ എണ്ണേണ്ടത്?\r\nഇത് ഷാഫിഈ മദ്‌ഹബ് അനുസരിച്ചാണ് ഞാൻ ചോദിക്കുന്നത്.\r\n\r\nഎന്റെ സംശയം എന്തെന്നാൽ, ഗർഭകാലത്തിന്റെ ഈ ആദ്യ ഘട്ടത്തിൽ ഭ്രൂണം ഇപ്പോഴും അംഗങ്ങൾയും വ്യക്തമായ രൂപവും ലഭിച്ചിട്ടില്ല. അതിനാൽ ഇത് നിഫാസ് ആയി കണക്കാക്കുമോ എന്ന കാര്യത്തിൽ വ്യക്തതയില്ലായിരുന്നു. പ്രത്യേകിച്ച് ഹനഫി മദ്‌ഹബിൽ ഈ വിഷയത്തിൽ വ്യത്യസ്ത കാഴ്ചപ്പാടുകൾ ഉണ്ടെന്ന് അറിയുന്നതിനാൽ ഞാൻ ആശയക്കുഴപ്പത്തിലായിരുന്നു.', '', 'Pending', '2025-11-18 20:16:28'),
(66, 'Q0066', 44, 'Shafi', NULL, NULL, 'ഗോൾഡിന്റെയും സിൽവറിന്റെയും etf കളിൽ നിക്ഷേപിക്കുന്നതിൻെ വിധി എന്താണ്..?', '', 'Pending', '2025-11-19 03:56:12'),
(67, 'Q0067', 45, 'Hanafi', NULL, NULL, 'ജാർ ആപ്പിൽ സേവിങ്സ് നടത്തൽ അനുവദനീയമാണോ?', '1763885925_jar-cover_.webp', 'Pending', '2025-11-23 08:18:45'),
(68, 'Q0068', 46, 'Hanafi', NULL, NULL, 'ഹെയർ ട്രാൻസ്പ്ലാൻറ് ചെയ്യുന്നതിന്റെ വിധി', '', 'Pending', '2025-11-30 08:26:45'),
(69, 'Q0069', 47, 'Common', NULL, NULL, 'A father and mother got divorced more than 15yrs, അവർ വീണ്ടും വിവാഹം കഴിക്കാൻ തയ്യാറായാൽ എങ്ങനെയാണ് അവർക്ക് വീണ്ടും നിക്കാഹ് നടത്താൻ സാധ്യമാകുന്നത് എന്തെങ്കിലും പ്രശ്നങ്ങൾ തടസ്സങ്ങൾ ഉണ്ടോ', '', 'Pending', '2025-11-30 08:39:26'),
(70, 'Q0070', 37, 'Shafi', NULL, NULL, 'റേഷൻ സംവിധാനങ്ങൾ പോലുള്ള സപ്ലൈകോ സ്ഥാപനങ്ങളിൽ ബാക്കി വരുന്ന പലചരക്ക് വകകൾ മറ്റുള്ളവരുടെ കൂടി അർഹതയിൽ ഉള്ളതാണ് എന്നറിയാമല്ലോ.ഉദാഹരണത്തിന്  25 രൂപക്ക് ഒരു കിലോ പഞ്ചസാര ഒരു റേഷൻ കാർഡിന് നൽകുന്നു. ഇങ്ങനെ അർഹർ ഉള്ള വസ്തുക്കൾ ഈ സ്ഥാപനങ്ങളിൽ ധാരാളം ബാക്കിയാവുന്നും ഉണ്ട്. ഇത് ഇസ്ലാമിക സ്ഥാപനങ്ങളിലേക്കും അല്ലെങ്കിൽ ഒറ്റ വ്യക്തിക്കും ഇതേ വിലയിൽ തന്നെ ഉപയോഗിക്കൽ അനുവദനീയം ആണോ?', '', 'Pending', '2025-12-06 10:36:30'),
(71, 'Q0071', 48, 'Shafi', NULL, NULL, 'ജമും കസറും യാത്രയിൽ ചെയ്യുമ്പോൾ eg. മഗ്റിബ്നൊപ്പം ഇഷാ ജമും കസറും ചെയ്യുമ്പോൾ വിത്ർ  ആ സമയം നമസ്കരിക്കാമോ?', '', 'Pending', '2025-12-07 15:21:57'),
(72, 'Q0072', 49, 'Shafi', NULL, NULL, 'ഭാര്യമാർക്ക് ജമാഅത്തിന്റെ പുണ്യം ലഭ്യമാക്കുക എന്നതോ ജമാഅത്തിന് പോയാൽ ഭാര്യ വീട്ടിൽ തനിച്ചാകുമെന്നതോ ഭർത്താവിനെ സംബന്ധിച്ചിടത്തോളം ജമാഅത്തിന്റെ ഉദ്റ് ആണോ?_', '', 'Pending', '2025-12-08 04:09:14'),
(73, 'Q0073', 50, 'Shafi', NULL, NULL, 'Bitcoin നിൽ invest ചെയ്യുന്നത് halal ആണോ', '', 'Pending', '2025-12-08 05:07:58'),
(74, 'Q0074', 51, 'Shafi', NULL, NULL, 'Forex ട്രേഡിംഗ് ( കറൻസി or ഗോൾഡ് or Commodities) ചെയ്യുന്നതിന്റെ വിധിയെന്ത് ?', '', 'Pending', '2025-12-08 05:17:47'),
(75, 'Q0075', 52, 'Shafi', NULL, NULL, 'ഉസ്താദേ,\r\nവീട് പണയത്തിന് കൊടുക്കുന്നതിൻ്റെ വിധി എന്താണ് ?', '', 'Pending', '2025-12-13 16:30:22'),
(76, 'Q0076', 1, 'Common', NULL, NULL, 'test', '1767710109_10.png', 'Pending', '2026-01-06 14:35:09'),
(77, 'Q0077', 7, 'Shafi', NULL, NULL, 'കച്ചവടവുമായി ബന്ധപ്പെട്ട ചില ചോദ്യങ്ങൾ :\r\n\r\n1.  ചില കച്ചവടക്കാർ അൽപ്പം പഴകിയ സാധനങ്ങളെ (ഉദാഹരണം : റെഡിമെയ്ഡ് വസ്ത്രങ്ങൾ) ഭംഗിക്ക് വേണ്ടിയോ പുതിയതാണെന്ന് തോന്നിപ്പിക്കാൻ വേണ്ടിയോ പഴകിയ പാക്കിങ് മാറ്റി പഴയ കച്ചവടച്ചരക്കുകളെ പുതിയ കവറുകളിലാക്കി കസ്റ്റമറുടെ മുന്നിൽ പ്രദർശിപ്പിക്കുന്നതായി കണ്ടിട്ടുണ്ട്. ഇത് അനുവദനീയമാണോ ?\r\n\r\n2.  ഒരു രാജ്യത്ത് നിർമ്മിക്കാത്ത സാധനത്തിന് വ്യാജമായി ആ രാജ്യത്തിന്റെ ഉല്പന്നമാണെന്ന് കാണിക്കുന്ന ലേബൽ ഒട്ടിച്ച് വ്യാപാരം നടത്തുന്നതിനെക്കുറിച്ചുള്ള ഇസ്ലാമിക വിധി എന്താണ് ? (ഉദാഹരണത്തിന് ഇന്ത്യയിൽ നിർമ്മിച്ച ഒരു ഉൽപ്പന്നത്തിൽ *Made in Japan* എന്ന ലേബൽ പതിക്കുന്നത് പോലെ)', '', 'Pending', '2026-01-09 21:04:39'),
(78, 'Q0078', 53, 'Shafi', NULL, NULL, 'When taking bath for major impurity , if the nose is not passed with water like specifically , then is the ghusl invalid ? \r\nOr is it fine that the face is washed so the nose also comes under that . \r\nJazakallah khair', '', 'Pending', '2026-01-11 12:02:04'),
(79, 'Q0079', 54, 'Shafi', NULL, NULL, 'അല്ലാഹുവിന്റെ സിഫാത്ത്കളുമായി ബന്ധപ്പെട്ട് വന്ന ആയത്തുകളും ഹദീസുകളും ഉദാഹരണമായി റബ്ബിന്റെ وجه ، يد، اصبع ഈ കലിമത്തുകൾ കൊണ്ടുള്ള വിവക്ഷ എന്താണ് റബ്ബിന് وجه،  يد ഒക്കെ ഹകീക്കിയായി തന്നെ ഉണ്ടോ \r\n\r\nവിശദമായൊരു മറുപടി പ്രതീക്ഷിക്കുന്നു', '', 'Pending', '2026-01-13 15:31:56'),
(80, 'Q0080', 55, 'Shafi', NULL, NULL, 'പേരെഴുതിയ മീസാൻ കല്ല് ഖബറിൽ നാട്ടാമോ? പിന്നീട് പൊളിക്കേണ്ടി വന്നാൽ അതിനു തടസ്സവാദം ഉന്നയിക്കുന്നതല്ല എന്ന് കരാർ എഴുതി ഒപ്പിട്ടു നൽകിയിട്ടുണ്ട്.', '', 'Pending', '2026-01-14 03:25:19'),
(81, 'Q0081', 55, 'Shafi', NULL, NULL, 'പേരെഴുതിയ മീസാൻ കല്ല് ഖബറിൽ നാട്ടാമോ? പിന്നീട് പൊളിക്കേണ്ടി വന്നാൽ അതിനു തടസ്സവാദം ഉന്നയിക്കുന്നതല്ല എന്ന് കരാർ എഴുതി ഒപ്പിട്ടു നൽകിയിട്ടുണ്ട്.', '', 'Pending', '2026-01-14 03:25:19'),
(82, 'Q0082', 56, 'Shafi', NULL, NULL, 'Are forex funded accounts permissible?\r\nFunded accounts are where you pay a certain amount to purchase an account where if you meet a certain profit target, they will give you a live account for you to trade with. We can take payouts with profit split. Is it allowed in islam?', '', 'Pending', '2026-01-18 12:21:44'),
(83, 'Q0083', 57, 'Hanafi', NULL, NULL, 'Is it allowed to take photo graphs in wedding taking in mobile. And keeping a photographer for taking picture', '', 'Pending', '2026-01-19 02:04:36'),
(84, 'Q0084', 58, 'Common', NULL, NULL, 'Assalamu Alaikum, Usthadh i have seen one fatwa that "FATWA:596-593/B=6/1443" from Darul uloom deoband "അത് പോലെ ഖബറിൽ മയ്യിത്തിന്റെ തലയുള്ള ഭാഗത്ത് നിന്ന് സൂറ ബഖറയുടെ ആദ്യ ആയത്തുകളും കാലിന്റെ ഭാഗത്ത് നിന്ന് അവസാന ആയത്തുകളും പാരായണം ചെയ്യുക. ഇങ്ങനെ ഈസാൽ സവാബ് ചെയ്യൽ ഫർളല്ല മറിച്ച് വെറും മുസ്തഹബ്ബ് മാത്രമാണ് എന്ന് എപ്പോഴും ഓർമയിൽ ഉണ്ടായിരിക്കണം." On the same way i have seen one another fatwa from other side that is" • മയ്യിത്തിന്റെ അരികിൽ കുര്‍ആൻ പാരായണം ചെയ്യാൻ നബി ﷺ യിൽ നിന്നുമുള്ള ചര്യയിൽ ഇല്ലാത്തതാണ്.\r\n• നബി ﷺ യോ സ്വഹാബത്തോ അപ്രകാരം ചെയ്തതായി പ്രാമാണികമായ യാതൊരു തെളിവും ഇല്ല." So please advise which should i go for authenticity.', '', 'Pending', '2026-01-20 12:14:43'),
(85, 'Q0085', 52, 'Shafi', NULL, NULL, 'അന്യ പുരുഷന്മാരോട് സംസാരിക്കേണ്ടി വരുന്ന സാഹചര്യങ്ങളിൽ സംസാരിക്കാൻ തുടങ്ങുമ്പോൾ സലാം പറയേണ്ടതുണ്ടോ, ഇനി പറയുകയാണെങ്കിൽ അത് കുഴപ്പമാണോ', '', 'Pending', '2026-02-03 12:47:46'),
(86, 'Q0086', 59, 'Shafi', NULL, NULL, 'റമദാനിൽ പകൽ സമയത്ത് അമുസ്ലിമികൾക്ക് ഫുഡ് കൊടുക്കാമോ?eg. വീട്ടിൽ ജോലിക്ക് നിൽക്കുന്ന ആൾക്കാർ etc', '', 'Pending', '2026-02-05 15:01:33'),
(87, 'Q0087', 59, 'Shafi', NULL, NULL, 'ഷാഫി മദബ് അനുസരിച്ച് സുന്നത് നമസ്ക്കരത്തിൽ സുജൂദിൽ dua ചെയ്യാമോ? മലയാളത്തിലോ അറബിയിലോ?', '', 'Pending', '2026-02-05 15:03:21'),
(88, 'Q0088', 59, 'Shafi', NULL, NULL, 'ബാങ്കുകളിൽ internship ചെയ്യാമോ? paid  or unpaid ആയിട്ട്? cybersecurity department ഇൽ.', '', 'Pending', '2026-02-05 15:05:32'),
(89, 'Q0089', 11, 'Shafi', NULL, NULL, 'ഒരാൾ ഒരു വർഷം കൊടുക്കാനുള്ള സക്കാത്തിന്റെ പണം കൊടുത്ത് വീട്ടി. ഇനി അടുത്ത വർഷമാണ് സക്കാത്ത് കൊടുക്കാനുള്ളത്. എന്നാൽ ആ രണ്ട് സക്കാത്തിниടയിൽ ആവശ്യക്കാരായ ആളുകൾ വരാറുണ്ട്. അവർക്ക് കൊടുക്കുന്ന പണം അടുത്ത വർഷത്തെ സക്കാത്തായി പരിഗണിക്കാമോ? അതുപോലെ ചാരിറ്റിയുടെ ആളുകൾ വന്നാൽ അവർ നിർധനരായ ആളുകൾക്ക് കൊടുക്കും എന്ന് വിശ്വസിച്ച് ചാരിറ്റിയുടെ ആളുകൾക്ക് പണം കൊടുക്കുകയും അത് അടുത്ത വർഷത്തെ സക്കാത്തായി പരിഗണിക്കുകയും ചെയ്യാമോ?', '', 'Pending', '2026-02-07 18:23:10'),
(90, 'Q0090', 11, 'Shafi', NULL, NULL, 'ഒരാൾ മറ്റൊരാൾക്ക് ‘ലാഭം കിട്ടിയാൽ എന്തെങ്കിലും തന്നാൽ മതി’ എന്ന് പറഞ്ഞ് ഭൂമിക്കച്ചവടം ചെയ്യാനായി ഒരു തുക നൽകി. ആ പണത്തിന്റെ സകാത്ത് ആരാണ് നൽകേണ്ടത്?', '', 'Pending', '2026-02-07 18:24:18'),
(91, 'Q0091', 11, 'Shafi', NULL, NULL, 'ഒരാൾ മറ്റൊരാൾക്ക് കച്ചവടം ചെയ്യാനായി പണം കടമായി നൽകി. പണത്തിന്റെ സക്കാത്ത് കടം കൊടുത്തയാൾ ആണോ അതല്ല പണം വാങ്ങിയ ആളാണോ നൽകേണ്ടത്? അതല്ല രണ്ടുപേരിൽ നിന്നും ഓരോരുത്തരും  നൽകണോ? അതല്ല രണ്ടുപേരും ചേർന്ന് നൽകണോ?\r\nഇനി രണ്ടുപേരിൽ ആരെങ്കിലും ഒരാൾ നൽകിയാൽ മതി എങ്കിൽ രണ്ടുപേരും ചേർന്ന് ആരാണ് നൽകേണ്ടത് എന്ന് തീരുമാനിക്കേണ്ടി വരില്ലേ?', '', 'Pending', '2026-02-07 18:25:25'),
(92, 'Q0092', 11, 'Shafi', NULL, NULL, 'ഒരാൾ ഒരു സാധനം മറ്റൊരാൾക്ക് വിറ്റു. പക്ഷേ ആ സാധനത്തിന്റെ പൈസ കിട്ടിയിട്ടില്ല. അതിൻ്റെ സക്കാത്ത് കൊടുക്കേണ്ടത് ആരാണ്? അഥവാ പൈസ കിട്ടേണ്ട ആൾ ആ പൈസ കിട്ടുന്നതിന് മുമ്പ് സക്കാത്ത് കൊടുക്കണോ? അതല്ല ആ സാധനം വാങ്ങിയ ആൾ ആണോ സക്കാത്ത് കൊടുക്കേണ്ടത്?', '', 'Pending', '2026-02-07 18:26:26'),
(93, 'Q0093', 11, 'Shafi', NULL, NULL, 'ഒരാൾ വില്പനക്കായി കുറച്ചു ഉൽപ്പന്നങ്ങൾ കടമായി വാങ്ങി. സക്കാത്തിന്റെ സ്റ്റോക്ക് എടുക്കുമ്പോൾ ആ ഉൽപ്പന്നങ്ങളും സക്കാത്തിൽ ഉൾപ്പെടുത്തണോ?', '', 'Pending', '2026-02-07 18:27:52'),
(94, 'Q0094', 60, 'Hanafi', NULL, NULL, 'മയ്യിത്ത് ഖബർ അടക്കിയ ശേഷം കൃത്യം മൂന്നാം ദിവസം ഔദ്വോഗിക ഖബർ സന്ദർശനം നടത്തുന്നതിന് ഇസ്ലാമിൽ എന്താണ് തെളിവ്. 2. മയ്യിത്തിനു വേണ്ടി എഴുപതിനായിരം ലാഇലാഹ....ചൊല്ലി ദുആ ചെയ്യാൻ \"ദിക്ർ\" എന്ന പേരിൽ നുറൂ കണക്കിന് ജനങ്ങളെ ക്ഷണിച്ച് വിവാഹ സദ്യ പോലെ  വിപുലമായി നടത്തപ്പെടുന്ന പരിപാടി ദീനീ കാഴ്ചപ്പാടിൽ എങ്ങനെയാണ്.', '', 'Pending', '2026-02-10 06:19:08'),
(95, 'Q0095', 61, 'Shafi', NULL, NULL, 'Is it permissable to hide hands under the shawl while salah', '', 'Pending', '2026-02-12 14:22:39'),
(96, 'Q0096', 31, 'Shafi', NULL, NULL, 'ഞാൻ പ്ലൈവുഡ് നിർമാണ കമ്പനി നടത്തുന്ന ആളാണ്. വർഷാവസാനം സകാത്ത് കണക്കാക്കേണ്ടത് എങ്ങനെയാണ്. മിഷനറികൾക്ക് കണക്കിൽ പെടുത്തേണ്ടതുണ്ടോ?', '', 'Pending', '2026-02-13 13:38:19'),
(97, 'Q0097', 62, 'Shafi', NULL, NULL, 'ما هو حكم صوم نصف شعبان ؟ هل يجوز الصوم من حيث كونه أيام البيض كما قال ابن حجر أو من حيث خصوصه باحتجاج حديث؛صوموا نهارها. كما قال الرملي', '', 'Pending', '2026-02-15 15:37:30'),
(98, 'Q0098', 63, 'Shafi', NULL, NULL, 'Question Regarding Nikāḥ validity and Wali\r\n\r\nAssalāmuʿalaykum,\r\nI am writing to seek clarification regarding the rulings of Nikāḥ according to the Shāfiʿī madhhab.\r\n\r\nCurrently, our parents approve of us getting married, however  wish for us to delay marriage until we have completed our studies. We are considering to  proceed  a Nikāḥ without informing our families.\r\nWe have known each other for over a year now.\r\n\r\nMy questions are as follows:\r\n\r\n1. According to the Shāfiʿī madhhab, who would serve as the walī for the bride in such a situation, particularly if the marriage is conducted in secrecy without  her family knowing?\r\n2. Is it permissible to conduct a Nikāḥ without the approval or knowledge of our parents?\r\n\r\nJazākumallāhu khayran.', '', 'Pending', '2026-02-16 18:29:23'),
(99, 'Q0099', 64, 'Hanafi', NULL, NULL, 'ഹാൻസ് വെച്ചാൽ നോമ്പ് മുറിയുമോ? കാരണം അത് അകത്തേത് പോകുന്നില്ലല്ലോ? ഇതിൻ്റെ വിധി എന്താണ്', '', 'Pending', '2026-02-20 10:25:23'),
(100, 'Q0100', 65, 'Shafi', NULL, NULL, 'മുസ്ലിമീങ്ങളും അമുസ്ലിമീങ്ങളും താമസിക്കുന്ന ഒരു നാട്ടിൽ മുസ്ലിമീങ്ങളുടെ മാത്രമേ കടകൾ ഉള്ളൂ ഈ നാട്ടിൽ അമുസ്ലിമീങ്ങളും ഉള്ളതിനാൽ അവർക്ക് കടകളിൽ നിന്നും ആഹാരം വാങ്ങിക്കേണ്ട ആവശ്യമുണ്ടായേക്കാം അതുപോലെതന്നെ യാത്രക്കാരായ ആളുകൾക്കും ആഹാരം ആവശ്യം വന്നേക്കാംഅങ്ങനെയുള്ള സാഹചര്യത്തിൽ നോമ്പ് സമയത്ത് കടകൾ തുറക്കാൻ അനുവാദം ഉണ്ടോ ?', '', 'Pending', '2026-02-21 12:51:20'),
(101, 'Q0101', 7, 'Shafi', NULL, NULL, 'റമളാൻ മാസത്തിൽ പള്ളികളിൽ പൊതുവായ നിലയിൽ നോമ്പുതുറയും ഭക്ഷണവും നൽകാറുണ്ട്. വ്യക്തിപരമായോ പൊതുവായോ ക്ഷണിക്കപ്പെടാതെയും സൗജന്യഭക്ഷണത്തിന് ആവശ്യക്കാരനല്ലാതിരിക്കുകയും ചെയ്യുന്ന സന്ദർഭങ്ങളിൽ ഇത്തരം സൽക്കാരങ്ങളിൽ പങ്കെടുക്കുന്നതിനെ കുറിച്ചുള്ള ഇസ്ലാമിക അധ്യാപനം എന്താണ് ?', '', 'Pending', '2026-02-24 10:05:13'),
(102, 'Q0102', 59, 'Shafi', NULL, NULL, 'തറവീഹ് നമസ്കാരത്തിൽ നമമുക് മുന്നേ കളാ \r\nആയ നമസ്കാരങ്ങളെ വീട്ടാം എന്ന് കേൾക്കുന്നു. ഏത് രീതിയിലാണ് ഇത് ചെയ്യേണ്ടത് ?', '', 'Pending', '2026-02-24 15:24:35'),
(103, 'Q0103', 66, 'Shafi', NULL, NULL, 'Oral manappoorvam nomb murichal enthaan vidhi.?', '', 'Pending', '2026-02-27 02:26:11'),
(104, 'Q0104', 67, 'Shafi', NULL, NULL, 'ഇസ്തിഗാസ( അല്ലാഹുവല്ലാത്തവരോട് നേരിട്ട് ചോദിക്കുക,) ചെയ്യുന്നതിന്റെ വിധി എന്താണ്, ഒരു വിശദമായ മറുപടി ഉദ്ദേശിക്കുന്നു..', '', 'Pending', '2026-03-29 06:06:53'),
(105, 'Q0105', 67, 'Shafi', NULL, NULL, 'ഇസ്തിഗാസ( അല്ലാഹുവല്ലാത്തവരോട് നേരിട്ട് ചോദിക്കുക,) ചെയ്യുന്നതിന്റെ വിധി എന്താണ്, ഒരു വിശദമായ മറുപടി ഉദ്ദേശിക്കുന്നു..', '', 'Pending', '2026-03-29 06:07:06'),
(106, 'Q0106', 67, 'Shafi', NULL, NULL, 'ശഹബാൻ 15 ന് മാത്രമായി പ്രത്യേകമായി ഒരു നോമ്പ്  സുന്നത്ത് ഉണ്ടോ', '', 'Pending', '2026-03-29 06:19:10'),
(107, 'Q0107', 67, 'Shafi', NULL, NULL, 'ശഹബാൻ 15 ന് മാത്രമായി പ്രത്യേകമായി(അയ്യഅമുൽ ബീള്ള അല്ലാതെ ) ഒരു നോമ്പ്  സുന്നത്ത് ഉണ്ടോ', '', 'Pending', '2026-03-29 06:22:41'),
(108, 'Q0108', 68, 'Shafi', NULL, NULL, 'അസ്സലാമുഅലൈക്കും \r\n     ഉസ്താദേ എന്റെ പേര് അബു ലയിക് ഏകദേശം 4 വർഷത്തോളമായി ഞാൻ കടക്കെണിയിൽ ആണ് അത്യാവശ്യം നല്ല ബുദ്ധിമുട്ടിലാണ് കടത്തിന്റെ വിഷയത്തിൽ  എനിക്ക് ഒരു ജോലി ഉണ്ട് അൽഹംദുലില്ലാഹ്  പക്ഷെ എനിക്ക് അതിൽ നിന്ന് എന്റെ കടത്തിലേക്ക് ഒന്നും തന്നെ ചെയ്യാനോ മാറ്റിവെക്കാനോ പറ്റുന്നില്ല കടക്കാരെ കൊണ്ട് വളരെ ബുദ്ധിമുട്ടിലാണ് ഞാൻ ഈ  ഒരു അവസ്ഥയിൽ \r\nഎനിക്ക് indraday forex treading polymarket പോലോത്ത ട്രേഡിങ്  അല്ലെങ്കിൽ FD ,SIP \r\nചെയ്യാൻ അനുവാദം ഉണ്ടാകുമോ ...???\r\nമറ്റെന്തെങ്കിലും പ്രധിവിധി .????', '', 'Pending', '2026-04-05 12:24:02'),
(109, 'Q0109', 42, 'Shafi', NULL, NULL, 'അസ്സലാമു അലൈക്കും,\r\nഎന്റെ വീട്ടിലെ വൈദ്യുതി ബിൽ വളരെ കൂടുതലാണ് (ഏകദേശം ₹16,000 രണ്ട് മാസത്തിൽ ഒരിക്കൽ). ഇത് കുറയ്ക്കുന്നതിനായി 5 kW സോളാർ സിസ്റ്റം സ്ഥാപിക്കാൻ ആലോചിക്കുന്നു.\r\n\r\nആകെ ചെലവ്: ₹3.2 ലക്ഷം\r\nസബ്സിഡി: ₹78,000\r\nഎനിക്ക് അടയ്ക്കേണ്ടത്: ₹2.44 ലക്ഷം\r\n\r\nസോളാർ സ്ഥാപിച്ചാൽ മാസവൈദ്യുതി ചെലവ് ഗണ്യമായി കുറയും. എന്നാൽ ഇപ്പോൾ മതിയായ പണം ഇല്ല, പലിശയില്ലാത്ത വായ്പയും ലഭ്യമല്ല.\r\n\r\nഇത്തരം സാഹചര്യത്തിൽ സാമ്പത്തിക ഭാരവും കുറയ്ക്കുന്നതിനായി പലിശയുള്ള വായ്പ എടുക്കുന്നത് അനുമതിയുള്ളതാണോ?', '', 'Pending', '2026-04-28 10:44:35');

ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `question_id` (`question_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;
COMMIT;
SQL;
        
        // Transform all table references to virtual staging table old_questions
        $status[] = ["Virtualizing SQL dump to staging table...", "info"];
        $sqlContent = str_replace("`questions`", "`old_questions`", $sqlContent);
        
        // Execute the complete staged SQL dump
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec($sqlContent);
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        
        // Get staging count
        $stagingCount = $pdo->query("SELECT COUNT(*) FROM old_questions")->fetchColumn();
        $status[] = ["Successfully loaded <strong>{$stagingCount}</strong> raw question entries into staging table.", "success"];

        // We do not load old_users into a database staging table. Instead, we keep them as a raw PHP array!
        $status[] = ["Parsed user dump records safely inside PHP memory.", "success"];

        // --- STEP 3: PREPARE AND RESOLVE MAPS ---
        $status[] = ["Fetching system Madhab maps...", "info"];
        $madhabRows = $pdo->query("SELECT id, name FROM madhabs")->fetchAll();
        $madhabMap = [];
        foreach ($madhabRows as $row) {
            $madhabMap[$row['name']] = $row['id'];
        }
        
        // Fallback or default Madhab ID (General/Other)
        $defaultMadhabId = $madhabMap['General/Other'] ?? $madhabMap['Other'] ?? null;
        if (!$defaultMadhabId) {
            // Find any valid ID
            $defaultMadhabId = reset($madhabMap);
        }
        
        // Map old enum string values to new system names (Shafii, Hanafi, Other)
        $madhabResolver = [
            'Shafi' => 'Shafii',
            'Hanafi' => 'Hanafi',
            'Common' => 'Other'
        ];

        // Spatie Questioner Role ID
        $questionerRoleId = $pdo->query("SELECT id FROM roles WHERE name = 'questioner'")->fetchColumn();

        // --- STEP 4: MIGRATE AND BUILD INTEGRITY ---
        $status[] = ["Beginning data transformation and user check...", "info"];
        
        $oldQuestions = $pdo->query("SELECT * FROM old_questions ORDER BY id ASC")->fetchAll();
        $migratedCount = 0;
        $usersCreated = 0;
        
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        
        // Prepared statements for fast query processing
        $userCheckStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $userCreateStmt = $pdo->prepare("INSERT INTO users (id, name, email, password, is_active, profile_completed, created_at, updated_at) VALUES (?, ?, ?, NULL, 1, 0, NOW(), NOW())");
        $roleAssignStmt = $pdo->prepare("INSERT IGNORE INTO model_has_roles (role_id, model_type, model_id) VALUES (?, 'App\\\\Models\\\\User', ?)");
        $userMadhabStmt = $pdo->prepare("INSERT IGNORE INTO madhab_user (user_id, madhab_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        
        // --- NEW: MIGRATE DETAILED USER INFORMATION DIRECTLY FROM PHP ARRAY ---
        $status[] = ["Migrating old user accounts with detailed profiles...", "info"];
        $oldUsers = [
            ['id' => 1, 'mobile' => '9747650176', 'name' => 'Haris I M', 'email' => 'haris.imran.etpa@gmail.com'],
            ['id' => 4, 'mobile' => '9747258258', 'name' => 'Haris Moulavi Kausari', 'email' => 'ashifcm2003@gmail.com'],
            ['id' => 5, 'mobile' => '9847486326', 'name' => 'Abidh Ibrahim', 'email' => 'zadhzainab@gmail.com'],
            ['id' => 6, 'mobile' => '8281840567', 'name' => 'Muhammed Hafiz', 'email' => 'pokihafiz090@gmail.com'],
            ['id' => 7, 'mobile' => '09446845704', 'name' => 'ABDUL RASHEED M K', 'email' => 'rasheediya@gmail.com'],
            ['id' => 8, 'mobile' => '9778013939', 'name' => 'salahudden', 'email' => 'Kauzariyyaweb527@gmail.com'],
            ['id' => 9, 'mobile' => '9544422730', 'name' => 'Muhammed shahin s', 'email' => 'Shahinshahu8655@gemil.com'],
            ['id' => 10, 'mobile' => '7025292394', 'name' => '', 'email' => 'qamar2020.kandoth@gmail.com'],
            ['id' => 11, 'mobile' => '8848426262', 'name' => 'MOHAMMED ANAS PCH', 'email' => ''],
            ['id' => 12, 'mobile' => '٩٨٤٧٣٦٣٢٥٠', 'name' => 'عمر الفاروق', 'email' => 'umarulfarooqttt@gmail.com'],
            ['id' => 13, 'mobile' => '9177224491', 'name' => 'Muhammed Thaha', 'email' => 'muhd.thaha@gmail.com'],
            ['id' => 14, 'mobile' => '9567610024', 'name' => 'Ameen', 'email' => ''],
            ['id' => 15, 'mobile' => '8848676768', 'name' => 'Fathima', 'email' => ''],
            ['id' => 16, 'mobile' => '7736141762', 'name' => 'Adil', 'email' => ''],
            ['id' => 17, 'mobile' => '9847363250', 'name' => '', 'email' => ''],
            ['id' => 18, 'mobile' => '8943096325', 'name' => 'Muhammad Abdulkhader', 'email' => ''],
            ['id' => 19, 'mobile' => '7994003414', 'name' => 'Mohammed nowfal', 'email' => ''],
            ['id' => 20, 'mobile' => '9947255079', 'name' => 'Muhammed Minhaj', 'email' => 'minhajkallara786@gmail.com'],
            ['id' => 21, 'mobile' => '8590863723', 'name' => 'Suhail kochi', 'email' => ''],
            ['id' => 22, 'mobile' => '9745205559', 'name' => 'Dr Mohammed Fazil L', 'email' => 'drfazilindia@gmail.com'],
            ['id' => 23, 'mobile' => '9048548899', 'name' => 'Kuttickal Aboobaker Abdul Samath', 'email' => 'asamad29@hotmail.com'],
            ['id' => 24, 'mobile' => '09947229145', 'name' => 'Hakkim sha', 'email' => 'abuaasiyaummumariyam@gmail.com'],
            ['id' => 25, 'mobile' => '8075050852', 'name' => 'Abid Hussain', 'email' => ''],
            ['id' => 26, 'mobile' => '6282974883', 'name' => 'Ashik', 'email' => 'vavashiray@gmail.com'],
            ['id' => 27, 'mobile' => '7736486364', 'name' => 'Faisal ka', 'email' => 'faisalka.faisal@gmail.com'],
            ['id' => 28, 'mobile' => '+919745872898', 'name' => 'Fazil Sulaiman', 'email' => 'fazilsulaimansogec7@gmail.com'],
            ['id' => 29, 'mobile' => '9947217492', 'name' => 'Shamsudheen', 'email' => 'shamsudheenps2000@gmail.com'],
            ['id' => 30, 'mobile' => '09947377303', 'name' => 'Ashif', 'email' => 'ashifcm2003@gmail.com'],
            ['id' => 31, 'mobile' => '9061446966', 'name' => 'മുഹമ്മദ്', 'email' => ''],
            ['id' => 32, 'mobile' => '9496933991', 'name' => 'Haneefa molavi', 'email' => 'rafeeqcngra@gmail.com'],
            ['id' => 33, 'mobile' => '6282 515 040', 'name' => 'Sahadsafary', 'email' => 'Saadsafari850@gmail.com'],
            ['id' => 34, 'mobile' => '08921127045', 'name' => 'Shafeek', 'email' => 'sebeekcontalam@gmail.com'],
            ['id' => 35, 'mobile' => '7510553671', 'name' => 'Qasim', 'email' => 'qasimiyya97@gmail.com'],
            ['id' => 36, 'mobile' => '7902623670', 'name' => 'Qasim', 'email' => 'qasimiyya97@gmail.com'],
            ['id' => 37, 'mobile' => '07994574495', 'name' => 'Muhammad Aslam', 'email' => 'muhammadaslamsulthans313@gmail.com'],
            ['id' => 38, 'mobile' => '9496963129', 'name' => 'SABIRA', 'email' => 'navasahmad.ka@gmail.com'],
            ['id' => 39, 'mobile' => '9656423999', 'name' => 'NIYAS', 'email' => 'Niyaskont@gmail.com'],
            ['id' => 40, 'mobile' => '8157946197', 'name' => 'Abdullah ibn Thajudheen', 'email' => 'abdullathajudheen123@gmail.com'],
            ['id' => 41, 'mobile' => '9947312438', 'name' => 'Fathimath swaliha', 'email' => 'fathimathswalihakb@gmail.com'],
            ['id' => 42, 'mobile' => '9037635480', 'name' => 'Mirza Khan', 'email' => 'mirzakhanf@gmail.com'],
            ['id' => 43, 'mobile' => '9447382815', 'name' => 'Noora', 'email' => 'aqthar.98@gmail.com'],
            ['id' => 44, 'mobile' => '9995778469', 'name' => 'Ilyas', 'email' => 'ilyasbinabdulla@gmail.com'],
            ['id' => 45, 'mobile' => '9747124147', 'name' => 'Ansal pa', 'email' => 'ansaletpa@gmail.com'],
            ['id' => 46, 'mobile' => '09995641411', 'name' => 'Farzeen Shefeek', 'email' => 'farzeenshefeek123@gmail.com'],
            ['id' => 47, 'mobile' => '7012664265', 'name' => 'Anvar Hussain', 'email' => 'anvaraf11@gmail.com'],
            ['id' => 48, 'mobile' => '0096896949010', 'name' => 'Noora', 'email' => 'noora.atnsa@gmail.com'],
            ['id' => 49, 'mobile' => '9448328956', 'name' => 'Hafiz', 'email' => 'hafizmuzzammil@gmail.com'],
            ['id' => 50, 'mobile' => '9633181410', 'name' => 'Nihal', 'email' => ''],
            ['id' => 51, 'mobile' => '9567013505', 'name' => 'Sinan P', 'email' => 'sinan.pathoor@gmail.com'],
            ['id' => 52, 'mobile' => '8590695557', 'name' => 'Afzal', 'email' => 'afsalahmad216@gmail.com'],
            ['id' => 53, 'mobile' => '+60 139401486', 'name' => 'Ammar', 'email' => 'ammar.atnsa@gmail.com'],
            ['id' => 54, 'mobile' => '9746467599', 'name' => 'Anwar Binkhalid', 'email' => 'anwarbinkhalid0@gmail.com'],
            ['id' => 55, 'mobile' => '8075191562', 'name' => 'JAWAD MOULAVI', 'email' => 'jawadmoulavi@gmail.com'],
            ['id' => 56, 'mobile' => '9037642019', 'name' => 'Shamsudheen Rasheed', 'email' => 'shamsudh55@gmail.com'],
            ['id' => 57, 'mobile' => '9884027860', 'name' => 'Y. Mohamed Suhail Sait', 'email' => 'sait121@gmail.com'],
            ['id' => 58, 'mobile' => '09746183268', 'name' => 'Nadheer Rasheed', 'email' => 'nadeerrasheed@gmail.com'],
            ['id' => 59, 'mobile' => '96949010', 'name' => 'Nooraaqthar Muhammedali', 'email' => 'noora.atnsa@gmail.com'],
            ['id' => 60, 'mobile' => '9656109686', 'name' => 'K Y Ansari', 'email' => 'ansaryalfalah70@gmail.com'],
            ['id' => 61, 'mobile' => '9535461413', 'name' => 'Ummulqasim', 'email' => 'shabnarahman39@gmail.com'],
            ['id' => 62, 'mobile' => '9188846213', 'name' => 'Fouzan', 'email' => 'theistonlyone1819@gmail.com'],
            ['id' => 63, 'mobile' => '+64224392571', 'name' => 'Abdullah', 'email' => 'orchidmsa@gmail.com'],
            ['id' => 64, 'mobile' => '9895940818', 'name' => 'Shahul', 'email' => ''],
            ['id' => 65, 'mobile' => '8089226810', 'name' => 'Abdulla Ebrahim K A', 'email' => 'abdullaebrahimka07@gmail.com'],
            ['id' => 66, 'mobile' => '9961970103', 'name' => 'Zaid', 'email' => '0103habeebrahman@gmail.com'],
            ['id' => 67, 'mobile' => '+91 80894 80713', 'name' => 'Abdul rahman', 'email' => 'muhammedjumail77@gmail.com'],
            ['id' => 68, 'mobile' => '9995229237', 'name' => 'Abu Laiq', 'email' => ''],
        ];
        $usersMigrated = 0;
        
        $userUpsertStmt = $pdo->prepare("INSERT INTO users (
            id, name, email, mobile, password, is_active, profile_completed, created_at, updated_at
        ) VALUES (?, ?, ?, ?, NULL, 1, 0, NOW(), NOW())
        ON DUPLICATE KEY UPDATE 
            name = VALUES(name),
            email = VALUES(email),
            mobile = VALUES(mobile),
            updated_at = NOW()");
            
        $seenEmails = [];
        $emailCheckStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");

        foreach ($oldUsers as $ou) {
            $rawName = trim($ou['name']);
            $name = empty($rawName) ? "Guest User #" . $ou['id'] : $rawName;
            
            $rawEmail = trim($ou['email']);
            if (empty($rawEmail)) {
                $email = "user" . $ou['id'] . "_imported@daruliftakauzariyya.com";
            } else {
                $email = strtolower($rawEmail);
            }
            
            // Track and ensure unique emails to avoid database index collisions (1062 Duplicate entry)
            // 1. Check if seen in the current migration run
            if (in_array($email, $seenEmails)) {
                $emailParts = explode('@', $email);
                if (count($emailParts) === 2) {
                    $email = $emailParts[0] . "+" . $ou['id'] . "@" . $emailParts[1];
                } else {
                    $email = $email . "_" . $ou['id'];
                }
            }
            
            // 2. Double-check if this email is already taken by another user ID in the live database
            $emailCheckStmt->execute([$email, $ou['id']]);
            $existingUserIdForEmail = $emailCheckStmt->fetchColumn();
            if ($existingUserIdForEmail) {
                $emailParts = explode('@', $email);
                if (count($emailParts) === 2) {
                    $email = $emailParts[0] . "+" . $ou['id'] . "@" . $emailParts[1];
                } else {
                    $email = $email . "_" . $ou['id'];
                }
            }
            
            $seenEmails[] = $email;
            
            $userUpsertStmt->execute([
                $ou['id'],
                $name,
                $email,
                $ou['mobile']
            ]);
            
            // Assign questioner role
            if ($questionerRoleId) {
                $roleAssignStmt->execute([$questionerRoleId, $ou['id']]);
            }
            
            // Assign default madhab
            if ($defaultMadhabId) {
                $userMadhabStmt->execute([$ou['id'], $defaultMadhabId]);
            }
            
            $usersMigrated++;
        }
        $status[] = ["Successfully migrated and mapped <strong>{$usersMigrated}</strong> detailed user profiles.", "success"];
        
        $newQuestionInsert = $pdo->prepare("INSERT INTO questions (
            id, question_id, user_id, category_id, madhab_id, 
            assigned_mufti_id, title, body, language, status, 
            accepted_at, assigned_at, created_at, updated_at
        ) VALUES (?, ?, ?, NULL, ?, NULL, NULL, ?, ?, ?, NULL, NULL, ?, ?)
        ON DUPLICATE KEY UPDATE 
            body = VALUES(body), 
            status = VALUES(status), 
            madhab_id = VALUES(madhab_id),
            updated_at = NOW()");

        foreach ($oldQuestions as $oq) {
            $oldUserId = $oq['user_id'];
            
            // Check if user exists, if not create dummy
            $userCheckStmt->execute([$oldUserId]);
            $exists = $userCheckStmt->fetchColumn();
            
            if (!$exists) {
                $guestName = "Guest User #" . $oldUserId;
                $guestEmail = "guest" . $oldUserId . "_" . uniqid() . "@daruliftakauzariyya.com";
                
                $userCreateStmt->execute([$oldUserId, $guestName, $guestEmail]);
                
                // Assign questioner role
                if ($questionerRoleId) {
                    $roleAssignStmt->execute([$questionerRoleId, $oldUserId]);
                }
                
                // Assign general/other madhab
                if ($defaultMadhabId) {
                    $userMadhabStmt->execute([$oldUserId, $defaultMadhabId]);
                }
                
                $usersCreated++;
            }
            
            // Resolve Madhab
            $oldMadh = $oq['madhhab'];
            $resolvedMadhabStr = $madhabResolver[$oldMadh] ?? 'Other';
            $madhabNameForMap = $resolvedMadhabStr === 'Other' ? 'General/Other' : $resolvedMadhabStr;
            $madhabId = $madhabMap[$madhabNameForMap] ?? $defaultMadhabId;
            
            // Resolve Status
            $statusVal = strtolower($oq['status']);
            if (!in_array($statusVal, ['pending', 'accepted', 'assigned', 'answered', 'under_review', 'approved', 'rejected', 'published'])) {
                $statusVal = 'pending';
            }
            
            // Detect Language
            $language = 'English';
            // Basic heuristic: Malayalam characters range: \u0D00 to \u0D7F
            if (preg_match('/[\x{0D00}-\x{0D7F}]/u', $oq['question'])) {
                $language = 'Malayalam';
            } elseif (preg_match('/[\x{0600}-\x{06FF}]/u', $oq['question'])) {
                $language = 'Arabic';
            }
            
            // Insert or Update the new questions entry
            $newQuestionInsert->execute([
                $oq['id'],
                $oq['question_id'],
                $oq['user_id'],
                $madhabId,
                $oq['question'],
                $language,
                $statusVal,
                $oq['created_at'],
                $oq['created_at']
            ]);
            
            $migratedCount++;
        }
        
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        
        // --- STEP 5: CLEANUP AND REPORT ---
        $status[] = ["Successfully migrated and mapped <strong>{$usersMigrated}</strong> detailed user profiles directly from PHP memory.", "success"];
        $status[] = ["Created <strong>{$usersCreated}</strong> placeholder User accounts to maintain database referential integrity.", "success"];
        $status[] = ["Cleaned and migrated <strong>{$migratedCount}</strong> entries into the primary `questions` table.", "success"];
        
        $status[] = ["Cleaning up temporary staging structures...", "info"];
        $pdo->exec("DROP TABLE IF EXISTS old_questions;");
        $pdo->exec("DROP TABLE IF EXISTS old_users;");
        
        $status[] = ["🚀 ALL DATABASES MIGRATIONS & SEEDINGS COMPLETED SUCCESSFULLY!", "completed"];

    } catch (PDOException $e) {
        $error = $e->getMessage();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staging & Data Migration Utility — Darulifta Kauzariyya</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --brand-primary: #10B981;
            --brand-secondary: #059669;
            --bg-slate: #0F172A;
            --card-bg: rgba(30, 41, 59, 0.7);
            --border-slate: rgba(255, 255, 255, 0.08);
            --text-light: #F8FAFC;
            --text-muted: #94A3B8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #1e293b, #0f172a);
            color: var(--text-light);
            min-height: 100vh;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            width: 100%;
            max-width: 900px;
            background: var(--card-bg);
            border: 1px solid var(--border-slate);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(16px);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header i {
            font-size: 3.5rem;
            color: var(--brand-primary);
            text-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
            margin-bottom: 15px;
            display: inline-block;
        }

        .header h1 {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #34D399, #10B981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header p {
            color: var(--text-muted);
            margin-top: 8px;
            font-size: 1rem;
        }

        .db-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-slate);
            padding: 8px 16px;
            border-radius: 100px;
            font-size: 0.85rem;
            margin-top: 15px;
            font-family: 'JetBrains Mono', monospace;
        }

        .db-badge span {
            color: var(--brand-primary);
            font-weight: 600;
            margin-left: 6px;
        }

        .action-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px dashed var(--border-slate);
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }

        .btn-run {
            background: linear-gradient(135deg, #10B981, #059669);
            color: white;
            border: none;
            padding: 16px 36px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
            transition: all 0.3s ease;
            font-family: 'Outfit', sans-serif;
        }

        .btn-run:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.5);
            background: linear-gradient(135deg, #059669, #047857);
        }

        .btn-run:active {
            transform: translateY(0);
        }

        .log-section {
            background: #090D16;
            border: 1px solid var(--border-slate);
            border-radius: 16px;
            padding: 25px;
            max-height: 450px;
            overflow-y: auto;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .log-entry {
            margin-bottom: 12px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .log-entry:last-child {
            margin-bottom: 0;
        }

        .badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            flex-shrink: 0;
        }

        .badge-info { background: rgba(59, 130, 246, 0.15); color: #60A5FA; border: 1px solid rgba(59, 130, 246, 0.3); }
        .badge-success { background: rgba(16, 185, 129, 0.15); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-completed { background: linear-gradient(135deg, #10B981, #059669); color: #fff; }

        .log-text {
            color: #E2E8F0;
        }

        .error-card {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 16px;
            padding: 25px;
            color: #FCA5A5;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .error-card i {
            font-size: 2rem;
            flex-shrink: 0;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 30px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: var(--brand-primary);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <i class="bi bi-database-fill-up"></i>
            <h1>Data Migration Staging Utility</h1>
            <p>Imports questions and normalizes them into the active schema structure</p>
            
            <div class="db-badge">
                <i class="bi bi-hdd-network me-1"></i> Database: <span><?php echo htmlspecialchars($dbConfig['database']); ?></span>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="error-card">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <h5 style="font-weight: 800; margin-bottom: 5px;">MIGRATION EXCEPTION OCCURRED</h5>
                    <p style="font-size: 0.9rem;"><?php echo htmlspecialchars($error); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($status) && !$error): ?>
            <div class="action-card">
                <p style="color: var(--text-muted); margin-bottom: 25px; font-size: 1.05rem;">
                    This script will read the old data from <code>public/questions.sql</code>, map Madhab entries, 
                    auto-generate missing user associations, and cleanly seed the active <code>questions</code> table.
                </p>
                <form method="POST">
                    <input type="hidden" name="action" value="import">
                    <button type="submit" class="btn-run">
                        <i class="bi bi-play-circle-fill me-2"></i>Execute Staged Migration
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <?php if (!empty($status)): ?>
            <h3 style="font-size: 1.2rem; font-weight: 600; margin-bottom: 15px; letter-spacing: -0.2px;"><i class="bi bi-terminal me-2 text-muted"></i>Execution Log Output</h3>
            <div class="log-section">
                <?php foreach ($status as $entry): ?>
                    <div class="log-entry">
                        <span class="badge badge-<?php echo $entry[1]; ?>"><?php echo $entry[1]; ?></span>
                        <span class="log-text"><?php echo $entry[0]; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="/" class="back-link"><i class="bi bi-arrow-left me-1"></i>Back to Fatwa Portal Home</a>
    </div>
</body>
</html>
