<?php
/**
 * Salamandre Magazine - Database Configuration
 * 
 * This file handles database connections for both development and production.
 * For development (local), uses JSON-based storage.
 * For production (cPanel), uses MySQL.
 */

// Detect environment
$environment = getenv('APP_ENV') ?: 'development';
$is_production = ($environment === 'production');

// ==========================================
// DEVELOPMENT CONFIGURATION (JSON-based)
// ==========================================
if (!$is_production) {
    define('DB_TYPE', 'json');
    define('DB_FILE', __DIR__ . '/data/database.json');
    
    // Helper: Load JSON database
    function load_database() {
        if (!file_exists(DB_FILE)) {
            return [
                'admin' => [],
                'magazines' => [],
                'blog' => [],
                'shop' => [],
                'ads' => [],
                'statistics' => ['page_reads' => [], 'total_views' => 0, 'views_by_day' => []],
                'settings' => []
            ];
        }
        $content = file_get_contents(DB_FILE);
        return json_decode($content, true) ?: [];
    }
    
    // Helper: Save JSON database
    function save_database($data) {
        if (!is_dir(dirname(DB_FILE))) {
            mkdir(dirname(DB_FILE), 0777, true);
        }
        return file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }
    
    // Global database instance
    $GLOBALS['db'] = load_database();
}

// ==========================================
// PRODUCTION CONFIGURATION (MySQL)
// ==========================================
else {
    define('DB_TYPE', 'mysql');
    
    // Load from environment variables or .env file
    if (file_exists(__DIR__ . '/.env')) {
        $env_vars = parse_ini_file(__DIR__ . '/.env');
        foreach ($env_vars as $key => $value) {
            putenv("$key=$value");
        }
    }
    
    // MySQL credentials
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_USER', getenv('DB_USER') ?: '');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('DB_NAME', getenv('DB_NAME') ?: 'salamandre_magazine');
    define('DB_PORT', getenv('DB_PORT') ?: 3306);
    
    // Create MySQL connection
    try {
        $mysqli = new mysqli(
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME,
            DB_PORT
        );
        
        // Check connection
        if ($mysqli->connect_error) {
            error_log('MySQL Connection failed: ' . $mysqli->connect_error);
            die('Database connection error. Please contact support.');
        }
        
        // Set charset to utf8mb4
        $mysqli->set_charset('utf8mb4');
        
        // Store connection as global
        $GLOBALS['db_connection'] = $mysqli;
        
    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
        die('Database connection error. Please contact support.');
    }
}

// ==========================================
// HELPER FUNCTIONS (Work with both JSON & MySQL)
// ==========================================

/**
 * Get a value from database (JSON or MySQL)
 */
function db_get($table, $id = null) {
    global $GLOBALS;
    
    if (DB_TYPE === 'json') {
        $db = $GLOBALS['db'];
        if ($id) {
            foreach ($db[$table] as $item) {
                if ($item['id'] === $id) return $item;
            }
            return null;
        }
        return $db[$table] ?? [];
    }
    // MySQL queries handled in individual endpoints
}

/**
 * Save/Update data (JSON or MySQL)
 */
function db_save($table, $data) {
    if (DB_TYPE === 'json') {
        $db = $GLOBALS['db'];
        $db[$table] = $data;
        save_database($db);
        return true;
    }
    // MySQL queries handled in individual endpoints
}

// ==========================================
// DATABASE INITIALIZATION (Production only)
// ==========================================

if ($is_production && DB_TYPE === 'mysql') {
    /**
     * Create tables on first run (optional)
     * Uncomment the line below if you want auto-initialization
     */
    // initialize_mysql_database();
    
    function initialize_mysql_database() {
        global $GLOBALS;
        $mysqli = $GLOBALS['db_connection'];
        
        $schema_file = __DIR__ . '/database/schema.sql';
        if (!file_exists($schema_file)) {
            error_log('Schema file not found at: ' . $schema_file);
            return false;
        }
        
        $sql_queries = file_get_contents($schema_file);
        $queries = array_filter(array_map('trim', explode(';', $sql_queries)));
        
        foreach ($queries as $query) {
            if (!empty($query)) {
                if (!$mysqli->query($query)) {
                    error_log('SQL error: ' . $mysqli->error);
                    return false;
                }
            }
        }
        
        return true;
    }
}

// ==========================================
// ERROR HANDLING
// ==========================================

set_error_handler(function($errno, $errstr) {
    error_log("Error [$errno]: $errstr");
    if ($is_production) {
        // Production: hide errors
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
        exit();
    }
});

?>
