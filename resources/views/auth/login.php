<?php

/** @var string|null $error */
/** @var array<string, mixed>|null $sessionUser */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar · ComandaFlex</title>
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
<main class="auth-shell">
    <section class="auth-card">
        <div class="brand-name">ComandaFlex</div>
        <h1>Entrar no sistema</h1>
        <p>Use um perfil já criado para acessar as telas e a navegação por função.</p>

        <?php if (! empty($error)): ?>
            <div class="auth-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="?page=login" class="auth-form">
            <label>
                <span>E-mail</span>
                <input type="email" name="email" placeholder="admin@comandaflex.local" required>
            </label>
            <label>
                <span>Senha</span>
                <input type="password" name="password" placeholder="Admin@123" required>
            </label>
            <button class="btn btn-primary" type="submit">Entrar</button>
        </form>

        <div class="auth-note">
            Admin padrão: <strong>admin@comandaflex.local</strong> / <strong>Admin@123</strong>
        </div>
    </section>
</main>
</body>
</html>
