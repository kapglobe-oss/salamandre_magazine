<?php
// Load Database
$db_file = __DIR__ . '/data/database.json';
$db = ['blog' => []];
if (file_exists($db_file)) {
    $db = json_decode(file_get_contents($db_file), true) ?: $db;
}

$posts = $db['blog'] ?? [];
// Filter out unpublished posts
$posts = array_filter($posts, function($p) {
    return isset($p['status']) && $p['status'] === 'published';
});

// Extract all unique tags
$all_tags = [];
foreach ($posts as $post) {
    if (!empty($post['tags'])) {
        foreach ($post['tags'] as $tag) {
            $all_tags[] = $tag;
        }
    }
}
$unique_tags = array_unique($all_tags);

// Handle Search and Filter (can be done client-side for dynamic responsive feel, which is MUCH faster and premium!)
include 'header.php';
?>

<div style="background: var(--bg-secondary); padding: 5rem 2rem 3rem;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <span style="font-family: var(--font-display); font-size: 0.9rem; color: var(--accent-gold); letter-spacing: 0.15em; text-transform: uppercase;">L'univers des mots</span>
        <h1 style="font-size: 3.5rem; font-family: var(--font-serif); font-weight: 400; margin-top: 0.5rem; margin-bottom: 2rem;">Le Blogue Littéraire</h1>
        
        <!-- Search and Filter controls -->
        <div style="display: flex; gap: 1rem; max-width: 600px; margin: 0 auto; background: var(--bg-primary); padding: 0.5rem 1rem; border-radius: 50px; border: 1px solid var(--border-color); align-items: center; box-shadow: var(--shadow-sm);">
            <svg viewBox="0 0 24 24" style="width: 20px; height: 20px; fill: none; stroke: var(--text-secondary); stroke-width: 2; margin-left: 0.5rem;"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input type="text" id="blog-search" placeholder="Rechercher un article..." style="flex: 1; border: none; background: none; outline: none; font-size: 0.95rem; font-family: var(--font-sans); color: var(--text-primary);">
        </div>
    </div>
</div>

<!-- Sidebar/Blog Ad Banner -->
<?php
$sidebar_ad = get_and_track_ad('sidebar');
if ($sidebar_ad):
?>
    <div style="max-width: 970px; margin: 2rem auto 0; background: var(--bg-secondary); border: 1px solid var(--border-color); padding: 0.5rem; text-align: center; border-radius: 4px; position: relative;">
        <span style="position: absolute; top: -10px; left: 15px; background: var(--bg-primary); padding: 0 8px; font-size: 0.55rem; color: var(--text-muted); font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase;">Publicité Partenaire</span>
        <a href="api.php?action=track_ad_click&id=<?php echo $sidebar_ad['id']; ?>" target="_blank" style="display: block; overflow: hidden; border-radius: 2px;">
            <img src="<?php echo $sidebar_ad['banner_path']; ?>" alt="<?php echo htmlspecialchars($sidebar_ad['title']); ?>" style="width: 100%; height: auto; max-height: 120px; object-fit: cover; display: block;">
        </a>
    </div>
<?php endif; ?>

