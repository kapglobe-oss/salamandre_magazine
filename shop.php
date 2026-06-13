<?php
// Load Database
$db_file = __DIR__ . '/data/database.json';
$db = ['shop' => []];
if (file_exists($db_file)) {
    $db = json_decode(file_get_contents($db_file), true) ?: $db;
}

$products = $db['shop'] ?? [];
$is_checkout = isset($_GET['checkout']) && $_GET['checkout'] == 1;

include 'header.php';
?>

<?php if ($is_checkout): ?>
<!-- CHECKOUT VIEW -->
<div style="background: var(--bg-secondary); padding: 4rem 2rem 2rem;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h1 style="font-size: 3rem; font-family: var(--font-serif); font-weight: 400;">Finaliser ma commande</h1>
        <p style="color: var(--text-secondary);">Remplissez vos détails pour finaliser l'achat de vos objets d'art et de lecture.</p>
    </div>
</div>

<div class="section" style="max-width: 1000px; padding-top: 3rem;">
    <form id="checkout-form" onsubmit="processCheckout(event)" style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 4rem; align-items: start;">
        <!-- Left: Delivery Details & Payment Card -->
        <div style="display: flex; flex-direction: column; gap: 2.5rem;">
            <!-- Address details -->
            <div style="background: var(--bg-secondary); padding: 2rem; border-radius: 4px; border: 1px solid var(--border-color);">
                <h3 style="font-family: var(--font-serif); font-size: 1.3rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">1. Informations de Livraison</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.4rem; display: block;">Prénom</label>
                        <input type="text" required style="width: 100%; padding: 0.6rem 1rem; border: 1px solid var(--border-color); background: var(--bg-primary); color: var(--text-primary); outline: none;">
                    </div>
                    <div>
                        <label style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.4rem; display: block;">Nom</label>
                        <input type="text" required style="width: 100%; padding: 0.6rem 1rem; border: 1px solid var(--border-color); background: var(--bg-primary); color: var(--text-primary); outline: none;">
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.4rem; display: block;">Adresse</label>
                    <input type="text" required style="width: 100%; padding: 0.6rem 1rem; border: 1px solid var(--border-color); background: var(--bg-primary); color: var(--text-primary); outline: none;">
                </div>
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                    <div>
                        <label style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.4rem; display: block;">Ville</label>
                        <input type="text" required style="width: 100%; padding: 0.6rem 1rem; border: 1px solid var(--border-color); background: var(--bg-primary); color: var(--text-primary); outline: none;">
                    </div>
                    <div>
                        <label style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.4rem; display: block;">Code Postal</label>
                        <input type="text" required style="width: 100%; padding: 0.6rem 1rem; border: 1px solid var(--border-color); background: var(--bg-primary); color: var(--text-primary); outline: none;">
                    </div>
                </div>
            </div>

            <!-- Credit Card Form -->
            <div style="background: var(--bg-secondary); padding: 2rem; border-radius: 4px; border: 1px solid var(--border-color);">
                <h3 style="font-family: var(--font-serif); font-size: 1.3rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">2. Paiement Sécurisé</h3>
                
                <!-- Animated Credit Card Preview -->
                <div style="perspective: 1000px; margin-bottom: 2rem; display: flex; justify-content: center;">
                    <div id="payment-card" style="width: 320px; height: 190px; position: relative; transform-style: preserve-3d; transition: transform 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275); transform: rotateY(0deg);">
                        <!-- Front of the card -->
                        <div style="position: absolute; width: 100%; height: 100%; backface-visibility: hidden; background: linear-gradient(135deg, #1f1c2c, #928dab); color: #fff; padding: 1.5rem; border-radius: 15px; box-shadow: var(--shadow-md); display: flex; flex-direction: column; justify-content: space-between; box-sizing: border-box;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-family: var(--font-display); font-size: 1rem; letter-spacing: 0.05em; font-weight: 600;">SALAMANDRE</span>
                                <svg viewBox="0 0 24 24" style="width: 35px; height: 35px; fill: rgba(255,255,255,0.7);"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.75z"/></svg>
                            </div>
                            <div id="card-preview-number" style="font-family: var(--font-display); font-size: 1.2rem; letter-spacing: 0.1em; text-align: center; margin: 1rem 0;">•••• •••• •••• ••••</div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                                <div>
                                    <div style="opacity: 0.6; text-transform: uppercase;">Titulaire</div>
                                    <div id="card-preview-name" style="font-weight: 600; text-transform: uppercase; font-size: 0.85rem;">PRENOM NOM</div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="opacity: 0.6; text-transform: uppercase;">Expire</div>
                                    <div id="card-preview-expiry" style="font-weight: 600; font-size: 0.85rem;">MM/AA</div>
                                </div>
                            </div>
                        </div>

                        <!-- Back of the card -->
                        <div style="position: absolute; width: 100%; height: 100%; backface-visibility: hidden; transform: rotateY(180deg); background: linear-gradient(135deg, #928dab, #1f1c2c); color: #fff; border-radius: 15px; box-shadow: var(--shadow-md); padding: 1.5rem 0; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between;">
                            <div style="height: 40px; background: #000; width: 100%;"></div>
                            <div style="padding: 0 1.5rem; display: flex; justify-content: flex-end; align-items: center;">
                                <div style="background: #fff; color: #000; font-family: monospace; font-size: 0.9rem; padding: 0.3rem 0.6rem; width: 50px; text-align: right; letter-spacing: 0.1em;" id="card-preview-cvv">•••</div>
                                <span style="font-size: 0.6rem; margin-left: 0.5rem; opacity: 0.8; text-transform: uppercase;">CVV</span>
                            </div>
                            <div style="font-size: 0.6rem; padding: 0 1.5rem; opacity: 0.6; text-align: center;">Cette transaction est un test sécurisé dans le cadre de la maquette.</div>
                        </div>
                    </div>
                </div>

                <!-- Input fields -->
                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.4rem; display: block;">Numéro de Carte</label>
                    <input type="text" id="card-number" required placeholder="4000 1234 5678 9010" maxlength="19" style="width: 100%; padding: 0.6rem 1rem; border: 1px solid var(--border-color); background: var(--bg-primary); color: var(--text-primary); outline: none;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.4rem; display: block;">Nom sur la carte</label>
                    <input type="text" id="card-name" required placeholder="Jean Tremblay" style="width: 100%; padding: 0.6rem 1rem; border: 1px solid var(--border-color); background: var(--bg-primary); color: var(--text-primary); outline: none;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.4rem; display: block;">Expiration</label>
                        <input type="text" id="card-expiry" required placeholder="MM/AA" maxlength="5" style="width: 100%; padding: 0.6rem 1rem; border: 1px solid var(--border-color); background: var(--bg-primary); color: var(--text-primary); outline: none;">
                    </div>
                    <div>
                        <label style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.4rem; display: block;">CVV</label>
                        <input type="text" id="card-cvv" required placeholder="123" maxlength="4" style="width: 100%; padding: 0.6rem 1rem; border: 1px solid var(--border-color); background: var(--bg-primary); color: var(--text-primary); outline: none;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Checkout Summary -->
        <div style="background: var(--bg-secondary); padding: 2rem; border-radius: 4px; border: 1px solid var(--border-color); position: sticky; top: 100px;">
            <h3 style="font-family: var(--font-serif); font-size: 1.3rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Récapitulatif</h3>
            
            <div id="checkout-summary-items" style="margin-bottom: 1.5rem;">
                <!-- Filled dynamically by JS -->
            </div>
            
            <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 1.25rem; border-top: 1px solid var(--border-color); padding-top: 1rem; margin-bottom: 2rem;">
                <span>Total :</span>
                <span id="checkout-total-value">0.00 $</span>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem;">Confirmer et Payer</button>
        </div>
    </form>

    <!-- Success Modal Screen -->
    <div id="checkout-success" style="display: none; text-align: center; padding: 4rem 2rem; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 4px; animation: var-fade-in 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
        <div style="width: 80px; height: 80px; background: rgba(197,168,128,0.1); border-radius: 50%; color: var(--accent-gold); display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;">
            <svg viewBox="0 0 24 24" style="width: 45px; height: 45px; fill: none; stroke: currentColor; stroke-width: 2;"><path d="M22 11.08V12a10 10 0 11-5.93-9.14M22 4L12 14.01l-3-3"/></svg>
        </div>
        <h2 style="font-family: var(--font-serif); font-size: 2.5rem; font-weight: 400; margin-bottom: 1rem;">Commande validée avec succès !</h2>
        <p style="color: var(--text-secondary); max-width: 500px; margin: 0 auto 2.5rem;">Merci pour votre commande. Un email de confirmation contenant votre facture vient de vous être envoyé. Votre numéro de commande est : <strong>#SLM-<?php echo rand(10000, 99999); ?></strong></p>
        
        <div id="digital-downloads" style="display:none; max-width: 500px; margin: 0 auto 2.5rem; background: var(--bg-primary); padding: 1.5rem; border: 1px dashed var(--accent-gold); text-align: left;">
            <h4 style="font-family: var(--font-serif); color: var(--accent-gold); margin-bottom: 0.5rem;">Vos produits numériques :</h4>
            <ul style="padding-left: 1.2rem;">
                <li>Numéro 12 (Version PDF) : <a href="uploads/magazines/numero_12.pdf" download style="text-decoration:underline; font-weight:600; color: var(--accent-gold);">Télécharger le PDF</a> ou <a href="viewer.php?id=mag-1" style="text-decoration:underline; font-weight:600; color: var(--accent-gold);">Visionner en ligne</a></li>
            </ul>
        </div>

        <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Populate Checkout summary from localStorage cart
    const cart = JSON.parse(localStorage.getItem('salamandre_cart')) || [];
    const container = document.getElementById('checkout-summary-items');
    const totalEl = document.getElementById('checkout-total-value');
    
    if (cart.length === 0) {
        alert("Votre panier est vide. Retour à la boutique.");
        window.location.href = 'shop.php';
        return;
    }

    let subtotal = 0;
    container.innerHTML = cart.map(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        return `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div style="max-width: 70%;">
                    <div style="font-family: var(--font-serif); font-size: 0.95rem; font-weight: 600;">${item.title}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Quantité : ${item.quantity}</div>
                </div>
                <div style="font-weight: 600;">${itemTotal.toFixed(2)} $</div>
            </div>
        `;
    }).join('');
    totalEl.textContent = `${subtotal.toFixed(2)} $`;

    // Credit Card interactions
    const card = document.getElementById('payment-card');
    const numberInput = document.getElementById('card-number');
    const nameInput = document.getElementById('card-name');
    const expiryInput = document.getElementById('card-expiry');
    const cvvInput = document.getElementById('card-cvv');

    const previewNumber = document.getElementById('card-preview-number');
    const previewName = document.getElementById('card-preview-name');
    const previewExpiry = document.getElementById('card-preview-expiry');
    const previewCvv = document.getElementById('card-preview-cvv');

    // Focus flip logic
    cvvInput.addEventListener('focus', () => card.style.transform = 'rotateY(180deg)');
    cvvInput.addEventListener('blur', () => card.style.transform = 'rotateY(0deg)');

    // Sync input to previews
    numberInput.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, '');
        // Format to groups of 4
        let formatted = [];
        for (let i = 0; i < value.length; i += 4) {
            formatted.push(value.substr(i, 4));
        }
        e.target.value = formatted.join(' ');
        previewNumber.textContent = e.target.value || '•••• •••• •••• ••••';
    });

    nameInput.addEventListener('input', (e) => {
        previewName.textContent = e.target.value.toUpperCase() || 'PRENOM NOM';
    });

    expiryInput.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 2) {
            value = value.substr(0,2) + '/' + value.substr(2,2);
        }
        e.target.value = value;
        previewExpiry.textContent = e.target.value || 'MM/AA';
    });

    cvvInput.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value;
        previewCvv.textContent = e.target.value || '•••';
    });
});

