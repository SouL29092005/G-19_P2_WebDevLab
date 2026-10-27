<?php

declare(strict_types=1);

$escape = static fn($value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$baseUrl = rtrim(APP_BASE_URL, '/');
$bookingGroup = is_array($bookingGroup ?? null) ? $bookingGroup : [];
$firstBooking = $bookingGroup[0] ?? null;

?>

<section class="student-page">
    <div class="student-page-heading">
        <div>
            <p class="eyebrow">STUDENT PORTAL</p>
            <h2>Confirm Booking</h2>
            <p>Review the temporary reservation before confirming it.</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" role="alert">
            <?= $escape($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($firstBooking !== null): ?>
        <section class="student-panel confirmation-panel">
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
                    <dd><span class="booking-status status-waiting">Waiting</span></dd>
                </div>
                <div>
                    <dt>Hold time remaining</dt>
                    <dd><span id="hold-countdown" data-seconds="<?= (int) $remainingSeconds ?>"></span></dd>
                </div>
            </dl>

            <h3>Selected time slots</h3>
            <div class="student-table-wrap">
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>Start</th>
                            <th>End</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookingGroup as $booking): ?>
                            <tr>
                                <td><?= $escape(substr((string) $booking['start_time'], 0, 5)) ?></td>
                                <td><?= $escape(substr((string) $booking['end_time'], 0, 5)) ?></td>
                                <td>30 minutes</td>
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
                    <button type="submit" id="confirm-booking-button" class="button button-primary">Confirm Booking</button>
                </form>

                <form method="post" action="<?= $escape($baseUrl . '/confirm-booking.php') ?>">
                    <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                    <input type="hidden" name="group_id" value="<?= $escape($groupId ?? '') ?>">
                    <input type="hidden" name="action" value="cancel">
                    <button type="submit" class="button button-secondary">Cancel and Go Back</button>
                </form>
            </div>
        </section>
    <?php else: ?>
        <p class="student-back-link">
            <a href="<?= $escape($baseUrl . '/new-booking.php') ?>">Return to New Booking</a>
        </p>
    <?php endif; ?>
</section>

<?php if ($firstBooking !== null): ?>
    <script>
        (function () {
            const counter = document.getElementById('hold-countdown');
            let seconds = Number(counter.dataset.seconds || 0);

            function render() {
                const minutes = Math.floor(seconds / 60);
                const remainder = seconds % 60;
                counter.textContent = minutes + ':' + String(remainder).padStart(2, '0');
                if (seconds > 0) {
                    seconds -= 1;
                    window.setTimeout(render, 1000);
                } else {
                    counter.textContent = 'Expired';
                    document.getElementById('confirm-booking-button').disabled = true;
                }
            }

            render();
        })();
    </script>
<?php endif; ?>