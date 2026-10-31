<?php

declare(strict_types=1);

// Dashboard values are optional-safe so the page can render if a controller omits an empty collection.
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$baseUrl = rtrim(APP_BASE_URL, '/');
$stats = is_array($stats ?? null) ? $stats : [];
$upcomingBookings = is_array($upcomingBookings ?? null) ? $upcomingBookings : [];

?>
<!-- Summary counts and upcoming rows are separate: only confirmed sessions appear in the short list. -->
<section class="student-page">
    <div class="student-page-heading">
        <div>
            <p class="eyebrow">STUDENT PORTAL</p>
            <h2>Welcome, <?= $escape($_SESSION['user']['name'] ?? 'Student') ?>!</h2>
            <p>Manage your laboratory computer reservations from one place.</p>
        </div>
        <a class="button button-primary" href="<?= $escape($baseUrl . '/new-booking.php') ?>">+ New Booking</a>
    </div>

    <div class="student-stat-grid">
        <article class="student-stat-card">
            <span>Upcoming bookings</span>
            <strong><?= (int) ($stats['upcoming'] ?? 0) ?></strong>
            <small>Confirmed from today onwards</small>
        </article>
        <article class="student-stat-card">
            <span>Total confirmed</span>
            <strong><?= (int) ($stats['total_booked'] ?? 0) ?></strong>
            <small>All confirmed reservations</small>
        </article>
        <article class="student-stat-card">
            <span>Cancelled</span>
            <strong><?= (int) ($stats['cancelled'] ?? 0) ?></strong>
            <small>Reservations cancelled</small>
        </article>
    </div>

    <section class="student-panel">
        <div class="student-panel-heading" style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h3>Upcoming bookings</h3>
                <p class="muted-text">Your next confirmed laboratory sessions.</p>
            </div>
            <a href="<?= $escape($baseUrl . '/my-bookings.php') ?>">View all bookings &rarr;</a>
        </div>

        <?php if ($upcomingBookings === []): ?>
            <div class="student-empty">
                <h4>No upcoming bookings</h4>
                <p>You haven't booked a computer yet. Choose a date and reserve an available slot.</p>
                <p style="margin-top:12px;"><a class="button button-primary" href="<?= $escape($baseUrl . '/new-booking.php') ?>">Make a booking</a></p>
            </div>
        <?php else: ?>
            <div class="student-booking-list">
                <?php foreach ($upcomingBookings as $booking): ?>
                    <article class="student-booking-row">
                        <div class="booking-date-tile">
                            <strong><?= $escape(date('d', strtotime($booking['booking_date']))) ?></strong>
                            <span><?= $escape(date('M', strtotime($booking['booking_date']))) ?></span>
                        </div>
                        <div class="booking-main">
                            <h4><?= $escape($booking['computer_name']) ?></h4>
                            <p class="muted-text">
                                <?= $escape(date('l, d M Y', strtotime($booking['booking_date']))) ?> &middot;
                                <?= $escape(substr($booking['start_time'], 0, 5)) ?> &ndash; <?= $escape(substr($booking['end_time'], 0, 5)) ?>
                            </p>
                        </div>
                        <span class="booking-status status-booked">Booked</span>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <div class="student-quick-links">
        <a class="student-quick-link" href="<?= $escape($baseUrl . '/availability') ?>">
            <span class="quick-link-icon">&boxbox;</span>
            <span>
                <strong>Check availability</strong><br>
                <small class="muted-text">See which computers are free</small>
            </span>
        </a>
        <a class="student-quick-link" href="<?= $escape($baseUrl . '/my-bookings.php') ?>">
            <span class="quick-link-icon">&equiv;</span>
            <span>
                <strong>My bookings</strong><br>
                <small class="muted-text">Review or cancel a reservation</small>
            </span>
        </a>
    </div>
</section>
