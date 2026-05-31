/**
 * Book Lending Manager - JavaScript
 * Vanilla JS - no external dependencies
 */

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initThemeToggle();
    initBookSearch();
    initBorrowerModal();
    initFormValidation();
});

/**
 * Light/Dark theme toggle.
 * Initial theme is applied inline in <head> (before paint); here we wire the
 * button, update its icon, and persist the user's choice in localStorage.
 */
function initThemeToggle() {
    const btn = document.getElementById('theme-toggle');
    if (!btn) return;

    const root = document.documentElement;

    const syncIcon = () => {
        const dark = root.getAttribute('data-theme') === 'dark';
        btn.textContent = dark ? '☀️' : '🌙';
        btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
    };

    syncIcon();

    btn.addEventListener('click', function() {
        const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-theme', next);
        try { localStorage.setItem('theme', next); } catch (e) {}
        syncIcon();
    });
}

/**
 * Book Search using Open Library API and Google Books API
 */
function initBookSearch() {
    const searchInput = document.getElementById('book-search');
    const searchBtn = document.getElementById('search-btn');
    const resultsContainer = document.getElementById('search-results');

    if (!searchInput || !searchBtn) return;

    // The API selector (#api-select) is rendered server-side in the view.

    // Search on button click
    searchBtn.addEventListener('click', performSearch);
    
    // Search on Enter key
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });
    
    async function performSearch() {
        const query = searchInput.value.trim();
        const apiSelect = document.getElementById('api-select');
        const apiChoice = apiSelect ? apiSelect.value : 'openlibrary';

        if (!query) return;

        // Show loading state
        resultsContainer.innerHTML = '<p class="text-muted">Buscando...</p>';

        let results = [];
        let attempted = 0;
        let errors = 0;

        // Each source fails independently so one outage doesn't kill the other.
        if (apiChoice === 'both' || apiChoice === 'openlibrary') {
            attempted++;
            try {
                const openLibResults = await searchOpenLibrary(query);
                results = results.concat(openLibResults.map(b => ({...b, source: 'Open Library'})));
            } catch (error) {
                errors++;
                console.error('Open Library search error:', error);
            }
        }

        if (apiChoice === 'both' || apiChoice === 'google') {
            attempted++;
            try {
                const googleResults = await searchGoogleBooks(query);
                results = results.concat(googleResults.map(b => ({...b, source: 'Google Books'})));
            } catch (error) {
                errors++;
                console.error('Google Books search error:', error);
            }
        }

        if (results.length > 0) {
            displayResults(results);
        } else if (attempted > 0 && errors === attempted) {
            resultsContainer.innerHTML = '<p class="text-danger">Erro na busca. Tente novamente.</p>';
        } else {
            resultsContainer.innerHTML = '<p class="text-muted">Nenhum livro encontrado.</p>';
        }
    }
    
    async function searchOpenLibrary(query) {
        const response = await fetch(
            `https://openlibrary.org/search.json?q=${encodeURIComponent(query)}&limit=5`
        );
        
        if (!response.ok) throw new Error('Open Library search failed');
        
        const data = await response.json();
        return (data.docs || []).map(book => ({
            title: book.title || 'Título desconhecido',
            author: book.author_name ? book.author_name[0] : 'Autor desconhecido',
            publisher: book.publisher ? book.publisher[0] : '',
            isbn: book.isbn ? book.isbn[0] : '',
            cover: book.cover_i ? `https://covers.openlibrary.org/b/id/${book.cover_i}-M.jpg` : null
        }));
    }
    
    async function searchGoogleBooks(query) {
        // Check if Google Books is enabled and has API key
        if (!window.GOOGLE_BOOKS_CONFIG || !window.GOOGLE_BOOKS_CONFIG.enabled || !window.GOOGLE_BOOKS_CONFIG.apiKey) {
            console.warn('Google Books API is not configured or disabled');
            return [];
        }
        
        try {
            const apiKey = window.GOOGLE_BOOKS_CONFIG.apiKey;
            const response = await fetch(
                `https://www.googleapis.com/books/v1/volumes?q=${encodeURIComponent(query)}&maxResults=5&printType=books&key=${apiKey}`
            );
            
            if (!response.ok) {
                console.warn('Google Books API error:', response.status, response.statusText);
                return []; // Return empty array on error (don't throw)
            }
            
            const data = await response.json();
            
            if (!data.items || !Array.isArray(data.items)) {
                console.warn('Google Books API returned no items');
                return [];
            }
            
            return data.items.map(item => {
                const volume = item.volumeInfo || {};
                const identifiers = volume.industryIdentifiers || [];
                const isbn = identifiers.find(id => id.type === 'ISBN_13' || id.type === 'ISBN_10');
                
                return {
                    title: volume.title || 'Título desconhecido',
                    author: volume.authors ? volume.authors.join(', ') : 'Autor desconhecido',
                    publisher: volume.publisher || '',
                    isbn: isbn ? isbn.identifier : '',
                    cover: volume.imageLinks && volume.imageLinks.thumbnail
                        ? volume.imageLinks.thumbnail.replace(/^http:\/\//, 'https://')
                        : null
                };
            });
        } catch (error) {
            console.warn('Google Books API fetch error:', error.message);
            return []; // Return empty array on error (don't throw)
        }
    }
    
    function displayResults(books) {
        let html = '<h4>Resultados da busca (clique para selecionar):</h4>';
        
        books.forEach(book => {
            html += `
                <div class="search-result-item" data-title="${escapeHtml(book.title)}" 
                     data-author="${escapeHtml(book.author)}"
                     data-publisher="${escapeHtml(book.publisher)}"
                     data-isbn="${escapeHtml(book.isbn)}"
                     data-cover="${book.cover || ''}">
                    ${book.cover 
                        ? `<img src="${book.cover}" alt="" class="search-result-cover">`
                        : `<div class="search-result-cover" style="background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:2rem;">📚</div>`
                    }
                    <div>
                        <strong>${escapeHtml(book.title)}</strong>
                        <p>${escapeHtml(book.author)}</p>
                        ${book.publisher ? `<small>${escapeHtml(book.publisher)}</small>` : ''}
                        <small style="color:#6b7280">Fonte: ${escapeHtml(book.source)}</small>
                    </div>
                </div>
            `;
        });
        
        resultsContainer.innerHTML = html;
        
        // Add click handlers to results
        resultsContainer.querySelectorAll('.search-result-item').forEach(item => {
            item.addEventListener('click', function() {
                // Fill form with selected book data
                document.getElementById('title').value = this.dataset.title;
                document.getElementById('author').value = this.dataset.author;
                document.getElementById('publisher').value = this.dataset.publisher;
                document.getElementById('isbn').value = this.dataset.isbn;
                document.getElementById('cover_url').value = this.dataset.cover;
                
                // Highlight selected
                resultsContainer.querySelectorAll('.search-result-item').forEach(el => {
                    el.style.background = '';
                });
                this.style.background = '#d1fae5';
            });
        });
    }
}

