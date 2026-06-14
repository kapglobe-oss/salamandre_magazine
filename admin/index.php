<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrateur - Salamandre</title>
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?php echo time(); ?>">
    <!-- PDF.js from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
</head>
<body class="admin-body">

    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
        <div>
            <div class="admin-brand">
                <svg viewBox="0 0 100 100">
                    <path d="M50 15C42 15 35 22 35 30C35 38 41 40 45 42C48 43 51 45 51 48C51 51 47 54 42 54C35 54 30 48 27 44C25 41 21 38 17 40C13 42 12 47 15 51C20 58 29 65 40 65C52 65 62 57 62 46C62 36 54 33 49 31C45 29 43 27 43 24C43 21 47 19 51 19C57 19 63 24 66 28C68 31 73 33 77 30C81 27 80 21 76 17C69 11 60 15 50 15ZM30 75C28 75 26 77 26 79C26 81 28 83 30 83C32 83 34 81 34 79C34 77 32 75 30 75ZM70 75C68 75 66 77 66 79C66 81 68 83 70 83C72 83 74 81 74 79C74 77 72 75 70 75Z"/>
                </svg>
                <span>SALAMANDRE</span>
            </div>
            
            <ul class="admin-nav">
                <li>
                    <div class="admin-nav-link active" data-tab="stats">
                        <svg viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                        <span>Statistiques</span>
                    </div>
                </li>
                <li>
                    <div class="admin-nav-link" data-tab="magazines">
                        <svg viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Magazines</span>
                    </div>
                </li>
                <li>
                    <div class="admin-nav-link" data-tab="blog">
                        <svg viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Articles Blog</span>
                    </div>
                </li>
                <li>
                    <div class="admin-nav-link" data-tab="shop">
                        <svg viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>Boutique</span>
                    </div>
                </li>
                <li>
                    <div class="admin-nav-link" data-tab="ads">
                        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        <span>Régie Pub</span>
                    </div>
                </li>
                <li>
                    <div class="admin-nav-link" data-tab="settings">
                        <svg viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span>Paramètres</span>
                    </div>
                </li>
            </ul>
        </div>
        
        <div>
            <a href="logout.php" class="admin-nav-link" style="color:var(--accent-red);">
                <svg viewBox="0 0 24 24" style="stroke:currentColor;"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span>Déconnexion</span>
            </a>
        </div>
    </aside>

    <!-- Admin Main View -->
    <div class="admin-main">
        <header class="admin-topbar">
            <h2 style="font-family: var(--font-serif); margin: 0; font-size: 1.3rem;">Espace d'Administration</h2>
            <div style="font-size: 0.9rem; color: #888;">Bonjour, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></div>
        </header>

        <!-- Main Content Area -->
        <main class="admin-content-area">

            <!-- TAB 1: STATISTICS -->
            <div id="tab-stats" class="admin-tab-content active">
                <div class="dashboard-stats-grid">
                    <div class="admin-card">
                        <div class="admin-card-val" id="stat-total-views">-</div>
                        <div class="admin-card-lbl">Vues totales</div>
                    </div>
                    <div class="admin-card">
                        <div class="admin-card-val" id="stat-pages-read">-</div>
                        <div class="admin-card-lbl">Pages lues</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 2rem;">
                    <!-- Views Chart -->
                    <div class="admin-card">
                        <h3 style="font-family: var(--font-serif); font-size: 1.15rem; margin-bottom: 2rem;">Vues sur les 7 derniers jours</h3>
                        <div id="chart-views-day" style="height: 190px;">
                            <!-- SVG Rendered by JS -->
                        </div>
                    </div>

                    <!-- Top read pages -->
                    <div class="admin-card">
                        <h3 style="font-family: var(--font-serif); font-size: 1.15rem; margin-bottom: 2rem;">Pages les plus lues</h3>
                        <div id="chart-top-pages">
                            <!-- SVG Rendered by JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: MAGAZINES -->
            <div id="tab-magazines" class="admin-tab-content">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
                    <h3 style="font-family: var(--font-serif); font-size: 1.3rem; margin: 0;">Gestion des Numéros PDF</h3>
                    <button class="btn btn-gold" onclick="openModal('modal-magazine')" style="padding: 0.6rem 1.2rem; font-size: 0.8rem; border-radius: 4px;">Ajouter un numéro</button>
                </div>
                
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Couverture</th>
                                <th>Titre du magazine</th>
                                <th>Date de publication</th>
                                <th>Overlays</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="magazines-table-body">
                            <!-- JS populated -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VISUAL WIDGET BUILDER VIEWPORT (HIDDEN BY DEFAULT) -->
            <div id="builder-section" style="display: none;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 1rem;">
                    <div>
                        <span style="font-size:0.8rem; text-transform:uppercase; color:var(--accent-gold); font-weight:600;">Concepteur de Pages Vivantes & FlippingBook</span>
                        <h3 id="builder-mag-title" style="font-family: var(--font-serif); font-size: 1.4rem; margin: 0.2rem 0 0;">-</h3>
                    </div>
                    <div style="display:flex; gap: 1rem;">
                        <button id="builder-save-btn" onclick="saveBuilderOverlays()" class="btn btn-gold" style="padding: 0.6rem 1.5rem; font-size: 0.8rem; border-radius:4px;">Sauvegarder</button>
                        <button onclick="closeBuilder()" class="btn btn-secondary" style="padding: 0.6rem 1.5rem; font-size: 0.8rem; border-radius:4px;">Fermer</button>
                    </div>
                </div>

                <div class="builder-split" style="display: grid; grid-template-columns: 240px 1fr 320px; gap: 1.5rem;">
                    <!-- Left Column: Pages List Manager -->
                    <aside class="builder-sidebar" style="overflow-y: auto; display: flex; flex-direction: column; gap: 1rem;">
                        <h4 style="font-family:var(--font-serif); border-bottom:1px solid var(--admin-border); padding-bottom:0.5rem; margin-bottom:0.5rem; font-size:1.1rem;">Pages Virtuelles</h4>
                        
                        <button onclick="addCustomBlankPage()" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.75rem; justify-content: center; width: 100%; border-radius: 4px;">+ Ajouter Page Vierge</button>
                        
                        <div id="builder-pages-list" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 0.5rem; margin-top:0.5rem;">
                            <!-- Populated dynamically by JS -->
                        </div>
                    </aside>

                    <!-- Center Column: Workspace for drag-drop click widgets -->
                    <div class="builder-workspace">
                        <div id="builder-book-container" class="builder-book">
                            <!-- Left Page -->
                            <div id="builder-page-left" class="builder-page left" onclick="handlePageClick(event, 'left')">
                                <canvas id="builder-canvas-left" class="builder-page-canvas"></canvas>
                                <div id="builder-overlay-left" class="builder-overlay-layer"></div>
                            </div>
                            <!-- Right Page -->
                            <div id="builder-page-right" class="builder-page right" onclick="handlePageClick(event, 'right')">
                                <canvas id="builder-canvas-right" class="builder-page-canvas"></canvas>
                                <div id="builder-overlay-right" class="builder-overlay-layer"></div>
                            </div>
                        </div>

                        <!-- Mini Pagination inside builder -->
                        <div style="position:absolute; bottom:10px; display:flex; align-items:center; gap: 1.5rem; background:rgba(0,0,0,0.8); padding:0.4rem 1rem; border-radius:30px;">
                            <button onclick="builderPrev()" style="background:none; border:none; color:#fff; cursor:pointer;">&larr;</button>
                            <span id="builder-page-indicator" style="font-size:0.8rem;">Page 1/--</span>
                            <button onclick="builderNext()" style="background:none; border:none; color:#fff; cursor:pointer;">&rarr;</button>
                        </div>
                    </div>

                    <!-- Right Column: Sidebar properties editor -->
                    <aside class="builder-sidebar" style="overflow-y: auto;">
                        <h4 style="font-family:var(--font-serif); border-bottom:1px solid var(--admin-border); padding-bottom:0.5rem; margin-bottom:1.5rem;">Propriétés</h4>
                        
                        <div style="color:#888; font-size:0.85rem; text-align:center; padding: 2rem 0;" id="builder-sidebar-help">
                            Sélectionnez une page à gauche ou cliquez sur le livre pour placer/éditer un widget.
                        </div>

                        <!-- PAGE PARAMETERS PANEL -->
                        <div id="page-editor-panel" style="display: none; margin-bottom: 2rem;">
                            <h5 style="font-size:0.8rem; text-transform:uppercase; color:var(--accent-gold); margin-bottom:1rem;">Option de la Page Active</h5>
                            <div class="admin-form-group">
                                <label>Type de page</label>
                                <input type="text" id="edit-p-type" class="admin-form-control" readonly style="opacity:0.6;">
                            </div>
                            <div class="admin-form-group" id="page-bg-color-group">
                                <label>Couleur de fond (Custom)</label>
                                <input type="color" id="edit-p-background" class="admin-form-control" style="height:40px; padding:0;">
                            </div>
                            <div class="admin-form-group" id="page-bg-image-group" style="display:none;">
                                <label>Image de fond (Custom)</label>
                                <input type="text" id="edit-p-bg-image" class="admin-form-control" placeholder="uploads/images/...">
                                <input type="file" style="margin-top:0.5rem; font-size:0.8rem;" accept="image/*" onchange="handleFileUpload(this, 'edit-p-bg-image', 'image')">
                                <span style="font-size:0.75rem; color:#888; display:block; margin-top:0.2rem;">Téléverser une image de fond</span>
                            </div>
                        </div>

                        <!-- WIDGET PARAMETERS PANEL -->
                        <div id="widget-editor-panel" style="display: none;">
                            <h5 style="font-size:0.8rem; text-transform:uppercase; color:var(--accent-gold); margin-bottom:1rem;">Configuration du Widget</h5>
                            <div class="admin-form-group">
                                <label>Type de Widget</label>
                                <select id="edit-w-type" class="admin-form-control">
                                    <option value="video">Vidéo</option>
                                    <option value="ad">Publicité / Image</option>
                                    <option value="text">Texte Riche</option>
                                    <option value="carousel">Diaporama (Carrousel)</option>
                                    <option value="audio">Lecteur Audio</option>
                                    <option value="shape">Masque / Forme de Couleur</option>
                                </select>
                            </div>
                            
                            <div class="admin-form-group">
                                <label>Format d'intégration</label>
                                <select id="edit-w-subtype" class="admin-form-control">
                                    <!-- Populated by JS -->
                                </select>
                            </div>

                            <!-- Standard Text Input Content (Videos, Audios) -->
                            <div class="admin-form-group" id="content-input-group">
                                <label>Source / Contenu (URL / Chemin)</label>
                                <input type="text" id="edit-w-content" class="admin-form-control" placeholder="URL youtube, mp4 ou mp3">
                                <div id="widget-file-upload-container" style="margin-top: 0.5rem; display: none;">
                                    <input type="file" id="edit-w-file" style="font-size:0.8rem;" accept="image/*,video/*,audio/*">
                                    <span id="edit-w-file-status" style="font-size:0.75rem; color:#888; display:block; margin-top:0.2rem;">Téléverser un fichier média</span>
                                </div>
                            </div>

                            <!-- Text Area Content (Rich text HTML, Carousels image lists) -->
                            <div class="admin-form-group" id="content-textarea-group" style="display:none;">
                                <label id="content-textarea-label">Contenu étendu</label>
                                <textarea id="edit-w-content-textarea" class="admin-form-control" rows="6" placeholder="Contenu HTML ou chemins d'images"></textarea>
                                <span style="font-size:0.7rem; color:#888; display:block; margin-top:0.2rem;" id="content-textarea-help"></span>
                            </div>

                            <div class="admin-form-group" id="link-group">
                                <label>Lien hypertexte (Clic)</label>
                                <input type="text" id="edit-w-link" class="admin-form-control" placeholder="https://votresite.com">
                            </div>

                            <h5 style="font-size:0.75rem; text-transform:uppercase; color:#888; border-bottom:1px solid var(--admin-border); padding-bottom:0.2rem; margin: 2rem 0 1rem;">Positionnement (%)</h5>
                            
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap: 1rem;">
                                <div class="admin-form-group">
                                    <label>Horizontal (X)</label>
                                    <input type="number" id="edit-w-x" class="admin-form-control" min="0" max="100">
                                </div>
                                <div class="admin-form-group">
                                    <label>Vertical (Y)</label>
                                    <input type="number" id="edit-w-y" class="admin-form-control" min="0" max="100">
                                </div>
                                <div class="admin-form-group">
                                    <label>Largeur (W)</label>
                                    <input type="number" id="edit-w-w" class="admin-form-control" min="5" max="100">
                                </div>
                                <div class="admin-form-group">
                                    <label>Hauteur (H)</label>
                                    <input type="number" id="edit-w-h" class="admin-form-control" min="5" max="100">
                                </div>
                            </div>

                            <!-- Text/Shape Advanced Styling Options -->
                            <div id="styling-group" style="display:none; border-top:1px solid var(--admin-border); padding-top:1.5rem; margin-top:1.5rem;">
                                <h5 style="font-size:0.8rem; text-transform:uppercase; color:var(--accent-gold); margin-bottom:1rem;">Formatage de l'élément</h5>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap: 1rem;">
                                    <div class="admin-form-group" id="w-font-size-group">
                                        <label>Taille du Texte</label>
                                        <input type="number" id="edit-w-font-size" class="admin-form-control" placeholder="14" min="8" max="72">
                                    </div>
                                    <div class="admin-form-group" id="w-font-color-group">
                                        <label>Couleur Texte</label>
                                        <input type="color" id="edit-w-font-color" class="admin-form-control" style="height:40px; padding:0; cursor:pointer;">
                                    </div>
                                    <div class="admin-form-group">
                                        <label>Fond Élément</label>
                                        <input type="color" id="edit-w-bg-color" class="admin-form-control" style="height:40px; padding:0; cursor:pointer;">
                                    </div>
                                    <div class="admin-form-group">
                                        <label>Opacité (0-100)</label>
                                        <input type="number" id="edit-w-opacity" class="admin-form-control" min="0" max="100" placeholder="100">
                                    </div>
                                    <div class="admin-form-group">
                                        <label>Arrondi (px)</label>
                                        <input type="number" id="edit-w-border-radius" class="admin-form-control" min="0" max="100" placeholder="0">
                                    </div>
                                    <div class="admin-form-group" id="w-padding-group">
                                        <label>Marges (px)</label>
                                        <input type="number" id="edit-w-padding" class="admin-form-control" min="0" max="50" placeholder="0">
                                    </div>
                                </div>
                            </div>

                            <!-- Z-Index plans & duplication -->
                            <div style="border-top:1px solid var(--admin-border); padding-top:1.5rem; margin-top:1.5rem; display:flex; flex-direction:column; gap:0.5rem;">
                                <h5 style="font-size:0.8rem; text-transform:uppercase; color:#888; margin-bottom:0.5rem;">Organisation des Plans</h5>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem;">
                                    <button type="button" onclick="bringWidgetToFront()" class="admin-btn-action" style="margin:0; text-align:center; justify-content:center;">Premier plan</button>
                                    <button type="button" onclick="sendWidgetToBack()" class="admin-btn-action" style="margin:0; text-align:center; justify-content:center;">Arrière-plan</button>
                                </div>
                                <button type="button" onclick="duplicateActiveWidget()" class="btn btn-primary" style="padding:0.5rem; font-size:0.75rem; justify-content:center; width:100%; border-radius:4px; margin-top:0.5rem;">Dupliquer le Widget</button>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>

            <!-- TAB 3: BLOG -->
            <div id="tab-blog" class="admin-tab-content">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
                    <h3 style="font-family: var(--font-serif); font-size: 1.3rem; margin: 0;">Gestion du Blogue Littéraire</h3>
                    <button class="btn btn-gold" onclick="openModal('modal-blog')" style="padding: 0.6rem 1.2rem; font-size: 0.8rem; border-radius: 4px;">Écrire un article</button>
                </div>

                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Titre de l'article</th>
                                <th>Date de création</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="blog-table-body">
                            <!-- JS populated -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: SHOP -->
            <div id="tab-shop" class="admin-tab-content">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
                    <h3 style="font-family: var(--font-serif); font-size: 1.3rem; margin: 0;">Gestion de la Boutique</h3>
                    <button class="btn btn-gold" onclick="openModal('modal-shop')" style="padding: 0.6rem 1.2rem; font-size: 0.8rem; border-radius: 4px;">Ajouter un produit</button>
                </div>

                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Titre du produit</th>
                                <th>Prix</th>
                                <th>Catégorie</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="shop-table-body">
                            <!-- JS populated -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 6: AD CAMPAIGNS (RÉGIE PUB) -->
            <div id="tab-ads" class="admin-tab-content">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
                    <h3 style="font-family: var(--font-serif); font-size: 1.3rem; margin: 0;">Régie Publicitaire</h3>
                    <button class="btn btn-gold" onclick="openModal('modal-ad')" style="padding: 0.6rem 1.2rem; font-size: 0.8rem; border-radius: 4px;">Créer une campagne</button>
                </div>

                <!-- Ad KPI Cards -->
                <div class="dashboard-stats-grid">
                    <div class="admin-card">
                        <div class="admin-card-val" id="stat-ads-total">0</div>
                        <div class="admin-card-lbl">Campagnes actives</div>
                    </div>
                    <div class="admin-card">
                        <div class="admin-card-val" id="stat-ads-impressions">0</div>
                        <div class="admin-card-lbl">Impressions</div>
                    </div>
                    <div class="admin-card">
                        <div class="admin-card-val" id="stat-ads-clicks">0</div>
                        <div class="admin-card-lbl">Clics</div>
                    </div>
                    <div class="admin-card">
                        <div class="admin-card-val" id="stat-ads-revenue">0.00 $</div>
                        <div class="admin-card-lbl">Chiffre d'affaires</div>
                    </div>
                </div>

                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Client & Campagne</th>
                                <th>Emplacement</th>
                                <th>Modèle</th>
                                <th>Tarif</th>
                                <th>Impressions / Clics / CTR</th>
                                <th>Revenu généré</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ads-table-body">
                            <!-- JS populated -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 5: SETTINGS -->
            <div id="tab-settings" class="admin-tab-content">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem; align-items: start;">
                    <!-- Password Update Card -->
                    <div style="background:var(--admin-panel-bg); border: 1px solid var(--admin-border); padding: 2.5rem; border-radius: 8px;">
                        <h3 style="font-family: var(--font-serif); font-size: 1.3rem; margin-bottom: 2rem;">Sécurité & Mot de Passe</h3>
                        
                        <form id="settings-form">
                            <div class="admin-form-group">
                                <label for="old-password">Mot de passe actuel</label>
                                <input type="password" id="old-password" class="admin-form-control" required>
                            </div>
                            <div class="admin-form-group">
                                <label for="new-password">Nouveau mot de passe</label>
                                <input type="password" id="new-password" class="admin-form-control" required>
                            </div>
                            
                            <button type="submit" class="btn btn-gold" style="padding:0.6rem 1.5rem; font-size:0.85rem; border-radius:4px; margin-top:1rem;">Mettre à jour</button>
                            <div id="password-status" style="margin-top:1rem; font-size:0.9rem; display:none;"></div>
                        </form>
                    </div>

                    <!-- Homepage Configurations Card -->
                    <div style="background:var(--admin-panel-bg); border: 1px solid var(--admin-border); padding: 2.5rem; border-radius: 8px;">
                        <h3 style="font-family: var(--font-serif); font-size: 1.3rem; margin-bottom: 2rem;">Configuration de l'Accueil</h3>
                        
                        <form id="homepage-settings-form">
                            <div class="admin-form-group">
                                <label for="setting-featured-mag">Numéro de magazine en vedette</label>
                                <select id="setting-featured-mag" class="admin-form-control">
                                    <option value="latest">Dernier numéro publié (Automatique)</option>
                                    <!-- Dynamic list of magazines -->
                                </select>
                            </div>

                            <div class="admin-form-group">
                                <label for="setting-custom-cover">Couverture du header personnalisée (Optionnel)</label>
                                <input type="text" id="setting-custom-cover" class="admin-form-control" placeholder="uploads/images/...">
                                <input type="file" style="margin-top:0.5rem; font-size:0.8rem;" accept="image/*" onchange="handleFileUpload(this, 'setting-custom-cover', 'image')">
                                <span style="font-size:0.75rem; color:#888; display:block; margin-top:0.2rem;">Téléverser pour remplacer la couverture par défaut</span>
                            </div>
                            
                            <button type="submit" class="btn btn-gold" style="padding:0.6rem 1.5rem; font-size:0.85rem; border-radius:4px; margin-top:1rem;">Enregistrer la configuration</button>
                            <div id="homepage-settings-status" style="margin-top:1rem; font-size:0.9rem; display:none;"></div>
                        </form>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- MODAL: ADD/EDIT MAGAZINE -->
    <div id="modal-magazine" class="admin-modal">
        <div class="admin-modal-box">
            <h3 style="font-family: var(--font-serif); font-size: 1.4rem; margin-bottom: 1.5rem;">Détails du numéro</h3>
            <form id="magazine-form">
                <input type="hidden" id="mag-id">
                
                <div class="admin-form-group">
                    <label>Titre de l'édition</label>
                    <input type="text" id="mag-title" class="admin-form-control" required placeholder="Salamandre - Numéro X">
                </div>

                <div class="admin-form-group">
                    <label>Fichier PDF</label>
                    <input type="text" id="mag-pdf-path" class="admin-form-control" required placeholder="uploads/magazines/...">
                    <input type="file" style="margin-top:0.5rem; font-size:0.8rem;" accept=".pdf" onchange="handleFileUpload(this, 'mag-pdf-path', 'pdf')">
                    <span style="font-size:0.75rem; color:#888; display:block; margin-top:0.2rem;">Téléverser ou saisir le chemin relatif</span>
                </div>

                <div class="admin-form-group">
                    <label>Couverture d'édition (Image)</label>
                    <input type="text" id="mag-cover-path" class="admin-form-control" placeholder="uploads/images/...">
                    <input type="file" style="margin-top:0.5rem; font-size:0.8rem;" accept="image/*" onchange="handleFileUpload(this, 'mag-cover-path', 'image')">
                    <span style="font-size:0.75rem; color:#888; display:block; margin-top:0.2rem;">Téléverser une image de couverture</span>
                </div>

                <div class="admin-form-group">
                    <label>Date de Publication</label>
                    <input type="date" id="mag-pub-date" class="admin-form-control" required>
                </div>

                <div style="display:flex; justify-content:flex-end; gap: 1rem; margin-top: 2rem;">
                    <button type="button" onclick="closeModal('modal-magazine')" class="btn btn-secondary" style="padding:0.6rem 1.2rem; font-size:0.8rem; border-radius:4px;">Annuler</button>
                    <button type="submit" class="btn btn-gold" style="padding:0.6rem 1.2rem; font-size:0.8rem; border-radius:4px;">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ADD/EDIT BLOG ARTICLE -->
    <div id="modal-blog" class="admin-modal">
        <div class="admin-modal-box" style="max-width: 800px;">
            <h3 style="font-family: var(--font-serif); font-size: 1.4rem; margin-bottom: 1.5rem;">Édition de l'article</h3>
            <form id="blog-form">
                <input type="hidden" id="post-id">

                <div class="admin-form-group">
                    <label>Titre de l'article</label>
                    <input type="text" id="post-title" class="admin-form-control" required>
                </div>

                <div class="admin-form-group">
                    <label>Résumé / Chapeau</label>
                    <textarea id="post-excerpt" class="admin-form-control" rows="2" style="resize:vertical;"></textarea>
                </div>

                <div class="admin-form-group">
                    <label>Contenu de l'article</label>
                    <!-- Rich text simplified text-editor -->
                    <div class="rich-editor-btnbar">
                        <button type="button" onclick="execEditorCommand('bold')" style="font-weight:bold;">B</button>
                        <button type="button" onclick="execEditorCommand('italic')" style="font-style:italic;">I</button>
                        <button type="button" onclick="execEditorCommand('formatBlock', '<h2>')">H2</button>
                        <button type="button" onclick="execEditorCommand('formatBlock', '<blockquote>')">Citation</button>
                    </div>
                    <div id="post-editor-area" class="rich-editor-area" contenteditable="true"></div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;">
                    <div class="admin-form-group">
                        <label>Image de couverture (Art)</label>
                        <input type="text" id="post-image-path" class="admin-form-control">
                        <input type="file" style="margin-top:0.5rem; font-size:0.8rem;" accept="image/*" onchange="handleFileUpload(this, 'post-image-path', 'image')">
                    </div>
                    
                    <div class="admin-form-group">
                        <label>Mots clés / Étiquettes (séparés par virgules)</label>
                        <input type="text" id="post-tags" class="admin-form-control" placeholder="Art, Littérature, Poésie">
                    </div>
                </div>

                <div class="admin-form-group">
                    <label>Statut de Publication</label>
                    <select id="post-status" class="admin-form-control">
                        <option value="published">Publié</option>
                        <option value="draft">Brouillon / Attente</option>
                    </select>
                </div>

                <div style="display:flex; justify-content:flex-end; gap: 1rem; margin-top: 2rem;">
                    <button type="button" onclick="closeModal('modal-blog')" class="btn btn-secondary" style="padding:0.6rem 1.2rem; font-size:0.8rem; border-radius:4px;">Annuler</button>
                    <button type="submit" class="btn btn-gold" style="padding:0.6rem 1.2rem; font-size:0.8rem; border-radius:4px;">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ADD/EDIT SHOP PRODUCT -->
    <div id="modal-shop" class="admin-modal">
        <div class="admin-modal-box">
            <h3 style="font-family: var(--font-serif); font-size: 1.4rem; margin-bottom: 1.5rem;">Détails du produit</h3>
            <form id="product-form">
                <input type="hidden" id="prod-id">

                <div class="admin-form-group">
                    <label>Nom du produit</label>
                    <input type="text" id="prod-title" class="admin-form-control" required>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1.2fr; gap:1.5rem;">
                    <div class="admin-form-group">
                        <label>Prix de vente ($)</label>
                        <input type="number" id="prod-price" class="admin-form-control" step="0.01" required>
                    </div>
                    <div class="admin-form-group">
                        <label>Catégorie</label>
                        <select id="prod-category" class="admin-form-control">
                            <option value="abo">Abonnements</option>
                            <option value="num">Numéros PDF</option>
                            <option value="merch">Papeterie / Goodies</option>
                        </select>
                    </div>
                </div>

                <div class="admin-form-group">
                    <label>Description du produit</label>
                    <textarea id="prod-desc" class="admin-form-control" rows="3" style="resize:vertical;"></textarea>
                </div>

                <div class="admin-form-group">
                    <label>Image de Présentation</label>
                    <input type="text" id="prod-image-path" class="admin-form-control">
                    <input type="file" style="margin-top:0.5rem; font-size:0.8rem;" accept="image/*" onchange="handleFileUpload(this, 'prod-image-path', 'image')">
                </div>

                <div style="border: 1px solid var(--admin-border); padding: 1.5rem; border-radius: 4px; margin-bottom: 1.5rem;">
                    <div class="admin-form-group" style="margin-bottom:0.8rem; display:flex; align-items:center; gap: 0.8rem;">
                        <input type="checkbox" id="prod-digital" style="width:18px; height:18px; cursor:pointer;">
                        <label for="prod-digital" style="margin-bottom:0; cursor:pointer;">Produit numérique téléchargeable</label>
                    </div>
                    <div class="admin-form-group" style="margin-bottom:0;">
                        <label>Fichier à télécharger (si numérique)</label>
                        <input type="text" id="prod-download-url" class="admin-form-control" placeholder="uploads/magazines/...">
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap: 1rem; margin-top: 2rem;">
                    <button type="button" onclick="closeModal('modal-shop')" class="btn btn-secondary" style="padding:0.6rem 1.2rem; font-size:0.8rem; border-radius:4px;">Annuler</button>
                    <button type="submit" class="btn btn-gold" style="padding:0.6rem 1.2rem; font-size:0.8rem; border-radius:4px;">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ADD/EDIT AD CAMPAIGN -->
    <div id="modal-ad" class="admin-modal">
        <div class="admin-modal-box">
            <h3 style="font-family: var(--font-serif); font-size: 1.4rem; margin-bottom: 1.5rem;">Détails de la campagne</h3>
            <form id="ad-form">
                <input type="hidden" id="ad-id">
                
                <div class="admin-form-group">
                    <label>Nom du Client</label>
                    <input type="text" id="ad-client" class="admin-form-control" required placeholder="Ex: Art & Lettres Éditions">
                </div>

                <div class="admin-form-group">
                    <label>Titre de la Campagne (Slogan / Alt)</label>
                    <input type="text" id="ad-title" class="admin-form-control" required placeholder="Ex: Découvrez les nouveaux poètes">
                </div>

                <div class="admin-form-group">
                    <label>Bannière Publicitaire (Image)</label>
                    <input type="text" id="ad-banner-path" class="admin-form-control" required placeholder="uploads/images/...">
                    <input type="file" style="margin-top:0.5rem; font-size:0.8rem;" accept="image/*" onchange="handleFileUpload(this, 'ad-banner-path', 'image')">
                    <span style="font-size:0.75rem; color:#888; display:block; margin-top:0.2rem;">Téléverser une bannière (recommandé: horizontal, min 728px de large)</span>
                </div>

                <div class="admin-form-group">
                    <label>Lien de redirection (URL cible)</label>
                    <input type="url" id="ad-link-url" class="admin-form-control" required placeholder="https://sitepartenaire.com/promo">
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;">
                    <div class="admin-form-group">
                        <label>Emplacement Stratégique</label>
                        <select id="ad-location" class="admin-form-control">
                            <option value="header">Bannière en-tête (Header)</option>
                            <option value="homepage">Milieu de page d'accueil</option>
                            <option value="sidebar">Sidebar/Articles (Blog)</option>
                            <option value="footer">Bas de page (Footer)</option>
                        </select>
                    </div>
                    <div class="admin-form-group">
                        <label>Modèle de Facturation</label>
                        <select id="ad-model" class="admin-form-control">
                            <option value="flat">Forfait fixe (Flat rate)</option>
                            <option value="cpc">Coût par clic (CPC)</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1.2fr; gap:1.5rem;">
                    <div class="admin-form-group">
                        <label>Prix / Tarif ($)</label>
                        <input type="number" id="ad-price" class="admin-form-control" step="0.01" required placeholder="Ex: 150.00 ou 0.50">
                    </div>
                    <div class="admin-form-group">
                        <label>Statut initial</label>
                        <select id="ad-status" class="admin-form-control">
                            <option value="active">Active (En diffusion)</option>
                            <option value="paused">En pause</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;">
                    <div class="admin-form-group">
                        <label>Date de début</label>
                        <input type="date" id="ad-start-date" class="admin-form-control">
                    </div>
                    <div class="admin-form-group">
                        <label>Date de fin</label>
                        <input type="date" id="ad-end-date" class="admin-form-control">
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap: 1rem; margin-top: 2rem;">
                    <button type="button" onclick="closeModal('modal-ad')" class="btn btn-secondary" style="padding:0.6rem 1.2rem; font-size:0.8rem; border-radius:4px;">Annuler</button>
                    <button type="submit" class="btn btn-gold" style="padding:0.6rem 1.2rem; font-size:0.8rem; border-radius:4px;">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JS -->
    <script src="../assets/js/admin.js?v=<?php echo time(); ?>"></script>
</body>
</html>
