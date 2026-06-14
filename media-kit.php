<?php
$extra_css = [];
include 'header.php';
?>

<!-- Media Kit Hero -->
<section class="media-kit-hero">
    <div style="max-width: 1000px; margin: 0 auto;">
        <span class="media-kit-subtitle">Opportunités Partenaires & Régie Pub</span>
        <h1 class="media-kit-title">Kit Média 2026</h1>
        <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto 2.5rem; font-size: 1.1rem; line-height: 1.6;">Salamandre Magazine est un carrefour culturel premium réunissant une communauté de passionnés d'arts visuels, de poésie et d'écrits contemplatifs.</p>
        <button onclick="alert('Téléchargement du document PDF (Simulé)');" class="btn btn-gold">Télécharger le Kit Média (PDF)</button>
    </div>
</section>

<!-- Stats and Demographic section -->
<section class="section" style="border-top: 1px solid var(--border-color);">
    <div class="section-header">
        <span class="section-subtitle">Notre Audience</span>
        <h2 class="section-title">Chiffres Clés & Lectorat</h2>
    </div>

    <div class="media-grid">
        <!-- Text details -->
        <div style="display:flex; flex-direction:column; gap: 2rem;">
            <h3 style="font-family: var(--font-serif); font-size: 1.8rem; font-weight: 400; line-height: 1.3;">Un public engagé et de grande qualité</h3>
            <p style="color: var(--text-secondary);">Nos lecteurs sont des artistes, des écrivains, des enseignants, des étudiants et des amateurs d'art exigeants. Ils apprécient l'esthétique minimaliste et passent en moyenne plus de 15 minutes sur notre liseur interactif par session.</p>
            
            <div class="media-stats-cards">
                <div class="stat-card">
                    <div class="stat-num">18K</div>
                    <div class="stat-label">Lecteurs / Mois</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num">8,5K</div>
                    <div class="stat-label">Abonnés Infolettre</div>
                </div>
            </div>
        </div>

        <!-- Animated SVG Graphs representing demographics -->
        <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); padding: 3rem; border-radius: 4px;">
            <h4 style="font-family: var(--font-serif); font-size: 1.25rem; margin-bottom: 2rem; text-align: center;">Répartition Démographique</h4>
            
            <!-- SVG Donut Chart (Age) -->
            <div style="display: flex; justify-content: center; align-items: center; gap: 2rem; margin-bottom: 2.5rem;">
                <svg width="120" height="120" viewBox="0 0 42 42" style="transform: rotate(-90deg);">
                    <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="var(--bg-primary)" stroke-width="4"></circle>
                    <!-- Segment 1: 25-34 years (40%) -->
                    <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="var(--accent-gold)" stroke-width="4" stroke-dasharray="40 60" stroke-dashoffset="0"></circle>
                    <!-- Segment 2: 35-54 years (45%) -->
                    <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="var(--accent-red)" stroke-width="4" stroke-dasharray="45 55" stroke-dashoffset="-40"></circle>
                    <!-- Segment 3: Autres (15%) -->
                    <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="var(--text-primary)" stroke-width="4" stroke-dasharray="15 85" stroke-dashoffset="-85"></circle>
                </svg>
                
                <div style="display:flex; flex-direction:column; gap:0.5rem; font-size:0.85rem;">
                    <div style="display:flex; align-items:center; gap:0.5rem;">
                        <span style="display:inline-block; width:12px; height:12px; background:var(--accent-gold);"></span>
                        <span>25-34 ans (40%)</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.5rem;">
                        <span style="display:inline-block; width:12px; height:12px; background:var(--accent-red);"></span>
                        <span>35-54 ans (45%)</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.5rem;">
                        <span style="display:inline-block; width:12px; height:12px; background:var(--text-primary);"></span>
                        <span>Autres (15%)</span>
                    </div>
                </div>
            </div>

            <!-- SVG Bar Chart (Geography) -->
            <div>
                <h5 style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 1rem;">Répartition Géographique</h5>
                <div style="display:flex; flex-direction:column; gap:0.8rem;">
                    <div>
                        <div style="display:flex; justify-content:space-between; font-size:0.85rem; margin-bottom:0.2rem;">
                            <span>Québec / Canada</span>
                            <strong>80%</strong>
                        </div>
                        <div style="height:6px; background:var(--bg-primary); border-radius:3px; overflow:hidden;">
                            <div style="width:80%; height:100%; background:var(--accent-gold); border-radius:3px;"></div>
                        </div>
                    </div>
                    <div>
                        <div style="display:flex; justify-content:space-between; font-size:0.85rem; margin-bottom:0.2rem;">
                            <span>Europe (France, Belgique)</span>
                            <strong>15%</strong>
                        </div>
                        <div style="height:6px; background:var(--bg-primary); border-radius:3px; overflow:hidden;">
                            <div style="width:15%; height:100%; background:var(--accent-red); border-radius:3px;"></div>
                        </div>
                    </div>
                    <div>
                        <div style="display:flex; justify-content:space-between; font-size:0.85rem; margin-bottom:0.2rem;">
                            <span>États-Unis / Reste du Monde</span>
                            <strong>5%</strong>
                        </div>
                        <div style="height:6px; background:var(--bg-primary); border-radius:3px; overflow:hidden;">
                            <div style="width:5%; height:100%; background:var(--text-primary); border-radius:3px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Ad Formats Section -->
