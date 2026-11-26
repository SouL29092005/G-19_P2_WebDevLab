<?php

declare(strict_types=1);

$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$baseUrl = rtrim(APP_BASE_URL, '/');
$bookingGroup = is_array($bookingGroup ?? null) ? $bookingGroup : [];
$firstBooking = $bookingGroup[0] ?? null;

?>
<section class="student-page">
    <div class="student-page-heading">
        <div>
            <p class="eyebrow">ALMOST THERE</p>
            <h2>Review your reservation.</h2>
            <p>Check the details below, then confirm before your temporary hold expires.</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" role="alert" data-toast="error"><?= $escape($error) ?></div>
    <?php endif; ?>

    <?php if ($firstBooking !== null): ?>
        <section class="student-panel confirmation-panel" data-reveal>
            <div class="confirmation-success-mark" aria-hidden="true"><i class="fa-solid fa-hourglass-half"></i></div>
            <div class="section-intro">
                <p class="eyebrow">TEMPORARY HOLD</p>
                <h2>Your seat is held for you.</h2>
                <p>Confirm the booking to finish reserving this computer.</p>
            </div>

            <dl class="confirmation-details">
                <div>
                    <dt>Computer</dt>
                    <dd><?= $escape($firstBooking['computer_name']) ?></dd>
                </div>
                <div>
                    <dt>Booking date</dt>
                    <dd><?= $escape(date('l, d M Y', strtotime($firstBooking['booking_date']))) ?></dd>
                </div>
                <div>
                    <dt>Reservation status</dt>
                    <dd><span class="booking-status status-waiting"><i class="fa-regular fa-clock" aria-hidden="true"></i> Waiting for confirmation</span></dd>
                </div>
                <div>
                    <dt>Hold time remaining</dt>
                    <dd><span id="hold-countdown" data-countdown="<?= (int) $remainingSeconds ?>"></span></dd>
                </div>
            </dl>

            <h3>Selected time slots</h3>
            <div class="student-table-wrap">
                <table class="student-table">
                    <thead>
                        <tr><th>Start</th><th>End</th><th>Duration</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookingGroup as $booking): ?>
                            <tr>
                                <td data-label="Start"><?= $escape(substr((string) $booking['start_time'], 0, 5)) ?></td>
                                <td data-label="End"><?= $escape(substr((string) $booking['end_time'], 0, 5)) ?></td>
                                <td data-label="Duration">30 minutes</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="confirmation-actions">
                <form method="post" action="<?= $escape($baseUrl . '/confirm-booking.php') ?>">
                    <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                    <input type="hidden" name="group_id" value="<?= $escape($groupId ?? '') ?>">
                    <input type="hidden" name="action" value="confirm">
                    <button type="submit" id="confirm-booking-button" class="button button-primary">Confirm booking <i class="fa-solid fa-check" aria-hidden="true"></i></button>
                </form>

                <form method="post" action="<?= $escape($baseUrl . '/confirm-booking.php') ?>" data-confirm-message="Release this temporary hold and return to booking?">
                    <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                    <input type="hidden" name="group_id" value="<?= $escape($groupId ?? '') ?>">
                    <input type="hidden" name="action" value="cancel">
                    <button type="submit" class="button button-secondary">Cancel hold</button>
                </form>
            </div>
        </section>
    <?php else: ?>
        <div class="student-panel empty-state">
            <span class="feature-icon" aria-hidden="true"><i class="fa-regular fa-hourglass"></i></span>
            <h3>This reservation is no longer available</h3>
            <p>Return to booking and choose a new time slot.</p>
            <a class="button button-primary" href="<?= $escape($baseUrl . '/new-booking.php') ?>">Return to New Booking</a>
        </div>
    <?php endif; ?>
</section>
