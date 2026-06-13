<?php
// Load Database
$db_file = __DIR__ . '/data/database.json';
$db = ['magazines' => [], 'blog' => [], 'shop' => []];
if (file_exists($db_file)) {
    $db = json_decode(file_get_contents($db_file), true) ?: $db;
}

// Get latest items
$featured_mag = null;
$featured_mag_id = $db['settings']['featured_mag_id'] ?? 'latest';

if ($featured_mag_id === 'latest') {
    $featured_mag = !empty($db['magazines']) ? end($db['magazines']) : null;
} else {
    foreach ($db['magazines'] as $m) {
        if ($m['id'] === $featured_mag_id) {
            $featured_mag = $m;
            break;
        }
    }
    if (!$featured_mag) {
        $featured_mag = !empty($db['magazines']) ? end($db['magazines']) : null;
    }
}

// Determine cover path
$homepage_cover = $db['settings']['custom_cover_path'] ?? '';
if (empty($homepage_cover)) {
    if ($featured_mag && !empty($featured_mag['cover_path'])) {
        $homepage_cover = $featured_mag['cover_path'];
    } else {
        $homepage_cover = 'uploads/images/magazine_cover_default.png';
    }
}

$featured_posts = array_slice($db['blog'] ?? [], -2); // latest 2 posts
$featured_posts = array_reverse($featured_posts);
$featured_products = array_slice($db['shop'] ?? [], 0, 3); // first 3 products

include 'header.php';
?>

<!-- Hero Banner Section -->
<section class="hero">
    <div class="hero-grid">
        <div class="hero-content">
            <span class="hero-tagline">Revue Littéraire & Artistique Canadienne</span>
            <h1 class="hero-title">Sentir le magazine au bout de <span>vos doigts</span></h1>
            <p class="hero-desc">Explorez des essais profonds, de la poésie inspirante et des portfolios artistiques à travers notre liseur de magazine interactif haut de gamme.</p>
            
            <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                <?php if ($featured_mag): ?>
                    <a href="viewer.php?id=<?php echo $featured_mag['id']; ?>" class="btn btn-primary">Découvrir le numéro</a>
                <?php else: ?>
                    <a href="viewer.php?id=mag-1" class="btn btn-primary">Découvrir le numéro</a>
                <?php endif; ?>
                <a href="shop.php" class="btn btn-secondary">Visiter la boutique</a>
            </div>
        </div>
        
        <div class="hero-visual">
            <div class="hero-book-shadow"></div>
            <img src="<?php echo $homepage_cover; ?>" alt="Couverture Magazine" class="hero-book">
        </div>
    </div>
</section>

<!-- Interactive Liseur Showcase Features -->
<section class="section" style="background: var(--bg-secondary); max-width: 100%;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div class="section-header">
            <span class="section-subtitle">Liseur Premium</span>
            <h2 class="section-title">Une expérience de lecture réinventée</h2>
            <p style="color: var(--text-secondary); max-width: 600px; margin: 1rem auto 0;">Notre visionneuse de magazine interactive fusionne la beauté du papier glacé avec l'agilité du numérique.</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 3rem; margin-top: 3rem;">
            <!-- Card 1 -->
            <div style="background: var(--bg-primary); padding: 3rem 2rem; border-radius: 4px; border: 1px solid var(--border-color); text-align: center; transition: var(--transition-smooth);" onmouseover="this.style.borderColor='var(--accent-gold)'; this.style.transform='translateY(-5px)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='none';">
                <div style="width: 60px; height: 60px; background: rgba(197, 168, 128, 0.1); color: var(--accent-gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <svg viewBox="0 0 24 24" style="width: 30px; height: 30px; fill: none; stroke: currentColor; stroke-width: 2;"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                </div>
                <h3 style="font-family: var(--font-serif); font-size: 1.3rem;">Double Page Réaliste</h3>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">Ressentez la fluidité des pages d'un vrai magazine avec reliure centrale et ombres portées douces.</p>
            </div>
            
            <!-- Card 2 -->
            <div style="background: var(--bg-primary); padding: 3rem 2rem; border-radius: 4px; border: 1px solid var(--border-color); text-align: center; transition: var(--transition-smooth);" onmouseover="this.style.borderColor='var(--accent-gold)'; this.style.transform='translateY(-5px)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='none';">
                <div style="width: 60px; height: 60px; background: rgba(197, 168, 128, 0.1); color: var(--accent-gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <svg viewBox="0 0 24 24" style="width: 30px; height: 30px; fill: none; stroke: currentColor; stroke-width: 2;"><path d="M15 10l5 5-5 5M4 4v7a4 4 0 004 4h12" /></svg>
                </div>
                <h3 style="font-family: var(--font-serif); font-size: 1.3rem;">Widgets Vidéos & Pubs</h3>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">Des vidéos YouTube intégrées et des publicités cliquables enrichissent directement votre lecture sans quitter la page.</p>
            </div>

            <!-- Card 3 -->
            <div style="background: var(--bg-primary); padding: 3rem 2rem; border-radius: 4px; border: 1px solid var(--border-color); text-align: center; transition: var(--transition-smooth);" onmouseover="this.style.borderColor='var(--accent-gold)'; this.style.transform='translateY(-5px)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='none';">
                <div style="width: 60px; height: 60px; background: rgba(197, 168, 128, 0.1); color: var(--accent-gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <svg viewBox="0 0 24 24" style="width: 30px; height: 30px; fill: none; stroke: currentColor; stroke-width: 2;"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2" /></svg>
                </div>
                <h3 style="font-family: var(--font-serif); font-size: 1.3rem;">Liens Précis & Stats</h3>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">Partagez un lien direct vers une page spécifique et récoltez des statistiques de lecture précises pour chaque page lue.</p>
            </div>
        </div>
    </div>
