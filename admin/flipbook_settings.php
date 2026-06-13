<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$configPath = __DIR__ . '/../assets/config/flipbook_config.json';
$config = [];
if (file_exists($configPath)) {
    $json = file_get_contents($configPath);
    $config = json_decode($json, true) ?? [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and cast inputs
    $config['showCover'] = isset($_POST['showCover']) ? true : false;
    $config['startPage'] = max(0, (int)($_POST['startPage'] ?? 0));
    $config['flippingTime'] = max(0, (int)($_POST['flippingTime'] ?? 800));
    $config['drawShadow'] = isset($_POST['drawShadow']) ? true : false;
    $config['maxShadowOpacity'] = min(1, max(0, floatval($_POST['maxShadowOpacity'] ?? 0.3)));
    $config['showPageCorners'] = isset($_POST['showPageCorners']) ? true : false;
    $config['usePortrait'] = isset($_POST['usePortrait']) ? true : false;
    $config['minWidth'] = max(0, (int)($_POST['minWidth'] ?? 300));
    $config['maxWidth'] = max($config['minWidth'], (int)($_POST['maxWidth'] ?? 1000));
    $config['minHeight'] = max(0, (int)($_POST['minHeight'] ?? 300));
    $config['maxHeight'] = max($config['minHeight'], (int)($_POST['maxHeight'] ?? 1000));

    // Save back to JSON file
    file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $message = 'Configuration enregistrée avec succès.';
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres Flipbook - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:linear-gradient(135deg,#0f0c20 0%,#15102a 50%,#241435 100%); }
        .settings-card { background:rgba(255,255,255,0.03); backdrop-filter:blur(20px); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:2rem 2rem; max-width:600px; width:100%; }
        .settings-card h2 { color:#fff; margin-bottom:1.5rem; font-family:var(--font-display); }
        .form-group { margin-bottom:1rem; display:flex; flex-direction:column; }
        .form-group label { color:#ccc; margin-bottom:0.3rem; font-size:0.9rem; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="range"] {
            padding:0.6rem 0.8rem; border:1px solid rgba(255,255,255,0.1); background:rgba(0,0,0,0.2); color:#fff; border-radius:4px; width:100%;
        }
        .form-group input[type="checkbox"] { align-self:flex-start; margin-top:0.3rem; }
        .submit-btn { background:var(--accent-gold); color:#000; border:none; padding:0.8rem 1.2rem; cursor:pointer; border-radius:4px; width:100%; font-weight:600; }
        .message { margin-top:1rem; color:#0f0; }
    </style>
</head>
<body>
    <div class="settings-card">
        <h2>Configuration du Flipbook</h2>
        <?php if (!empty($message)): ?><div class="message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label><input type="checkbox" name="showCover" <?php echo ($config['showCover'] ?? true) ? 'checked' : ''; ?>> Afficher la couverture</label>
            </div>
            <div class="form-group">
                <label for="preRenderRange">Plage de pré‑rendu (nombre de pages)</label>
                <input type="number" id="preRenderRange" name="preRenderRange" min="1" value="<?php echo htmlspecialchars($config['preRenderRange'] ?? 5); ?>" required>
            </div>
            <div class="form-group">
                <label for="flippingTime">Temps de retournement (ms)</label>
                <input type="number" id="flippingTime" name="flippingTime" min="0" value="<?php echo htmlspecialchars($config['flippingTime'] ?? 800); ?>" required>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="drawShadow" <?php echo ($config['drawShadow'] ?? true) ? 'checked' : ''; ?>> Ombre du pli</label>
            </div>
            <div class="form-group">
                <label for="maxShadowOpacity">Opacité maximale de l'ombre (0‑1)</label>
                <input type="range" id="maxShadowOpacity" name="maxShadowOpacity" min="0" max="1" step="0.01" value="<?php echo htmlspecialchars($config['maxShadowOpacity'] ?? 0.3); ?>">
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="showPageCorners" <?php echo ($config['showPageCorners'] ?? true) ? 'checked' : ''; ?>> Coins de page visibles</label>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="usePortrait" <?php echo ($config['usePortrait'] ?? true) ? 'checked' : ''; ?>> Mode portrait</label>
            </div>
            <div class="form-group">
                <label for="minWidth">Largeur minimale (px)</label>
                <input type="number" id="minWidth" name="minWidth" min="0" value="<?php echo htmlspecialchars($config['minWidth'] ?? 300); ?>" required>
            </div>
            <div class="form-group">
                <label for="maxWidth">Largeur maximale (px)</label>
                <input type="number" id="maxWidth" name="maxWidth" min="0" value="<?php echo htmlspecialchars($config['maxWidth'] ?? 1000); ?>" required>
            </div>
            <div class="form-group">
                <label for="minHeight">Hauteur minimale (px)</label>
                <input type="number" id="minHeight" name="minHeight" min="0" value="<?php echo htmlspecialchars($config['minHeight'] ?? 300); ?>" required>
            </div>
            <div class="form-group">
                <label for="maxHeight">Hauteur maximale (px)</label>
                <input type="number" id="maxHeight" name="maxHeight" min="0" value="<?php echo htmlspecialchars($config['maxHeight'] ?? 1000); ?>" required>
            </div>
            <button type="submit" class="submit-btn">Enregistrer les paramètres</button>
        </form>
    </div>
</body>
</html>
