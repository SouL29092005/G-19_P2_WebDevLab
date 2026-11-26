<?php

declare(strict_types=1);

$baseUrl = rtrim(APP_BASE_URL, '/');
$computers = is_array($computers ?? null) ? $computers : [];
$showAddForm = !empty($error);

?>
<section class="admin-computers">
    <div class="admin-page-heading">
        <div>
            <p class="eyebrow">ADMINISTRATION</p>
            <h2>Computer inventory.</h2>
            <p>Review the lab fleet and add new workstations.</p>
        </div>
        <div class="computer-actions">
            <a class="button button-secondary" href="<?= htmlspecialchars($baseUrl . '/admin', ENT_QUOTES, 'UTF-8') ?>"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Dashboard</a>
            <button type="button" id="toggle-add-computer" class="button button-primary" data-toggle-target="add-computer-form" aria-expanded="<?= $showAddForm ? 'true' : 'false' ?>" aria-controls="add-computer-form"><i class="fa-solid fa-plus" aria-hidden="true"></i> Add computer</button>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <p class="success-message" role="status" data-toast="success"><i class="fa-solid fa-circle-check" aria-hidden="true"></i>Computer added successfully.</p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p class="form-error" role="alert" data-toast="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <section id="add-computer-form" class="student-panel add-computer-form" <?= $showAddForm ? '' : 'hidden' ?> data-reveal>
        <div class="admin-panel-heading">
            <div>
                <h3>Add a laboratory computer</h3>
                <p class="muted-text">Add a workstation to the active lab inventory.</p>
            </div>
        </div>
        <form method="POST" action="<?= htmlspecialchars($baseUrl . '/admin/computers', ENT_QUOTES, 'UTF-8') ?>" data-validate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label for="computer_name">Computer name</label>
                <input type="text" id="computer_name" name="computer_name" maxlength="50" required value="<?= htmlspecialchars($computerName ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. LAB-PC-01">
            </div>

            <div class="form-group">
                <label for="description">Description <span class="muted-text">(optional)</span></label>
                <textarea id="description" name="description" rows="3" placeholder="Specifications or details"><?= htmlspecialchars($description ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="form-actions">
                <button type="button" id="cancel-add-computer" class="button button-secondary" data-toggle-target="add-computer-form" aria-expanded="true">Cancel</button>
                <button type="submit" class="button button-primary">Save computer <i class="fa-solid fa-check" aria-hidden="true"></i></button>
            </div>
        </form>
    </section>

    <section class="student-panel">
        <div class="student-panel-heading">
            <div>
                <h3>All computers</h3>
                <p class="muted-text">Includes inactive machines for audit visibility.</p>
            </div>
            <span class="booking-status status-booked"><?= count($computers) ?> total</span>
        </div>

        <div class="table-tools" data-table-tools data-table-target="admin-computers-table">
            <label class="table-search-wrap">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <span class="sr-only">Search computers</span>
                <input class="table-search" type="search" data-table-search placeholder="Search computers">
            </label>
            <div class="filter-tabs" role="group" aria-label="Filter computers by status">
                <button type="button" class="is-active" data-filter-value="all" aria-pressed="true">All</button>
                <button type="button" data-filter-value="active" aria-pressed="false">Active</button>
                <button type="button" data-filter-value="inactive" aria-pressed="false">Inactive</button>
            </div>
            <div class="table-tool-actions">
                <button type="button" class="button button-secondary button-small" data-table-export="admin-computers-table" data-filename="lab-computers"><i class="fa-solid fa-download" aria-hidden="true"></i> Export CSV</button>
                <label class="sr-only" for="computer-page-size">Rows per page</label>
                <select class="table-page-size" id="computer-page-size" data-table-page-size aria-label="Rows per page">
                    <option value="8">8 rows</option><option value="15">15 rows</option><option value="30">30 rows</option>
                </select>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="admin-table" id="admin-computers-table">
                <thead>
                    <tr>
                        <th data-sortable>ID</th>
                        <th data-sortable>Computer name</th>
                        <th data-sortable>Description</th>
                        <th data-sortable>Status</th>
                        <th data-sortable>Created at</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($computers === []): ?>
                        <tr class="empty-row"><td colspan="5" class="empty-state">No computers have been added yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($computers as $computer): ?>
                            <tr data-status="<?= $computer['is_active'] ? 'active' : 'inactive' ?>">
                                <td data-label="ID"><?= htmlspecialchars((string) $computer['computer_id'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Computer name"><strong><?= htmlspecialchars((string) $computer['computer_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td data-label="Description"><?= htmlspecialchars((string) ($computer['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Status">
                                    <span class="<?= $computer['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                        <i class="fa-solid fa-circle" aria-hidden="true"></i> <?= $computer['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td data-label="Created at"><?= htmlspecialchars((string) $computer['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <p class="empty-state" data-table-empty hidden>No computers match your search.</p>
        </div>
        <div class="table-pagination" data-table-pagination></div>
    </section>
</section>
