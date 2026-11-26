<?php

declare(strict_types=1);

$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$baseUrl = rtrim(APP_BASE_URL, '/');
$users = is_array($users ?? null) ? $users : [];
$formData = is_array($formData ?? null) ? $formData : ['full_name' => '', 'email' => '', 'role' => 'student'];

?>
<section class="admin-users-page">
    <div class="admin-page-heading">
        <div>
            <p class="eyebrow">ADMINISTRATION</p>
            <h2>People &amp; access.</h2>
            <p>Create accounts and keep the lab community up to date.</p>
        </div>
        <a class="button button-secondary" href="<?= $escape($baseUrl . '/admin') ?>"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Dashboard</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" role="alert" data-toast="error"><?= $escape($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success" role="status" data-toast="success"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><?= $escape($success) ?></div>
    <?php endif; ?>

    <section class="student-panel">
        <div class="admin-panel-heading">
            <div>
                <h3>Add a user</h3>
                <p class="muted-text">Create a student or administrator account.</p>
            </div>
            <span class="feature-icon" aria-hidden="true"><i class="fa-solid fa-user-plus"></i></span>
        </div>

        <form method="post" action="<?= $escape($baseUrl . '/admin/users') ?>" class="admin-user-form" data-validate>
            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken ?? '') ?>">
            <input type="hidden" name="action" value="create">

            <div class="form-group">
                <label for="full_name">Full name</label>
                <input type="text" id="full_name" name="full_name" maxlength="100" autocomplete="name" value="<?= $escape($formData['full_name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" maxlength="255" autocomplete="email" value="<?= $escape($formData['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Initial password</label>
                <input type="password" id="password" name="password" minlength="6" maxlength="4096" autocomplete="new-password" required>
                <small class="muted-text">Minimum 6 characters. Stored securely as a hash.</small>
            </div>

            <div class="form-group">
                <label for="role">Account role</label>
                <select id="role" name="role" required>
                    <option value="student" <?= ($formData['role'] ?? '') === 'student' ? 'selected' : '' ?>>Student</option>
                    <option value="admin" <?= ($formData['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrator</option>
                </select>
            </div>

            <div class="form-actions form-actions-wide">
                <button type="submit" class="button button-primary"><i class="fa-solid fa-user-plus" aria-hidden="true"></i> Add user</button>
            </div>
        </form>
    </section>

    <section class="student-panel">
        <div class="student-panel-heading">
            <div>
                <h3>Registered users</h3>
                <p class="muted-text">Manage account visibility and access.</p>
            </div>
            <span class="booking-status status-booked"><?= count($users) ?> accounts</span>
        </div>

        <div class="table-tools" data-table-tools data-table-target="users-table">
            <label class="table-search-wrap">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <span class="sr-only">Search users</span>
                <input class="table-search" type="search" data-table-search placeholder="Search name, email, or role">
            </label>
            <div class="filter-tabs" role="group" aria-label="Filter users by role">
                <button type="button" class="is-active" data-filter-value="all" aria-pressed="true">All</button>
                <button type="button" data-filter-value="student" aria-pressed="false">Students</button>
                <button type="button" data-filter-value="admin" aria-pressed="false">Admins</button>
            </div>
            <div class="table-tool-actions">
                <button type="button" class="button button-secondary button-small" data-table-export="users-table" data-filename="lab-users"><i class="fa-solid fa-download" aria-hidden="true"></i> Export CSV</button>
                <label class="sr-only" for="users-page-size">Rows per page</label>
                <select class="table-page-size" id="users-page-size" data-table-page-size aria-label="Rows per page">
                    <option value="8">8 rows</option><option value="15">15 rows</option><option value="30">30 rows</option>
                </select>
            </div>
        </div>

        <?php if ($users === []): ?>
            <div class="empty-state">
                <h3>No accounts yet</h3>
                <p>New users will appear here after they are added.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table admin-user-table" id="users-table">
                    <thead>
                        <tr>
                            <th data-sortable>ID</th>
                            <th data-sortable>Name</th>
                            <th data-sortable>Email</th>
                            <th data-sortable>Role</th>
                            <th data-sortable>Created at</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php
                            $userId = (int) $user['user_id'];
                            $isCurrentUser = $userId === (int) ($currentUserId ?? 0);
                            $isAdmin = $user['role'] === 'admin';
                            ?>
                            <tr data-status="<?= $escape($user['role']) ?>">
                                <td data-label="ID"><?= $userId ?></td>
                                <td data-label="Name"><strong><?= $escape($user['full_name']) ?></strong></td>
                                <td data-label="Email"><?= $escape($user['email']) ?></td>
                                <td data-label="Role"><span class="role-badge role-<?= $escape($user['role']) ?>"><?= $escape(ucfirst($user['role'])) ?></span></td>
                                <td data-label="Created at"><time><?= $escape(date('d M Y, h:i A', strtotime((string) $user['created_at']))) ?></time></td>
                                <td data-label="Actions">
                                    <?php if ($isCurrentUser): ?>
                                        <span class="action-note">Current account</span>
                                    <?php elseif ($isAdmin): ?>
                                        <span class="action-note">Protected admin</span>
                                    <?php else: ?>
                                        <form method="post" action="<?= $escape($baseUrl . '/admin/users') ?>" data-confirm-message="Delete this student account? This cannot be undone.">
                                            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken ?? '') ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?= $userId ?>">
                                            <button type="submit" class="button button-danger button-small"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="empty-state" data-table-empty hidden>No users match your search.</p>
            </div>
        <?php endif; ?>
        <div class="table-pagination" data-table-pagination></div>
    </section>
</section>
