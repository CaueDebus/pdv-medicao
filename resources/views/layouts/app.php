<?php

/** @var array<string, mixed> $app */
/** @var array<string, mixed> $screen */
/** @var array<string, mixed>|null $user */
/** @var array<int, array<string, mixed>> $navigation */

$pageTitle = ($screen['title'] ?? 'ComandaFlex') . ' · ' . ($app['name'] ?? 'ComandaFlex');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand-block">
            <div class="brand-name"><?= htmlspecialchars($app['name'] ?? 'ComandaFlex', ENT_QUOTES, 'UTF-8') ?></div>
            <div class="brand-meta">PHP modular · v<?= htmlspecialchars($app['version'] ?? '0.1.0', ENT_QUOTES, 'UTF-8') ?></div>
            <?php if (is_array($user ?? null)): ?>
                <div class="brand-user">
                    <?= htmlspecialchars((string) ($user['name'] ?? 'Usuário'), ENT_QUOTES, 'UTF-8') ?>
                    · <?= htmlspecialchars((string) ($user['role'] ?? 'operator'), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <a class="brand-logout" href="?action=logout">Sair</a>
            <?php endif; ?>
        </div>
        <nav class="nav-list">
            <?php foreach ($navigation as $item): ?>
                <a class="nav-link <?= ! empty($item['active']) ? 'is-active' : '' ?> <?= ! empty($item['locked']) ? 'is-locked' : '' ?>" href="<?= htmlspecialchars($item['url'] ?? '#', ENT_QUOTES, 'UTF-8') ?>">
                    <span class="nav-title"><?= htmlspecialchars($item['label'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="nav-desc"><?= htmlspecialchars($item['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if (! empty($item['locked'])): ?>
                        <span class="nav-lock"><?= htmlspecialchars((string) ($item['reason'] ?? 'bloqueado'), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <main class="content-area">
        <header class="page-hero">
            <div class="hero-eyebrow">ComandaFlex · design system em PHP</div>
            <h1><?= htmlspecialchars($screen['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
            <p><?= htmlspecialchars($screen['lead'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
        </header>

        <?php require __DIR__ . '/../pages/screen.php'; ?>
    </main>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
