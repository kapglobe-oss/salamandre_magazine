<?php
$magazine_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';
$initial_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$has_id = !empty($magazine_id);
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Salamandre Magazine - Liseur</title>
    <!-- Fonts -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/viewer.css?v=<?php echo time(); ?>">
    <!-- PDF.js library CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <!-- Nodlik page-flip library CDN -->
    <script src="https://cdn.jsdelivr.net/npm/page-flip/dist/js/page-flip.browser.js"></script>
</head>
<body class="viewer-body">

    <!-- Magazine Selection Screen -->
    <div id="magazine-picker" class="magazine-picker <?php echo $has_id ? 'hidden' : ''; ?>">
        <div class="picker-header">
            <a href="index.php" class="picker-back" title="Retour au site">
                <svg viewBox="0 0 24 24" style="width:20px; height:20px; fill:currentColor;"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                <span>Retour</span>
            </a>
            <div class="picker-title-area">
                <div class="picker-logo">Salamandre</div>
                <div class="picker-subtitle">Choisissez votre lecture</div>
            </div>
            <div style="width: 100px;"></div>
        </div>

        <div class="picker-content">
            <div class="picker-hero-text">
                <h1 class="picker-heading">Nos <span>Magazines</span></h1>
                <p class="picker-desc">Sélectionnez un numéro pour ouvrir le liseur interactif et découvrir une expérience de lecture premium.</p>
            </div>

            <div id="magazine-grid" class="magazine-grid">
                <!-- Populated by JS -->
                <div class="magazine-grid-loader">
                    <div class="spinner"></div>
                    <div style="color: #888; font-size: 0.85rem; margin-top: 0.5rem;">Chargement des magazines...</div>
                </div>
            </div>

            <div id="picker-empty" class="picker-empty hidden">
                <svg viewBox="0 0 24 24" style="width: 48px; height: 48px; fill: none; stroke: #555; stroke-width: 1.5;"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                <h3>Aucun magazine disponible</h3>
                <p>Revenez bientôt pour découvrir nos prochaines publications.</p>
                <a href="index.php" class="btn btn-secondary" style="margin-top: 1rem;">Retour à l'accueil</a>
            </div>
        </div>
    </div>

    <!-- Loading Screen -->
    <div id="viewer-loader" class="viewer-loader <?php echo $has_id ? '' : 'hidden'; ?>">
        <div class="spinner"></div>
        <div class="loader-text">Salamandre Magazine</div>
        <div style="font-size: 0.8rem; color: #666; margin-top: 0.5rem; letter-spacing: 0.05em;">Chargement du liseur interactif...</div>
    </div>

    <!-- Viewer Header -->
    <div id="viewer-ui" class="viewer-header <?php echo $has_id ? '' : 'hidden'; ?>">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <button id="sidebar-btn" class="control-btn" title="Table des matières / Miniatures">
                <svg viewBox="0 0 24 24" style="width:20px; height:20px;"><path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/></svg>
            </button>
            <div class="viewer-title">Chargement...</div>
        </div>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <button id="change-mag-btn" class="control-btn" title="Changer de magazine" style="font-size: 0.75rem; border-radius: 20px; padding: 0.3rem 0.8rem; width: auto; gap: 0.4rem; display: flex; align-items: center;">
                <svg viewBox="0 0 24 24" style="width:16px; height:16px;"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H8V4h12v12z"/></svg>
                <span>Changer</span>
            </button>
            <a href="index.php" class="viewer-close" title="Retour au site">&times;</a>
        </div>
    </div>

    <!-- Sidebar Thumbnails -->
    <div id="viewer-sidebar" class="viewer-sidebar">
        <div class="sidebar-title">Pages du magazine</div>
        <div id="thumbnail-grid" class="thumbnail-grid">
            <!-- Rendered by JS -->
        </div>
    </div>

    <!-- Book Viewport -->
    <div id="book-wrapper" class="book-wrapper <?php echo $has_id ? '' : 'hidden'; ?>">
        <div class="book-viewport">
            <div id="book-container" class="book-container">
                <!-- Pages will be populated dynamically by viewer.js:
                     <div class="page" data-density="soft">...</div>
                -->
            </div>
        </div>
    </div>

    <!-- Controls Panel -->
    <div id="viewer-controls" class="viewer-controls <?php echo $has_id ? '' : 'hidden'; ?>">
        <button id="first-btn" class="control-btn" title="Première page">
            <svg viewBox="0 0 24 24"><path d="M18.41 16.59L13.82 12l4.59-4.59L17 6l-6 6 6 6zM6 6h2v12H6z"/></svg>
        </button>
        <button id="prev-btn" class="control-btn" title="Page précédente">
            <svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
        </button>
        
        <div id="page-indicator" class="page-indicator">Pages --/--</div>
        
        <button id="next-btn" class="control-btn" title="Page suivante">
            <svg viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
        </button>
        <button id="last-btn" class="control-btn" title="Dernière page">
            <svg viewBox="0 0 24 24"><path d="M5.59 7.41L10.18 12l-4.59 4.59L7 18l6-6-6-6zM16 6h2v12h-2z"/></svg>
        </button>

        <span style="width: 1px; height: 20px; background: rgba(255,255,255,0.2); margin: 0 0.5rem;"></span>

        <button id="zoomout-btn" class="control-btn" title="Zoom arrière">
            <svg viewBox="0 0 24 24"><path d="M19 13H5v-2h14v2z"/></svg>
        </button>
        <button id="zoomin-btn" class="control-btn" title="Zoom avant">
            <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        </button>
        <button id="fullscreen-btn" class="control-btn" title="Plein écran">
            <svg viewBox="0 0 24 24"><path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/></svg>
        </button>
    </div>

    <!-- Script imports -->
    <script src="assets/js/viewer.js?v=<?php echo time(); ?>"></script>
</body>
</html>
