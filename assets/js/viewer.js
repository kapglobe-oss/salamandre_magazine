// Upgraded PDF & Custom Pages 'FlippingBook' Liseur Engine using St.PageFlip
document.addEventListener('DOMContentLoaded', () => {
    // PDF.js Global configuration
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

    // Parse URL params
    const urlParams = new URLSearchParams(window.location.search);
    let magazineId = urlParams.get('id') || '';
    const initialPage = parseInt(urlParams.get('page')) || 1;

    // State Variables
    let pdfDoc = null;
    let magazineData = null;
    let currentPage = initialPage;
    let totalPages = 0;
    let scale = 1.5; // Render resolution scale
    let readTrackingTimer = null;
    let trackedPagesInSession = new Set();
    let audioCtx = null; // Web Audio Context for synthesized sound
    let pageFlip = null;

    // DOM Elements
    const loaderEl = document.getElementById('viewer-loader');
    const bookContainer = document.getElementById('book-container');
    const magazinePicker = document.getElementById('magazine-picker');
    const magazineGrid = document.getElementById('magazine-grid');
    const pickerEmpty = document.getElementById('picker-empty');
    const changeMagBtn = document.getElementById('change-mag-btn');
    const viewerUI = document.getElementById('viewer-ui');
    const bookWrapper = document.getElementById('book-wrapper');
    const viewerControls = document.getElementById('viewer-controls');
    
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const firstBtn = document.getElementById('first-btn');
    const lastBtn = document.getElementById('last-btn');
    const zoomInBtn = document.getElementById('zoomin-btn');
    const zoomOutBtn = document.getElementById('zoomout-btn');
    const fullscreenBtn = document.getElementById('fullscreen-btn');
    const sidebarBtn = document.getElementById('sidebar-btn');
    
    const pageIndicator = document.getElementById('page-indicator');
    const sidebar = document.getElementById('viewer-sidebar');
    const thumbnailGrid = document.getElementById('thumbnail-grid');

    // =======================================
    // MAGAZINE PICKER LOGIC
    // =======================================
    if (!magazineId) {
        // No magazine ID — show the picker
        initMagazinePicker();
    } else {
        // Magazine ID provided — load it directly
        loadMagazine(magazineId);
    }

    function initMagazinePicker() {
        fetch('api.php?action=get_magazines')
            .then(res => res.json())
            .then(magazines => {
                magazineGrid.innerHTML = '';

                if (!magazines || magazines.length === 0) {
                    magazineGrid.classList.add('hidden');
                    pickerEmpty.classList.remove('hidden');
                    return;
                }

                // Sort by date (newest first)
                magazines.sort((a, b) => {
                    const dateA = a.pub_date || '1970-01-01';
                    const dateB = b.pub_date || '1970-01-01';
                    return dateB.localeCompare(dateA);
                });

                magazines.forEach((mag, index) => {
                    const card = document.createElement('div');
                    card.className = 'magazine-card';
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.animation = `pickerFadeIn 0.5s ${index * 0.08}s forwards ease`;

                    const pageCount = mag.pages ? mag.pages.length : '—';
                    const coverPath = mag.cover_path || 'uploads/images/magazine_cover_default.png';
                    const pubDate = mag.pub_date ? formatDate(mag.pub_date) : '';

                    card.innerHTML = `
                        <div class="magazine-card-cover">
                            <img src="${coverPath}" alt="${mag.title}" loading="lazy">
                            <div class="magazine-card-cta">Lire le magazine</div>
                        </div>
                        <div class="magazine-card-info">
                            <div class="magazine-card-title" title="${mag.title}">${mag.title}</div>
                            <div class="magazine-card-meta">
                                ${pubDate ? `<span>${pubDate}</span>` : ''}
                                ${pubDate && pageCount !== '—' ? '<span class="meta-dot"></span>' : ''}
                                ${pageCount !== '—' ? `<span class="magazine-card-pages">${pageCount} pages</span>` : ''}
                            </div>
                        </div>
                    `;

                    card.addEventListener('click', () => {
                        selectMagazine(mag.id);
                    });

                    magazineGrid.appendChild(card);
                });
            })
            .catch(err => {
                console.error('Erreur chargement magazines:', err);
                magazineGrid.innerHTML = '';
                magazineGrid.classList.add('hidden');
                pickerEmpty.classList.remove('hidden');
            });
    }

    function formatDate(dateStr) {
        try {
            const months = ['jan.', 'fév.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
            const parts = dateStr.split('-');
            const year = parts[0];
            const month = months[parseInt(parts[1]) - 1] || '';
            return `${month} ${year}`;
        } catch (e) {
            return dateStr;
        }
    }

    function selectMagazine(id) {
        magazineId = id;

        // Update URL without reload
        const newUrl = `${window.location.pathname}?id=${id}`;
        window.history.pushState({ path: newUrl }, '', newUrl);

        // Hide picker, show viewer
        magazinePicker.classList.add('hidden');
        loaderEl.classList.remove('hidden');
        viewerUI.classList.remove('hidden');
        bookWrapper.classList.remove('hidden');
        viewerControls.classList.remove('hidden');

        // Reset state
        pdfDoc = null;
        magazineData = null;
        currentPage = 1;
        totalPages = 0;
        pageFlip = null;
        trackedPagesInSession.clear();
        bookContainer.innerHTML = '';

        // Load the magazine
        loadMagazine(id);
    }

    // "Change magazine" button in viewer header
    if (changeMagBtn) {
        changeMagBtn.addEventListener('click', () => {
            // Destroy current pageFlip
            if (pageFlip) {
                try { pageFlip.destroy(); } catch(e) {}
                pageFlip = null;
            }
            bookContainer.innerHTML = '';

            // Hide viewer UI, show picker
            loaderEl.classList.add('hidden');
            viewerUI.classList.add('hidden');
            bookWrapper.classList.add('hidden');
            viewerControls.classList.add('hidden');
            sidebar.classList.remove('open');
            magazinePicker.classList.remove('hidden');

            // Update URL
            const newUrl = window.location.pathname;
            window.history.pushState({ path: newUrl }, '', newUrl);
            document.title = 'Salamandre Magazine - Liseur';

            // Refresh magazine list
            initMagazinePicker();
        });
    }

    // =======================================
    // MAGAZINE LOADING
    // =======================================
    function loadMagazine(id) {
        fetch(`api.php?action=get_magazine&id=${id}`)
            .then(res => {
                if (!res.ok) throw new Error("Magazine introuvable");
                return res.json();
            })
            .then(data => {
                magazineData = data;
                document.title = `${data.title} - Salamandre FlippingBook`;
                const titleEl = document.querySelector('.viewer-title');
                if (titleEl) titleEl.textContent = data.title;
                
                // Load PDF
                loadPDF(data.pdf_path);
            })
            .catch(err => {
                console.error(err);
                alert("Erreur de chargement du liseur.");
                // Go back to picker instead of index
                if (magazinePicker) {
                    loaderEl.classList.add('hidden');
                    viewerUI.classList.add('hidden');
                    bookWrapper.classList.add('hidden');
                    viewerControls.classList.add('hidden');
                    magazinePicker.classList.remove('hidden');
                    initMagazinePicker();
                } else {
                    window.location.href = 'index.php';
                }
            });
    }

    function loadPDF(url) {
        const loadingTask = pdfjsLib.getDocument(url);
        loadingTask.promise.then(pdf => {
            pdfDoc = pdf;
            
            // Get aspect ratio from first page
            pdf.getPage(1).then(firstPage => {
                const viewport = firstPage.getPageViewport ? firstPage.getPageViewport({ scale: scale }) : firstPage.getViewport({ scale: scale });
                window.pdfPageWidth = viewport.width;
                window.pdfPageHeight = viewport.height;

                if (!magazineData.pages || magazineData.pages.length === 0) {
                    initializePdfPagesOnBackend(pdf.numPages);
                } else {
                    totalPages = magazineData.pages.length;
                    initFlippingBook();
                }
            }).catch(err => {
                console.error("Erreur dimensions page liseur:", err);
                if (!magazineData.pages || magazineData.pages.length === 0) {
                    initializePdfPagesOnBackend(pdf.numPages);
                } else {
                    totalPages = magazineData.pages.length;
                    initFlippingBook();
                }
            });
        }).catch(error => {
            console.error("Erreur PDF.js: ", error);
            if (magazineData.pages && magazineData.pages.length > 0) {
                totalPages = magazineData.pages.length;
                initFlippingBook();
            } else {
                alert("Impossible de charger le document.");
            }
        });
    }

    function initializePdfPagesOnBackend(numPages) {
        fetch('api.php?action=initialize_pdf_pages', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ magazine_id: magazineId, num_pages: numPages })
        })
        .then(res => res.json())
        .then(resData => {
            if (resData.success) {
                fetch(`api.php?action=get_magazine&id=${magazineId}`)
                    .then(res => res.json())
                    .then(data => {
                        magazineData = data;
                        totalPages = data.pages.length;
                        initFlippingBook();
                    });
            }
        });
    }

    function initFlippingBook() {
        // Hide Loader
        if (loaderEl) {
            loaderEl.style.opacity = '0';
            setTimeout(() => loaderEl.style.display = 'none', 500);
        }

        // Render Sidebar Thumbnails
        renderThumbnails();

        // Setup DOM pages
        setupPageElements();

        // Initialize page-flip library
        initPageFlip();
    }

    function setupPageElements() {
        bookContainer.innerHTML = '';
        
        for (let i = 1; i <= totalPages; i++) {
            const pageDiv = document.createElement('div');
            pageDiv.className = 'page';
            pageDiv.dataset.density = 'soft';
            
            pageDiv.innerHTML = `
                <div class="page-content" style="width:100%; height:100%; position:relative; background:#fff; overflow:hidden;">
                    <canvas class="page-canvas" style="width:100%; height:100%; display:block;"></canvas>
                    <div class="overlay-layer" style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:4;"></div>
                </div>
            `;
            bookContainer.appendChild(pageDiv);
        }
    }

    function initPageFlip() {
        const pageAspect = window.pdfPageWidth && window.pdfPageHeight 
            ? window.pdfPageWidth / window.pdfPageHeight 
            : 300 / 400;

        const baseWidth = 600;
        const baseHeight = baseWidth / pageAspect;

        // Pre-render the first few pages BEFORE initializing PageFlip
        // so the cover and early pages have content when the flip animation runs
        const preRenderPromises = [];
        const pageElements = bookContainer.querySelectorAll('.page');
        const preRenderCount = Math.min(4, totalPages);
        for (let i = 1; i <= preRenderCount; i++) {
            const pageDiv = pageElements[i - 1];
            if (!pageDiv) continue;
            const canvas = pageDiv.querySelector('.page-canvas');
            const overlay = pageDiv.querySelector('.overlay-layer');
            const pageContainer = pageDiv.querySelector('.page-content');
            if (canvas && !canvas.dataset.rendered) {
                canvas.dataset.rendered = "true";
                const p = renderPageObject(i, canvas, overlay, pageContainer);
                if (p && typeof p.then === 'function') {
                    preRenderPromises.push(p);
                }
            }
        }

        // Load configuration and initialize PageFlip after pre-render completes
        Promise.all([
            fetch('assets/config/flipbook_config.json').then(r => r.ok ? r.json() : {}).catch(() => ({})),
            Promise.all(preRenderPromises)
        ]).then(([config]) => {
                // Merge config but force aspect-ratio-correct height values
                const mergedConfig = Object.assign({
                    showCover: true,
                    startPage: 0,
                    drawShadow: true,
                    maxShadowOpacity: 0.3,
                    showPageCorners: true,
                    usePortrait: true,
                    flippingTime: 800
                }, config);

                // Always compute height from aspect ratio (don't let config override with wrong values)
                const finalMinWidth = mergedConfig.minWidth || 300;
                const finalMaxWidth = mergedConfig.maxWidth || 1000;

                const options = {
                    width: baseWidth,
                    height: baseHeight,
                    size: "stretch",
                    minWidth: finalMinWidth,
                    minHeight: Math.round(finalMinWidth / pageAspect),
                    maxWidth: finalMaxWidth,
                    maxHeight: Math.round(finalMaxWidth / pageAspect),
                    showCover: mergedConfig.showCover,
                    startPage: mergedConfig.startPage,
                    drawShadow: mergedConfig.drawShadow,
                    maxShadowOpacity: mergedConfig.maxShadowOpacity,
                    showPageCorners: mergedConfig.showPageCorners,
                    usePortrait: mergedConfig.usePortrait,
                    flippingTime: mergedConfig.flippingTime,
                    mobileScrollSupport: false
                };
                pageFlip = new St.PageFlip(bookContainer, options);
                // Load pages — canvases already have rendered content
                pageFlip.loadFromHTML(bookContainer.querySelectorAll('.page'));

                if (currentPage > 1) {
                    pageFlip.turnToPage(currentPage - 1);
                }

                // Register event listeners AFTER pageFlip is created
                pageFlip.on('flip', (e) => {
                    currentPage = e.data + 1;
                    
                    // Clear cache for visible pages to force redraw
                    const orientation = pageFlip.getOrientation();
                    const activePages = [currentPage];
                    if (orientation === 'landscape' && currentPage > 1) {
                        if (currentPage % 2 === 0) {
                            activePages.push(currentPage + 1);
                        } else {
                            activePages.push(currentPage - 1);
                        }
                    }

                    const allPageElements = bookContainer.querySelectorAll('.page');
                    activePages.forEach(pageNum => {
                        const pageDiv = allPageElements[pageNum - 1];
                        if (pageDiv) {
                            const canvas = pageDiv.querySelector('.page-canvas');
                            if (canvas) canvas.removeAttribute('data-rendered');
                        }
                    });

                    playPageTurnSound();
                    renderVisiblePages();
                    updateUIControls();
                    triggerReadTracking();
                });

                pageFlip.on('onChangeOrientation', (e) => {
                    // Force redraw on orientation change
                    bookContainer.querySelectorAll('.page-canvas').forEach(c => c.removeAttribute('data-rendered'));
                    renderVisiblePages();
                    updateUIControls();
                });

                // Render remaining pages and update UI
                renderVisiblePages();
                updateUIControls();
            });
    }

    function renderVisiblePages() {
        if (!pageFlip) return;

        const range = 5; // Augmenter la plage de pré‑rendu pour que la page suivante soit visible
        const start = Math.max(1, currentPage - range);
        const end = Math.min(totalPages, currentPage + range);
        const pageElements = bookContainer.querySelectorAll('.page');

        for (let i = 1; i <= totalPages; i++) {
            const pageDiv = pageElements[i - 1];
            if (!pageDiv) continue;

            const canvas = pageDiv.querySelector('.page-canvas');
            const overlay = pageDiv.querySelector('.overlay-layer');
            const pageContainer = pageDiv.querySelector('.page-content');

            if (i >= start && i <= end) {
                if (!canvas.dataset.rendered) {
                    canvas.dataset.rendered = "true";
                    renderPageObject(i, canvas, overlay, pageContainer);
                }
            } else {
                if (canvas.dataset.rendered) {
                    canvas.removeAttribute('data-rendered');
                    clearPage(canvas, overlay, pageContainer);
                }
            }
        }
    }

    function renderPageObject(pageIndex, canvas, overlayContainer, pageContainer) {
        const pageObj = magazineData.pages[pageIndex - 1];
        if (!pageObj) return clearPage(canvas, overlayContainer, pageContainer);

        if (pageObj.type === 'pdf') {
            const pdfPageNum = pageObj.pdf_page_num;
            if (pdfDoc && pdfPageNum <= pdfDoc.numPages) {
                return pdfDoc.getPage(pdfPageNum).then(page => {
                    const viewport = page.getViewport({ scale: scale });
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    const ctx = canvas.getContext('2d');
                    
                    return page.render({ canvasContext: ctx, viewport: viewport }).promise.then(() => {
                        renderWidgets(pageObj.widgets, overlayContainer);
                    });
                });
            } else {
                return renderBlankPageCanvas(canvas, overlayContainer, `Page PDF ${pdfPageNum} (Indisponible)`, pageObj.widgets);
            }
        } else if (pageObj.type === 'custom') {
            return renderCustomPageHTML(pageObj, canvas, overlayContainer);
        }
        
        return Promise.resolve();
    }

    function renderBlankPageCanvas(canvas, overlayContainer, message, widgets = []) {
        if (window.pdfPageWidth && window.pdfPageHeight) {
            canvas.width = window.pdfPageWidth;
            canvas.height = window.pdfPageHeight;
        } else {
            canvas.width = 600;
            canvas.height = 800;
        }
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#faf8f5';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#999';
        ctx.font = 'italic 16px Inter';
        ctx.textAlign = 'center';
        ctx.fillText(message, canvas.width / 2, canvas.height / 2);
        
        renderWidgets(widgets, overlayContainer);
        return Promise.resolve();
    }

    function renderCustomPageHTML(pageObj, canvas, overlayContainer) {
        if (window.pdfPageWidth && window.pdfPageHeight) {
            canvas.width = window.pdfPageWidth;
            canvas.height = window.pdfPageHeight;
        } else {
            canvas.width = 600;
            canvas.height = 800;
        }
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = pageObj.background || '#faf8f5';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        if (pageObj.backgroundImage) {
            return new Promise((resolve) => {
                const img = new Image();
                img.src = pageObj.backgroundImage;
                img.onload = () => {
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    renderWidgets(pageObj.widgets || [], overlayContainer);
                    resolve();
                };
                img.onerror = () => {
                    renderWidgets(pageObj.widgets || [], overlayContainer);
                    resolve();
                };
            });
        } else {
            renderWidgets(pageObj.widgets || [], overlayContainer);
            return Promise.resolve();
        }
    }

    function clearPage(canvas, overlayContainer, pageContainer) {
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        overlayContainer.innerHTML = '';
        return Promise.resolve();
    }

    function renderWidgets(widgets, container) {
        container.innerHTML = '';
        if (!widgets) return;

        const canvas = container.parentElement.querySelector('canvas');
        const refWidth = canvas ? canvas.width : 600;
        
        let pageWidth = container.clientWidth;
        if (pageWidth === 0) {
            const bookWidth = bookContainer.clientWidth;
            const orientation = pageFlip ? pageFlip.getOrientation() : 'landscape';
            pageWidth = orientation === 'landscape' ? bookWidth / 2 : bookWidth;
        }
        const scaleFactor = refWidth > 0 ? pageWidth / refWidth : 1;

        const sortedWidgets = [...widgets].sort((a, b) => (a.zIndex || 1) - (b.zIndex || 1));

        sortedWidgets.forEach(widget => {
            const wrapper = document.createElement('div');
            wrapper.className = 'widget-wrapper';
            wrapper.style.left = `${widget.x}%`;
            wrapper.style.top = `${widget.y}%`;
            wrapper.style.width = `${widget.w}%`;
            wrapper.style.height = `${widget.h}%`;
            wrapper.style.zIndex = widget.zIndex || 1;
            
            if (widget.opacity !== undefined) {
                wrapper.style.opacity = parseFloat(widget.opacity) / 100;
            } else {
                wrapper.style.opacity = '1';
            }

            if (widget.type === 'video') {
                if (widget.subType === 'youtube') {
                    wrapper.innerHTML = `<iframe src="${widget.content}?autoplay=0&rel=0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
                } else if (widget.subType === 'mp4') {
                    wrapper.innerHTML = `<video src="${widget.content}" controls></video>`;
                }
            } else if (widget.type === 'ad') {
                wrapper.innerHTML = `
                    <a href="${widget.link}" target="_blank" class="widget-ad-link">
                        <img src="${widget.content}" class="widget-ad-img" alt="Publicité">
                    </a>
                `;
            } else if (widget.type === 'text') {
                wrapper.style.background = widget.bgColor || 'transparent';
                wrapper.style.boxShadow = 'none';
                wrapper.style.overflow = 'auto';
                wrapper.style.borderRadius = ((widget.borderRadius || 0) * scaleFactor) + 'px';
                
                const textContainer = document.createElement('div');
                textContainer.className = 'widget-text-container';
                textContainer.style.width = '100%';
                textContainer.style.height = '100%';
                textContainer.style.color = widget.fontColor || 'var(--text-primary)';
                textContainer.style.fontSize = ((widget.fontSize || 14) * scaleFactor) + 'px';
                textContainer.style.padding = ((widget.padding || 0) * scaleFactor) + 'px';
                textContainer.style.boxSizing = 'border-box';
                textContainer.innerHTML = widget.content;

                wrapper.appendChild(textContainer);
            } else if (widget.type === 'carousel') {
                wrapper.style.background = '#000';
                const images = widget.content.split(/[;,]/).map(url => url.trim()).filter(url => url);
                if (images.length === 0) {
                    wrapper.innerHTML = '<div style="color:#666; font-size:0.7rem; text-align:center; padding:1rem;">Carrousel vide</div>';
                } else {
                    let activeIdx = 0;
                    const renderSlide = () => {
                        wrapper.innerHTML = `
                            <img src="${images[activeIdx]}" style="width:100%; height:100%; object-fit:cover;">
                            ${images.length > 1 ? `
                                <button class="carousel-nav-btn prev" style="position:absolute; left:5px; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.6); color:#fff; border:none; border-radius:50%; width:24px; height:24px; cursor:pointer;">&lsaquo;</button>
                                <button class="carousel-nav-btn next" style="position:absolute; right:5px; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.6); color:#fff; border:none; border-radius:50%; width:24px; height:24px; cursor:pointer;">&rsaquo;</button>
                            ` : ''}
                        `;

                        const prevBtn = wrapper.querySelector('.carousel-nav-btn.prev');
                        const nextBtn = wrapper.querySelector('.carousel-nav-btn.next');

                        if (prevBtn) {
                            prevBtn.addEventListener('click', (e) => {
                                e.stopPropagation();
                                activeIdx = (activeIdx - 1 + images.length) % images.length;
                                renderSlide();
                            });
                        }
                        if (nextBtn) {
                            nextBtn.addEventListener('click', (e) => {
                                e.stopPropagation();
                                activeIdx = (activeIdx + 1) % images.length;
                                renderSlide();
                            });
                        }
                    };
                    renderSlide();
                }
            } else if (widget.type === 'audio') {
                wrapper.style.background = 'rgba(26,26,26,0.95)';
                wrapper.innerHTML = `
                    <div style="display:flex; align-items:center; justify-content:center; gap:0.5rem; width:100%; height:100%; padding:0.5rem;">
                        <audio src="${widget.content}" style="width:100%; height:30px;"></audio>
                    </div>
                `;
                const audioTag = wrapper.querySelector('audio');
                if (audioTag) {
                    audioTag.style.display = 'block';
                }
            } else if (widget.type === 'shape') {
                wrapper.style.boxShadow = 'none';
                wrapper.style.background = widget.bgColor || 'rgba(0,0,0,0.5)';
                if (widget.subType === 'circle') {
                    wrapper.style.borderRadius = '50%';
                } else {
                    wrapper.style.borderRadius = ((widget.borderRadius || 0) * scaleFactor) + 'px';
                }
                wrapper.innerHTML = '<div class="widget-shape"></div>';
            }

            container.appendChild(wrapper);
        });
    }

    function updateUIControls() {
        if (!pageFlip) return;

        const orientation = pageFlip.getOrientation();
        if (orientation === 'portrait') {
            pageIndicator.textContent = `Page ${currentPage} / ${totalPages}`;
        } else {
            if (currentPage === 1) {
                pageIndicator.textContent = `Page 1 / ${totalPages}`;
            } else {
                const rightPage = currentPage + 1 <= totalPages ? currentPage + 1 : null;
                pageIndicator.textContent = rightPage 
                    ? `Pages ${currentPage}-${rightPage} / ${totalPages}`
                    : `Page ${currentPage} / ${totalPages}`;
            }
        }

        const newUrl = `${window.location.pathname}?id=${magazineId}&page=${currentPage}`;
        window.history.replaceState({ path: newUrl }, '', newUrl);

        // Highlight thumbnail
        document.querySelectorAll('.thumbnail-item').forEach(item => {
            const pageNum = parseInt(item.dataset.page);
            if (pageNum === currentPage || (orientation === 'landscape' && pageNum === currentPage + 1)) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }

    function playPageTurnSound() {
        try {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }

            const sampleRate = audioCtx.sampleRate;
            const duration = 0.45;
            const bufferSize = sampleRate * duration;
            const buffer = audioCtx.createBuffer(1, bufferSize, sampleRate);
            const data = buffer.getChannelData(0);

            for (let i = 0; i < bufferSize; i++) {
                data[i] = Math.random() * 2 - 1;
            }

            const noiseSource = audioCtx.createBufferSource();
            noiseSource.buffer = buffer;

            const filter = audioCtx.createBiquadFilter();
            filter.type = 'lowpass';
            filter.frequency.setValueAtTime(1400, audioCtx.currentTime);
            filter.frequency.exponentialRampToValueAtTime(300, audioCtx.currentTime + duration);
            filter.Q.setValueAtTime(2.0, audioCtx.currentTime);

            const bandpass = audioCtx.createBiquadFilter();
            bandpass.type = 'bandpass';
            bandpass.frequency.setValueAtTime(2000, audioCtx.currentTime);
            bandpass.frequency.exponentialRampToValueAtTime(800, audioCtx.currentTime + duration);
            bandpass.Q.setValueAtTime(1.0, audioCtx.currentTime);

            const gainNode = audioCtx.createGain();
            gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
            gainNode.gain.linearRampToValueAtTime(0.08, audioCtx.currentTime + 0.05);
            gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);

            noiseSource.connect(filter);
            filter.connect(bandpass);
            bandpass.connect(gainNode);
            gainNode.connect(audioCtx.destination);

            noiseSource.start();
        } catch (e) {
            console.warn("Paper audio synthesis failed:", e);
        }
    }

    function goNext() {
        if (pageFlip) pageFlip.flipNext();
    }

    function goPrev() {
        if (pageFlip) pageFlip.flipPrev();
    }

    if (nextBtn) nextBtn.addEventListener('click', goNext);
    if (prevBtn) prevBtn.addEventListener('click', goPrev);
    if (firstBtn) firstBtn.addEventListener('click', () => pageFlip && pageFlip.turnToPage(0));
    if (lastBtn) lastBtn.addEventListener('click', () => pageFlip && pageFlip.turnToPage(totalPages - 1));

    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight' || e.key === ' ') {
            goNext();
        } else if (e.key === 'ArrowLeft') {
            goPrev();
        }
    });

    if (zoomInBtn) {
        zoomInBtn.addEventListener('click', () => {
            if (scale < 3) {
                scale += 0.25;
                document.querySelectorAll('.page-canvas').forEach(c => c.removeAttribute('data-rendered'));
                renderVisiblePages();
            }
        });
    }

    if (zoomOutBtn) {
        zoomOutBtn.addEventListener('click', () => {
            if (scale > 0.75) {
                scale -= 0.25;
                document.querySelectorAll('.page-canvas').forEach(c => c.removeAttribute('data-rendered'));
                renderVisiblePages();
            }
        });
    }

    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().then(() => {
                    fullscreenBtn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z"/></svg>';
                });
            } else {
                document.exitFullscreen().then(() => {
                    fullscreenBtn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/></svg>';
                });
            }
        });
    }

    if (sidebarBtn) {
        sidebarBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    function renderThumbnails() {
        if (!thumbnailGrid) return;
        thumbnailGrid.innerHTML = '';

        for (let i = 1; i <= totalPages; i++) {
            const item = document.createElement('div');
            item.className = 'thumbnail-item';
            item.dataset.page = i;
            
            const pageObj = magazineData.pages[i - 1];
            const label = pageObj.type === 'custom' ? `Page ${i} (Vivante)` : `Page ${i} (PDF)`;

            item.innerHTML = `
                <div style="background: ${pageObj.type === 'custom' ? (pageObj.background || '#faf8f5') : '#333'}; height: 100px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-family: 'Cinzel', serif; color: ${pageObj.type === 'custom' ? 'var(--text-primary)' : '#fff'};">
                    ${i}
                </div>
                <div class="thumbnail-label">${label}</div>
            `;

            item.addEventListener('click', () => {
                if (pageFlip) {
                    pageFlip.turnToPage(i - 1);
                }
                sidebar.classList.remove('open');
            });

            thumbnailGrid.appendChild(item);
        }
    }

    function triggerReadTracking() {
        if (readTrackingTimer) clearTimeout(readTrackingTimer);

        readTrackingTimer = setTimeout(() => {
            if (currentPage && !trackedPagesInSession.has(currentPage)) {
                fetch('api.php?action=track_page', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ magazine_id: magazineId, page: currentPage })
                })
                .then(res => res.json())
                .then(resData => {
                    if (resData.success) {
                        trackedPagesInSession.add(currentPage);
                    }
                })
                .catch(err => console.error("Erreur de tracking: ", err));
            }
        }, 3000);
    }
});
