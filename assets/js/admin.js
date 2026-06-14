// Upgraded Admin Dashboard & Visual Page Builder Logic
document.addEventListener('DOMContentLoaded', () => {
    // 1. Tab Switching System
    const navLinks = document.querySelectorAll('.admin-nav-link');
    const tabContents = document.querySelectorAll('.admin-tab-content');

    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            const targetTab = link.dataset.tab;
            
            // Close the visual builder if it's currently open
            if (typeof window.closeBuilder === 'function' && document.getElementById('builder-section') && document.getElementById('builder-section').style.display === 'block') {
                window.closeBuilder();
            }

            navLinks.forEach(l => l.classList.remove('active'));
            tabContents.forEach(c => {
                c.classList.remove('active');
                c.style.display = '';
            });

            link.classList.add('active');
            const targetEl = document.getElementById(`tab-${targetTab}`);
            if (targetEl) targetEl.classList.add('active');

            // Trigger loaders
            if (targetTab === 'stats') loadStats();
            if (targetTab === 'magazines') loadMagazines();
            if (targetTab === 'blog') loadBlog();
            if (targetTab === 'shop') loadShop();
            if (targetTab === 'settings') loadSettings();
            if (targetTab === 'ads') loadAds();
        });
    });

    // 2. Load Stats Tab
    function loadStats() {
        fetch('../api.php?action=get_stats')
            .then(res => res.json())
            .then(stats => {
                document.getElementById('stat-total-views').textContent = stats.total_views || 0;
                
                const pageReads = stats.page_reads || {};
                const totalPagesRead = Object.values(pageReads).reduce((a, b) => a + b, 0);
                document.getElementById('stat-pages-read').textContent = totalPagesRead;

                renderViewsByDayChart(stats.views_by_day || {});
                renderTopPagesChart(stats.page_reads || {});
            })
            .catch(err => console.error("Erreur de stats: ", err));
    }

    function renderViewsByDayChart(viewsByDay) {
        const container = document.getElementById('chart-views-day');
        if (!container) return;

        const keys = Object.keys(viewsByDay).sort().slice(-7);
        if (keys.length === 0) {
            container.innerHTML = '<div style="color:#666; text-align:center; padding: 2rem;">Aucune statistique disponible.</div>';
            return;
        }

        const values = keys.map(k => viewsByDay[k]);
        const maxVal = Math.max(...values, 10);
        
        let barWidth = 40;
        let gap = 20;
        let chartHeight = 150;
        let svgWidth = keys.length * (barWidth + gap) + gap;

        let bars = keys.map((key, i) => {
            const val = viewsByDay[key];
            const barHeight = (val / maxVal) * chartHeight;
            const x = i * (barWidth + gap) + gap;
            const y = chartHeight - barHeight;
            const parts = key.split('-');
            const label = parts.length === 3 ? `${parts[2]}/${parts[1]}` : key;

            return `
                <g>
                    <rect x="${x}" y="${y}" width="${barWidth}" height="${barHeight}" fill="var(--accent-gold)" rx="4">
                        <title>${val} vues</title>
                    </rect>
                    <text x="${x + barWidth/2}" y="${chartHeight + 20}" fill="#888" font-size="10" text-anchor="middle">${label}</text>
                    <text x="${x + barWidth/2}" y="${y - 8}" fill="#fff" font-size="10" font-weight="bold" text-anchor="middle">${val}</text>
                </g>
            `;
        }).join('');

        container.innerHTML = `
            <svg width="100%" height="190" viewBox="0 0 ${svgWidth} 190" preserveAspectRatio="xMidYMid meet" style="display:block; margin:0 auto;">
                ${bars}
            </svg>
        `;
    }

    function renderTopPagesChart(pageReads) {
        const container = document.getElementById('chart-top-pages');
        if (!container) return;

        const sorted = Object.entries(pageReads)
            .sort((a, b) => b[1] - a[1])
            .slice(0, 5);

        if (sorted.length === 0) {
            container.innerHTML = '<div style="color:#666; text-align:center; padding:2rem;">Aucune statistique disponible.</div>';
            return;
        }

        const maxVal = Math.max(...sorted.map(s => s[1]), 10);
        
        let bars = sorted.map((entry, i) => {
            const label = entry[0].replace('mag-1_', 'Page ');
            const val = entry[1];
            const widthPercent = (val / maxVal) * 80;
            const y = i * 30 + 10;

            return `
                <g>
                    <text x="10" y="${y + 15}" fill="#ccc" font-size="11" alignment-baseline="middle">${label}</text>
                    <rect x="80" y="${y}" width="${widthPercent}%" height="18" fill="var(--accent-red)" rx="3">
                        <title>${val} lectures</title>
                    </rect>
                    <text x="85" y="${y + 13}" fill="#fff" font-size="10" font-weight="bold">${val}</text>
                </g>
            `;
        }).join('');

        container.innerHTML = `
            <svg width="100%" height="${sorted.length * 30 + 20}" style="display:block;">
                ${bars}
            </svg>
        `;
    }

    // 3. Magazines Tab (CRUD)
    let magazinesList = [];
    
    function loadMagazines() {
        fetch('../api.php?action=get_magazines')
            .then(res => res.json())
            .then(data => {
                magazinesList = data;
                renderMagazinesTable();
            });
    }

    function renderMagazinesTable() {
        const tbody = document.getElementById('magazines-table-body');
        if (!tbody) return;

        if (magazinesList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#666;">Aucun magazine.</td></tr>';
            return;
        }

        tbody.innerHTML = magazinesList.map(mag => `
            <tr>
                <td style="width: 60px; text-align: center;">
                    <img src="../${mag.cover_path}" alt="Couverture" style="width: 50px; height: auto; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                </td>
                <td style="font-weight:600;">${mag.title}</td>
                <td>${mag.pub_date}</td>
                <td>${mag.pages ? mag.pages.length : 0} pages</td>
                <td>
                    <button class="admin-btn-action" onclick="openBuilder('${mag.id}')">Concepteur FlippingBook</button>
                    <button class="admin-btn-action" onclick="editMagazine('${mag.id}')">Éditer</button>
                    <button class="admin-btn-action admin-btn-danger" onclick="deleteMagazine('${mag.id}')">Supprimer</button>
                </td>
            </tr>
        `).join('');
    }

    window.openModal = function(id) {
        document.getElementById(id).classList.add('open');
    };
    window.closeModal = function(id) {
        document.getElementById(id).classList.remove('open');
    };

    // Client-side web path sanitization helper
    function sanitizeWebPath(path) {
        if (!path) return '';
        let clean = path.replace(/\\/g, '/');
        const index = clean.indexOf('uploads/');
        if (index !== -1) {
            return clean.substring(index);
        }
        return clean.replace(/^\/+/, '');
    }

    window.handleFileUpload = function(input, targetInputId, type) {
        const file = input.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);
        const statusEl = input.nextElementSibling;
        statusEl.textContent = "Téléversement...";

        fetch(`../api.php?action=upload&type=${type}`, {
            method: 'POST',
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error("Upload échoué");
            return res.json();
        })
        .then(data => {
            if (data.success) {
                const targetEl = document.getElementById(targetInputId);
                targetEl.value = data.path;
                targetEl.dispatchEvent(new Event('input')); // Trigger update event
                statusEl.textContent = "Téléversé avec succès !";
                statusEl.style.color = "#50E3C2";
            }
        })
        .catch(err => {
            statusEl.textContent = "Erreur de téléversement.";
            statusEl.style.color = "#e35461";
        });
    };

    // Setup file upload listener for widgets
    const widgetFileInput = document.getElementById('edit-w-file');
    if (widgetFileInput) {
        widgetFileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            const activeWidgetType = document.getElementById('edit-w-type').value;
            const activeWidgetSubtype = document.getElementById('edit-w-subtype').value;

            let uploadType = 'image';
            if (activeWidgetType === 'video' && activeWidgetSubtype === 'mp4') {
                uploadType = 'video';
            } else if (activeWidgetType === 'audio') {
                uploadType = 'audio';
            }

            const formData = new FormData();
            formData.append('file', file);
            const statusEl = document.getElementById('edit-w-file-status');
            statusEl.textContent = "Téléversement...";
            statusEl.style.color = "#888";

            fetch(`../api.php?action=upload&type=${uploadType}`, {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) throw new Error("Upload échoué");
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    if (activeWidgetType === 'carousel') {
                        const targetEl = document.getElementById('edit-w-content-textarea');
                        let currentVal = targetEl.value.trim();
                        if (currentVal) {
                            targetEl.value = currentVal + ', ' + data.path;
                        } else {
                            targetEl.value = data.path;
                        }
                        targetEl.dispatchEvent(new Event('input')); // Trigger update event
                    } else {
                        const targetEl = document.getElementById('edit-w-content');
                        targetEl.value = data.path;
                        targetEl.dispatchEvent(new Event('input')); // Trigger update event
                    }
                    statusEl.textContent = "Téléversé avec succès !";
                    statusEl.style.color = "#50E3C2";
                }
            })
            .catch(err => {
                statusEl.textContent = "Erreur de téléversement.";
                statusEl.style.color = "#e35461";
            });
        });
    }

    document.getElementById('magazine-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const id = document.getElementById('mag-id').value;
        const title = document.getElementById('mag-title').value;
        const pdfPath = sanitizeWebPath(document.getElementById('mag-pdf-path').value);
        const coverPath = sanitizeWebPath(document.getElementById('mag-cover-path').value);
        const pubDate = document.getElementById('mag-pub-date').value;

        const action = id ? 'update_magazine' : 'add_magazine';
        const payload = { id, title, pdf_path: pdfPath, cover_path: coverPath, pub_date: pubDate };

        fetch(`../api.php?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(() => {
            closeModal('modal-magazine');
            loadMagazines();
            document.getElementById('magazine-form').reset();
            document.getElementById('mag-id').value = '';
        });
    });

    window.editMagazine = function(id) {
        const mag = magazinesList.find(m => m.id === id);
        if (!mag) return;

        document.getElementById('mag-id').value = mag.id;
        document.getElementById('mag-title').value = mag.title;
        document.getElementById('mag-pdf-path').value = mag.pdf_path;
        document.getElementById('mag-cover-path').value = mag.cover_path;
        document.getElementById('mag-pub-date').value = mag.pub_date;

        openModal('modal-magazine');
    };

    window.deleteMagazine = function(id) {
        if (!confirm("Voulez-vous vraiment supprimer ce magazine ?")) return;

        fetch(`../api.php?action=delete_magazine&id=${id}`, { method: 'DELETE' })
            .then(res => res.json())
            .then(() => loadMagazines());
    };


    // 4. VISUAL WIDGET & VIRTUAL PAGE BUILDER ENGINE
    let builderPdfDoc = null;
    let builderActiveMag = null;
    let builderCurrentPage = 1; // 1-indexed (virtual index + 1)
    let builderActiveWidgetId = null;
    let builderActiveWidgetPageNum = null;
    let builderActivePageIndex = null; // For page properties editing
    let builderPages = []; // Local copy of magazine pages array
    let builderJustDragged = false;

    const workspaceLeft = document.getElementById('builder-page-left');
    const workspaceRight = document.getElementById('builder-page-right');
    const canvasLeft = document.getElementById('builder-canvas-left');
    const canvasRight = document.getElementById('builder-canvas-right');
    const overlayLeft = document.getElementById('builder-overlay-left');
    const overlayRight = document.getElementById('builder-overlay-right');

    window.openBuilder = function(magId) {
        builderActiveMag = magazinesList.find(m => m.id === magId);
        if (!builderActiveMag) return;

        builderPages = JSON.parse(JSON.stringify(builderActiveMag.pages || []));
        builderCurrentPage = 1;
        builderActiveWidgetId = null;
        builderActivePageIndex = null;
        builderPdfDoc = null;
        window.builderPageAspect = 300 / 400; // Reset to default
        window.builderPageWidth = 600;
        window.builderPageHeight = 800;

        // Hide widgets editor panel & show builder help
        document.getElementById('widget-editor-panel').style.display = 'none';
        document.getElementById('page-editor-panel').style.display = 'none';
        document.getElementById('builder-sidebar-help').style.display = 'block';

        // Show builder section, hide main content list
        document.getElementById('builder-section').style.display = 'block';
        document.getElementById('tab-magazines').style.display = 'none';
        document.getElementById('builder-mag-title').textContent = builderActiveMag.title;

        // Load PDF in builder
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
        pdfjsLib.getDocument(`../${builderActiveMag.pdf_path}`).promise.then(pdf => {
            builderPdfDoc = pdf;
            
            // Get aspect ratio from first page
            pdf.getPage(1).then(page => {
                const viewport = page.getViewport({ scale: 1.0 });
                window.builderPageAspect = viewport.width / viewport.height;
                window.builderPageWidth = viewport.width;
                window.builderPageHeight = viewport.height;

                // If pages is empty, initialize them dynamically
                if (builderPages.length === 0) {
                    builderPages = [];
                    for (let i = 1; i <= pdf.numPages; i++) {
                        builderPages.push({ type: 'pdf', pdf_page_num: i, widgets: [] });
                    }
                    // Auto-save the initialized pages to the server so the table shows the correct count immediately
                    fetch('../api.php?action=initialize_pdf_pages', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ magazine_id: builderActiveMag.id, num_pages: pdf.numPages })
                    });
                }
                
                renderBuilderPagesList();
                renderBuilderPages();
            }).catch(err => {
                console.error("Erreur de dimensions de page PDF:", err);
                renderBuilderPagesList();
                renderBuilderPages();
            });
        }).catch(err => {
            console.warn("Erreur d'initialisation du PDF, mode dégradé:", err);
            // PDF load error fallback: if we have virtual pages, render them anyway!
            if (builderPages.length === 0) {
                builderPages = [
                    { type: 'custom', background: '#faf8f5', widgets: [] }
                ];
            }
            renderBuilderPagesList();
            renderBuilderPages();
            alert("Erreur de chargement du PDF en local. Le concepteur est ouvert en mode conception de pages vivantes.");
        });
    };

    window.closeBuilder = function() {
        document.getElementById('builder-section').style.display = 'none';
        document.getElementById('tab-magazines').style.display = '';
        loadMagazines(); // Refresh list to sync page counts
    };

    // Render left pages list manager
    function renderBuilderPagesList() {
        const listContainer = document.getElementById('builder-pages-list');
        if (!listContainer) return;

        listContainer.innerHTML = builderPages.map((page, idx) => {
            const pageNum = idx + 1;
            const isActive = builderActivePageIndex === idx ? 'background:rgba(197,168,128,0.15); border-color:var(--accent-gold);' : '';
            const pageLabel = page.type === 'pdf' ? `Page ${pageNum} (PDF #${page.pdf_page_num})` : `Page ${pageNum} (Vivante)`;

            return `
                <div onclick="selectBuilderPage(${idx})" style="padding:0.6rem 0.8rem; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.08); border-radius:4px; display:flex; flex-direction:column; gap:0.4rem; cursor:pointer; transition:var(--transition-fast); ${isActive}">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.85rem; font-weight:600; color:${page.type === 'custom' ? 'var(--accent-gold)' : '#ccc'};">${pageLabel}</span>
                        <button onclick="event.stopPropagation(); deleteBuilderPage(${idx})" style="background:none; border:none; color:#e35461; cursor:pointer; font-size:0.75rem; font-weight:bold;">&times;</button>
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:0.4rem;">
                        <button onclick="event.stopPropagation(); moveBuilderPage(${idx}, -1)" class="admin-btn-action" style="padding:0.1rem 0.3rem; font-size:0.65rem;" ${idx === 0 ? 'disabled style="opacity:0.3;"' : ''}>&uarr;</button>
                        <button onclick="event.stopPropagation(); moveBuilderPage(${idx}, 1)" class="admin-btn-action" style="padding:0.1rem 0.3rem; font-size:0.65rem;" ${idx === builderPages.length - 1 ? 'disabled style="opacity:0.3;"' : ''}>&darr;</button>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Swapping virtual page indexes
    window.moveBuilderPage = function(index, direction) {
        const target = index + direction;
        if (target < 0 || target >= builderPages.length) return;

        // Swap
        const temp = builderPages[index];
        builderPages[index] = builderPages[target];
        builderPages[target] = temp;

        // Reset selections
        builderActiveWidgetId = null;
        builderActivePageIndex = null;

        renderBuilderPagesList();
        renderBuilderPages();
    };

    // Deleting page
    window.deleteBuilderPage = function(index) {
        if (!confirm(`Supprimer la page virtuelle ${index + 1} ? (Cette action ne modifie pas le fichier PDF lui-même).`)) return;

        builderPages.splice(index, 1);
        builderActiveWidgetId = null;
        builderActivePageIndex = null;

        // Adjust current double page pointer
        if (builderCurrentPage > builderPages.length) {
            builderCurrentPage = Math.max(1, builderPages.length);
        }

        renderBuilderPagesList();
        renderBuilderPages();
    };

    // Adding custom page
    window.addCustomBlankPage = function() {
        const newPage = {
            type: 'custom',
            background: '#faf8f5',
            widgets: []
        };
        builderPages.push(newPage);
        builderActivePageIndex = builderPages.length - 1;
        builderCurrentPage = builderPages.length; // Jump to pages end

        renderBuilderPagesList();
        renderBuilderPages();
        selectBuilderPage(builderActivePageIndex);
    };

    // Selection of a virtual page to edit background settings
    window.selectBuilderPage = function(index) {
        builderActivePageIndex = index;
        builderActiveWidgetId = null; // Unselect widget
        
        // Refresh pages list highlight
        renderBuilderPagesList();

        // Calculate double-page slot to jump the viewport
        const pageNum = index + 1;
        if (pageNum === 1) {
            builderCurrentPage = 1;
        } else {
            builderCurrentPage = pageNum % 2 === 0 ? pageNum : pageNum - 1;
        }
        renderBuilderPages();

        // Show page properties sidebar
        const pageObj = builderPages[index];
        document.getElementById('builder-sidebar-help').style.display = 'none';
        document.getElementById('widget-editor-panel').style.display = 'none';
        
        const pagePanel = document.getElementById('page-editor-panel');
        pagePanel.style.display = 'block';

        document.getElementById('edit-p-type').value = pageObj.type === 'pdf' ? 'Page PDF' : 'Page Vivante';
        const colorGroup = document.getElementById('page-bg-color-group');
        const bgInput = document.getElementById('edit-p-background');
        const bgImgGroup = document.getElementById('page-bg-image-group');
        const bgImgInput = document.getElementById('edit-p-bg-image');

        if (pageObj.type === 'custom') {
            colorGroup.style.display = 'block';
            bgImgGroup.style.display = 'block';
            
            bgInput.value = pageObj.background || '#faf8f5';
            bgImgInput.value = pageObj.backgroundImage || '';

            bgInput.oninput = (e) => {
                pageObj.background = e.target.value;
                renderBuilderPages();
            };

            bgImgInput.oninput = (e) => {
                pageObj.backgroundImage = sanitizeWebPath(e.target.value);
                renderBuilderPages();
            };
        } else {
            colorGroup.style.display = 'none';
            bgImgGroup.style.display = 'none';
        }
    };

    // Clear workspace page single canvas & overlays
    function clearBuilderPageSingle(canvas, overlayContainer, pageWrapper) {
        pageWrapper.style.display = 'none';
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        overlayContainer.innerHTML = '';
    }

    // Responsive aspect ratio adjustments
    function adjustBuilderBookDimensions() {
        const bookContainer = document.getElementById('builder-book-container');
        if (!bookContainer) return;

        let pageAspect = 300 / 400; // default (3:4)
        if (window.builderPageAspect) {
            pageAspect = window.builderPageAspect;
        }

        const leftPageNum = builderCurrentPage > 1 ? (builderCurrentPage % 2 === 0 ? builderCurrentPage : builderCurrentPage - 1) : null;
        const rightPageNum = leftPageNum ? leftPageNum + 1 : 1;
        const hasLeft = leftPageNum && leftPageNum <= builderPages.length;
        const hasRight = rightPageNum && rightPageNum <= builderPages.length;

        const totalAspect = (hasLeft && hasRight) ? pageAspect * 2 : pageAspect;

        const workspace = bookContainer.parentElement; // .builder-workspace
        const viewportWidth = workspace.clientWidth - 40;
        const viewportHeight = workspace.clientHeight - 40;

        let targetWidth = viewportWidth;
        let targetHeight = targetWidth / totalAspect;

        if (targetHeight > viewportHeight) {
            targetHeight = viewportHeight;
            targetWidth = targetHeight * totalAspect;
        }

        bookContainer.style.width = `${targetWidth}px`;
        bookContainer.style.height = `${targetHeight}px`;
    }

    // Render double-page book workspace
    function renderBuilderPages() {
        if (builderPages.length === 0) {
            clearBuilderPageSingle(canvasLeft, overlayLeft, workspaceLeft);
            clearBuilderPageSingle(canvasRight, overlayRight, workspaceRight);
            document.getElementById('builder-page-indicator').textContent = 'Aucune page';
            return;
        }

        adjustBuilderBookDimensions();

        const leftPageNum = builderCurrentPage > 1 ? (builderCurrentPage % 2 === 0 ? builderCurrentPage : builderCurrentPage - 1) : null;
        const rightPageNum = leftPageNum ? leftPageNum + 1 : 1;
        const hasLeft = leftPageNum && leftPageNum <= builderPages.length;
        const hasRight = rightPageNum && rightPageNum <= builderPages.length;

        document.getElementById('builder-page-indicator').textContent = leftPageNum 
            ? `Pages ${leftPageNum}-${rightPageNum} / ${builderPages.length}`
            : `Page ${rightPageNum} / ${builderPages.length}`;

        // Adjust widths dynamically for single-page presentation (e.g. cover page)
        if (!hasLeft || !hasRight) {
            workspaceLeft.style.width = '100%';
            workspaceRight.style.width = '100%';
        } else {
            workspaceLeft.style.width = '50%';
            workspaceRight.style.width = '50%';
        }

        // Render Left
        if (hasLeft) {
            renderBuilderPageSingle(leftPageNum, canvasLeft, overlayLeft, workspaceLeft);
        } else {
            clearBuilderPageSingle(canvasLeft, overlayLeft, workspaceLeft);
        }

        // Render Right
        if (hasRight) {
            renderBuilderPageSingle(rightPageNum, canvasRight, overlayRight, workspaceRight);
        } else {
            clearBuilderPageSingle(canvasRight, overlayRight, workspaceRight);
        }
    }

    function renderBuilderPageSingle(pageNum, canvas, overlayContainer, pageWrapper) {
        pageWrapper.style.display = 'block';
        const pageObj = builderPages[pageNum - 1];
        if (!pageObj) return;

        if (pageObj.type === 'pdf') {
            const pdfPageNum = pageObj.pdf_page_num;
            if (builderPdfDoc && pdfPageNum <= builderPdfDoc.numPages) {
                builderPdfDoc.getPage(pdfPageNum).then(page => {
                    const viewport = page.getViewport({ scale: 1.0 });
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    const ctx = canvas.getContext('2d');
                    
                    page.render({ canvasContext: ctx, viewport: viewport }).promise.then(() => {
                        renderBuilderWidgets(pageNum, overlayContainer);
                    });
                });
            } else {
                // Render fallback blank page
                renderBuilderPageBlankCanvas(canvas, overlayContainer, pageNum, `Page PDF ${pdfPageNum} (Indisponible)`);
            }
        } else if (pageObj.type === 'custom') {
            const bgColor = pageObj.background || '#faf8f5';
            if (window.builderPageWidth && window.builderPageHeight) {
                canvas.width = window.builderPageWidth;
                canvas.height = window.builderPageHeight;
            } else {
                canvas.width = 600;
                canvas.height = 800;
            }
            const ctx = canvas.getContext('2d');
            
            if (pageObj.backgroundImage) {
                const img = new Image();
                img.src = `../${pageObj.backgroundImage}`;
                img.onload = () => {
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    renderBuilderWidgets(pageNum, overlayContainer);
                };
                img.onerror = () => {
                    ctx.fillStyle = bgColor;
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.fillStyle = '#999';
                    ctx.font = 'italic 16px Inter';
                    ctx.textAlign = 'center';
                    ctx.fillText("Image de fond indisponible", canvas.width / 2, canvas.height / 2);
                    renderBuilderWidgets(pageNum, overlayContainer);
                };
            } else {
                ctx.fillStyle = bgColor;
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                renderBuilderWidgets(pageNum, overlayContainer);
            }
        }
    }

    function renderBuilderPageBlankCanvas(canvas, overlayContainer, pageNum, msg, bgColor = '#faf8f5') {
        if (window.builderPageWidth && window.builderPageHeight) {
            canvas.width = window.builderPageWidth;
            canvas.height = window.builderPageHeight;
        } else {
            canvas.width = 600;
            canvas.height = 800;
        }
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = bgColor;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        if (msg) {
            ctx.fillStyle = '#999';
            ctx.font = 'italic 16px Inter';
            ctx.textAlign = 'center';
            ctx.fillText(msg, canvas.width / 2, canvas.height / 2);
        }
        renderBuilderWidgets(pageNum, overlayContainer);
    }

    function renderBuilderWidgets(pageNum, container) {
        container.innerHTML = '';
        const pageObj = builderPages[pageNum - 1];
        if (!pageObj || !pageObj.widgets) return;

        // Calculate responsive scale factor relative to the rendered canvas resolution
        const canvas = container.parentElement.querySelector('canvas');
        const refWidth = canvas ? canvas.width : 600;
        const pageWidth = container.clientWidth || 300;
        const scaleFactor = refWidth > 0 ? pageWidth / refWidth : 0.5;

        // Sort widgets by zIndex so they stack correctly in the builder
        const sortedWidgets = [...pageObj.widgets].sort((a, b) => (a.zIndex || 1) - (b.zIndex || 1));
        
        sortedWidgets.forEach(widget => {
            const box = document.createElement('div');
            box.className = 'builder-widget';
            if (widget.id === builderActiveWidgetId) box.classList.add('active');
            
            box.style.left = `${widget.x}%`;
            box.style.top = `${widget.y}%`;
            box.style.width = `${widget.w}%`;
            box.style.height = `${widget.h}%`;
            box.style.zIndex = widget.zIndex || 1;
            
            // Set opacity
            if (widget.opacity !== undefined) {
                box.style.opacity = parseFloat(widget.opacity) / 100;
            } else {
                box.style.opacity = '1';
            }

            // Default border override for WYSIWYG feel unless active
            if (widget.id === builderActiveWidgetId) {
                box.style.border = '2px solid var(--accent-red)';
            } else {
                box.style.border = '1px dashed rgba(197, 168, 128, 0.4)';
            }

            if (widget.type === 'text') {
                box.style.background = widget.bgColor || 'transparent';
                box.style.color = widget.fontColor || '#000000';
                box.style.fontSize = ((widget.fontSize || 14) * scaleFactor) + 'px';
                box.style.padding = ((widget.padding || 0) * scaleFactor) + 'px';
                box.style.borderRadius = ((widget.borderRadius || 0) * scaleFactor) + 'px';
                box.style.display = 'block';
                box.style.overflow = 'hidden';
                box.style.textAlign = 'left';
                box.style.justifyContent = 'unset';
                box.style.alignItems = 'unset';
                
                box.innerHTML = `
                    <div style="width:100%; height:100%; overflow:hidden; pointer-events:none; font-family:var(--font-sans); line-height:1.4;">${widget.content || 'Texte...'}</div>
                    <button class="widget-delete-btn" onclick="event.stopPropagation(); deleteWidget('${pageNum}', '${widget.id}')">&times;</button>
                `;
            } else if (widget.type === 'shape') {
                box.style.background = widget.bgColor || 'rgba(0,0,0,0.5)';
                if (widget.subType === 'circle') {
                    box.style.borderRadius = '50%';
                } else {
                    box.style.borderRadius = ((widget.borderRadius || 0) * scaleFactor) + 'px';
                }
                box.style.display = 'flex';
                box.style.justifyContent = 'center';
                box.style.alignItems = 'center';
                
                box.innerHTML = `
                    <button class="widget-delete-btn" onclick="event.stopPropagation(); deleteWidget('${pageNum}', '${widget.id}')">&times;</button>
                `;
            } else if (widget.type === 'video') {
                box.style.background = '#111';
                box.style.borderRadius = '4px';
                box.innerHTML = `
                    <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; width:100%; height:100%; pointer-events:none; color:var(--accent-gold);">
                        <svg viewBox="0 0 24 24" style="width:24px; height:24px; fill:currentColor; margin-bottom:4px;"><path d="M8 5v14l11-7z"/></svg>
                        <span style="font-size:0.65rem;">Vidéo (${widget.subType})</span>
                    </div>
                    <button class="widget-delete-btn" onclick="event.stopPropagation(); deleteWidget('${pageNum}', '${widget.id}')">&times;</button>
                `;
            } else if (widget.type === 'ad') {
                const showImg = widget.content ? `<img src="../${widget.content}" style="width:100%; height:100%; object-fit:cover; pointer-events:none;">` : `<span style="font-size:0.65rem; color:var(--accent-gold);">Publicité</span>`;
                box.innerHTML = `
                    <div style="width:100%; height:100%; position:relative; overflow:hidden;">
                        ${showImg}
                        <span style="position:absolute; top:2px; right:2px; background:rgba(0,0,0,0.8); color:#fff; font-size:0.5rem; padding:1px 4px; border-radius:2px; pointer-events:none;">PUB</span>
                    </div>
                    <button class="widget-delete-btn" onclick="event.stopPropagation(); deleteWidget('${pageNum}', '${widget.id}')">&times;</button>
                `;
            } else if (widget.type === 'carousel') {
                const imgs = widget.content ? widget.content.split(/[;,]/).filter(u => u.trim()) : [];
                box.innerHTML = `
                    <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; width:100%; height:100%; pointer-events:none; color:var(--accent-gold);">
                        <svg viewBox="0 0 24 24" style="width:24px; height:24px; fill:currentColor; margin-bottom:4px;"><path d="M22 16V4c0-1.1-.9-2-2-2H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2zm-11-4l2.03 2.71L16 11l4 5H8l3-4zM2 6v14c0 1.1.9 2 2 2h14v-2H4V6H2z"/></svg>
                        <span style="font-size:0.65rem;">Carrousel (${imgs.length} img)</span>
                    </div>
                    <button class="widget-delete-btn" onclick="event.stopPropagation(); deleteWidget('${pageNum}', '${widget.id}')">&times;</button>
                `;
            } else if (widget.type === 'audio') {
                box.innerHTML = `
                    <div style="display:flex; align-items:center; justify-content:center; gap:4px; width:100%; height:100%; pointer-events:none; padding:4px; background:rgba(0,0,0,0.7); border-radius:4px; color:var(--accent-gold);">
                        <svg viewBox="0 0 24 24" style="width:16px; height:16px; fill:currentColor;"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
                        <span style="font-size:0.6rem; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Audio</span>
                    </div>
                    <button class="widget-delete-btn" onclick="event.stopPropagation(); deleteWidget('${pageNum}', '${widget.id}')">&times;</button>
                `;
            } else {
                box.innerHTML = `
                    <span>Widget</span>
                    <span style="font-size:0.6rem; font-weight:normal; opacity:0.8;">${widget.subType}</span>
                    <button class="widget-delete-btn" onclick="event.stopPropagation(); deleteWidget('${pageNum}', '${widget.id}')">&times;</button>
                `;
            }

            box.addEventListener('click', (e) => {
                e.stopPropagation();
                selectWidget(widget, pageNum);
            });

            // Set up Drag and Resize handlers
            setupWidgetDragAndResize(box, widget, pageNum);

            container.appendChild(box);
        });
    }

    // Interactive Drag and Resize logic for widgets (coordinates in %)
    function setupWidgetDragAndResize(box, widget, pageNum) {
        // Add visual resize handle at bottom right corner
        const handle = document.createElement('div');
        handle.className = 'widget-resize-handle';
        box.appendChild(handle);

        let isDragging = false;
        let isResizing = false;
        let startX, startY;
        let startLeft, startTop, startWidth, startHeight;

        box.addEventListener('mousedown', (e) => {
            if (e.target.classList.contains('widget-delete-btn')) return;
            e.preventDefault();
            e.stopPropagation();

            const pageWrapper = box.parentElement;
            if (!pageWrapper) return;

            // Always select widget when clicking on it
            selectWidget(widget, pageNum);

            startX = e.clientX;
            startY = e.clientY;

            const rect = pageWrapper.getBoundingClientRect();
            
            // Convert current percentages to pixels for precise dragging calculations
            startLeft = (widget.x / 100) * rect.width;
            startTop = (widget.y / 100) * rect.height;
            startWidth = (widget.w / 100) * rect.width;
            startHeight = (widget.h / 100) * rect.height;

            if (e.target.classList.contains('widget-resize-handle')) {
                isResizing = true;
            } else {
                isDragging = true;
            }

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });

        function onMouseMove(e) {
            if (!isDragging && !isResizing) return;
            
            const pageWrapper = box.parentElement;
            if (!pageWrapper) return;
            
            const rect = pageWrapper.getBoundingClientRect();
            const deltaX = e.clientX - startX;
            const deltaY = e.clientY - startY;

            // Mark that we dragged or resized
            builderJustDragged = true;

            if (isDragging) {
                let newLeft = startLeft + deltaX;
                let newTop = startTop + deltaY;

                // Restrict movement within page container
                newLeft = Math.max(0, Math.min(rect.width - startWidth, newLeft));
                newTop = Math.max(0, Math.min(rect.height - startHeight, newTop));

                // Save back as integer percentage
                widget.x = Math.round((newLeft / rect.width) * 100);
                widget.y = Math.round((newTop / rect.height) * 100);

                box.style.left = `${widget.x}%`;
                box.style.top = `${widget.y}%`;

                // Update input values if sidebar editor is currently showing this widget
                if (builderActiveWidgetId === widget.id) {
                    document.getElementById('edit-w-x').value = widget.x;
                    document.getElementById('edit-w-y').value = widget.y;
                }
            }

            if (isResizing) {
                let newWidth = startWidth + deltaX;
                let newHeight = startHeight + deltaY;

                // Set minimum drag constraints (5% width/height minimum)
                const minWidth = 0.05 * rect.width;
                const minHeight = 0.05 * rect.height;
                newWidth = Math.max(minWidth, Math.min(rect.width - startLeft, newWidth));
                newHeight = Math.max(minHeight, Math.min(rect.height - startTop, newHeight));

                widget.w = Math.round((newWidth / rect.width) * 100);
                widget.h = Math.round((newHeight / rect.height) * 100);

                box.style.width = `${widget.w}%`;
                box.style.height = `${widget.h}%`;

                // Update input values if sidebar editor is currently showing this widget
                if (builderActiveWidgetId === widget.id) {
                    document.getElementById('edit-w-w').value = widget.w;
                    document.getElementById('edit-w-h').value = widget.h;
                }
            }
        }

        function onMouseUp() {
            if (isDragging || isResizing) {
                isDragging = false;
                isResizing = false;
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
                
                // Redraw to align widgets visually
                renderBuilderPages();

                // Clear drag flag with delay to prevent handlePageClick trigger
                setTimeout(() => {
                    builderJustDragged = false;
                }, 100);
            }
        }
    }

    // Visual page click to add widgets
    window.handlePageClick = function(event, side) {
        // Prevent click if we just finished dragging/resizing
        if (builderJustDragged) {
            return;
        }

        // Prevent if clicking on or inside a widget
        if (event.target.closest('.builder-widget')) {
            return;
        }

        const rect = event.currentTarget.getBoundingClientRect();
        const x = ((event.clientX - rect.left) / rect.width) * 100;
        const y = ((event.clientY - rect.top) / rect.height) * 100;

        const leftPageNum = builderCurrentPage > 1 ? (builderCurrentPage % 2 === 0 ? builderCurrentPage : builderCurrentPage - 1) : null;
        const rightPageNum = leftPageNum ? leftPageNum + 1 : 1;
        const targetPageNum = side === 'left' ? leftPageNum : rightPageNum;

        if (!targetPageNum) return;
        const pageObj = builderPages[targetPageNum - 1];
        if (!pageObj) return;

        const newWidget = {
            id: 'w-' + Date.now(),
            type: 'video',
            subType: 'youtube',
            content: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            link: '',
            x: Math.round(x - 15),
            y: Math.round(y - 10),
            w: 30,
            h: 20
        };

        if (!pageObj.widgets) pageObj.widgets = [];
        pageObj.widgets.push(newWidget);

        builderActiveWidgetId = newWidget.id;
        renderBuilderPages();
        selectWidget(newWidget, targetPageNum);
    };

    function selectWidget(widget, pageNum) {
        builderActiveWidgetId = widget.id;
        builderActiveWidgetPageNum = pageNum; // Track active page for duplicate and z-index functions
        builderActivePageIndex = null; // Unselect active page in sidebar
        renderBuilderPagesList();

        document.querySelectorAll('.builder-widget').forEach(box => {
            box.classList.remove('active');
        });

        // Toggle Panels
        document.getElementById('builder-sidebar-help').style.display = 'none';
        document.getElementById('page-editor-panel').style.display = 'none';
        
        const widgetPanel = document.getElementById('widget-editor-panel');
        widgetPanel.style.display = 'block';

        // Set form fields
        document.getElementById('edit-w-type').value = widget.type;
        toggleSubtypeOptions(widget.type);
        document.getElementById('edit-w-subtype').value = widget.subType;
        
        document.getElementById('edit-w-content').value = widget.content;
        document.getElementById('edit-w-content-textarea').value = widget.content;
        document.getElementById('edit-w-link').value = widget.link || '';
        document.getElementById('edit-w-x').value = widget.x;
        document.getElementById('edit-w-y').value = widget.y;
        document.getElementById('edit-w-w').value = widget.w;
        document.getElementById('edit-w-h').value = widget.h;

        // Load advanced styling values
        document.getElementById('edit-w-font-size').value = widget.fontSize || 14;
        document.getElementById('edit-w-font-color').value = widget.fontColor || '#000000';
        document.getElementById('edit-w-bg-color').value = widget.bgColor || '#ffffff';
        document.getElementById('edit-w-opacity').value = widget.opacity !== undefined ? widget.opacity : 100;
        document.getElementById('edit-w-border-radius').value = widget.borderRadius || 0;
        document.getElementById('edit-w-padding').value = widget.padding || 0;

        // Toggle upload input container
        const toggleUploadContainer = (type, subType) => {
            const uploadContainer = document.getElementById('widget-file-upload-container');
            const fileInput = document.getElementById('edit-w-file');
            if (!uploadContainer || !fileInput) return;

            // Reset file input
            fileInput.value = '';
            document.getElementById('edit-w-file-status').textContent = "Téléverser un fichier média";
            document.getElementById('edit-w-file-status').style.color = "#888";

            if (type === 'ad' && subType === 'image_ad') {
                uploadContainer.style.display = 'block';
                fileInput.setAttribute('accept', 'image/*');
            } else if (type === 'video' && subType === 'mp4') {
                uploadContainer.style.display = 'block';
                fileInput.setAttribute('accept', 'video/*');
            } else if (type === 'audio') {
                uploadContainer.style.display = 'block';
                fileInput.setAttribute('accept', 'audio/*');
            } else if (type === 'carousel') {
                uploadContainer.style.display = 'block';
                fileInput.setAttribute('accept', 'image/*');
            } else {
                uploadContainer.style.display = 'none';
            }
        };

        // Toggle textarea visibilities
        toggleContentInputVisibilities(widget.type);
        toggleUploadContainer(widget.type, widget.subType);

        const stylingGroup = document.getElementById('styling-group');
        const fontSizeGroup = document.getElementById('w-font-size-group');
        const fontColorGroup = document.getElementById('w-font-color-group');
        const paddingGroup = document.getElementById('w-padding-group');

        const toggleStylingVisibility = (type) => {
            if (type === 'text') {
                stylingGroup.style.display = 'block';
                fontSizeGroup.style.display = 'block';
                fontColorGroup.style.display = 'block';
                paddingGroup.style.display = 'block';
            } else if (type === 'shape') {
                stylingGroup.style.display = 'block';
                fontSizeGroup.style.display = 'none';
                fontColorGroup.style.display = 'none';
                paddingGroup.style.display = 'none';
            } else {
                stylingGroup.style.display = 'none';
            }
        };

        toggleStylingVisibility(widget.type);

        // Save handlers
        const updateWidgetFromInputs = () => {
            const oldType = widget.type;
            const newType = document.getElementById('edit-w-type').value;
            widget.type = newType;
            widget.subType = document.getElementById('edit-w-subtype').value;
            
            // Set defaults if changing type
            if (newType !== oldType) {
                if (newType === 'text') {
                    widget.content = widget.content || '<h2>Titre</h2><p>Paragraphe...</p>';
                    widget.fontSize = widget.fontSize || 14;
                    widget.fontColor = widget.fontColor || '#000000';
                    widget.bgColor = widget.bgColor || '#ffffff';
                    widget.opacity = widget.opacity !== undefined ? widget.opacity : 100;
                    widget.borderRadius = widget.borderRadius || 0;
                    widget.padding = widget.padding || 10;
                } else if (newType === 'shape') {
                    widget.content = '';
                    widget.bgColor = widget.bgColor || '#ffffff';
                    widget.opacity = widget.opacity !== undefined ? widget.opacity : 100;
                    widget.borderRadius = widget.borderRadius || 0;
                    widget.subType = 'rect';
                }
                
                // Update form fields to match new defaults
                document.getElementById('edit-w-content').value = widget.content;
                document.getElementById('edit-w-content-textarea').value = widget.content;
                document.getElementById('edit-w-font-size').value = widget.fontSize || 14;
                document.getElementById('edit-w-font-color').value = widget.fontColor || '#000000';
                document.getElementById('edit-w-bg-color').value = widget.bgColor || '#ffffff';
                document.getElementById('edit-w-opacity').value = widget.opacity !== undefined ? widget.opacity : 100;
                document.getElementById('edit-w-border-radius').value = widget.borderRadius || 0;
                document.getElementById('edit-w-padding').value = widget.padding || 0;
            }

            // Get content from textarea or text input and sanitize if it contains paths
            if (widget.type === 'text' || widget.type === 'carousel') {
                let contentVal = document.getElementById('edit-w-content-textarea').value;
                if (widget.type === 'carousel') {
                    contentVal = contentVal.split(/[;,]/)
                        .map(path => sanitizeWebPath(path.trim()))
                        .filter(path => path)
                        .join(', ');
                }
                widget.content = contentVal;
            } else {
                let contentVal = document.getElementById('edit-w-content').value;
                if (widget.type === 'ad' || (widget.type === 'video' && widget.subType === 'mp4') || widget.type === 'audio') {
                    contentVal = sanitizeWebPath(contentVal);
                }
                widget.content = contentVal;
            }

            widget.link = document.getElementById('edit-w-link').value;
            widget.x = parseInt(document.getElementById('edit-w-x').value);
            widget.y = parseInt(document.getElementById('edit-w-y').value);
            widget.w = parseInt(document.getElementById('edit-w-w').value);
            widget.h = parseInt(document.getElementById('edit-w-h').value);

            if (widget.type === 'text' || widget.type === 'shape') {
                widget.fontSize = parseInt(document.getElementById('edit-w-font-size').value) || 14;
                widget.fontColor = document.getElementById('edit-w-font-color').value;
                widget.bgColor = document.getElementById('edit-w-bg-color').value;
                widget.opacity = parseInt(document.getElementById('edit-w-opacity').value);
                if (isNaN(widget.opacity)) widget.opacity = 100;
                widget.borderRadius = parseInt(document.getElementById('edit-w-border-radius').value) || 0;
                widget.padding = parseInt(document.getElementById('edit-w-padding').value) || 0;
            }

            toggleUploadContainer(widget.type, widget.subType);
            renderBuilderPages();
        };

        const inputs = [
            'edit-w-type', 'edit-w-subtype', 'edit-w-content', 'edit-w-content-textarea', 
            'edit-w-link', 'edit-w-x', 'edit-w-y', 'edit-w-w', 'edit-w-h',
            'edit-w-font-size', 'edit-w-font-color', 'edit-w-bg-color', 
            'edit-w-opacity', 'edit-w-border-radius', 'edit-w-padding'
        ];
        inputs.forEach(id => {
            const el = document.getElementById(id);
            el.oninput = updateWidgetFromInputs;
        });

        document.getElementById('edit-w-type').onchange = (e) => {
            toggleSubtypeOptions(e.target.value);
            toggleContentInputVisibilities(e.target.value);
            toggleStylingVisibility(e.target.value);
            toggleUploadContainer(e.target.value, document.getElementById('edit-w-subtype').value);
            updateWidgetFromInputs();
        };
    }

    function toggleSubtypeOptions(type) {
        const sub = document.getElementById('edit-w-subtype');
        if (type === 'video') {
            sub.innerHTML = '<option value="youtube">YouTube Embed</option><option value="mp4">Vidéo MP4 Locale</option>';
        } else if (type === 'ad') {
            sub.innerHTML = '<option value="image_ad">Bannière Image</option><option value="html_ad">Code HTML Custom</option>';
        } else if (type === 'text') {
            sub.innerHTML = '<option value="richtext">Paragraphe Texte</option>';
        } else if (type === 'carousel') {
            sub.innerHTML = '<option value="slider">Photos Défilantes</option>';
        } else if (type === 'audio') {
            sub.innerHTML = '<option value="ambient">Lecteur Audio MP3</option>';
        } else if (type === 'shape') {
            sub.innerHTML = '<option value="rect">Rectangle (Masque)</option><option value="circle">Cercle (Masque)</option>';
        }
    }

    function toggleContentInputVisibilities(type) {
        const inputGrp = document.getElementById('content-input-group');
        const txtareaGrp = document.getElementById('content-textarea-group');
        const linkGrp = document.getElementById('link-group');
        
        const txtLabel = document.getElementById('content-textarea-label');
        const txtHelp = document.getElementById('content-textarea-help');

        if (type === 'text') {
            inputGrp.style.display = 'none';
            txtareaGrp.style.display = 'block';
            linkGrp.style.display = 'none';
            txtLabel.textContent = "Contenu HTML du texte";
            txtHelp.textContent = "Vous pouvez saisir des balises HTML (ex: <h2>Titre</h2><p>Paragraphe...</p>)";
        } else if (type === 'carousel') {
            inputGrp.style.display = 'none';
            txtareaGrp.style.display = 'block';
            linkGrp.style.display = 'none';
            txtLabel.textContent = "Images du diaporama";
            txtHelp.textContent = "Saisissez les chemins d'images séparés par des virgules (ex: uploads/images/image1.png, uploads/images/image2.png)";
        } else if (type === 'shape') {
            inputGrp.style.display = 'none';
            txtareaGrp.style.display = 'none';
            linkGrp.style.display = 'none';
        } else {
            inputGrp.style.display = 'block';
            txtareaGrp.style.display = 'none';
            
            if (type === 'ad') {
                linkGrp.style.display = 'block';
            } else {
                linkGrp.style.display = 'none';
            }
        }
    }

    window.deleteWidget = function(pageNum, widgetId) {
        const pageObj = builderPages[pageNum - 1];
        if (!pageObj || !pageObj.widgets) return;

        pageObj.widgets = pageObj.widgets.filter(w => w.id !== widgetId);
        
        if (builderActiveWidgetId === widgetId) {
            builderActiveWidgetId = null;
            document.getElementById('widget-editor-panel').style.display = 'none';
            document.getElementById('builder-sidebar-help').style.display = 'block';
        }
        renderBuilderPages();
    };

    window.builderNext = function() {
        if (builderCurrentPage === 1) {
            builderCurrentPage = 2;
        } else {
            builderCurrentPage = Math.min(builderPages.length, builderCurrentPage + 2);
        }
        renderBuilderPages();
    };

    window.builderPrev = function() {
        if (builderCurrentPage <= 3) {
            builderCurrentPage = 1;
        } else {
            builderCurrentPage = Math.max(1, builderCurrentPage - 2);
        }
        renderBuilderPages();
    };

    // Save pages array to API
    window.saveBuilderOverlays = function() {
        const saveBtn = document.getElementById('builder-save-btn');
        saveBtn.textContent = 'Sauvegarde...';
        saveBtn.disabled = true;

        fetch('../api.php?action=save_magazine_pages', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ magazine_id: builderActiveMag.id, pages: builderPages })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                builderActiveMag.pages = builderPages;
                saveBtn.textContent = 'Enregistré !';
                saveBtn.style.background = '#50E3C2';
                setTimeout(() => {
                    saveBtn.textContent = 'Sauvegarder';
                    saveBtn.style.background = '';
                    saveBtn.disabled = false;
                }, 1500);
            }
        })
        .catch(err => {
            alert("Erreur de sauvegarde.");
            saveBtn.disabled = false;
            saveBtn.textContent = 'Sauvegarder';
        });
    };

    // Bring widget to front (max z-index + 1)
    window.bringWidgetToFront = function() {
        if (!builderActiveWidgetId || !builderActiveWidgetPageNum) return;
        const pageObj = builderPages[builderActiveWidgetPageNum - 1];
        if (!pageObj || !pageObj.widgets) return;

        let maxZ = 1;
        pageObj.widgets.forEach(w => {
            if (w.id !== builderActiveWidgetId && w.zIndex) {
                maxZ = Math.max(maxZ, w.zIndex);
            }
        });

        const widget = pageObj.widgets.find(w => w.id === builderActiveWidgetId);
        if (widget) {
            widget.zIndex = maxZ + 1;
            renderBuilderPages();
        }
    };

    // Send widget to back (min z-index - 1)
    window.sendWidgetToBack = function() {
        if (!builderActiveWidgetId || !builderActiveWidgetPageNum) return;
        const pageObj = builderPages[builderActiveWidgetPageNum - 1];
        if (!pageObj || !pageObj.widgets) return;

        let minZ = 1;
        pageObj.widgets.forEach(w => {
            if (w.id !== builderActiveWidgetId && w.zIndex) {
                minZ = Math.min(minZ, w.zIndex);
            }
        });

        const widget = pageObj.widgets.find(w => w.id === builderActiveWidgetId);
        if (widget) {
            widget.zIndex = minZ - 1;
            renderBuilderPages();
        }
    };

    // Duplicate active widget
    window.duplicateActiveWidget = function() {
        if (!builderActiveWidgetId || !builderActiveWidgetPageNum) return;
        const pageObj = builderPages[builderActiveWidgetPageNum - 1];
        if (!pageObj || !pageObj.widgets) return;

        const original = pageObj.widgets.find(w => w.id === builderActiveWidgetId);
        if (!original) return;

        const clone = JSON.parse(JSON.stringify(original));
        clone.id = 'w-' + Date.now();
        // Offset coordinates by 5% but keep them within bounds [0, 100 - size]
        clone.x = Math.min(100 - clone.w, clone.x + 5);
        clone.y = Math.min(100 - clone.h, clone.y + 5);

        pageObj.widgets.push(clone);
        builderActiveWidgetId = clone.id;

        renderBuilderPages();
        selectWidget(clone, builderActiveWidgetPageNum);
    };


    // 5. BLOG TAB (CRUD)
    let blogList = [];
    function loadBlog() {
        fetch('../api.php?action=get_blog')
            .then(res => res.json())
            .then(data => {
                blogList = data;
                renderBlogTable();
            });
    }

    function renderBlogTable() {
        const tbody = document.getElementById('blog-table-body');
        if (!tbody) return;

        if (blogList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#666;">Aucun article de blogue.</td></tr>';
            return;
        }

        tbody.innerHTML = blogList.map(post => `
            <tr>
                <td style="font-weight:600;">${post.title}</td>
                <td>${post.date}</td>
                <td><span style="padding:0.2rem 0.6rem; border-radius:4px; font-size:0.75rem; background:${post.status === 'published' ? 'rgba(80,227,194,0.1)' : 'rgba(255,255,255,0.05)'}; color:${post.status === 'published' ? '#50E3C2' : '#888'};">${post.status === 'published' ? 'Publié' : 'Brouillon'}</span></td>
                <td>
                    <button class="admin-btn-action" onclick="editPost('${post.id}')">Éditer</button>
                    <button class="admin-btn-action admin-btn-danger" onclick="deletePost('${post.id}')">Supprimer</button>
                </td>
            </tr>
        `).join('');
    }

    window.execEditorCommand = function(command, arg = '') {
        document.execCommand(command, false, arg);
    };

    document.getElementById('blog-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const id = document.getElementById('post-id').value;
        const title = document.getElementById('post-title').value;
        const excerpt = document.getElementById('post-excerpt').value;
        const content = document.getElementById('post-editor-area').innerHTML;
        const image = document.getElementById('post-image-path').value;
        const tags = document.getElementById('post-tags').value.split(',').map(t => t.trim()).filter(t => t);
        const status = document.getElementById('post-status').value;

        const action = id ? 'update_blog' : 'add_blog';
        const payload = { id, title, excerpt, content, image, tags, status };

        fetch(`../api.php?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(() => {
            closeModal('modal-blog');
            loadBlog();
            document.getElementById('blog-form').reset();
            document.getElementById('post-id').value = '';
            document.getElementById('post-editor-area').innerHTML = '';
        });
    });

    window.editPost = function(id) {
        const post = blogList.find(p => p.id === id);
        if (!post) return;

        document.getElementById('post-id').value = post.id;
        document.getElementById('post-title').value = post.title;
        document.getElementById('post-excerpt').value = post.excerpt;
        document.getElementById('post-editor-area').innerHTML = post.content;
        document.getElementById('post-image-path').value = post.image;
        document.getElementById('post-tags').value = (post.tags || []).join(', ');
        document.getElementById('post-status').value = post.status;

        openModal('modal-blog');
    };

    window.deletePost = function(id) {
        if (!confirm("Voulez-vous vraiment supprimer cet article ?")) return;

        fetch(`../api.php?action=delete_blog&id=${id}`, { method: 'DELETE' })
            .then(res => res.json())
            .then(() => loadBlog());
    };


    // 6. SHOP TAB (CRUD)
    let shopList = [];
    function loadShop() {
        fetch('../api.php?action=get_products')
            .then(res => res.json())
            .then(data => {
                shopList = data;
                renderShopTable();
            });
    }

    function renderShopTable() {
        const tbody = document.getElementById('shop-table-body');
        if (!tbody) return;

        if (shopList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#666;">Aucun produit dans la boutique.</td></tr>';
            return;
        }

        tbody.innerHTML = shopList.map(prod => `
            <tr>
                <td style="font-weight:600;">${prod.title}</td>
                <td>${prod.price.toFixed(2)} $</td>
                <td>${prod.category}</td>
                <td>
                    <button class="admin-btn-action" onclick="editProduct('${prod.id}')">Éditer</button>
                    <button class="admin-btn-action admin-btn-danger" onclick="deleteProduct('${prod.id}')">Supprimer</button>
                </td>
            </tr>
        `).join('');
    }

    document.getElementById('product-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const id = document.getElementById('prod-id').value;
        const title = document.getElementById('prod-title').value;
        const price = document.getElementById('prod-price').value;
        const description = document.getElementById('prod-desc').value;
        const image = document.getElementById('prod-image-path').value;
        const category = document.getElementById('prod-category').value;
        const digital = document.getElementById('prod-digital').checked;
        const downloadUrl = document.getElementById('prod-download-url').value;

        const action = id ? 'update_product' : 'add_product';
        const payload = { id, title, price, description, image, category, digital, download_url: downloadUrl };

        fetch(`../api.php?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(() => {
            closeModal('modal-shop');
            loadShop();
            document.getElementById('product-form').reset();
            document.getElementById('prod-id').value = '';
        });
    });

    window.editProduct = function(id) {
        const prod = shopList.find(p => p.id === id);
        if (!prod) return;

        document.getElementById('prod-id').value = prod.id;
        document.getElementById('prod-title').value = prod.title;
        document.getElementById('prod-price').value = prod.price;
        document.getElementById('prod-desc').value = prod.description;
        document.getElementById('prod-image-path').value = prod.image;
        document.getElementById('prod-category').value = prod.category;
        document.getElementById('prod-digital').checked = prod.digital || false;
        document.getElementById('prod-download-url').value = prod.download_url || '';

        openModal('modal-shop');
    };

    window.deleteProduct = function(id) {
        if (!confirm("Voulez-vous vraiment supprimer ce produit ?")) return;

        fetch(`../api.php?action=delete_product&id=${id}`, { method: 'DELETE' })
            .then(res => res.json())
            .then(() => loadShop());
    };


    // 7. SETTINGS PANEL (Password Update & Homepage Configuration)
    const settingsForm = document.getElementById('settings-form');
    if (settingsForm) {
        settingsForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const oldPass = document.getElementById('old-password').value;
            const newPass = document.getElementById('new-password').value;
            const statusBox = document.getElementById('password-status');

            statusBox.style.display = 'none';

            fetch('../api.php?action=update_password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ old_password: oldPass, new_password: newPass })
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw new Error(err.error) });
                }
                return res.json();
            })
            .then(data => {
                statusBox.textContent = data.message;
                statusBox.style.color = "#50E3C2";
                statusBox.style.display = "block";
                settingsForm.reset();
            })
            .catch(err => {
                statusBox.textContent = err.message;
                statusBox.style.color = "#e35461";
                statusBox.style.display = "block";
            });
        });
    }

    window.loadSettings = function() {
        fetch('../api.php?action=get_settings')
            .then(res => res.json())
            .then(data => {
                const settings = data.settings || { featured_mag_id: 'latest', custom_cover_path: '' };
                const magazines = data.magazines || [];
                
                // Populate dropdown
                const selectEl = document.getElementById('setting-featured-mag');
                if (selectEl) {
                    selectEl.innerHTML = '<option value="latest">Dernier numéro publié (Automatique)</option>';
                    magazines.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m.id;
                        opt.textContent = m.title;
                        selectEl.appendChild(opt);
                    });
                    selectEl.value = settings.featured_mag_id || 'latest';
                }

                // Populate custom cover path input
                const coverInput = document.getElementById('setting-custom-cover');
                if (coverInput) {
                    coverInput.value = settings.custom_cover_path || '';
                }

                // Clear upload messages
                const fileInput = document.querySelector('#homepage-settings-form input[type="file"]');
                if (fileInput) {
                    const statusEl = fileInput.nextElementSibling;
                    if (statusEl) {
                        statusEl.textContent = "Téléverser pour remplacer la couverture par défaut";
                        statusEl.style.color = "#888";
                    }
                    fileInput.value = '';
                }
            })
            .catch(err => console.error("Erreur de chargement des paramètres: ", err));
    };

    const homepageForm = document.getElementById('homepage-settings-form');
    if (homepageForm) {
        homepageForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const featuredMagId = document.getElementById('setting-featured-mag').value;
            const customCoverPath = sanitizeWebPath(document.getElementById('setting-custom-cover').value);
            const statusEl = document.getElementById('homepage-settings-status');

            statusEl.textContent = "Enregistrement...";
            statusEl.style.color = "#888";
            statusEl.style.display = "block";

            fetch('../api.php?action=update_settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    featured_mag_id: featuredMagId,
                    custom_cover_path: customCoverPath
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    statusEl.textContent = "Configuration enregistrée avec succès !";
                    statusEl.style.color = "#50E3C2";
                } else {
                    statusEl.textContent = "Erreur lors de l'enregistrement.";
                    statusEl.style.color = "#e35461";
                }
            })
            .catch(err => {
                console.error(err);
                statusEl.textContent = "Erreur lors de l'enregistrement.";
                statusEl.textContent = "Erreur lors de l'enregistrement.";
                statusEl.style.color = "#e35461";
            });
        });
    }

    // ==========================================
    // 8. AD CAMPAIGNS (RÉGIE PUB) MANAGEMENT
    // ==========================================
    let adsList = [];

    function loadAds() {
        fetch('../api.php?action=get_ads_admin')
            .then(res => res.json())
            .then(data => {
                adsList = data;
                renderAdsKPIs();
                renderAdsTable();
            })
            .catch(err => console.error("Erreur chargement campagnes pub:", err));
    }

    function renderAdsKPIs() {
        const activeCount = adsList.filter(ad => ad.status === 'active').length;
        const totalImpressions = adsList.reduce((sum, ad) => sum + (parseInt(ad.impressions) || 0), 0);
        const totalClicks = adsList.reduce((sum, ad) => sum + (parseInt(ad.clicks) || 0), 0);
        const totalRevenue = adsList.reduce((sum, ad) => sum + (parseFloat(ad.earnings) || 0), 0);

        document.getElementById('stat-ads-total').textContent = activeCount;
        document.getElementById('stat-ads-impressions').textContent = totalImpressions;
        document.getElementById('stat-ads-clicks').textContent = totalClicks;
        document.getElementById('stat-ads-revenue').textContent = `${totalRevenue.toFixed(2)} $`;
    }

    function renderAdsTable() {
        const tbody = document.getElementById('ads-table-body');
        if (!tbody) return;

        if (adsList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; color:#666;">Aucune campagne publicitaire.</td></tr>';
            return;
        }

        const locationLabels = {
            'header': 'En-tête (Header)',
            'homepage': 'Page d\'accueil',
            'sidebar': 'Sidebar/Blog',
            'footer': 'Bas de page (Footer)'
        };

        const modelLabels = {
            'flat': 'Forfait fixe',
            'cpc': 'Coût par clic (CPC)'
        };

        tbody.innerHTML = adsList.map(ad => {
            const impressions = parseInt(ad.impressions) || 0;
            const clicks = parseInt(ad.clicks) || 0;
            const ctr = impressions > 0 ? ((clicks / impressions) * 100).toFixed(2) : '0.00';
            const price = parseFloat(ad.price) || 0;
            const revenue = parseFloat(ad.earnings) || 0;
            
            const statusColor = ad.status === 'active' ? '#50E3C2' : '#888';
            const statusLabel = ad.status === 'active' ? 'Active' : 'En pause';

            return `
                <tr>
                    <td>
                        <div style="font-weight:600; color:#fff;">${ad.client_name}</div>
                        <div style="font-size:0.75rem; color:#888;">${ad.title}</div>
                    </td>
                    <td style="font-size:0.85rem;">${locationLabels[ad.location] || ad.location}</td>
                    <td style="font-size:0.85rem;">${modelLabels[ad.pricing_model] || ad.pricing_model}</td>
                    <td style="font-size:0.85rem; font-weight:600;">${price.toFixed(2)} $</td>
                    <td style="font-size:0.85rem;">
                        <div>${impressions} Imp. / ${clicks} Clics</div>
                        <div style="font-size:0.75rem; color:var(--accent-gold); font-weight:600;">CTR: ${ctr}%</div>
                    </td>
                    <td style="font-size:0.9rem; font-weight:700; color:#50E3C2;">${revenue.toFixed(2)} $</td>
                    <td>
                        <span style="font-size:0.75rem; padding:0.2rem 0.5rem; background:rgba(255,255,255,0.03); border:1px solid ${statusColor}; color:${statusColor}; border-radius:3px; font-weight:600; cursor:pointer;" onclick="toggleAdStatus('${ad.id}')">
                            ${statusLabel}
                        </span>
                    </td>
                    <td>
                        <button class="admin-btn-action" onclick="editAd('${ad.id}')">Éditer</button>
                        <button class="admin-btn-action admin-btn-danger" onclick="deleteAd('${ad.id}')">Supprimer</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    document.getElementById('ad-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const id = document.getElementById('ad-id').value;
        const client_name = document.getElementById('ad-client').value;
        const title = document.getElementById('ad-title').value;
        const banner_path = sanitizeWebPath(document.getElementById('ad-banner-path').value);
        const link_url = document.getElementById('ad-link-url').value;
        const location = document.getElementById('ad-location').value;
        const pricing_model = document.getElementById('ad-model').value;
        const price = document.getElementById('ad-price').value;
        const status = document.getElementById('ad-status').value;
        const start_date = document.getElementById('ad-start-date').value;
        const end_date = document.getElementById('ad-end-date').value;

        const action = id ? 'update_ad' : 'add_ad';
        const payload = { 
            id, client_name, title, banner_path, link_url, 
            location, pricing_model, price, status, start_date, end_date 
        };

        fetch(`../api.php?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(() => {
            closeModal('modal-ad');
            loadAds();
            document.getElementById('ad-form').reset();
            document.getElementById('ad-id').value = '';
        })
        .catch(err => console.error("Erreur enregistrement campagne:", err));
    });

    window.editAd = function(id) {
        const ad = adsList.find(a => a.id === id);
        if (!ad) return;

        document.getElementById('ad-id').value = ad.id;
        document.getElementById('ad-client').value = ad.client_name;
        document.getElementById('ad-title').value = ad.title;
        document.getElementById('ad-banner-path').value = ad.banner_path;
        document.getElementById('ad-link-url').value = ad.link_url;
        document.getElementById('ad-location').value = ad.location;
        document.getElementById('ad-model').value = ad.pricing_model;
        document.getElementById('ad-price').value = ad.price;
        document.getElementById('ad-status').value = ad.status;
        document.getElementById('ad-start-date').value = ad.start_date || '';
        document.getElementById('ad-end-date').value = ad.end_date || '';

        openModal('modal-ad');
    };

    window.deleteAd = function(id) {
        if (!confirm("Voulez-vous vraiment supprimer cette campagne ?")) return;

        fetch(`../api.php?action=delete_ad&id=${id}`, { method: 'DELETE' })
            .then(res => res.json())
            .then(() => loadAds())
            .catch(err => console.error("Erreur suppression:", err));
    };

    window.toggleAdStatus = function(id) {
        const ad = adsList.find(a => a.id === id);
        if (!ad) return;

        const newStatus = ad.status === 'active' ? 'paused' : 'active';
        fetch(`../api.php?action=update_ad`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: ad.id, status: newStatus })
        })
        .then(res => res.json())
        .then(() => loadAds())
        .catch(err => console.error("Erreur basculement statut:", err));
    };

    let resizeTimer;
    window.addEventListener('resize', () => {
        if (document.getElementById('builder-section').style.display === 'block') {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                adjustBuilderBookDimensions();
            }, 100);
        }
    });

    loadStats();
});