<section class="section" style="background: var(--bg-secondary); max-width: 100%;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div class="section-header">
            <span class="section-subtitle">Formats Publicitaires</span>
            <h2 class="section-title">Nos Options d'Intégration</h2>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2.5rem; margin-top: 3rem;">
            <!-- Format 1 -->
            <div style="background: var(--bg-primary); padding: 2rem; border-radius: 4px; border: 1px solid var(--border-color);">
                <div style="background: var(--bg-secondary); border: 2px dashed var(--accent-gold); aspect-ratio: 1/1.414; margin-bottom: 1.5rem; display:flex; align-items:center; justify-content:center; flex-direction:column; font-family:var(--font-display); font-size: 0.9rem;">
                    <span>Plaquette Complète</span>
                    <span style="font-size:0.75rem; color: var(--text-muted); margin-top:0.3rem;">(1/1 Page Liseur PDF)</span>
                </div>
                <h3 style="font-family: var(--font-serif); font-size: 1.25rem; margin-bottom: 0.5rem;">Page Entière Liseur</h3>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">Intégration d'une page de publicité dans le PDF, assortie d'un calque de lien interactif pointant vers votre site.</p>
            </div>
            
            <!-- Format 2 -->
            <div style="background: var(--bg-primary); padding: 2rem; border-radius: 4px; border: 1px solid var(--border-color);">
                <div style="background: var(--bg-secondary); border: 2px dashed var(--accent-gold); aspect-ratio: 16/9; margin-bottom: 1.5rem; display:flex; align-items:center; justify-content:center; flex-direction:column; font-family:var(--font-display); font-size: 0.9rem;">
                    <span>Widget Vidéo</span>
                    <span style="font-size:0.75rem; color: var(--text-muted); margin-top:0.3rem;">(Embed Liseur PDF)</span>
                </div>
                <h3 style="font-family: var(--font-serif); font-size: 1.25rem; margin-bottom: 0.5rem;">Widget Vidéo Interactif</h3>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">Une publicité vidéo (YouTube/Vimeo) intégrée de manière responsive sur une page stratégique du magazine.</p>
            </div>

            <!-- Format 3 -->
            <div style="background: var(--bg-primary); padding: 2rem; border-radius: 4px; border: 1px solid var(--border-color);">
                <div style="background: var(--bg-secondary); border: 2px dashed var(--accent-gold); aspect-ratio: 3/1; margin-bottom: 1.5rem; display:flex; align-items:center; justify-content:center; flex-direction:column; font-family:var(--font-display); font-size: 0.9rem;">
                    <span>Bannière Web</span>
                    <span style="font-size:0.75rem; color: var(--text-muted); margin-top:0.3rem;">(Site web / Blogue)</span>
                </div>
                <h3 style="font-family: var(--font-serif); font-size: 1.25rem; margin-bottom: 0.5rem;">Bannière Editoriale Site</h3>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">Présence publicitaire dans nos grilles d'articles de blogue ou dans la barre latérale sous forme de bannière textuelle/image.</p>
            </div>
        </div>
    </div>
</section>

