<?php

declare(strict_types=1);

$baseUrl = rtrim(APP_BASE_URL, '/');

?>
<section class="admin-dashboard">
    <h2>Admin Dashboard</h2>
    <p>Welcome to the Laboratory Computer Reservation System administration panel.</p>

    <nav class="admin-navigation">
        <div class="admin-action-grid">
            <a href="<?= htmlspecialchars($baseUrl . '/admin/computers', ENT_QUOTES, 'UTF-8') ?>" class="admin-action-card">
                <h3>Manage Computers</h3>
                <p>View, add, and manage laboratory computer accounts.</p>
            </a>

            <a href="<?= htmlspecialchars($baseUrl . '/admin/users', ENT_QUOTES, 'UTF-8') ?>" class="admin-action-card">
                <h3>Manage Users</h3>
                <p>View, create, and manage user accounts.</p>
            </a>

            <a href="<?= htmlspecialchars($baseUrl . '/admin/bookings', ENT_QUOTES, 'UTF-8') ?>" class="admin-action-card">
                <h3>Show Bookings</h3>
                <p>View all student booking records and their statuses.</p>
            </a>
        </div>
    </nav>
</section>
