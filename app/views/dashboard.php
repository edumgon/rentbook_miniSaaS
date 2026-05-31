<h1>Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📚</div>
        <div class="stat-value"><?= $bookStats['total'] ?></div>
        <div class="stat-label">Total de Livros</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-value"><?= $bookStats['available'] ?></div>
        <div class="stat-label">Disponíveis</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📤</div>
        <div class="stat-value"><?= $bookStats['lent'] ?></div>
        <div class="stat-label">Emprestados</div>
    </div>
    <div class="stat-card stat-warning">
        <div class="stat-icon">⚠️</div>
        <div class="stat-value"><?= count($overdueLoans) ?></div>
        <div class="stat-label">Em Atraso</div>
    </div>
</div>

<div class="actions-bar">
    <a href="/books/add" class="btn btn-primary">+ Adicionar Livro</a>
    <a href="/loans/create" class="btn btn-success">📤 Registrar Empréstimo</a>
</div>

<h2>Empréstimos Ativos</h2>

<?php if (empty($activeLoans)): ?>
    <div class="empty-state">
        <p>Nenhum livro emprestado no momento.</p>
        <a href="/loans/create" class="btn btn-primary">Registrar Empréstimo</a>
    </div>
<?php else: ?>
    <div class="loans-grid">
        <?php foreach ($activeLoans as $loan): ?>
        <div class="loan-card">
            <div class="loan-book">
                <?php if ($loan['cover_url']): ?>
                    <img src="<?= htmlspecialchars($loan['cover_url']) ?>" alt="" class="book-cover-small">
                <?php else: ?>
                    <div class="book-cover-placeholder">📚</div>
                <?php endif; ?>
                <div class="book-info">
                    <h3><?= htmlspecialchars($loan['book_title']) ?></h3>
                    <p><?= htmlspecialchars($loan['book_author']) ?></p>
                </div>
            </div>
            <div class="loan-details">
                <p><strong>Emprestado para:</strong> <?= htmlspecialchars($loan['borrower_name'] ?? 'Desconhecido') ?></p>
                <?php if ($loan['borrower_phone']): ?>
                    <p><strong>Telefone:</strong> <?= htmlspecialchars($loan['borrower_phone']) ?></p>
                <?php endif; ?>
                <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($loan['loan_date'])) ?></p>
                <?php 
                    $days = floor((time() - strtotime($loan['loan_date'])) / 86400);
                    $class = $days > 30 ? 'text-danger' : '';
                ?>
                <p class="<?= $class ?>"><strong>Há:</strong> <?= $days ?> dia(s)</p>
            </div>
            <form action="/loans/<?= $loan['id'] ?>/return" method="POST" class="loan-actions">
                <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
                <button type="submit" class="btn btn-success btn-sm">✓ Marcar Devolvido</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($overdueLoans)): ?>
<h2>⚠️ Empréstimos em Atraso (+30 dias)</h2>
<div class="loans-grid">
    <?php foreach ($overdueLoans as $loan): ?>
    <div class="loan-card loan-overdue">
        <div class="loan-book">
            <?php if ($loan['cover_url']): ?>
                <img src="<?= htmlspecialchars($loan['cover_url']) ?>" alt="" class="book-cover-small">
            <?php else: ?>
                <div class="book-cover-placeholder">📚</div>
            <?php endif; ?>
            <div class="book-info">
                <h3><?= htmlspecialchars($loan['book_title']) ?></h3>
                <p><?= htmlspecialchars($loan['book_author']) ?></p>
            </div>
        </div>
        <div class="loan-details">
            <p><strong>Emprestado para:</strong> <?= htmlspecialchars($loan['borrower_name'] ?? 'Desconhecido') ?></p>
            <?php if ($loan['borrower_phone']): ?>
                <p><strong>Telefone:</strong> <?= htmlspecialchars($loan['borrower_phone']) ?></p>
            <?php endif; ?>
            <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($loan['loan_date'])) ?></p>
            <p class="text-danger"><strong>⚠️ Atraso:</strong> <?= $loan['days_loaned'] ?> dias</p>
        </div>
        <form action="/loans/<?= $loan['id'] ?>/return" method="POST" class="loan-actions">
            <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
            <button type="submit" class="btn btn-success btn-sm">✓ Marcar Devolvido</button>
        </form>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
