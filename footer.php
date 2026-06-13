    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-grid">
            <div class="footer-brand">
                <h3>Salamandre</h3>
                <p>Une revue numérique et imprimée canadienne consacrée à la poésie, aux arts visuels et aux essais littéraires.</p>
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <!-- Simple stylish social icons -->
                    <a href="#" class="icon-btn" style="width:32px; height:32px;"><svg viewBox="0 0 24 24"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/></svg></a>
                    <a href="#" class="icon-btn" style="width:32px; height:32px;"><svg viewBox="0 0 24 24"><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zM12 21a9.003 9.003 0 008.354-5.646 9.003 9.003 0 00-8.354-5.646A9.003 9.003 0 003.646 15.354 9.003 9.003 0 0012 21z"/></svg></a>
                </div>
            </div>
            
            <div>
                <h4 class="footer-links-title">Explorer</h4>
                <ul class="footer-links-list">
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="viewer.php?id=mag-1">Visionneuse</a></li>
                    <li><a href="blog.php">Blogue littéraire</a></li>
                    <li><a href="shop.php">Boutique en ligne</a></li>
                </ul>
            </div>

            <div>
                <h4 class="footer-links-title">Informations</h4>
                <ul class="footer-links-list">
                    <li><a href="media-kit.php">Kit Média 2026</a></li>
                    <li><a href="admin/login.php">Espace Administrateur</a></li>
                    <li><a href="#">Politique de confidentialité</a></li>
                    <li><a href="#">Conditions de vente</a></li>
                </ul>
            </div>

            <div>
                <h4 class="footer-links-title">Newsletter</h4>
                <p style="font-size: 0.9rem; margin-bottom: 1rem;">Inscrivez-vous pour recevoir nos actualités et alertes de parutions.</p>
                <form onsubmit="event.preventDefault(); alert('Merci pour votre inscription !'); this.reset();" style="display: flex; gap: 0.5rem;">
                    <input type="email" placeholder="Votre email" required style="flex: 1; padding: 0.6rem 1rem; border: 1px solid var(--border-color); background: var(--bg-primary); color: var(--text-primary); outline: none; font-family: var(--font-sans);">
                    <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.2rem; font-size: 0.8rem;">S'abonner</button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Salamandre Magazine. Tous droits réservés.</p>
            <p>Conçu avec passion pour la littérature & l'art.</p>
        </div>
    </footer>

    <!-- Global JS Engine -->
    <script src="assets/js/main.js"></script>
</body>
</html>
