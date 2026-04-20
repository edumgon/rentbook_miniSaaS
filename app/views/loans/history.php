<h1>Histórico de Empréstimos</h1>

<div class="card book-header">
    <?php if ($book['cover_url']): ?>
        <img src="<?= htmlspecialchars($book['cover_url']) ?>" alt="" class="book-cover-medium">
    <?php else: ?>
        <div class="book-cover-medium-placeholder">📚</div>
    <?php endif; ?>
    <div>
        <h2><?= htmlspecialchars($book['title']) ?></h2>
        <p><?= htmlspecialchars($book['author'] ?? 'Autor desconhecido') ?></p>
        <span class="status-badge status-<?= $book['status'] ?>">
            <?= $book['status'] === 'available' ? 'Disponível' : 'Emprestado' ?>
        </span>
    </div>
</div>

<h3>Histórico</h3>

<?php if (empty($history)): ?>
    <p>Este livro nunca foi emprestado.</p>
<?php else: ?>
    <div class="history-list">
        <?php foreach ($history as $loan): ?>
        <div class="history-item">
            <div class="history-icon">
                <?= $loan['status'] === 'active' ? '📤' : '✅' ?>
            </div>
            <div class="history-content">
                <p><strong>Emprestado para:</strong> <?= htmlspecialchars($loan['borrower_name']) ?></p>
                <p><strong>Data do empréstimo:</strong> <?= date('d/m/Y', strtotime($loan['loan_date'])) ?></p>
                <?php if ($loan['status'] === 'returned' && $loan['return_date']): ?>
                    <p><strong>Devolvido em:</strong> <?= date('d/m/Y', strtotime($loan['return_date'])) ?></p>
                <?php endif; ?>
                <?php if ($loan['notes']): ?>
                    <p class="text-muted"><strong>Obs:</strong> <?= htmlspecialchars($loan['notes']) ?></p>
                <?php endif; ?>
            </div>
            <div class="history-status">
                <span class="badge badge-<?= $loan['status'] ?>">
                    <?= $loan['status'] === 'active' ? 'Ativo' : 'Devolvido' ?>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="form-actions">
    <a href="/books" class="btn btn-secondary">← Voltar para Livros</a>
</div>
