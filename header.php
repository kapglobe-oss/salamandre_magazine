<?php
// Shared header template
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
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