/**
 * Borrower Modal for quick add
 */
function initBorrowerModal() {
    const modal = document.getElementById('borrower-modal');
    const form = document.getElementById('borrower-form');
    
    if (!modal || !form) return;
    
    // Make functions global for inline onclick handlers
    window.openModal = function() {
        modal.classList.add('active');
        document.getElementById('name').focus();
    };
    
    window.closeModal = function() {
        modal.classList.remove('active');
        form.reset();
    };
    
    // Close on backdrop click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    // Handle form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        try {
            const response = await fetch('/borrowers/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Add new borrower to the list without page reload
                addBorrowerToList(result.borrower);
                closeModal();
                
                // Show success message
                showFlash('success', 'Amigo adicionado com sucesso!');
            } else {
                showFlash('error', result.error || 'Erro ao adicionar amigo');
            }
            
        } catch (error) {
            console.error('Error:', error);
            showFlash('error', 'Erro ao adicionar amigo');
        }
    });
}

/**
 * Add new borrower to the grid (for AJAX response)
 */
function addBorrowerToList(borrower) {
    const grid = document.querySelector('.borrowers-grid');
    const emptyState = document.querySelector('.empty-state');
    
    // Remove empty state if exists
    if (emptyState) {
        emptyState.remove();
    }
    
    // Create borrower card HTML
    const card = document.createElement('div');
    card.className = 'borrower-card';
    card.innerHTML = `
        <div class="borrower-avatar">👤</div>
        <div class="borrower-info">
            <h3>${escapeHtml(borrower.name)}</h3>
            ${borrower.phone ? `<p>📞 ${escapeHtml(borrower.phone)}</p>` : ''}
            ${borrower.email ? `<p>✉️ ${escapeHtml(borrower.email)}</p>` : ''}
            ${borrower.location ? `<p>📍 ${escapeHtml(borrower.location)}</p>` : ''}
        </div>
        <div class="borrower-actions">
            <a href="/borrowers/${borrower.id}/edit" class="btn btn-sm btn-secondary">Editar</a>
            <form action="/borrowers/${borrower.id}/delete" method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este amigo?');">
                <input type="hidden" name="csrf_token" value="${document.getElementById('csrf_token').value}">
                <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
            </form>
        </div>
    `;
    
    // Add to grid
    if (grid) {
        grid.insertBefore(card, grid.firstChild);
    } else {
        // Create grid if doesn't exist
        const newGrid = document.createElement('div');
        newGrid.className = 'borrowers-grid';
        newGrid.appendChild(card);
        document.querySelector('.actions-bar').after(newGrid);
    }
}

/**
 * Form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let valid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.style.borderColor = '#ef4444';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios.');
            }
        });
    });
}

/**
 * Show flash message
 */
function showFlash(type, message) {
    // Check if there's already a flash container
    let flashContainer = document.querySelector('.flash-container');
    
    if (!flashContainer) {
        flashContainer = document.createElement('div');
        flashContainer.className = 'flash-container';
        document.querySelector('.container').insertBefore(flashContainer, document.querySelector('.container').firstChild);
    }
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    
    flashContainer.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Confirm delete actions
 */
document.addEventListener('click', function(e) {
    if (e.target.matches('.btn-delete, [data-confirm]')) {
        const message = e.target.dataset.confirm || 'Tem certeza?';
        if (!confirm(message)) {
            e.preventDefault();
        }
    }
});
