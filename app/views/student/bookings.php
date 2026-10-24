<?php

declare(strict_types=1);

$escape = static fn($value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$bookings = is_array($bookings ?? null) ? $bookings : [];

?>

<section class="student-page">

    <div class="student-page-heading">
        <div>
            <p class="eyebrow">STUDENT PORTAL</p>
            <h2>My Bookings</h2>
            <p>
                Review your reservations and cancel bookings you no longer need.
            </p>
        </div>

        <a
            class="button button-primary"
            href="<?= $escape(rtrim(APP_BASE_URL, '/') . '/new-booking.php') ?>"
        >
            + New Booking
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" role="alert">
            <?= $escape($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success" role="status">
            <?= $escape($success) ?>
        </div>
    <?php endif; ?>

    <section class="student-panel">

        <?php if ($bookings === []): ?>

            <div class="student-empty">
                <h3>No bookings yet</h3>

                <p>
                    Once you reserve a computer, your booking details will appear here.
                </p>

                <a
                    class="button button-primary"
                    href="<?= $escape(rtrim(APP_BASE_URL, '/') . '/new-booking.php') ?>"
                >
                    Book a computer
                </a>
            </div>

        <?php else: ?>

            <div class="student-table-wrap">
                <table class="student-table">

                    <thead>
                        <tr>
                            <th>Computer</th>
                            <th>Date</th>
                            <th>Time slot</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($bookings as $booking): ?>

                            <tr>
                                <td>
                                    <strong>
                                        <?= $escape($booking['computer_name']) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= $escape(date(
                                        'd M Y',
                                        strtotime($booking['booking_date'])
                                    )) ?>
                                </td>

                                <td>
                                    <?= $escape(substr($booking['start_time'], 0, 5)) ?>
                                    –
                                    <?= $escape(substr($booking['end_time'], 0, 5)) ?>
                                </td>

                                <td>
                                    <span class="booking-status status-<?= $escape($booking['status']) ?>">
                                        <?= $escape(ucfirst($booking['status'])) ?>
                                    </span>
                                </td>

                                <td>

                                    <?php
                                    if (
                                        in_array(
                                            $booking['status'],
                                            ['booked', 'waiting'],
                                            true
                                        )
                                        && $booking['booking_date'] >= date('Y-m-d')
                                    ):
                                    ?>

                                        <form
                                            method="post"
                                            onsubmit="return confirm('Cancel this booking?');"
                                            action="<?= $escape(rtrim(APP_BASE_URL, '/') . '/my-bookings.php') ?>"
                                        >
                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= $escape($csrfToken) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="cancel"
                                            >

                                            <input
                                                type="hidden"
                                                name="booking_id"
                                                value="<?= (int) $booking['booking_id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="button button-danger button-small"
                                            >
                                                Cancel
                                            </button>
                                        </form>

                                    <?php else: ?>

                                        <span class="muted-text">—</span>

                                    <?php endif; ?>

                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>
            </div>

        <?php endif; ?>

    </section>

    <p class="student-back-link">
        <a href="<?= $escape(rtrim(APP_BASE_URL, '/') . '/dashboard.php') ?>">
            ← Back to dashboard
        </a>
    </p>

</section>
