<?php

declare(strict_types=1);

$escape = static function ($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
};

$users = is_array($users ?? null) ? $users : [];

$formData = is_array($formData ?? null)
    ? $formData
    : [
        'full_name' => '',
        'email' => '',
        'role' => 'student',
    ];

?>

<section class="admin-users-page">
    <div class="admin-page-heading">
        <div>
            <h2>Manage Users</h2>
            <p>
                View registered accounts, create users, and manage
                eligible student accounts.
            </p>
        </div>

        <a
            class="button button-secondary"
            href="<?= $escape(rtrim(APP_BASE_URL, '/') . '/admin') ?>"
        >
            Back to Dashboard
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" role="alert">
            <?= $escape($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success" role="status">
            <?= $escape($success) ?>
        </div>
    <?php endif; ?>

    <section class="admin-panel">
        <div class="admin-panel-heading">
            <h3>Add New User</h3>
            <p>Create a student or administrator account.</p>
        </div>

        <form
            method="post"
            action="<?= $escape(
                rtrim(APP_BASE_URL, '/') . '/admin/users'
            ) ?>"
            class="admin-user-form"
        >
            <input
                type="hidden"
                name="csrf_token"
                value="<?= $escape($csrfToken ?? '') ?>"
            >

            <input type="hidden" name="action" value="create">

            <div class="form-group">
                <label for="full_name">Full name</label>
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    maxlength="100"
                    autocomplete="name"
                    value="<?= $escape($formData['full_name'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    maxlength="255"
                    autocomplete="email"
                    value="<?= $escape($formData['email'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Initial password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    minlength="6"
                    maxlength="4096"
                    autocomplete="new-password"
                    required
                >
                <small>
                    Minimum 6 characters. The password will be
                    securely hashed before storage.
                </small>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option
                        value="student"
                        <?= ($formData['role'] ?? '') === 'student'
                            ? 'selected'
                            : '' ?>
                    >
                        Student
                    </option>

                    <option
                        value="admin"
                        <?= ($formData['role'] ?? '') === 'admin'
                            ? 'selected'
                            : '' ?>
                    >
                        Administrator
                    </option>
                </select>
                <small>
                    Administrator accounts have privileged access.
                </small>
            </div>

            <div class="form-actions">
                <button type="submit" class="button button-primary">
                    Add User
                </button>
            </div>
        </form>
    </section>

    <section class="admin-panel">
        <div class="admin-panel-heading users-table-heading">
            <div>
                <h3>Registered Users</h3>
                <p>
                    Total accounts:
                    <strong><?= count($users) ?></strong>
                </p>
            </div>

            <div class="form-group user-search-group">
                <label for="user-search">Search users</label>
                <input
                    type="search"
                    id="user-search"
                    placeholder="Search by name, email, role..."
                >
            </div>
        </div>

        <?php if ($users === []): ?>
            <p class="empty-state">
                No users have been registered yet.
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table" id="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php
                            $userId = (int) $user['user_id'];
                            $isCurrentUser =
                                $userId === (int) ($currentUserId ?? 0);

                            $isAdmin = $user['role'] === 'admin';
                            ?>

                            <tr>
                                <td><?= $userId ?></td>

                                <td><?= $escape($user['full_name']) ?></td>

                                <td><?= $escape($user['email']) ?></td>

                                <td>
                                    <span class="role-badge role-<?= $escape(
                                        $user['role']
                                    ) ?>">
                                        <?= $escape(
                                            ucfirst($user['role'])
                                        ) ?>
                                    </span>
                                </td>

                                <td>
                                    <time>
                                        <?= $escape(
                                            date(
                                                'd M Y, h:i A',
                                                strtotime(
                                                    (string) $user['created_at']
                                                )
                                            )
                                        ) ?>
                                    </time>
                                </td>

                                <td>
                                    <?php if ($isCurrentUser): ?>
                                        <span class="action-note">
                                            Current account
                                        </span>
                                    <?php elseif ($isAdmin): ?>
                                        <span class="action-note">
                                            Protected admin
                                        </span>
                                    <?php else: ?>
                                        <form
                                            method="post"
                                            action="<?= $escape(
                                                rtrim(APP_BASE_URL, '/')
                                                . '/admin/users'
                                            ) ?>"
                                            class="delete-user-form"
                                        >
                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= $escape(
                                                    $csrfToken ?? ''
                                                ) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="delete"
                                            >

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?= $userId ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="button button-danger"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <p id="users-no-results" class="empty-state" hidden>
                    No users match your search.
                </p>
            </div>
        <?php endif; ?>
    </section>
</section>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('user-search');
        const table = document.getElementById('users-table');
        const noResults = document.getElementById('users-no-results');

        if (searchInput && table) {
            searchInput.addEventListener('input', function () {
                const query = searchInput.value.trim().toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                let visibleCount = 0;

                rows.forEach(function (row) {
                    const matches = row.textContent
                        .toLowerCase()
                        .includes(query);

                    row.hidden = !matches;

                    if (matches) {
                        visibleCount++;
                    }
                });

                if (noResults) {
                    noResults.hidden = visibleCount !== 0;
                }
            });
        }

        document.querySelectorAll('.delete-user-form').forEach(
            function (form) {
                form.addEventListener('submit', function (event) {
                    const confirmed = window.confirm(
                        'Are you sure you want to delete this user? ' +
                        'This action cannot be undone.'
                    );

                    if (!confirmed) {
                        event.preventDefault();
                    }
                });
            }
        );
    });
</script>
