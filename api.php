<?php
// Secure session startup
session_start();

// Set JSON output headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$db_file = __DIR__ . '/data/database.json';
$uploads_dir = __DIR__ . '/uploads';

// Load Database
function load_db() {
    global $db_file;
    if (!file_exists($db_file)) {
        return ['admin' => [], 'magazines' => [], 'blog' => [], 'shop' => [], 'statistics' => ['page_reads' => [], 'total_views' => 0, 'views_by_day' => []]];
    }
    $content = file_get_contents($db_file);
    return json_decode($content, true) ?: [];
}

// Save Database
function save_db($data) {
    global $db_file;
    if (!is_dir(dirname($db_file))) {
        mkdir(dirname($db_file), 0777, true);
    }
    return file_put_contents($db_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

// Check admin credentials
function is_admin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Simple Helper to clean user inputs
function clean_input($input) {
    if (is_array($input)) {
        return array_map('clean_input', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Helper to sanitize paths to be web-relative (starting with uploads/)
function sanitize_web_path($path) {
    if (empty($path)) return '';
    // Replace backslashes with forward slashes
    $path = str_replace('\\', '/', $path);
    // Find if 'uploads/' is in the path
    $pos = strpos($path, 'uploads/');
    if ($pos !== false) {
        // Return everything starting from 'uploads/'
        return substr($path, $pos);
    }
    // If 'uploads/' is not found, trim slash from beginning
    return ltrim($path, '/');
}

// Parse request path / actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$method = $_SERVER['REQUEST_METHOD'];

// Handle OPTIONS (CORS preflight)
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$db = load_db();

switch ($action) {
    
    // AUTHENTICATION
    case 'login':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée.']);
            exit();
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $username = isset($input['username']) ? trim($input['username']) : '';
        $password = isset($input['password']) ? trim($input['password']) : '';
        
        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Veuillez remplir tous les champs.']);
            exit();
        }

        $admin = isset($db['admin']) ? $db['admin'] : null;

        if (!$admin) {
            // Self-healing: if no admin in DB, let's create a default admin
            $admin = [
                'username' => 'admin',
                'password_hash' => password_hash('salamandre2026', PASSWORD_DEFAULT)
            ];
            $db['admin'] = $admin;
            save_db($db);
        }

        // Validate username and verify bcrypt hash
        if ($username === $admin['username'] && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            echo json_encode(['success' => true, 'message' => 'Connexion réussie.']);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Identifiants incorrects.']);
        }
        break;

    case 'logout':
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        echo json_encode(['success' => true]);
        break;

    case 'check_auth':
        echo json_encode(['logged_in' => is_admin()]);
        break;

    // STATISTICS TRACKING (PUBLIC)
    case 'track_page':
        if ($method !== 'POST') {
            http_response_code(405);
            exit();
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $mag_id = isset($input['magazine_id']) ? clean_input($input['magazine_id']) : '';
        $page_num = isset($input['page']) ? intval($input['page']) : 0;
        
        if ($mag_id && $page_num > 0) {
            $stats_key = $mag_id . '_' . $page_num;
            
            // Increment page reads
            if (!isset($db['statistics']['page_reads'])) {
                $db['statistics']['page_reads'] = [];
            }
            if (!isset($db['statistics']['page_reads'][$stats_key])) {
                $db['statistics']['page_reads'][$stats_key] = 0;
            }
            $db['statistics']['page_reads'][$stats_key]++;
            
            // Increment total views
            if (!isset($db['statistics']['total_views'])) {
                $db['statistics']['total_views'] = 0;
            }
            $db['statistics']['total_views']++;
            
            // Daily stats
            $today = date('Y-m-d');
            if (!isset($db['statistics']['views_by_day'])) {
                $db['statistics']['views_by_day'] = [];
            }
            if (!isset($db['statistics']['views_by_day'][$today])) {
                $db['statistics']['views_by_day'][$today] = 0;
            }
            $db['statistics']['views_by_day'][$today]++;
            
            save_db($db);
            echo json_encode(['success' => true, 'page_reads' => $db['statistics']['page_reads'][$stats_key]]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Paramètres invalides.']);
        }
        break;

    // GET PUBLIC DATA
    case 'get_magazines':
        echo json_encode($db['magazines'] ?? []);
        break;

    case 'get_magazine':
        $id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
        $found = null;
        foreach ($db['magazines'] as $m) {
            if ($m['id'] === $id) {
                $found = $m;
                break;
            }
        }
        if ($found) {
            echo json_encode($found);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Magazine non trouvé.']);
        }
        break;

    case 'get_blog':
        echo json_encode($db['blog'] ?? []);
        break;

    case 'get_products':
        echo json_encode($db['shop'] ?? []);
        break;

    case 'track_ad_click':
        $id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Identifiant publicitaire manquant.']);
            exit();
        }
        
        $found_ad = null;
        foreach ($db['ads'] as &$ad) {
            if ($ad['id'] === $id) {
                if (!isset($ad['clicks'])) $ad['clicks'] = 0;
                $ad['clicks']++;
                
                // If CPC, calculate earnings
                if (isset($ad['pricing_model']) && $ad['pricing_model'] === 'cpc') {
                    if (!isset($ad['earnings'])) $ad['earnings'] = 0;
                    $ad['earnings'] = $ad['clicks'] * floatval($ad['price']);
                }
                
                $found_ad = $ad;
                break;
            }
        }
        
        if ($found_ad) {
            save_db($db);
            header("Location: " . html_entity_decode($found_ad['link_url']));
            exit();
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Publicité introuvable.']);
            exit();
        }
        break;

    // ADMIN ENDPOINTS (PROTECTED)
    default:
        // Everything below requires admin authorization
        if (!is_admin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Accès refusé. Veuillez vous connecter.']);
            exit();
        }

        // Routing for admin CRUD operations
        switch ($action) {
            case 'get_stats':
                echo json_encode($db['statistics'] ?? []);
                break;

            // AD CAMPAIGNS CRUD (ADMIN)
            case 'get_ads_admin':
                echo json_encode($db['ads'] ?? []);
                break;

            case 'add_ad':
                $input = json_decode(file_get_contents('php://input'), true);
                if (empty($input['client_name']) || empty($input['title']) || empty($input['banner_path']) || empty($input['link_url'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Champs obligatoires manquants.']);
                    exit();
                }

                $new_ad = [
                    'id' => 'ad-' . uniqid(),
                    'client_name' => clean_input($input['client_name']),
                    'title' => clean_input($input['title']),
                    'banner_path' => sanitize_web_path($input['banner_path']),
                    'link_url' => clean_input($input['link_url']),
                    'location' => clean_input($input['location'] ?? 'homepage'),
                    'pricing_model' => clean_input($input['pricing_model'] ?? 'flat'),
                    'price' => floatval($input['price'] ?? 0),
                    'status' => clean_input($input['status'] ?? 'active'),
                    'clicks' => 0,
                    'impressions' => 0,
                    'earnings' => ($input['pricing_model'] === 'flat') ? floatval($input['price'] ?? 0) : 0,
                    'start_date' => clean_input($input['start_date'] ?? ''),
                    'end_date' => clean_input($input['end_date'] ?? '')
                ];

                if (!isset($db['ads'])) $db['ads'] = [];
                $db['ads'][] = $new_ad;
                save_db($db);
                echo json_encode($new_ad);
                break;

            case 'update_ad':
                $input = json_decode(file_get_contents('php://input'), true);
                $id = isset($input['id']) ? clean_input($input['id']) : '';

                $updated = false;
                foreach ($db['ads'] as &$ad) {
                    if ($ad['id'] === $id) {
                        $ad['client_name'] = clean_input($input['client_name'] ?? $ad['client_name']);
                        $ad['title'] = clean_input($input['title'] ?? $ad['title']);
                        $ad['banner_path'] = isset($input['banner_path']) ? sanitize_web_path($input['banner_path']) : $ad['banner_path'];
                        $ad['link_url'] = clean_input($input['link_url'] ?? $ad['link_url']);
                        $ad['location'] = clean_input($input['location'] ?? $ad['location']);
                        
                        $ad['pricing_model'] = clean_input($input['pricing_model'] ?? $ad['pricing_model']);
                        $ad['price'] = floatval($input['price'] ?? $ad['price']);
                        
                        // Recalculate earnings
                        if ($ad['pricing_model'] === 'flat') {
                            $ad['earnings'] = $ad['price'];
                        } else {
                            $ad['earnings'] = ($ad['clicks'] ?? 0) * $ad['price'];
                        }

                        $ad['status'] = clean_input($input['status'] ?? $ad['status']);
                        $ad['start_date'] = clean_input($input['start_date'] ?? $ad['start_date']);
                        $ad['end_date'] = clean_input($input['end_date'] ?? $ad['end_date']);
                        $updated = true;
                        break;
                    }
                }
                if ($updated) {
                    save_db($db);
                    echo json_encode(['success' => true]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Campagne pub non trouvée.']);
                }
                break;

            case 'delete_ad':
                $id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
                $index = -1;
                foreach ($db['ads'] as $idx => $ad) {
                    if ($ad['id'] === $id) {
                        $index = $idx;
                        break;
                    }
                }
                if ($index !== -1) {
                    array_splice($db['ads'], $index, 1);
                    save_db($db);
                    echo json_encode(['success' => true]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Campagne pub non trouvée.']);
                }
                break;

            case 'get_settings':
                echo json_encode([
                    'settings' => $db['settings'] ?? [
                        'featured_mag_id' => 'latest',
                        'custom_cover_path' => ''
                    ],
                    'magazines' => array_map(function($m) {
                        return [
                            'id' => $m['id'],
                            'title' => $m['title']
                        ];
                    }, $db['magazines'] ?? [])
                ]);
                break;

            case 'update_settings':
                $input = json_decode(file_get_contents('php://input'), true);
                $featured_mag_id = isset($input['featured_mag_id']) ? clean_input($input['featured_mag_id']) : 'latest';
                $custom_cover_path = isset($input['custom_cover_path']) ? sanitize_web_path($input['custom_cover_path']) : '';

                $db['settings'] = [
                    'featured_mag_id' => $featured_mag_id,
                    'custom_cover_path' => $custom_cover_path
                ];
                save_db($db);
                echo json_encode(['success' => true]);
                break;

            // MAGAZINE CRUD
            case 'add_magazine':
                $input = json_decode(file_get_contents('php://input'), true);
                if (empty($input['title']) || empty($input['pdf_path'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Titre et PDF requis.']);
                    exit();
                }
                
                $new_mag = [
                    'id' => 'mag-' . uniqid(),
                    'title' => clean_input($input['title']),
                    'pdf_path' => sanitize_web_path($input['pdf_path']),
                    'cover_path' => sanitize_web_path($input['cover_path'] ?? 'uploads/images/magazine_cover_default.png'),
                    'pub_date' => clean_input($input['pub_date'] ?? date('Y-m-d')),
                    'pages' => []
                ];
                
                $db['magazines'][] = $new_mag;
                save_db($db);
                echo json_encode($new_mag);
                break;

            case 'update_magazine':
                $input = json_decode(file_get_contents('php://input'), true);
                $id = isset($input['id']) ? clean_input($input['id']) : '';
                
                $updated = false;
                foreach ($db['magazines'] as &$m) {
                    if ($m['id'] === $id) {
                        $m['title'] = clean_input($input['title'] ?? $m['title']);
                        $m['pdf_path'] = isset($input['pdf_path']) ? sanitize_web_path($input['pdf_path']) : $m['pdf_path'];
                        $m['cover_path'] = isset($input['cover_path']) ? sanitize_web_path($input['cover_path']) : $m['cover_path'];
                        $m['pub_date'] = clean_input($input['pub_date'] ?? $m['pub_date']);
                        $updated = true;
                        break;
                    }
                }
                if ($updated) {
                    save_db($db);
                    echo json_encode(['success' => true]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Magazine non trouvé.']);
                }
                break;

            case 'delete_magazine':
                $id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
                $index = -1;
                foreach ($db['magazines'] as $idx => $m) {
                    if ($m['id'] === $id) {
                        $index = $idx;
                        break;
                    }
                }
                if ($index !== -1) {
                    // Optionally delete files, but let's keep them on disk for safety
                    array_splice($db['magazines'], $index, 1);
                    save_db($db);
                    echo json_encode(['success' => true]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Magazine non trouvé.']);
                }
                break;

            case 'save_magazine_pages':
                $input = json_decode(file_get_contents('php://input'), true);
                $mag_id = isset($input['magazine_id']) ? clean_input($input['magazine_id']) : '';
                $pages = isset($input['pages']) ? $input['pages'] : null;

                if (!$mag_id || !is_array($pages)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Paramètres invalides.']);
                    exit();
                }

                $found = false;
                foreach ($db['magazines'] as &$m) {
                    if ($m['id'] === $mag_id) {
                        $m['pages'] = $pages;
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    save_db($db);
                    echo json_encode(['success' => true]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Magazine non trouvé.']);
                }
                break;

            case 'initialize_pdf_pages':
                $input = json_decode(file_get_contents('php://input'), true);
                $mag_id = isset($input['magazine_id']) ? clean_input($input['magazine_id']) : '';
                $num_pages = isset($input['num_pages']) ? intval($input['num_pages']) : 0;

                if (!$mag_id || $num_pages <= 0) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Paramètres invalides.']);
                    exit();
                }

                $found = false;
                foreach ($db['magazines'] as &$m) {
                    if ($m['id'] === $mag_id) {
                        // Only initialize if pages is empty
                        if (empty($m['pages'])) {
                            $m['pages'] = [];
                            for ($i = 1; $i <= $num_pages; $i++) {
                                $m['pages'][] = [
                                    'type' => 'pdf',
                                    'pdf_page_num' => $i,
                                    'widgets' => []
                                ];
                            }
                            $found = true;
                        }
                        break;
                    }
                }
                if ($found) {
                    save_db($db);
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => true, 'already_initialized' => true]);
                }
                break;

            // BLOG CRUD
            case 'add_blog':
                $input = json_decode(file_get_contents('php://input'), true);
                if (empty($input['title']) || empty($input['content'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Titre et contenu requis.']);
                    exit();
                }

                // Generate slug
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $input['title'])));

                $new_post = [
                    'id' => 'blog-' . uniqid(),
                    'title' => clean_input($input['title']),
                    'slug' => $slug,
                    'excerpt' => clean_input($input['excerpt'] ?? substr(strip_tags($input['content']), 0, 150)),
                    'content' => $input['content'], // Keep HTML content
                    'image' => sanitize_web_path($input['image'] ?? 'uploads/images/blog_post_art.png'),
                    'tags' => isset($input['tags']) ? array_map('clean_input', $input['tags']) : [],
                    'status' => clean_input($input['status'] ?? 'published'),
                    'date' => date('Y-m-d')
                ];

                $db['blog'][] = $new_post;
                save_db($db);
                echo json_encode($new_post);
                break;

            case 'update_blog':
                $input = json_decode(file_get_contents('php://input'), true);
                $id = isset($input['id']) ? clean_input($input['id']) : '';

                $updated = false;
                foreach ($db['blog'] as &$post) {
                    if ($post['id'] === $id) {
                        $post['title'] = clean_input($input['title'] ?? $post['title']);
                        $post['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $post['title'])));
                        $post['excerpt'] = clean_input($input['excerpt'] ?? $post['excerpt']);
                        $post['content'] = $input['content'] ?? $post['content'];
                        $post['image'] = isset($input['image']) ? sanitize_web_path($input['image']) : $post['image'];
                        $post['tags'] = isset($input['tags']) ? array_map('clean_input', $input['tags']) : $post['tags'];
                        $post['status'] = clean_input($input['status'] ?? $post['status']);
                        $updated = true;
                        break;
                    }
                }
                if ($updated) {
                    save_db($db);
                    echo json_encode(['success' => true]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Article non trouvé.']);
                }
                break;

            case 'delete_blog':
                $id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
                $index = -1;
                foreach ($db['blog'] as $idx => $post) {
                    if ($post['id'] === $id) {
                        $index = $idx;
                        break;
                    }
                }
                if ($index !== -1) {
                    array_splice($db['blog'], $index, 1);
                    save_db($db);
                    echo json_encode(['success' => true]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Article non trouvé.']);
                }
                break;

            // PRODUCT CRUD
            case 'add_product':
                $input = json_decode(file_get_contents('php://input'), true);
                if (empty($input['title']) || empty($input['price'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Titre et prix requis.']);
                    exit();
                }

                $new_prod = [
                    'id' => 'prod-' . uniqid(),
                    'title' => clean_input($input['title']),
                    'price' => floatval($input['price']),
                    'image' => sanitize_web_path($input['image'] ?? 'uploads/images/shop_item_journal.png'),
                    'description' => clean_input($input['description'] ?? ''),
                    'category' => clean_input($input['category'] ?? 'merch'),
                    'digital' => isset($input['digital']) ? (bool)$input['digital'] : false,
                    'download_url' => sanitize_web_path($input['download_url'] ?? '')
                ];

                $db['shop'][] = $new_prod;
                save_db($db);
                echo json_encode($new_prod);
                break;

            case 'update_product':
                $input = json_decode(file_get_contents('php://input'), true);
                $id = isset($input['id']) ? clean_input($input['id']) : '';

                $updated = false;
                foreach ($db['shop'] as &$prod) {
                    if ($prod['id'] === $id) {
                        $prod['title'] = clean_input($input['title'] ?? $prod['title']);
                        $prod['price'] = isset($input['price']) ? floatval($input['price']) : $prod['price'];
                        $prod['image'] = isset($input['image']) ? sanitize_web_path($input['image']) : $prod['image'];
                        $prod['description'] = clean_input($input['description'] ?? $prod['description']);
                        $prod['category'] = clean_input($input['category'] ?? $prod['category']);
                        $prod['digital'] = isset($input['digital']) ? (bool)$input['digital'] : $prod['digital'];
                        $prod['download_url'] = isset($input['download_url']) ? sanitize_web_path($input['download_url']) : $prod['download_url'];
                        $updated = true;
                        break;
                    }
                }
                if ($updated) {
                    save_db($db);
                    echo json_encode(['success' => true]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Produit non trouvé.']);
                }
                break;

            case 'delete_product':
                $id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
                $index = -1;
                foreach ($db['shop'] as $idx => $prod) {
                    if ($prod['id'] === $id) {
                        $index = $idx;
                        break;
                    }
                }
                if ($index !== -1) {
                    array_splice($db['shop'], $index, 1);
                    save_db($db);
                    echo json_encode(['success' => true]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Produit non trouvé.']);
                }
                break;

            // FILE UPLOADS
            case 'upload':
                if (!isset($_FILES['file'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Aucun fichier fourni.']);
                    exit();
                }

                $file = $_FILES['file'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $type = isset($_GET['type']) ? $_GET['type'] : 'image';

                // Extensions checks
                if ($type === 'pdf') {
                    $allowed = ['pdf'];
                    $subdir = '/magazines';
                } elseif ($type === 'video') {
                    $allowed = ['mp4', 'webm', 'ogv'];
                    $subdir = '/videos';
                } elseif ($type === 'audio') {
                    $allowed = ['mp3', 'wav', 'ogg', 'm4a'];
                    $subdir = '/audios';
                } else {
                    $allowed = ['png', 'jpg', 'jpeg', 'svg', 'webp'];
                    $subdir = '/images';
                }

                if (!in_array($ext, $allowed)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Format de fichier non autorisé.']);
                    exit();
                }

                // Secure folder creation
                $target_dir = $uploads_dir . $subdir;
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                // Unique filename
                $new_filename = uniqid('file_', true) . '.' . $ext;
                $target_path = $target_dir . '/' . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    $web_path = 'uploads' . $subdir . '/' . $new_filename;
                    echo json_encode(['success' => true, 'path' => $web_path]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => "Erreur lors du déplacement de l'upload."]);
                }
                break;

            case 'update_password':
                $input = json_decode(file_get_contents('php://input'), true);
                $old = isset($input['old_password']) ? trim($input['old_password']) : '';
                $new = isset($input['new_password']) ? trim($input['new_password']) : '';

                if (empty($old) || empty($new)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Tous les champs sont requis.']);
                    exit();
                }

                $admin = $db['admin'];
                if (password_verify($old, $admin['password_hash'])) {
                    $db['admin']['password_hash'] = password_hash($new, PASSWORD_DEFAULT);
                    save_db($db);
                    echo json_encode(['success' => true, 'message' => 'Mot de passe mis à jour avec succès.']);
                } else {
                    http_response_code(401);
                    echo json_encode(['error' => 'Mot de passe actuel incorrect.']);
                }
                break;

            default:
                http_response_code(404);
                echo json_encode(['error' => 'Action admin non trouvée.']);
                break;
        }
        break;
}
