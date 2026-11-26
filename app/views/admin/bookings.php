<?php

declare(strict_types=1);

$baseUrl = rtrim(APP_BASE_URL, '/');
$bookings = is_array($bookings ?? null) ? $bookings : [];
$bookingNow = new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE));
$today = $bookingNow->format('Y-m-d');
$currentTime = $bookingNow->format('H:i:s');

?>
<section class="admin-dashboard">
    <div class="admin-page-heading">
        <div>
            <p class="eyebrow">ADMINISTRATION</p>
            <h2>Reservation ledger.</h2>
            <p>Review every student booking and its current status.</p>
        </div>
        <a href="<?= htmlspecialchars($baseUrl . '/admin', ENT_QUOTES, 'UTF-8') ?>" class="button button-secondary"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Dashboard</a>
    </div>

    <section class="student-panel">
        <div class="student-panel-heading">
            <div>
                <h3>All bookings</h3>
                <p class="muted-text">Search and sort the complete reservation record.</p>
            </div>
            <span class="booking-status status-booked"><?= count($bookings) ?> records</span>
        </div>

        <?php if ($bookings === []): ?>
            <div class="empty-state">
                <span class="feature-icon" aria-hidden="true"><i class="fa-regular fa-calendar"></i></span>
                <h3>No bookings found</h3>
                <p>Reservations will appear here once students book a workstation.</p>
            </div>
        <?php else: ?>
            <div class="table-tools" data-table-tools data-table-target="admin-bookings-table">
                <label class="table-search-wrap">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <span class="sr-only">Search bookings</span>
                    <input class="table-search" type="search" data-table-search placeholder="Search student, computer, status">
                </label>
                <div class="filter-tabs" role="group" aria-label="Filter bookings by status">
                    <button type="button" class="is-active" data-filter-value="all" aria-pressed="true">All</button>
                    <button type="button" data-filter-value="booked" aria-pressed="false">Booked</button>
                    <button type="button" data-filter-value="expired" aria-pressed="false">Expired</button>
                    <button type="button" data-filter-value="waiting" aria-pressed="false">Waiting</button>
                    <button type="button" data-filter-value="cancelled" aria-pressed="false">Cancelled</button>
                </div>
                <div class="table-tool-actions">
                    <button type="button" class="button button-secondary button-small" data-table-export="admin-bookings-table" data-filename="lab-bookings"><i class="fa-solid fa-download" aria-hidden="true"></i> Export CSV</button>
                    <label class="sr-only" for="admin-bookings-page-size">Rows per page</label>
                    <select class="table-page-size" id="admin-bookings-page-size" data-table-page-size aria-label="Rows per page">
                        <option value="8">8 rows</option><option value="15">15 rows</option><option value="30">30 rows</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="admin-table admin-booking-table" id="admin-bookings-table">
                    <thead>
                        <tr>
                            <th data-sortable>ID</th>
                            <th data-sortable>Student</th>
                            <th data-sortable>Email</th>
                            <th data-sortable>Computer</th>
                            <th data-sortable>Date</th>
                            <th data-sortable>Time slot</th>
                            <th data-sortable>Status</th>
                            <th data-sortable>Created at</th>
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
                            <tr data-booking-date="<?= htmlspecialchars((string) $booking['booking_date'], ENT_QUOTES, 'UTF-8') ?>" data-status="<?= htmlspecialchars((string) $booking['status'], ENT_QUOTES, 'UTF-8') ?>" data-display-status="<?= htmlspecialchars($displayStatus, ENT_QUOTES, 'UTF-8') ?>">
                                <td data-label="ID"><?= htmlspecialchars((string) $booking['booking_id'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Student"><strong><?= htmlspecialchars((string) $booking['full_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td data-label="Email"><?= htmlspecialchars((string) $booking['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Computer"><?= htmlspecialchars((string) $booking['computer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Date"><?= htmlspecialchars((string) $booking['booking_date'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td data-label="Time slot">
                                    <?= htmlspecialchars(substr((string) $booking['start_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?>
                                    –
                                    <?= htmlspecialchars(substr((string) $booking['end_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td data-label="Status">
                                    <span class="booking-status status-<?= htmlspecialchars($displayStatus, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($displayStatus), ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td data-label="Created at"><?= htmlspecialchars(substr((string) $booking['created_at'], 0, 16), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="empty-state" data-table-empty hidden>No bookings match your search.</p>
            </div>
            <div class="table-pagination" data-table-pagination></div>
        <?php endif; ?>
    </section>
</section>