<div class="section" style="padding-top: 3rem;">
    <!-- Tag filters -->
    <div style="display: flex; gap: 0.8rem; justify-content: center; flex-wrap: wrap; margin-bottom: 4rem;">
        <button class="tag-filter-btn active" data-tag="all" style="background: var(--text-primary); color: var(--bg-primary); border: 1px solid var(--text-primary); padding: 0.5rem 1.5rem; border-radius: 30px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: var(--transition-fast);">Tous</button>
        <?php foreach ($unique_tags as $tag): ?>
            <button class="tag-filter-btn" data-tag="<?php echo htmlspecialchars($tag); ?>" style="background: transparent; color: var(--text-secondary); border: 1px solid var(--border-color); padding: 0.5rem 1.5rem; border-radius: 30px; font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: var(--transition-fast);"><?php echo htmlspecialchars($tag); ?></button>
        <?php endforeach; ?>
    </div>

    <!-- Articles Grid -->
    <div id="blog-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 3rem;">
        <?php if (empty($posts)): ?>
            <div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 4rem;">Aucun article publié pour le moment.</div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <article class="blog-card" data-tags="<?php echo htmlspecialchars(implode(',', $post['tags'] ?? [])); ?>" style="display: flex; flex-direction: column; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 4px; overflow: hidden; transition: var(--transition-smooth);" onmouseover="this.style.transform='translateY(-5px)'; this.style.borderColor='var(--accent-gold)';" onmouseout="this.style.transform='none'; this.style.borderColor='var(--border-color)';">
                    <div style="overflow: hidden; aspect-ratio: 16/10; position: relative;">
                        <img src="<?php echo $post['image']; ?>" alt="<?php echo $post['title']; ?>" style="width: 100%; height: 100%; object-fit: cover; transition: var(--transition-smooth);">
                    </div>
                    
                    <div style="padding: 2rem; flex: 1; display: flex; flex-direction: column;">
                        <div style="display: flex; gap: 0.8rem; margin-bottom: 0.8rem; font-size: 0.8rem; color: var(--text-muted);">
                            <span><?php echo date('d M Y', strtotime($post['date'])); ?></span>
                            <span>&bull;</span>
                            <span><?php echo implode(', ', $post['tags'] ?? []); ?></span>
                        </div>
                        
                        <h2 class="blog-post-title" style="font-family: var(--font-serif); font-size: 1.5rem; font-weight: 600; line-height: 1.3; margin-bottom: 1rem;">
                            <a href="post.php?slug=<?php echo $post['slug']; ?>" style="color: var(--text-primary);"><?php echo $post['title']; ?></a>
                        </h2>
                        
                        <p class="blog-post-excerpt" style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.5; margin-bottom: 2rem;"><?php echo $post['excerpt']; ?></p>
                        
                        <a href="post.php?slug=<?php echo $post['slug']; ?>" style="margin-top: auto; font-weight: 600; font-size: 0.85rem; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 0.4rem;">
                            Lire l'article
                            <svg viewBox="0 0 24 24" style="width: 14px; height: 14px; fill: none; stroke: currentColor; stroke-width: 2;"><path d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('blog-search');
    const tagButtons = document.querySelectorAll('.tag-filter-btn');
    const cards = document.querySelectorAll('.blog-card');
    let currentTag = 'all';
    let searchQuery = '';

    function filterCards() {
        cards.forEach(card => {
            const title = card.querySelector('.blog-post-title').textContent.toLowerCase();
            const excerpt = card.querySelector('.blog-post-excerpt').textContent.toLowerCase();
            const tags = card.dataset.tags.split(',');

            const matchesSearch = title.includes(searchQuery) || excerpt.includes(searchQuery);
            const matchesTag = currentTag === 'all' || tags.includes(currentTag);

            if (matchesSearch && matchesTag) {
                card.style.display = 'flex';
                card.animate([
                    { opacity: 0, transform: 'scale(0.95)' },
                    { opacity: 1, transform: 'scale(1)' }
                ], { duration: 300, easing: 'ease-out' });
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Search Input
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchQuery = e.target.value.toLowerCase();
            filterCards();
        });
    }

    // Tag Filter buttons
    tagButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            tagButtons.forEach(b => {
                b.classList.remove('active');
                b.style.background = 'transparent';
                b.style.color = 'var(--text-secondary)';
                b.style.borderColor = 'var(--border-color)';
            });
            
            btn.classList.add('active');
            btn.style.background = 'var(--text-primary)';
            btn.style.color = 'var(--bg-primary)';
            btn.style.borderColor = 'var(--text-primary)';

            currentTag = btn.dataset.tag;
            filterCards();
        });
    });
});
</script>

<?php include 'footer.php'; ?>
