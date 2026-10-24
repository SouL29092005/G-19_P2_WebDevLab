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

        <ul>
            <li>
                <a href="<?= htmlspecialchars(
                    rtrim(APP_BASE_URL, '/') . '/admin/computers',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>">
                    Manage Computers
                </a>
            </li>
        </ul>
    </nav>
</section>