function processCheckout(event) {
    event.preventDefault();
    const form = document.getElementById('checkout-form');
    const successScreen = document.getElementById('checkout-success');
    const cart = JSON.parse(localStorage.getItem('salamandre_cart')) || [];
    
    // Check if there are digital items
    const hasDigital = cart.some(item => {
        // If digital is true (from products)
        return item.title.toLowerCase().includes('pdf') || item.title.toLowerCase().includes('numérique') || item.id === 'prod-4';
    });

    if (hasDigital) {
        document.getElementById('digital-downloads').style.display = 'block';
    }

    // Loader style mock transition
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Traitement en cours...';

    setTimeout(() => {
        form.style.display = 'none';
        successScreen.style.display = 'block';
        
        // Trigger confetti
        if (window.spawnConfetti) {
            window.spawnConfetti();
        }

        // Reset cart
        localStorage.removeItem('salamandre_cart');
        
        // Sync badge
        const badge = document.querySelector('.cart-count');
        if (badge) badge.style.display = 'none';
    }, 2000);
}
</script>

<?php else: ?>
<!-- PRODUCT DIRECTORY VIEW -->
<div style="background: var(--bg-secondary); padding: 5rem 2rem 3rem;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <span style="font-family: var(--font-display); font-size: 0.9rem; color: var(--accent-gold); letter-spacing: 0.15em; text-transform: uppercase;">Commandez nos numéros</span>
        <h1 style="font-size: 3.5rem; font-family: var(--font-serif); font-weight: 400; margin-top: 0.5rem; margin-bottom: 2rem;">La Boutique</h1>
    </div>
