<?php

declare(strict_types=1);

$baseUrl = rtrim(APP_BASE_URL, '/');

?>
<section class="admin-dashboard">
    <div class="admin-page-heading">
        <div>
            <p class="eyebrow">LABSPACE CONTROL</p>
            <h2>Good morning, <?= htmlspecialchars((string) ($_SESSION['user']['name'] ?? 'Admin'), ENT_QUOTES, 'UTF-8') ?>.</h2>
            <p>Manage your lab operations from one clear overview.</p>
        </div>
        <a class="button button-primary" href="<?= htmlspecialchars($baseUrl . '/availability', ENT_QUOTES, 'UTF-8') ?>"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> View lab availability</a>
    </div>

    <div class="student-stat-grid">
        <article class="admin-stat-card" data-reveal>
            <span>Reservations</span>
            <strong aria-label="Not available">—</strong>
            <small>Live metric not supplied by this route</small>
        </article>
        <article class="admin-stat-card" data-reveal>
            <span>Active computers</span>
            <strong aria-label="Not available">—</strong>
            <small>Live metric not supplied by this route</small>
        </article>
        <article class="admin-stat-card" data-reveal>
            <span>Student accounts</span>
            <strong aria-label="Not available">—</strong>
            <small>Live metric not supplied by this route</small>
        </article>
    </div>

    <div class="admin-charts">
        <article class="chart-card" data-reveal>
            <h3>Reservation activity</h3>
            <p>Sample weekly trend · not live booking data</p>
            <div class="chart-wrap"><canvas data-sample-chart="activity" role="img" aria-label="Sample weekly reservation trend"></canvas></div>
            <small class="sample-data-note">Demo visualization only. TODO: backend endpoint needed for real reservation trends.</small>
        </article>
        <article class="chart-card" data-reveal>
            <h3>Computer utilization</h3>
            <p>Sample distribution · placeholder stations</p>
            <div class="chart-wrap"><canvas data-sample-chart="usage" role="img" aria-label="Sample computer utilization distribution"></canvas></div>
            <small class="sample-data-note">Demo visualization only. TODO: backend endpoint needed for real computer usage.</small>
        </article>
    </div>

    <section class="admin-panel">
        <div class="student-panel-heading">
            <div>
                <h3>Workspace management</h3>
                <p class="muted-text">Jump to the area you need.</p>
            </div>
        </div>
        <nav class="admin-navigation" aria-label="Administration tools">
            <div class="admin-action-grid">
                <a href="<?= htmlspecialchars($baseUrl . '/admin/computers', ENT_QUOTES, 'UTF-8') ?>" class="admin-action-card">
                    <span class="admin-action-icon" aria-hidden="true"><i class="fa-solid fa-computer"></i></span>
                    <h3>Manage computers</h3>
                    <p>Review and add laboratory workstations.</p>
                </a>
                <a href="<?= htmlspecialchars($baseUrl . '/admin/users', ENT_QUOTES, 'UTF-8') ?>" class="admin-action-card">
                    <span class="admin-action-icon" aria-hidden="true"><i class="fa-solid fa-users"></i></span>
                    <h3>Manage users</h3>
                    <p>Create accounts and manage student access.</p>
                </a>
                <a href="<?= htmlspecialchars($baseUrl . '/admin/bookings', ENT_QUOTES, 'UTF-8') ?>" class="admin-action-card">
                    <span class="admin-action-icon" aria-hidden="true"><i class="fa-regular fa-calendar-check"></i></span>
                    <h3>Review bookings</h3>
                    <p>Search reservation records and statuses.</p>
                </a>
            </div>
        </nav>
    </section>
</section>
