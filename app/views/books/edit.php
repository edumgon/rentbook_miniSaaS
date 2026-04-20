<h1>Editar Livro</h1>

<div class="card">
    <form action="/books/<?= $book['id'] ?>/update" method="POST" class="form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        
        <div class="form-group">
            <label for="title">Título *</label>
            <input type="text" id="title" name="title" class="form-input" value="<?= htmlspecialchars($book['title']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="author">Autor</label>
            <input type="text" id="author" name="author" class="form-input" value="<?= htmlspecialchars($book['author'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label for="publisher">Editora</label>
            <input type="text" id="publisher" name="publisher" class="form-input" value="<?= htmlspecialchars($book['publisher'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label for="isbn">ISBN</label>
            <input type="text" id="isbn" name="isbn" class="form-input" value="<?= htmlspecialchars($book['isbn'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label for="cover_url">URL da Capa</label>
            <input type="url" id="cover_url" name="cover_url" class="form-input" value="<?= htmlspecialchars($book['cover_url'] ?? '') ?>">
            <small>Deixe em branco se não houver capa</small>
        </div>
        
        <?php if ($book['cover_url']): ?>
        <div class="form-group">
            <label>Capa Atual</label>
            <img src="<?= htmlspecialchars($book['cover_url']) ?>" alt="Capa do livro" class="book-cover-preview">
        </div>
        <?php endif; ?>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Atualizar Livro</button>
            <a href="/books" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
