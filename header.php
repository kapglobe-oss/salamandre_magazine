<?php
// Shared header template
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);

if (!function_exists('get_and_track_ad')) {
    function get_and_track_ad($location) {
        $db_file = __DIR__ . '/data/database.json';
        if (!file_exists($db_file)) return null;
        $db = json_decode(file_get_contents($db_file), true);
        if (empty($db['ads'])) return null;
        
        $active_ads = array_filter($db['ads'], function($a) use ($location) {
            $status_ok = isset($a['status']) && $a['status'] === 'active';
            $today = date('Y-m-d');
            $date_ok = true;
            if (!empty($a['start_date']) && $a['start_date'] > $today) $date_ok = false;
            if (!empty($a['end_date']) && $a['end_date'] < $today) $date_ok = false;
            return $status_ok && $date_ok && $a['location'] === $location;
        });
        
        if (empty($active_ads)) return null;
        
        // Pick random active ad
        $ad_to_show = $active_ads[array_rand($active_ads)];
        
        // Track impression
        foreach ($db['ads'] as &$ad) {
            if ($ad['id'] === $ad_to_show['id']) {
                if (!isset($ad['impressions'])) $ad['impressions'] = 0;
                $ad['impressions']++;
                break;
            }
        }
        
        // Save database
        file_put_contents($db_file, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $ad_to_show;
    }
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salamandre Magazine</title>
    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css_path): ?>
            <link rel="stylesheet" href="<?php echo $css_path; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>

    <!-- Header Navigation -->
    <header>
        <?php 
        $header_ad = get_and_track_ad('header');
        if ($header_ad): 
        ?>
            <style>
                main, .hero, .magazine-picker { margin-top: 190px !important; }
                header { height: auto !important; }
            </style>
            <div class="ad-banner-header" style="background: var(--bg-secondary); text-align: center; padding: 0.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: center; align-items: center; width: 100%;">
                <a href="api.php?action=track_ad_click&id=<?php echo $header_ad['id']; ?>" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; gap: 1rem; height: 90px; text-decoration: none;">
                    <span style="background: var(--accent-gold); color: #000; font-size: 0.65rem; padding: 3px 8px; font-weight: 800; border-radius: 2px;">SPONSORISÉ</span>
                    <img src="<?php echo $header_ad['banner_path']; ?>" alt="<?php echo htmlspecialchars($header_ad['title']); ?>" style="max-height: 90px; width: auto; display: block; object-fit: contain;">
                </a>
            </div>
        <?php endif; ?>
        <div class="nav-container">
            <a href="index.php" class="logo">
                <!-- Golden/Bronze Salamander Vector SVG -->
                <svg viewBox="0 0 100 100">
                    <path d="M50 15C42 15 35 22 35 30C35 38 41 40 45 42C48 43 51 45 51 48C51 51 47 54 42 54C35 54 30 48 27 44C25 41 21 38 17 40C13 42 12 47 15 51C20 58 29 65 40 65C52 65 62 57 62 46C62 36 54 33 49 31C45 29 43 27 43 24C43 21 47 19 51 19C57 19 63 24 66 28C68 31 73 33 77 30C81 27 80 21 76 17C69 11 60 15 50 15ZM30 75C28 75 26 77 26 79C26 81 28 83 30 83C32 83 34 81 34 79C34 77 32 75 30 75ZM70 75C68 75 66 77 66 79C66 81 68 83 70 83C72 83 74 81 74 79C74 77 72 75 70 75Z"/>
                </svg>
                <span>Salamandre</span>
            </a>

            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Accueil</a></li>
                    <li><a href="viewer.php" class="nav-link">Liseur</a></li>
                    <li><a href="blog.php" class="nav-link <?php echo ($current_page == 'blog.php' || $current_page == 'post.php') ? 'active' : ''; ?>">Blogue</a></li>
                    <li><a href="shop.php" class="nav-link <?php echo ($current_page == 'shop.php') ? 'active' : ''; ?>">Boutique</a></li>
                    <li><a href="media-kit.php" class="nav-link <?php echo ($current_page == 'media-kit.php') ? 'active' : ''; ?>">Kit Média</a></li>
                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                        <li><a href="admin/index.php" class="nav-link" style="color: var(--accent-gold); font-weight: 700;">Dashboard</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="nav-actions">
                <!-- Theme Toggle Button -->
                <button id="theme-toggle" class="icon-btn" title="Changer le thème">
                    <svg viewBox="0 0 24 24"><path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </button>
                
                <!-- Cart Trigger -->
                <button id="cart-btn" class="icon-btn" title="Voir le panier">
                    <svg viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    <span class="cart-count" style="display: none;">0</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Cart Drawer Markup (Shared across all pages) -->
    <div id="cart-overlay" class="cart-overlay"></div>
    <div id="cart-drawer" class="cart-drawer">
        <div class="cart-header">
            <h3 style="font-family: var(--font-display); font-size: 1.2rem; margin: 0;">Votre Panier</h3>
            <button id="close-cart" style="background:none; border:none; font-size: 1.8rem; cursor:pointer; color: var(--text-primary);">&times;</button>
        </div>
        
        <!-- Cart Items Container -->
        <div id="cart-items-container" class="cart-items">
            <!-- Rendered dynamically by JS -->
        </div>

        <div class="cart-footer">
            <div class="cart-total-row">
                <span>Sous-total :</span>
                <span id="cart-subtotal">0.00 $</span>
            </div>
            <a href="shop.php?checkout=1" class="btn btn-primary" style="width: 100%; justify-content: center;">Passer la commande</a>
        </div>
    </div>

    <!-- Main Content Area -->
    <main>
