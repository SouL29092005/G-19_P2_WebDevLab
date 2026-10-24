<?php

declare(strict_types=1);

$computers = $computers ?? [];

?>

<section class="admin-computers">
    <h2>Manage Laboratory Computers</h2>

    <p>
        View all laboratory computers, including inactive ones.
    </p>

    <div class="computer-actions">
        <a
            class="dashboard-button"
            href="<?= htmlspecialchars(
                rtrim(APP_BASE_URL, '/') . '/admin',
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >Dashboard</a>

        <button
            type="button"
            id="toggle-add-computer"
            aria-expanded="false"
            aria-controls="add-computer-form"
        >
            + Add Computer
        </button>
    </div>

    <?php if (!empty($success)): ?>
        <p class="success-message" role="status">
            Computer added successfully.
        </p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p class="form-error" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <section id="add-computer-form" class="add-computer-form" hidden>
        <h3>Add a Laboratory Computer</h3>

        <form
            method="POST"
            action="<?= htmlspecialchars(
                rtrim(APP_BASE_URL, '/') . '/admin/computers',
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(
                    $csrfToken,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

            <div class="form-group">
                <label for="computer_name">Computer Name</label>
                <input
                    type="text"
                    id="computer_name"
                    name="computer_name"
                    maxlength="50"
                    required
                    value="<?= htmlspecialchars(
                        $computerName ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    placeholder="e.g. LAB-PC-01"
                >
            </div>

            <div class="form-group">
                <label for="description">Description (optional)</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="Enter computer specifications or details"
                ><?= htmlspecialchars(
                    $description ?? '',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit">Save Computer</button>
                <button type="button" id="cancel-add-computer">Cancel</button>
            </div>
        </form>
    </section>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Computer Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($computers === []): ?>
                    <tr>
                        <td colspan="5">
                            No computers have been added yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($computers as $computer): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars(
                                    (string) $computer['computer_id'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    (string) $computer['computer_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    (string) (
                                        $computer['description'] ?? ''
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <?php if ($computer['is_active']): ?>
                                    <span class="status-active">
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span class="status-inactive">
                                        Inactive
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    (string) $computer['created_at'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('toggle-add-computer');
    const cancelButton = document.getElementById('cancel-add-computer');
    const formSection = document.getElementById('add-computer-form');

    function setFormVisible(visible) {
        formSection.hidden = !visible;
        toggleButton.setAttribute('aria-expanded', String(visible));

        if (visible) {
            document.getElementById('computer_name').focus();
        }
    }

    toggleButton.addEventListener('click', function () {
        setFormVisible(formSection.hidden);
    });

    cancelButton.addEventListener('click', function () {
        setFormVisible(false);
    });

    <?php if (!empty($error)): ?>
    setFormVisible(true);
    <?php endif; ?>
});
</script>
