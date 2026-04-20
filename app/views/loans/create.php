<h1>Registrar Empréstimo</h1>

<div class="card">
    <form action="/loans/store" method="POST" class="form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        
        <div class="form-group">
            <label for="book_id">Livro *</label>
            <select id="book_id" name="book_id" class="form-select" required>
                <option value="">Selecione um livro disponível</option>
                <?php foreach ($books as $book): ?>
                <option value="<?= $book['id'] ?>">
                    <?= htmlspecialchars($book['title']) ?> - <?= htmlspecialchars($book['author'] ?? 'Autor desconhecido') ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="borrower_id">Amigo *</label>
            <div class="input-with-action">
                <select id="borrower_id" name="borrower_id" class="form-select" required>
                    <option value="">Selecione um amigo</option>
                    <?php foreach ($borrowers as $borrower): ?>
                    <option value="<?= $borrower['id'] ?>">
                        <?= htmlspecialchars($borrower['name']) ?> 
                        <?php if ($borrower['location']): ?>(<?= htmlspecialchars($borrower['location']) ?>)<?php endif; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <a href="/borrowers" class="btn btn-sm btn-secondary">+ Novo</a>
            </div>
        </div>
        
        <div class="form-group">
            <label for="loan_date">Data do Empréstimo *</label>
            <input type="date" id="loan_date" name="loan_date" class="form-input" value="<?= date('Y-m-d') ?>" required>
        </div>
        
        <div class="form-group">
            <label for="notes">Observações</label>
            <textarea id="notes" name="notes" class="form-textarea" rows="3" placeholder="Ex: Devolver até o final do mês"></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Registrar Empréstimo</button>
            <a href="/dashboard" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
