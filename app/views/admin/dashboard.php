<?php

declare(strict_types=1);

?>

<section class="admin-dashboard">
    <h2>Admin Dashboard</h2>

    <p>
        Welcome to the Laboratory Computer Reservation System
        administration panel.
    </p>

    <p>
        Admin authorization is active.
    </p>

    <nav class="admin-navigation">
        <h3>Manage System</h3>

        <div class="admin-action-grid">
            <a
                href="<?= htmlspecialchars(
                    rtrim(APP_BASE_URL, '/') . '/admin/computers',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                class="admin-action-card"
            >
                <h3>Manage Computers</h3>
                <p>View and manage laboratory computer accounts.</p>
            </a>

            <a
                href="<?= htmlspecialchars(
                    rtrim(APP_BASE_URL, '/') . '/admin/users',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                class="admin-action-card"
            >
                <h3>Manage Users</h3>
                <p>View, create, and manage user accounts.</p>
            </a>
        </div>
    </nav>
</section>
