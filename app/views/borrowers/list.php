<h1>Meus Amigos</h1>

<div class="actions-bar">
    <button type="button" class="btn btn-primary" onclick="openModal()">+ Adicionar Amigo</button>
</div>

<?php if (empty($borrowers)): ?>
    <div class="empty-state">
        <p>Nenhum amigo cadastrado.</p>
        <button type="button" class="btn btn-primary" onclick="openModal()">Adicionar Primeiro Amigo</button>
    </div>
<?php else: ?>
    <div class="borrowers-grid">
        <?php foreach ($borrowers as $borrower): ?>
        <div class="borrower-card">
            <div class="borrower-avatar">👤</div>
            <div class="borrower-info">
                <h3><?= htmlspecialchars($borrower['name']) ?></h3>
                <?php if ($borrower['phone']): ?>
                    <p>📞 <?= htmlspecialchars($borrower['phone']) ?></p>
                <?php endif; ?>
                <?php if ($borrower['email']): ?>
                    <p>✉️ <?= htmlspecialchars($borrower['email']) ?></p>
                <?php endif; ?>
                <?php if ($borrower['location']): ?>
                    <p>📍 <?= htmlspecialchars($borrower['location']) ?></p>
                <?php endif; ?>
            </div>
            <div class="borrower-actions">
                <a href="/borrowers/<?= $borrower['id'] ?>/edit" class="btn btn-sm btn-secondary">Editar</a>
                <form action="/borrowers/<?= $borrower['id'] ?>/delete" method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este amigo?');">
                    <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal para adicionar amigo -->
<div id="borrower-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Adicionar Amigo</h2>
            <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="borrower-form" class="form">
            <input type="hidden" name="csrf_token" id="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
            
            <div class="form-group">
                <label for="name">Nome *</label>
                <input type="text" id="name" name="name" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Telefone</label>
                <input type="tel" id="phone" name="phone" class="form-input">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input">
            </div>
            
            <div class="form-group">
                <label for="location">Localização</label>
                <input type="text" id="location" name="location" class="form-input" placeholder="Bairro, Cidade, etc.">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>
