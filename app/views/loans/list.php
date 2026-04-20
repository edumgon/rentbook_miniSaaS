<h1>Empréstimos</h1>

<div class="filter-bar">
    <a href="/loans?status=active" class="btn btn-sm <?= $status === 'active' ? 'btn-active' : '' ?>">Ativos</a>
    <a href="/loans?status=returned" class="btn btn-sm <?= $status === 'returned' ? 'btn-active' : '' ?>">Devolvidos</a>
</div>

<?php if (empty($loans)): ?>
    <div class="empty-state">
        <p><?= $status === 'active' ? 'Nenhum empréstimo ativo.' : 'Nenhum livro devolvido ainda.' ?></p>
        <?php if ($status === 'active'): ?>
            <a href="/loans/create" class="btn btn-primary">Registrar Empréstimo</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="loans-list">
        <table class="table">
            <thead>
                <tr>
                    <th>Livro</th>
                    <th>Emprestado para</th>
                    <th>Data</th>
                    <?php if ($status === 'returned'): ?>
                        <th>Devolvido em</th>
                    <?php else: ?>
                        <th>Dias</th>
                        <th>Ações</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($loans as $loan): ?>
                <tr>
                    <td>
                        <div class="book-cell">
                            <?php if ($loan['cover_url']): ?>
                                <img src="<?= htmlspecialchars($loan['cover_url']) ?>" alt="" class="book-thumb">
                            <?php else: ?>
                                <div class="book-thumb-placeholder">📚</div>
                            <?php endif; ?>
                            <div>
                                <strong><?= htmlspecialchars($loan['book_title']) ?></strong>
                                <small><?= htmlspecialchars($loan['book_author'] ?? '') ?></small>
                            </div>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($loan['borrower_name']) ?></td>
                    <td><?= date('d/m/Y', strtotime($loan['loan_date'])) ?></td>
                    <?php if ($status === 'returned'): ?>
                        <td><?= date('d/m/Y', strtotime($loan['return_date'])) ?></td>
                    <?php else: ?>
                        <td>
                            <?php 
                                $days = floor((time() - strtotime($loan['loan_date'])) / 86400);
                                $class = $days > 30 ? 'text-danger' : '';
                            ?>
                            <span class="<?= $class ?>"><?= $days ?> dias</span>
                        </td>
                        <td>
                            <form action="/loans/<?= $loan['id'] ?>/return" method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
                                <button type="submit" class="btn btn-sm btn-success">✓ Devolvido</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
