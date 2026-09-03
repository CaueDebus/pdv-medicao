<?php

/** @var array<string, mixed> $screen */
?>

<section class="panel panel-metrics">
    <h2>Resumo rápido</h2>
    <div class="metric-grid">
        <?php foreach (($screen['metrics'] ?? []) as $metric): ?>
            <article class="metric-card tone-<?= htmlspecialchars((string) ($metric['tone'] ?? 'success'), ENT_QUOTES, 'UTF-8') ?>">
                <span class="metric-label"><?= htmlspecialchars((string) ($metric['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                <strong class="metric-value"><?= htmlspecialchars((string) ($metric['value'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="panel panel-highlights">
    <h2>Destaques</h2>
    <div class="stack-list">
        <?php foreach (($screen['highlights'] ?? []) as $highlight): ?>
            <article class="stack-item">
                <strong><?= htmlspecialchars((string) ($highlight['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                <p><?= htmlspecialchars((string) ($highlight['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="panel panel-sections">
    <h2>Funcionalidades</h2>
    <div class="feature-grid">
        <?php foreach (($screen['sections'] ?? []) as $section): ?>
            <article class="feature-card">
                <span class="feature-kicker"><?= htmlspecialchars((string) ($section['type'] ?? 'feature'), ENT_QUOTES, 'UTF-8') ?></span>
                <h3><?= htmlspecialchars((string) ($section['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                <p>Estrutura inicial pronta para evoluir sem quebrar a navegação entre telas.</p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="panel panel-data">
    <h2>Dados carregados</h2>
    <?php if (($screen['page'] ?? '') === 'dashboard'): ?>
        <div class="data-grid">
            <div class="data-card"><span>Comandas abertas</span><strong><?= htmlspecialchars((string) ($screen['context']['dashboard']['open_orders'] ?? 0), ENT_QUOTES, 'UTF-8') ?></strong></div>
            <div class="data-card"><span>Produtos em estoque</span><strong><?= htmlspecialchars((string) count($screen['context']['products'] ?? []), ENT_QUOTES, 'UTF-8') ?></strong></div>
        </div>
    <?php elseif (($screen['page'] ?? '') === 'cardapio'): ?>
        <div class="stack-list">
            <?php foreach (($screen['context']['products'] ?? []) as $product): ?>
                <article class="stack-item">
                    <strong><?= htmlspecialchars((string) ($product['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    <p><?= htmlspecialchars((string) ($product['category'] ?? ''), ENT_QUOTES, 'UTF-8') ?> · R$ <?= number_format((float) ($product['price'] ?? 0), 2, ',', '.') ?> · estoque <?= htmlspecialchars((string) ($product['stock_qty'] ?? 0), ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php elseif (($screen['page'] ?? '') === 'comandas'): ?>
        <div class="stack-list">
            <?php foreach (($screen['context']['orders'] ?? []) as $order): ?>
                <article class="stack-item">
                    <strong><?= htmlspecialchars((string) ($order['table_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    <p><?= htmlspecialchars((string) ($order['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?> · R$ <?= number_format((float) ($order['total_value'] ?? 0), 2, ',', '.') ?> · <?= htmlspecialchars((string) ($order['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php elseif (($screen['page'] ?? '') === 'estoque'): ?>
        <div class="stack-list">
            <?php foreach (($screen['context']['low_stock'] ?? []) as $product): ?>
                <article class="stack-item">
                    <strong><?= htmlspecialchars((string) ($product['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    <p>Restam <?= htmlspecialchars((string) ($product['stock_qty'] ?? 0), ENT_QUOTES, 'UTF-8') ?> unidades · categoria <?= htmlspecialchars((string) ($product['category'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php elseif (($screen['page'] ?? '') === 'producao'): ?>
        <div class="stack-list">
            <?php foreach (($screen['context']['queue'] ?? []) as $queueItem): ?>
                <article class="stack-item">
                    <strong><?= htmlspecialchars((string) ($queueItem['stage'] ?? ''), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string) ($queueItem['table'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    <p><?= htmlspecialchars((string) ($queueItem['item'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php elseif (($screen['page'] ?? '') === 'modulos'): ?>
        <div class="stack-list">
            <?php foreach (($screen['context']['modules'] ?? []) as $module): ?>
                <article class="stack-item">
                    <strong><?= htmlspecialchars((string) ($module['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    <p><?= htmlspecialchars((string) ($module['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php elseif (($screen['page'] ?? '') === 'relatorios'): ?>
        <div class="data-grid">
            <div class="data-card"><span>Total de itens vendidos</span><strong>318</strong></div>
            <div class="data-card"><span>Cancelamentos</span><strong>2</strong></div>
            <div class="data-card"><span>Ticket médio</span><strong>R$ 64,20</strong></div>
        </div>
    <?php elseif (($screen['page'] ?? '') === 'configuracoes'): ?>
        <div class="stack-list">
            <article class="stack-item">
                <strong>Tema da marca</strong>
                <p>Configuração inicial orientada pelo design system do protótipo anexado.</p>
            </article>
            <article class="stack-item">
                <strong>Perfis</strong>
                <p>Operador, gerente e admin já são previstos no modelo de navegação.</p>
            </article>
        </div>
    <?php else: ?>
        <div class="stack-list">
            <article class="stack-item">
                <strong>Base pronta</strong>
                <p>Este bloco será substituído pelas views específicas de cada módulo à medida que as features forem entrando.</p>
            </article>
        </div>
    <?php endif; ?>
</section>
