<?php

declare(strict_types=1);

$escape = static fn($value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$stats = is_array($stats ?? null) ? $stats : [];

$upcomingBookings = is_array($upcomingBookings ?? null)
    ? $upcomingBookings
    : [];

?>

<section class="student-page">

    <div class="student-page-heading">
        <div>
            <p class="eyebrow">STUDENT PORTAL</p>

            <h2>
                Welcome, <?= $escape($_SESSION['user']['name'] ?? 'Student') ?>!
            </h2>

            <p>
                Manage your laboratory computer reservations from one place.
            </p>
        </div>

        <a
            class="button button-primary"
            href="<?= $escape(rtrim(APP_BASE_URL, '/') . '/new-booking.php') ?>"
        >
            + New Booking
        </a>
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

        <div class="student-panel-heading">
            <div>
                <h3>Upcoming bookings</h3>
                <p>Your next confirmed laboratory sessions.</p>
            </div>

            <a href="<?= $escape(rtrim(APP_BASE_URL, '/') . '/my-bookings.php') ?>">
                View all bookings →
            </a>
        </div>

        <?php if ($upcomingBookings === []): ?>

            <div class="student-empty">
                <h4>No upcoming bookings</h4>

                <p>
                    You haven't booked a computer yet.
                    Choose a date and reserve an available slot.
                </p>

                <a
                    class="button button-primary"
                    href="<?= $escape(rtrim(APP_BASE_URL, '/') . '/new-booking.php') ?>"
                >
                    Make a booking
                </a>
            </div>

        <?php else: ?>

            <div class="student-booking-list">

                <?php foreach ($upcomingBookings as $booking): ?>

                    <article class="student-booking-row">

                        <div class="booking-date-tile">
                            <strong>
                                <?= $escape(date(
                                    'd',
                                    strtotime($booking['booking_date'])
                                )) ?>
                            </strong>

                            <span>
                                <?= $escape(date(
                                    'M',
                                    strtotime($booking['booking_date'])
                                )) ?>
                            </span>
                        </div>

                        <div class="booking-main">
                            <h4>
                                <?= $escape($booking['computer_name']) ?>
                            </h4>

                            <p>
                                <?= $escape(date(
                                    'l, d M Y',
                                    strtotime($booking['booking_date'])
                                )) ?>

                                ·

                                <?= $escape(substr($booking['start_time'], 0, 5)) ?>
                                –
                                <?= $escape(substr($booking['end_time'], 0, 5)) ?>
                            </p>
                        </div>

                        <span class="booking-status status-booked">
                            Booked
                        </span>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

    <div class="student-quick-links">

        <a
            class="student-quick-link"
            href="<?= $escape(rtrim(APP_BASE_URL, '/') . '/availability') ?>"
        >
            <span class="quick-link-icon">⌕</span>

            <span>
                <strong>Check availability</strong>
                <small>See which computers are free</small>
            </span>

            <span>→</span>
        </a>

        <a
            class="student-quick-link"
            href="<?= $escape(rtrim(APP_BASE_URL, '/') . '/my-bookings.php') ?>"
        >
            <span class="quick-link-icon">▤</span>

            <span>
                <strong>My bookings</strong>
                <small>Review or cancel a reservation</small>
            </span>

            <span>→</span>
        </a>

    </div>

</section>
