<?php

declare(strict_types=1);

$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$baseUrl = rtrim(APP_BASE_URL, '/');
$bookings = is_array($bookings ?? null) ? $bookings : [];
$bookingNow = new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE));
$today = $bookingNow->format('Y-m-d');
$currentTime = $bookingNow->format('H:i:s');

?>
<section class="student-page">
    <div class="student-page-heading">
        <div>
            <p class="eyebrow">YOUR RESERVATIONS</p>
            <h2>My bookings.</h2>
            <p>Keep track of your sessions, past visits, and any changes.</p>
        </div>
        <a class="button button-primary" href="<?= $escape($baseUrl . '/new-booking.php') ?>"><i class="fa-solid fa-plus" aria-hidden="true"></i> New booking</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" role="alert" data-toast="error"><?= $escape($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success" role="status" data-toast="success"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><?= $escape($success) ?></div>
    <?php endif; ?>

    <section class="student-panel" data-reveal>
        <?php if ($bookings === []): ?>
            <div class="student-empty">
                <span class="feature-icon" aria-hidden="true"><i class="fa-regular fa-calendar-xmark"></i></span>
                <h3>No bookings yet</h3>
                <p>Once you reserve a computer, your booking details will appear here.</p>
                <p><a class="button button-primary" href="<?= $escape($baseUrl . '/new-booking.php') ?>">Book a computer</a></p>
            </div>
        <?php else: ?>
            <div class="student-panel-heading">
                <div>
                    <h3>Reservation history</h3>
                    <p class="muted-text">Filter, search, or explore bookings on the calendar.</p>
                </div>
                <button class="button button-secondary button-small" type="button" data-toggle-target="booking-calendar" aria-expanded="false">
                    <i class="fa-regular fa-calendar" aria-hidden="true"></i> Calendar
                </button>
            </div>

            <div class="booking-calendar" id="booking-calendar" data-booking-calendar hidden>
                <div class="calendar-header">
                    <button class="icon-button" type="button" data-calendar-shift="-1" aria-label="Previous month"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                    <strong data-calendar-month></strong>
                    <button class="icon-button" type="button" data-calendar-shift="1" aria-label="Next month"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                </div>
                <div class="calendar-grid" data-calendar-grid role="grid" aria-label="Bookings calendar"></div>
            </div>

            <div class="table-tools" data-table-tools data-table-target="student-bookings-table" data-today="<?= $escape($today) ?>">
                <label class="table-search-wrap">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <span class="sr-only">Search bookings</span>
                    <input class="table-search" type="search" data-table-search placeholder="Search computer, date, status">
                </label>
                <div class="filter-tabs" role="group" aria-label="Filter bookings">
                    <button type="button" class="is-active" data-filter-value="all" aria-pressed="true">All</button>
                    <button type="button" data-filter-value="upcoming" aria-pressed="false">Upcoming</button>
                    <button type="button" data-filter-value="past" aria-pressed="false">Past</button>
                    <button type="button" data-filter-value="expired" aria-pressed="false">Expired</button>
                    <button type="button" data-filter-value="cancelled" aria-pressed="false">Cancelled</button>
                </div>
                <label class="sr-only" for="booking-page-size">Rows per page</label>
                <select class="table-page-size" id="booking-page-size" data-table-page-size aria-label="Rows per page">
                    <option value="8">8 rows</option>
                    <option value="15">15 rows</option>
                    <option value="30">30 rows</option>
                </select>
            </div>

            <div class="student-table-wrap">
                <table class="student-table" id="student-bookings-table">
                    <thead>
                        <tr>
                            <th data-sortable>Computer</th>
                            <th data-sortable>Date</th>
                            <th data-sortable>Time slot</th>
                            <th data-sortable>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <?php
                            $isExpired = $booking['status'] === 'booked'
                                && (
                                    (string) $booking['booking_date'] < $today
                                    || (
                                        (string) $booking['booking_date'] === $today
                                        && substr((string) $booking['end_time'], 0, 8) <= $currentTime
                                    )
                                );
                            $displayStatus = $isExpired ? 'expired' : (string) $booking['status'];
                            ?>
                            <tr data-calendar-item data-calendar-date="<?= $escape($booking['booking_date']) ?>" data-booking-date="<?= $escape($booking['booking_date']) ?>" data-status="<?= $escape($booking['status']) ?>" data-display-status="<?= $escape($displayStatus) ?>">
                                <td data-label="Computer"><strong><?= $escape($booking['computer_name']) ?></strong></td>
                                <td data-label="Date"><?= $escape(date('d M Y', strtotime($booking['booking_date']))) ?></td>
                                <td data-label="Time slot"><?= $escape(substr($booking['start_time'], 0, 5)) ?> &ndash; <?= $escape(substr($booking['end_time'], 0, 5)) ?></td>
                                <td data-label="Status"><span class="booking-status status-<?= $escape($displayStatus) ?>"><?= $escape(ucfirst($displayStatus)) ?></span></td>
                                <td data-label="Action">
                                    <?php if (in_array($booking['status'], ['booked', 'waiting'], true) && $booking['booking_date'] >= date('Y-m-d')): ?>
                                        <form method="post" action="<?= $escape($baseUrl . '/my-bookings.php') ?>" data-confirm-message="Cancel this booking? A temporary multi-slot hold is released as a group.">
                                            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="booking_id" value="<?= (int) $booking['booking_id'] ?>">
                                            <button type="submit" class="button button-danger button-small"><i class="fa-regular fa-circle-xmark" aria-hidden="true"></i> Cancel</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="muted-text">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="empty-state" data-table-empty hidden>No bookings match your filters.</p>
            </div>
            <div class="table-pagination" data-table-pagination></div>
        <?php endif; ?>
    </section>

    <p class="student-back-link"><a href="<?= $escape($baseUrl . '/dashboard.php') ?>"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to dashboard</a></p>
</section>
