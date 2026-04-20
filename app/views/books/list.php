<h1>Meus Livros</h1>

<div class="actions-bar">
    <a href="/books/add" class="btn btn-primary">+ Adicionar Livro</a>
</div>

<div class="filter-bar">
    <a href="/books" class="btn btn-sm <?= !$status ? 'btn-active' : '' ?>">Todos</a>
    <a href="/books?status=available" class="btn btn-sm <?= $status === 'available' ? 'btn-active' : '' ?>">Disponíveis</a>
    <a href="/books?status=lent" class="btn btn-sm <?= $status === 'lent' ? 'btn-active' : '' ?>">Emprestados</a>
</div>

<?php if (empty($books)): ?>
    <div class="empty-state">
        <p>Nenhum livro cadastrado.</p>
        <a href="/books/add" class="btn btn-primary">Adicionar Primeiro Livro</a>
    </div>
<?php else: ?>
    <div class="books-grid">
        <?php foreach ($books as $book): ?>
        <div class="book-card">
            <div class="book-cover">
                <?php if ($book['cover_url']): ?>
                    <img src="<?= htmlspecialchars($book['cover_url']) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                <?php else: ?>
                    <div class="book-cover-placeholder">
                        <span>📚</span>
                        <small><?= htmlspecialchars(substr($book['title'], 0, 30)) ?></small>
                    </div>
                <?php endif; ?>
                <span class="book-status status-<?= $book['status'] ?>">
                    <?= $book['status'] === 'available' ? 'Disponível' : 'Emprestado' ?>
                </span>
            </div>
            <div class="book-details">
                <h3><?= htmlspecialchars($book['title']) ?></h3>
                <p class="book-author"><?= htmlspecialchars($book['author'] ?? 'Autor desconhecido') ?></p>
                <?php if ($book['publisher']): ?>
                    <p class="book-publisher"><?= htmlspecialchars($book['publisher']) ?></p>
                <?php endif; ?>
            </div>
            <div class="book-actions">
                <a href="/books/<?= $book['id'] ?>/edit" class="btn btn-sm btn-secondary">Editar</a>
                <a href="/loans/history/<?= $book['id'] ?>" class="btn btn-sm btn-info">Histórico</a>
                <form action="/books/<?= $book['id'] ?>/delete" method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este livro?');">
                    <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
