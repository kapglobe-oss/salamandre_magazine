<?php
// Load Database
$db_file = __DIR__ . '/data/database.json';
$db = ['blog' => []];
if (file_exists($db_file)) {
    $db = json_decode(file_get_contents($db_file), true) ?: $db;
}

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$post = null;

foreach ($db['blog'] ?? [] as $p) {
    if ($p['slug'] === $slug) {
        $post = $p;
        break;
    }
}

// 404 Fallback
if (!$post || $post['status'] !== 'published') {
    header("HTTP/1.0 404 Not Found");
    include 'header.php';
    echo '<div style="text-align:center; padding: 10rem 2rem;"><h1 style="font-family: var(--font-serif); font-size: 3rem;">Article Introuvable</h1><p style="margin-bottom:2rem;">L\'article que vous recherchez n\'existe pas ou a été retiré.</p><a href="blog.php" class="btn btn-primary">Retour au blogue</a></div>';
    include 'footer.php';
    exit();
}

// Find related articles (sharing at least one tag, excluding current post)
$related = [];
foreach ($db['blog'] ?? [] as $p) {
    if ($p['id'] !== $post['id'] && $p['status'] === 'published') {
        $common = array_intersect($post['tags'] ?? [], $p['tags'] ?? []);
        if (!empty($common)) {
            $related[] = $p;
        }
    }
}
// If not enough related, backfill with others
if (count($related) < 2) {
    foreach ($db['blog'] ?? [] as $p) {
        if ($p['id'] !== $post['id'] && $p['status'] === 'published' && !in_array($p, $related)) {
            $related[] = $p;
            if (count($related) >= 2) break;
        }
    }
}
$related = array_slice($related, 0, 2);

include 'header.php';
?>

<!-- Reading Progress Bar -->
<div id="read-progress" style="position: fixed; top: 0; left: 0; width: 0%; height: 4px; background: var(--accent-gold); z-index: 101; transition: width 0.1s ease;"></div>

<!-- Immersive Article Hero -->
<article style="max-width: 900px; margin: 0 auto; padding: 4rem 2rem 6rem;">
    <!-- Meta and Title -->
    <header style="text-align: center; margin-bottom: 4rem; position: static; background: none; backdrop-filter: none; border-bottom: none; padding: 0;">
        <div style="display: flex; gap: 1rem; justify-content: center; margin-bottom: 1rem; font-size: 0.9rem; color: var(--accent-gold); text-transform: uppercase; font-family: var(--font-display); font-weight: 600;">
            <span><?php echo date('d F Y', strtotime($post['date'])); ?></span>
            <span>&bull;</span>
            <span>Par la rédaction</span>
        </div>
        
        <h1 style="font-size: 3.2rem; font-family: var(--font-serif); font-weight: 400; line-height: 1.15; margin-bottom: 2rem;"><?php echo $post['title']; ?></h1>
        
        <div style="display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap;">
            <?php foreach ($post['tags'] as $tag): ?>
                <span style="font-size: 0.8rem; background: var(--bg-secondary); border: 1px solid var(--border-color); padding: 0.3rem 1rem; border-radius: 20px; color: var(--text-secondary);"><?php echo htmlspecialchars($tag); ?></span>
            <?php endforeach; ?>
        </div>
    </header>

    <!-- Large Featured Image -->
    <div style="width: 100vw; position: relative; left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw; margin-bottom: 4rem; overflow: hidden; aspect-ratio: 21/9; box-shadow: var(--shadow-md);">
        <img src="<?php echo $post['image']; ?>" alt="<?php echo $post['title']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
    </div>

    <!-- Article Content -->
    <div class="article-content" style="font-size: 1.15rem; line-height: 1.8; color: var(--text-primary); font-family: var(--font-sans);">
        <?php 
        // Stylize content in PHP or let CSS handle it.
        // We'll output the content as-is. We can inject custom styling for dropcaps or let standard tag style classes handle it.
        echo $post['content']; 
        ?>
    </div>

    <!-- Article Ad Banner -->
    <?php
    $sidebar_ad = get_and_track_ad('sidebar');
    if ($sidebar_ad):
    ?>
        <div style="margin: 3rem 0; background: var(--bg-secondary); border: 1px solid var(--border-color); padding: 0.5rem; text-align: center; border-radius: 4px; position: relative;">
            <span style="position: absolute; top: -10px; left: 15px; background: var(--bg-primary); padding: 0 8px; font-size: 0.55rem; color: var(--text-muted); font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase;">Publicité Partenaire</span>
            <a href="api.php?action=track_ad_click&id=<?php echo $sidebar_ad['id']; ?>" target="_blank" style="display: block; overflow: hidden; border-radius: 2px;">
                <img src="<?php echo $sidebar_ad['banner_path']; ?>" alt="<?php echo htmlspecialchars($sidebar_ad['title']); ?>" style="width: 100%; height: auto; max-height: 150px; object-fit: cover; display: block;">
            </a>
        </div>
    <?php endif; ?>

    <!-- Article Footer / Share buttons -->
    <footer style="margin-top: 5rem; padding-top: 2rem; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; position: static; background: none; border-bottom: none; flex-wrap: wrap; gap: 2rem;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Partager l'article :</span>
            <button onclick="navigator.clipboard.writeText(window.location.href); alert('Lien copié dans le presse-papiers !');" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.8rem; text-transform: none;">Copier le lien</button>
        </div>
        
        <a href="blog.php" style="font-weight: 600; font-size: 0.9rem; color: var(--accent-gold); display: flex; align-items: center; gap: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">
            <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2; transform: scaleX(-1);"><path d="M9 5l7 7-7 7"/></svg>
            Retour au blogue
        </a>
    </footer>