<!-- Rates Table Section -->
<section class="section">
    <div class="section-header">
        <span class="section-subtitle">Tarification</span>
        <h2 class="section-title">Grille Tarifaire 2026</h2>
    </div>

    <div style="max-width: 800px; margin: 0 auto; overflow-x: auto; background: var(--bg-secondary); padding: 2rem; border-radius:4px; border: 1px solid var(--border-color);">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-family: var(--font-sans); font-size: 0.95rem;">
            <thead>
                <tr style="border-bottom: 2px solid var(--accent-gold); color: var(--text-primary); font-weight: 700;">
                    <th style="padding: 1rem;">Format d'insertion</th>
                    <th style="padding: 1rem;">Emplacement</th>
                    <th style="padding: 1rem; text-align: right;">Tarif par numéro</th>
                </tr>
            </thead>
            <tbody style="color: var(--text-secondary);">
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 1.2rem; font-family: var(--font-serif); color: var(--text-primary); font-weight:600;">Double Page Centrale</td>
                    <td style="padding: 1.2rem;">Liseur PDF (reliure centrale)</td>
                    <td style="padding: 1.2rem; text-align: right; font-weight: 600; color: var(--accent-gold);">1 200 $</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 1.2rem; font-family: var(--font-serif); color: var(--text-primary); font-weight:600;">Page Entière Simple</td>
                    <td style="padding: 1.2rem;">Liseur PDF (Pages paires/impaires)</td>
                    <td style="padding: 1.2rem; text-align: right; font-weight: 600; color: var(--accent-gold);">750 $</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 1.2rem; font-family: var(--font-serif); color: var(--text-primary); font-weight:600;">Widget Vidéo Incrusté</td>
                    <td style="padding: 1.2rem;">Liseur PDF (Interactif)</td>
                    <td style="padding: 1.2rem; text-align: right; font-weight: 600; color: var(--accent-gold);">600 $</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 1.2rem; font-family: var(--font-serif); color: var(--text-primary); font-weight:600;">Bannière en Blogue</td>
                    <td style="padding: 1.2rem;">Site Web (mensuel)</td>
                    <td style="padding: 1.2rem; text-align: right; font-weight: 600; color: var(--accent-gold);">350 $</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Contact CTA -->
    <div style="margin-top: 5rem; text-align:center; background: radial-gradient(circle, var(--bg-secondary) 0%, var(--bg-primary) 100%); padding: 4rem 2rem; border-radius:4px; border: 1px solid var(--border-color);">
        <h3 style="font-family: var(--font-serif); font-size: 2rem; margin-bottom: 1rem;">Envie de collaborer ?</h3>
        <p style="color:var(--text-secondary); max-width:500px; margin: 0 auto 2rem;">Contactez notre directrice commerciale pour concevoir une campagne sur-mesure adaptée à vos objectifs.</p>
        <a href="mailto:pub@salamandremagazine.ca" class="btn btn-primary">Nous contacter</a>
    </div>
</section>

<!-- Livres de Soeur Angèle Section -->
<section class="section" style="border-top: 1px solid var(--border-color); padding-top: 4rem; padding-bottom: 4rem;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div class="section-header" style="text-align: center; margin-bottom: 4rem;">
            <span class="section-subtitle">Boutique & Publications</span>
            <h2 class="section-title" style="text-transform: uppercase;">RETROUVEZ LES LIVRES DE SŒUR ANGÈLE</h2>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 3rem; align-items: start;">
            <!-- Book 1 -->
            <div style="background: var(--bg-secondary); padding: 2.5rem; border-radius: 4px; border: 1px solid var(--border-color); text-align: center; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 35px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <div style="margin-bottom: 2rem; display: flex; justify-content: center;">
                    <img src="uploads/images/51-768x994.png" alt="Livre Soeur Angèle" style="max-width: 100%; height: auto; border-radius: 4px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);" />
                </div>
                <h3 style="font-family: var(--font-serif); font-size: 1.35rem; margin-bottom: 0.5rem; color: var(--text-primary);">Un héritage culinaire</h3>
                <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 2rem;">Découvrez ses recettes et son histoire inspirante à travers cet ouvrage incontournable.</p>
                <a href="#" class="btn btn-gold" style="width: 100%; display: block; text-decoration: none;">Découvrir le livre</a>
            </div>
            
            <!-- Book 2 -->
            <div style="background: var(--bg-secondary); padding: 2.5rem; border-radius: 4px; border: 1px solid var(--border-color); text-align: center; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 35px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <div style="margin-bottom: 2rem; display: flex; justify-content: center;">
                    <img src="uploads/images/Sortie-en-Aout-15-768x384.png" alt="Sortie en Août" style="max-width: 100%; height: auto; border-radius: 4px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);" />
                </div>
                <h3 style="font-family: var(--font-serif); font-size: 1.35rem; margin-bottom: 0.5rem; color: var(--text-primary);">Sortie en Août</h3>
                <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 2rem;">La toute nouvelle parution à ne pas manquer pour les passionnés de cuisine.</p>
                <a href="#" class="btn btn-gold" style="width: 100%; display: block; text-decoration: none;">Découvrir le livre</a>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