</div>

<div class="section" style="padding-top: 3rem;">
    <!-- Category filter buttons -->
    <div style="display: flex; gap: 0.8rem; justify-content: center; flex-wrap: wrap; margin-bottom: 4rem;">
        <button class="shop-filter-btn active" data-cat="all" style="background: var(--text-primary); color: var(--bg-primary); border: 1px solid var(--text-primary); padding: 0.5rem 1.5rem; border-radius: 30px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: var(--transition-fast);">Tous</button>
        <button class="shop-filter-btn" data-cat="abo" style="background: transparent; color: var(--text-secondary); border: 1px solid var(--border-color); padding: 0.5rem 1.5rem; border-radius: 30px; font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: var(--transition-fast);">Abonnements</button>
        <button class="shop-filter-btn" data-cat="num" style="background: transparent; color: var(--text-secondary); border: 1px solid var(--border-color); padding: 0.5rem 1.5rem; border-radius: 30px; font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: var(--transition-fast);">Numéros (PDF)</button>
        <button class="shop-filter-btn" data-cat="merch" style="background: transparent; color: var(--text-secondary); border: 1px solid var(--border-color); padding: 0.5rem 1.5rem; border-radius: 30px; font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: var(--transition-fast);">Papeterie & Goodies</button>
    </div>

    <!-- Shop grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 3rem;">
        <?php foreach ($products as $prod): ?>
            <div class="product-card" data-cat="<?php echo htmlspecialchars($prod['category']); ?>" style="display: flex; flex-direction: column; background: var(--bg-secondary); padding: 1.5rem; border-radius: 4px; border: 1px solid transparent; transition: var(--transition-smooth);" onmouseover="this.style.borderColor='var(--accent-gold)';" onmouseout="this.style.borderColor='transparent';">
                <div style="aspect-ratio: 3/4; overflow: hidden; background: #fff; margin-bottom: 1.5rem; box-shadow: var(--shadow-sm);">
                    <img src="<?php echo $prod['image']; ?>" alt="<?php echo $prod['title']; ?>" style="width: 100%; height: 100%; object-fit: cover; transition: var(--transition-smooth);" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='none';">
                </div>
                
                <h3 style="font-family: var(--font-serif); font-size: 1.2rem; margin-bottom: 0.5rem; height: 3.2rem; overflow: hidden; line-height: 1.35;"><?php echo $prod['title']; ?></h3>
                
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 2.4rem;">
                    <?php echo $prod['description']; ?>
                </p>

                <div style="color: var(--accent-gold); font-weight: 700; font-size: 1.3rem; margin-bottom: 1.5rem;"><?php echo number_format($prod['price'], 2); ?> $</div>
                
                <button onclick="addToCart(<?php echo htmlspecialchars(json_encode($prod)); ?>)" class="btn btn-primary" style="padding: 0.7rem; justify-content: center; font-size: 0.85rem; margin-top: auto; width:100%;">Ajouter au panier</button>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const filterButtons = document.querySelectorAll('.shop-filter-btn');
    const cards = document.querySelectorAll('.product-card');

    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            filterButtons.forEach(b => {
                b.classList.remove('active');
                b.style.background = 'transparent';
                b.style.color = 'var(--text-secondary)';
                b.style.borderColor = 'var(--border-color)';
            });

            btn.classList.add('active');
            btn.style.background = 'var(--text-primary)';
            btn.style.color = 'var(--bg-primary)';
            btn.style.borderColor = 'var(--text-primary)';

            const cat = btn.dataset.cat;
            cards.forEach(card => {
                if (cat === 'all' || card.dataset.cat === cat) {
                    card.style.display = 'flex';
                    card.animate([
                        { opacity: 0, transform: 'scale(0.95)' },
                        { opacity: 1, transform: 'scale(1)' }
                    ], { duration: 300, easing: 'ease-out' });
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});
</script>
<?php endif; ?>

<?php include 'footer.php'; ?>
