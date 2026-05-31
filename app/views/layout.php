<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Book Lending Manager') ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <script>
        // Apply saved/system theme before paint to avoid flash of wrong theme
        (function () {
            try {
                var saved = localStorage.getItem('theme');
                var theme = saved || ((window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {}
        })();
    </script>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="/" class="navbar-brand">📚 BookLend</a>

            <div class="navbar-right">
                <?php if (Auth::check()): ?>
                <ul class="navbar-nav">
                    <li><a href="/dashboard">Dashboard</a></li>
                    <li><a href="/books">Meus Livros</a></li>
                    <li><a href="/borrowers">Amigos</a></li>
                    <li><a href="/loans">Empréstimos</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle">
                            <?= htmlspecialchars(Auth::user()['name'] ?? 'Usuário') ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <form action="/logout" method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
                                    <button type="submit" class="btn-link">Sair</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
                <?php endif; ?>
                <button type="button" id="theme-toggle" class="theme-toggle" aria-label="Alternar tema claro/escuro" title="Alternar tema claro/escuro">🌙</button>
            </div>
        </div>
    </nav>

    <main class="main">
        <div class="container">
            <?php if (isset($flash) && $flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>
            
            <?= $content ?? '' ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> BookLend - Sistema de Empréstimo de Livros</p>
        </div>
    </footer>

    <script src="/js/app.js"></script>
</body>
</html>
