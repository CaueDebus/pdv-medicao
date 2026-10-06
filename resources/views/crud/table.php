<?php

/**
 * Listagem compartilhada por TODOS os CRUDs do sistema.
 *
 * Não conhece produto, comanda, estoque ou módulo: percorre os campos
 * declarados no CrudResource entregue pela CrudFactory.
 *
 * @var array<string, mixed> $crud
 */

use App\Domain\Crud\CrudResource;

/** @var CrudResource $resource */
$resource = $crud['resource'];
$rows = $crud['rows'] ?? [];
$page = (string) ($crud['page'] ?? '');
$token = (string) ($crud['token'] ?? '');
?>

<section class="panel panel-crud">
    <div class="crud-head">
        <h2><?= htmlspecialchars($resource->label, ENT_QUOTES, 'UTF-8') ?></h2>
        <?php if (! empty($crud['can_write'])): ?>
            <a class="btn btn-primary btn-compact" href="?page=<?= urlencode($page) ?>&amp;action=create">
                <?= htmlspecialchars($resource->newLabel(), ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($crud['persists'])): ?>
        <div class="alert alert-warn">
            Modo demonstração: o MySQL não está disponível, então esta lista mostra dados de exemplo e nada é gravado.
        </div>
    <?php endif; ?>

    <?php if ($rows === []): ?>
        <div class="alert alert-info">Nenhum registro cadastrado ainda.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <?php foreach ($resource->fields as $field): ?>
                        <th><?= htmlspecialchars($field->label, ENT_QUOTES, 'UTF-8') ?></th>
                    <?php endforeach; ?>
                    <th class="col-actions">Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($resource->fields as $field): ?>
                            <td>
                                <?php $raw = $row[$field->name] ?? ''; ?>
                                <?php if ($field->type === 'toggle' && ! empty($crud['can_write'])): ?>
                                    <form class="inline-form" method="post" action="?page=<?= urlencode($page) ?>&amp;action=toggle&amp;id=<?= (int) ($row['id'] ?? 0) ?>">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="field" value="<?= htmlspecialchars($field->name, ENT_QUOTES, 'UTF-8') ?>">
                                        <button
                                            class="module-chip module-chip-button"
                                            type="submit"
                                            title="Clique para <?= $raw ? 'desativar' : 'ativar' ?>"
                                            aria-pressed="<?= $raw ? 'true' : 'false' ?>"
                                        >
                                            <span class="swdot <?= $raw ? 'on' : '' ?>"></span>
                                            <?= htmlspecialchars($field->display($raw), ENT_QUOTES, 'UTF-8') ?>
                                        </button>
                                    </form>
                                <?php elseif ($field->type === 'toggle'): ?>
                                    <span class="module-chip">
                                        <span class="swdot <?= $raw ? 'on' : '' ?>"></span>
                                        <?= htmlspecialchars($field->display($raw), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                <?php elseif ($field->options !== []): ?>
                                    <span class="pill pill-neutral" data-value="<?= htmlspecialchars((string) $raw, ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="dot"></span><?= htmlspecialchars($field->display($raw), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                <?php else: ?>
                                    <?= htmlspecialchars($field->display($raw), ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="col-actions">
                            <?php if (! empty($crud['can_write'])): ?>
                                <a class="btn btn-secondary btn-compact" href="?page=<?= urlencode($page) ?>&amp;action=edit&amp;id=<?= (int) ($row['id'] ?? 0) ?>">Editar</a>
                            <?php endif; ?>
                            <?php if (! empty($crud['can_destroy'])): ?>
                                <form class="inline-form" method="post" action="?page=<?= urlencode($page) ?>&amp;action=destroy&amp;id=<?= (int) ($row['id'] ?? 0) ?>">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                                    <button class="btn btn-danger btn-compact" type="submit">Remover</button>
                                </form>
                            <?php endif; ?>
                            <?php if (empty($crud['can_write'])): ?>
                                <span class="muted-note">somente leitura</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
