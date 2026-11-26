<?php

declare(strict_types=1);

$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$baseUrl = rtrim(APP_BASE_URL, '/');
$stats = is_array($stats ?? null) ? $stats : [];
$upcomingBookings = is_array($upcomingBookings ?? null) ? $upcomingBookings : [];

?>
<section class="student-page">
    <div class="student-page-heading">
        <div>
            <p class="eyebrow">YOUR WORKSPACE</p>
            <h2>Good to see you, <?= $escape($_SESSION['user']['name'] ?? 'Student') ?>.</h2>
            <p>Here’s what’s coming up in your lab schedule.</p>
        </div>
        <a class="button button-primary" href="<?= $escape($baseUrl . '/new-booking.php') ?>"><i class="fa-solid fa-plus" aria-hidden="true"></i> New booking</a>
    </div>

    <div class="student-stat-grid">
        <article class="student-stat-card" data-reveal>
            <span>Upcoming bookings</span>
            <strong data-count-up="<?= (int) ($stats['upcoming'] ?? 0) ?>"><?= (int) ($stats['upcoming'] ?? 0) ?></strong>
            <small>Confirmed from today onwards</small>
        </article>
        <article class="student-stat-card" data-reveal>
            <span>Total confirmed</span>
            <strong data-count-up="<?= (int) ($stats['total_booked'] ?? 0) ?>"><?= (int) ($stats['total_booked'] ?? 0) ?></strong>
            <small>All confirmed reservations</small>
        </article>
        <article class="student-stat-card" data-reveal>
            <span>Cancelled</span>
            <strong data-count-up="<?= (int) ($stats['cancelled'] ?? 0) ?>"><?= (int) ($stats['cancelled'] ?? 0) ?></strong>
            <small>Reservations cancelled</small>
        </article>
    </div>

    <section class="student-panel" data-reveal>
        <div class="student-panel-heading">
            <div>
                <h3>Your next sessions</h3>
                <p class="muted-text">Upcoming confirmed laboratory bookings.</p>
            </div>
            <a href="<?= $escape($baseUrl . '/my-bookings.php') ?>">View all bookings <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>

        <?php if ($upcomingBookings === []): ?>
            <div class="student-empty">
                <span class="feature-icon" aria-hidden="true"><i class="fa-regular fa-calendar"></i></span>
                <h4>No upcoming bookings</h4>
                <p>You haven’t reserved a computer yet. Choose a time that works for you.</p>
                <p><a class="button button-primary" href="<?= $escape($baseUrl . '/new-booking.php') ?>">Make a booking</a></p>
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
                        <span class="booking-status status-booked"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Booked</span>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <div class="student-quick-links">
        <a class="student-quick-link" href="<?= $escape($baseUrl . '/availability') ?>">
            <span class="quick-link-icon" aria-hidden="true"><i class="fa-solid fa-display"></i></span>
            <span><strong>Check availability</strong><br><small>See which computers are free</small></span>
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </a>
        <a class="student-quick-link" href="<?= $escape($baseUrl . '/my-bookings.php') ?>">
            <span class="quick-link-icon" aria-hidden="true"><i class="fa-regular fa-calendar-check"></i></span>
            <span><strong>My bookings</strong><br><small>Review or cancel a reservation</small></span>
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>
</section>
