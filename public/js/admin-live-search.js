/**
 * Darul Ifta Admin Console - Universal Live Search & Table Synchronization Engine
 * Handles debounced live-search, AbortController stale-request cancellation, 
 * URL history synchronization, dynamic DOM swapping, and accessible loading spinners.
 */
(function (window, document) {
    'use strict';

    class AdminLiveSearch {
        constructor(options = {}) {
            this.form = typeof options.form === 'string' ? document.querySelector(options.form) : options.form;
            this.input = typeof options.input === 'string' ? document.querySelector(options.input) : options.input;
            this.clearBtn = typeof options.clearBtn === 'string' ? document.querySelector(options.clearBtn) : options.clearBtn;
            this.spinner = typeof options.spinner === 'string' ? document.querySelector(options.spinner) : options.spinner;
            this.searchIcon = typeof options.searchIcon === 'string' ? document.querySelector(options.searchIcon) : options.searchIcon;
            this.container = typeof options.container === 'string' ? document.querySelector(options.container) : options.container;
            this.statusEl = typeof options.statusEl === 'string' ? document.querySelector(options.statusEl) : options.statusEl;
            this.statusTextEl = typeof options.statusTextEl === 'string' ? document.querySelector(options.statusTextEl) : options.statusTextEl;
            
            this.debounceMs = options.debounceMs || 350;
            this.onUpdated = typeof options.onUpdated === 'function' ? options.onUpdated : null;
            
            this.timer = null;
            this.abortController = null;
            this.isSearching = false;

            if (this.input) {
                this.init();
            }
        }

        init() {
            // 1. Debounced Input Keystroke Listener
            this.input.addEventListener('input', () => {
                this.toggleClearButton();
                this.updateSearchStatus(true);
                clearTimeout(this.timer);
                this.timer = setTimeout(() => {
                    this.executeSearch({ resetPage: true });
                }, this.debounceMs);
            });

            // 2. Immediate Enter Key Trigger
            this.input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(this.timer);
                    this.executeSearch({ resetPage: true });
                } else if (e.key === 'Escape') {
                    this.clearSearch();
                }
            });

            // 3. Clear Button Event
            if (this.clearBtn) {
                this.clearBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.clearSearch();
                });
            }

            // 4. Form Submit Interception
            if (this.form) {
                this.form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    clearTimeout(this.timer);
                    this.executeSearch({ resetPage: true });
                });
            }

            // 5. Browser Back & Forward Navigation (Popstate)
            window.addEventListener('popstate', () => {
                this.loadFromUrl(window.location.href);
            });

            // 6. Bind Pagination & Sort Links
            this.bindTableLinks();
            this.toggleClearButton();
        }

        toggleClearButton() {
            if (!this.clearBtn) return;
            if (this.input.value.trim().length > 0) {
                this.clearBtn.style.display = 'inline-flex';
            } else {
                this.clearBtn.style.display = 'none';
            }
        }

        clearSearch() {
            this.input.value = '';
            this.toggleClearButton();
            this.updateSearchStatus(false);
            this.input.focus();
            clearTimeout(this.timer);
            this.executeSearch({ resetPage: true });
        }

        updateSearchStatus(loading) {
            if (!this.statusEl) return;
            const val = this.input ? this.input.value.trim() : '';
            if (val.length > 0) {
                this.statusEl.style.display = 'flex';
                if (this.statusTextEl) {
                    this.statusTextEl.textContent = `Searching "${val}"... (auto search)`;
                }
            } else {
                this.statusEl.style.display = 'none';
            }
        }

        setLoading(loading) {
            this.isSearching = loading;
            this.updateSearchStatus(loading);
            if (this.spinner) {
                this.spinner.style.display = loading ? 'inline-block' : 'none';
            }
            if (this.searchIcon) {
                this.searchIcon.style.display = loading ? 'none' : 'inline-block';
            }
            if (this.container) {
                if (loading) {
                    this.container.style.opacity = '0.55';
                    this.container.style.pointerEvents = 'none';
                    this.container.style.transition = 'opacity 0.15s ease';
                } else {
                    this.container.style.opacity = '1';
                    this.container.style.pointerEvents = 'auto';
                }
            }
        }

        buildUrl(extraParams = {}) {
            let baseUrl = this.form ? this.form.getAttribute('action') || window.location.pathname : window.location.pathname;
            let currentUrl = new URL(window.location.href);
            let url = new URL(baseUrl, window.location.origin);

            // Copy existing query parameters
            for (let [key, val] of currentUrl.searchParams.entries()) {
                if (val !== '') {
                    url.searchParams.set(key, val);
                }
            }

            // Read form inputs
            if (this.form) {
                const formData = new FormData(this.form);
                for (let [key, val] of formData.entries()) {
                    if (val !== null && val !== '') {
                        url.searchParams.set(key, val);
                    } else {
                        url.searchParams.delete(key);
                    }
                }
            }

            // Apply search input
            const searchVal = this.input.value.trim();
            if (searchVal) {
                url.searchParams.set('search', searchVal);
            } else {
                url.searchParams.delete('search');
            }

            // Apply overrides
            for (let [key, val] of Object.entries(extraParams)) {
                if (val !== null && val !== '') {
                    url.searchParams.set(key, val);
                } else {
                    url.searchParams.delete(key);
                }
            }

            return url.toString();
        }

        async executeSearch(options = {}) {
            if (options.resetPage) {
                if (this.form && this.form.querySelector('input[name="page"]')) {
                    this.form.querySelector('input[name="page"]').value = '1';
                }
            }

            const targetUrl = options.url || this.buildUrl(options.resetPage ? { page: 1 } : {});

            // Abort previous in-flight request
            if (this.abortController) {
                this.abortController.abort();
            }
            this.abortController = new AbortController();

            this.setLoading(true);

            try {
                const response = await fetch(targetUrl, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    signal: this.abortController.signal
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const html = await response.text();
                this.updateDOM(html);

                // Synchronize browser URL bar without page refresh
                if (window.location.href !== targetUrl) {
                    window.history.pushState({ path: targetUrl }, '', targetUrl);
                }
            } catch (err) {
                if (err.name === 'AbortError') {
                    return; // Ignore aborted requests
                }
                console.error('[AdminLiveSearch] Search request failed:', err);
            } finally {
                this.setLoading(false);
            }
        }

        async loadFromUrl(url) {
            try {
                const urlObj = new URL(url);
                const searchVal = urlObj.searchParams.get('search') || '';
                if (this.input) {
                    this.input.value = searchVal;
                    this.toggleClearButton();
                }

                // Sync form inputs
                if (this.form) {
                    for (let [key, val] of urlObj.searchParams.entries()) {
                        const input = this.form.querySelector(`[name="${key}"]`);
                        if (input) input.value = val;
                    }
                }

                await this.executeSearch({ url });
            } catch (err) {
                console.error('[AdminLiveSearch] Popstate sync error:', err);
            }
        }

        updateDOM(html) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            if (this.container) {
                const containerId = this.container.id;
                let newContent = containerId ? doc.getElementById(containerId) : null;

                if (!newContent && this.container.getAttribute('data-live-container')) {
                    newContent = doc.querySelector(`[data-live-container="${this.container.getAttribute('data-live-container')}"]`);
                }

                if (newContent) {
                    this.container.innerHTML = newContent.innerHTML;
                } else {
                    // Fallback to table replacement
                    const newTable = doc.querySelector('table');
                    const oldTable = this.container.querySelector('table');
                    if (newTable && oldTable) {
                        oldTable.innerHTML = newTable.innerHTML;
                    }
                }
            }

            // Sync Summary KPI numbers if present
            const summaryTiles = document.querySelectorAll('[data-summary-key]');
            summaryTiles.forEach(tile => {
                const key = tile.getAttribute('data-summary-key');
                const newTile = doc.querySelector(`[data-summary-key="${key}"]`);
                if (newTile) {
                    tile.innerHTML = newTile.innerHTML;
                }
            });

            this.bindTableLinks();

            if (typeof this.onUpdated === 'function') {
                this.onUpdated(doc);
            }
        }

        bindTableLinks() {
            if (!this.container) return;

            // Intercept Pagination Links
            const paginationLinks = this.container.querySelectorAll('.pagination a, [data-pagination-link]');
            paginationLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    const href = link.getAttribute('href');
                    if (href && href !== '#' && !href.startsWith('javascript:')) {
                        e.preventDefault();
                        this.executeSearch({ url: href });
                        // Smooth scroll back to table top
                        this.container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });
            });

            // Intercept Sortable TH clicks
            const sortableThs = this.container.querySelectorAll('.sortable-th[data-sort]');
            sortableThs.forEach(th => {
                th.addEventListener('click', (e) => {
                    e.preventDefault();
                    const sort = th.getAttribute('data-sort');
                    const direction = th.getAttribute('data-direction') || 'asc';
                    
                    if (this.form) {
                        const sortInput = this.form.querySelector('input[name="sort"]');
                        const dirInput = this.form.querySelector('input[name="direction"]');
                        if (sortInput) sortInput.value = sort;
                        if (dirInput) dirInput.value = direction;
                    }

                    this.executeSearch({ resetPage: false });
                });
            });
        }
    }

    // Expose to window
    window.AdminLiveSearch = AdminLiveSearch;

})(window, document);

