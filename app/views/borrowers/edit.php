<h1>Editar Amigo</h1>

<div class="card">
    <form action="/borrowers/<?= $borrower['id'] ?>/update" method="POST" class="form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        
        <div class="form-group">
            <label for="name">Nome *</label>
            <input type="text" id="name" name="name" class="form-input" value="<?= htmlspecialchars($borrower['name']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="phone">Telefone</label>
            <input type="tel" id="phone" name="phone" class="form-input" value="<?= htmlspecialchars($borrower['phone'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-input" value="<?= htmlspecialchars($borrower['email'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label for="location">Localização</label>
            <input type="text" id="location" name="location" class="form-input" value="<?= htmlspecialchars($borrower['location'] ?? '') ?>">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Atualizar</button>
            <a href="/borrowers" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