</article>

<!-- Related Articles Section -->
<section style="background: var(--bg-secondary); padding: 5rem 2rem;">
    <div style="max-width: 900px; margin: 0 auto;">
        <h3 style="font-family: var(--font-serif); font-size: 1.8rem; margin-bottom: 3rem; text-align: center; font-weight: 400;">À lire également</h3>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
            <?php foreach ($related as $rel_post): ?>
                <a href="post.php?slug=<?php echo $rel_post['slug']; ?>" style="display: block; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 4px; overflow: hidden; transition: var(--transition-smooth);" onmouseover="this.style.borderColor='var(--accent-gold)';" onmouseout="this.style.borderColor='var(--border-color)';">
                    <div style="aspect-ratio: 16/9; overflow: hidden;">
                        <img src="<?php echo $rel_post['image']; ?>" alt="<?php echo $rel_post['title']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div style="padding: 1.5rem;">
                        <h4 style="font-family: var(--font-serif); font-size: 1.2rem; color: var(--text-primary); margin-bottom: 0.5rem; line-height: 1.3;"><?php echo $rel_post['title']; ?></h4>
                        <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo date('d M Y', strtotime($rel_post['date'])); ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Custom Styling for article typography elements -->
<style>
.article-content > p:first-of-type::first-letter {
    font-size: 3.5rem;
    font-family: var(--font-serif);
    font-weight: 700;
    float: left;
    line-height: 0.85;
    padding-right: 8px;
    padding-top: 4px;
    color: var(--accent-gold);
}
.article-content p {
    margin-bottom: 1.8rem;
    font-size: 1.15rem;
    letter-spacing: -0.01em;
}
.article-content h2 {
    font-family: var(--font-serif);
    font-size: 2rem;
    margin-top: 3rem;
    margin-bottom: 1.5rem;
    font-weight: 400;
    color: var(--text-primary);
}
.article-content blockquote {
    font-family: var(--font-serif);
    font-size: 1.4rem;
    font-style: italic;
    border-left: 3px solid var(--accent-gold);
    padding-left: 2rem;
    margin: 2.5rem 0;
    color: var(--text-secondary);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const progress = document.getElementById('read-progress');
    window.addEventListener('scroll', () => {
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = (window.scrollY / docHeight) * 100;
        progress.style.width = scrollPercent + '%';
    });
});
</script>

<?php include 'footer.php'; ?>