</section>

<!-- Blog Section Preview -->
<section class="section">
    <div class="section-header">
        <span class="section-subtitle">Dernières Publications</span>
        <h2 class="section-title">Le Blogue Littéraire</h2>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 4rem; margin-top: 3rem;">
        <?php foreach ($featured_posts as $post): ?>
            <article style="display: flex; flex-direction: column; gap: 1.5rem; opacity: 0; transform: translateY(20px); animation: var-fade-in 0.8s forwards ease; margin-bottom: 2rem;">
                <div style="overflow: hidden; aspect-ratio: 16/9; position: relative;">
                    <img src="<?php echo $post['image']; ?>" alt="<?php echo $post['title']; ?>" style="width: 100%; height: 100%; object-fit: cover; transition: var(--transition-smooth);" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='none';">
                </div>
                <div>
                    <div style="display: flex; gap: 1rem; margin-bottom: 0.8rem; font-size: 0.85rem; color: var(--text-muted);">
                        <span><?php echo date('d M Y', strtotime($post['date'])); ?></span>
                        <span>&bull;</span>
                        <span><?php echo implode(', ', array_slice($post['tags'], 0, 2)); ?></span>
                    </div>
                    <h3 style="font-family: var(--font-serif); font-size: 1.8rem; margin-bottom: 1rem;">
                        <a href="post.php?slug=<?php echo $post['slug']; ?>" style="color: var(--text-primary);"><?php echo $post['title']; ?></a>
                    </h3>
                    <p style="color: var(--text-secondary); margin-bottom: 1.5rem;"><?php echo $post['excerpt']; ?></p>
                    <a href="post.php?slug=<?php echo $post['slug']; ?>" style="font-weight: 600; font-size: 0.9rem; color: var(--accent-gold); display: inline-flex; align-items: center; gap: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        Lire la suite 
                        <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2;"><path d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    
    <div style="text-align: center; margin-top: 2rem;">
        <a href="blog.php" class="btn btn-secondary">Découvrir le blogue</a>
    </div>
</section>

<!-- Boutique Preview Section -->
<section class="section" style="border-top: 1px solid var(--border-color);">
    <div class="section-header">
        <span class="section-subtitle">Objets d'Art et Lecture</span>
        <h2 class="section-title">Notre Boutique</h2>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2.5rem; margin-top: 3rem;">
        <?php foreach ($featured_products as $prod): ?>
            <div style="display: flex; flex-direction: column; background: var(--bg-secondary); padding: 1.5rem; border-radius: 4px; border: 1px solid transparent; transition: var(--transition-smooth);" onmouseover="this.style.borderColor='var(--accent-gold)';" onmouseout="this.style.borderColor='transparent';">
                <div style="aspect-ratio: 3/4; overflow: hidden; background: #fff; margin-bottom: 1.5rem;">
                    <img src="<?php echo $prod['image']; ?>" alt="<?php echo $prod['title']; ?>" style="width: 100%; height: 100%; object-fit: cover; transition: var(--transition-smooth);" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='none';">
                </div>
                <h3 style="font-family: var(--font-serif); font-size: 1.15rem; margin-bottom: 0.5rem; height: 3rem; overflow: hidden; line-height: 1.4;"><?php echo $prod['title']; ?></h3>
                <div style="color: var(--accent-gold); font-weight: 700; font-size: 1.2rem; margin-bottom: 1rem;"><?php echo number_format($prod['price'], 2); ?> $</div>
                
                <button onclick="addToCart(<?php echo htmlspecialchars(json_encode($prod)); ?>)" class="btn btn-primary" style="padding: 0.6rem; justify-content: center; font-size: 0.8rem; margin-top: auto;">Ajouter au panier</button>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div style="text-align: center; margin-top: 4rem;">
        <a href="shop.php" class="btn btn-secondary">Visiter la boutique complète</a>
    </div>
</section>

<?php include 'footer.php'; ?>
