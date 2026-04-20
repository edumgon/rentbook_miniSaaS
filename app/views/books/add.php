<h1>Adicionar Livro</h1>

<div class="card">
    <div class="search-section">
        <h3>Buscar na Open Library</h3>
        <div class="search-form">
            <input type="text" id="book-search" class="form-input" placeholder="Digite o título ou autor...">
            <button type="button" id="search-btn" class="btn btn-secondary">Buscar</button>
        </div>
        <div id="search-results" class="search-results"></div>
    </div>

    <hr class="divider">

    <form action="/books/store" method="POST" class="form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        
        <div class="form-group">
            <label for="title">Título *</label>
            <input type="text" id="title" name="title" class="form-input" required>
        </div>
        
        <div class="form-group">
            <label for="author">Autor</label>
            <input type="text" id="author" name="author" class="form-input">
        </div>
        
        <div class="form-group">
            <label for="publisher">Editora</label>
            <input type="text" id="publisher" name="publisher" class="form-input">
        </div>
        
        <div class="form-group">
            <label for="isbn">ISBN</label>
            <input type="text" id="isbn" name="isbn" class="form-input">
        </div>
        
        <div class="form-group">
            <label for="cover_url">URL da Capa</label>
            <input type="url" id="cover_url" name="cover_url" class="form-input">
            <small>Deixe em branco se não houver capa</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Salvar Livro</button>
            <a href="/books" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
