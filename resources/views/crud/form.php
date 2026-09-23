<?php

/**
 * Formulário compartilhado por TODOS os CRUDs do sistema.
 *
 * Os campos saem do CrudResource; criar e editar usam a mesma marcação.
 *
 * @var array<string, mixed> $screen
 */

use App\Domain\Crud\CrudResource;

$crud = $screen['crud'] ?? [];
/** @var CrudResource $resource */
$resource = $crud['resource'];
$values = $crud['values'] ?? [];
$id = $crud['id'] ?? null;
$page = (string) ($crud['page'] ?? '');
$token = (string) ($crud['token'] ?? '');
$action = $id === null
    ? '?page=' . urlencode($page) . '&action=store'
    : '?page=' . urlencode($page) . '&action=update&id=' . (int) $id;
?>

<section class="panel panel-form">
    <h2><?= htmlspecialchars($resource->label, ENT_QUOTES, 'UTF-8') ?></h2>

    <form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-grid">
            <?php foreach ($resource->fields as $field): ?>
                <?php $current = $values[$field->name] ?? ''; ?>
                <div class="field">
                    <label for="field-<?= htmlspecialchars($field->name, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($field->label, ENT_QUOTES, 'UTF-8') ?>
                    </label>

                    <?php if ($field->type === 'select'): ?>
                        <select id="field-<?= htmlspecialchars($field->name, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($field->name, ENT_QUOTES, 'UTF-8') ?>">
                            <?php foreach ($field->options as $value => $label): ?>
                                <option value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>" <?= (string) $current === (string) $value ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($field->type === 'toggle'): ?>
                        <label class="check-line">
                            <input type="checkbox" name="<?= htmlspecialchars($field->name, ENT_QUOTES, 'UTF-8') ?>" value="1" <?= $current ? 'checked' : '' ?>>
                            Ativo nesta instalação
                        </label>
                    <?php else: ?>
                        <input
                            id="field-<?= htmlspecialchars($field->name, ENT_QUOTES, 'UTF-8') ?>"
                            type="text"
                            name="<?= htmlspecialchars($field->name, ENT_QUOTES, 'UTF-8') ?>"
                            value="<?= htmlspecialchars((string) $current, ENT_QUOTES, 'UTF-8') ?>"
                            <?= $field->required ? 'required' : '' ?>
                        >
                    <?php endif; ?>

                    <?php if ($field->help !== ''): ?>
                        <span class="field-help"><?= htmlspecialchars($field->help, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-secondary" href="?page=<?= urlencode($page) ?>">Cancelar</a>
        </div>
    </form>
</section>
