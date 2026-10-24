<?php

declare(strict_types=1);

$escape = static fn($value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$availableCombinations = isset($availableCombinations) && is_array($availableCombinations)
    ? $availableCombinations
    : [];

?>

<section class="student-page">

    <div class="student-page-heading">
        <div>
            <p class="eyebrow">STUDENT PORTAL</p>
            <h2>New Booking</h2>
            <p>
                Select a date and an available computer time slot.
            </p>
        </div>

        <a
            class="button button-secondary"
            href="<?= $escape(rtrim(APP_BASE_URL, '/') . '/my-bookings.php') ?>"
        >
            My Bookings
        </a>
    </div>

    <?php if (!empty($error)): ?>

        <div class="alert alert-error" role="alert">
            <?= $escape($error) ?>
        </div>

    <?php endif; ?>

    <section class="student-panel booking-form-panel">

        <form
            method="get"
            action="<?= $escape(rtrim(APP_BASE_URL, '/') . '/new-booking.php') ?>"
            class="booking-date-form"
        >
            <div class="form-group">
                <label for="booking-date">Booking date</label>

                <input
                    type="date"
                    id="booking-date"
                    name="date"
                    value="<?= $escape($selectedDate) ?>"
                    min="<?= $escape($minDate) ?>"
                    max="<?= $escape($maxDate) ?>"
                    required
                >
            </div>

            <button type="submit" class="button button-secondary">
                Show availability
            </button>
        </form>

        <div class="booking-rules">
            <strong>Booking window:</strong>
            today through the next 3 days. Each reservation is
            for one 30-minute slot. Bookings are confirmed immediately.
        </div>

        <?php if ($availableCombinations === []): ?>

            <div class="student-empty">
                <h3>No available slots</h3>

                <p>
                    All active computers are booked for this date.
                    Try another date.
                </p>
            </div>

        <?php else: ?>

            <form
                method="post"
                action="<?= $escape(rtrim(APP_BASE_URL, '/') . '/new-booking.php') ?>"
                class="new-booking-form"
            >
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= $escape($csrfToken) ?>"
                >

                <input
                    type="hidden"
                    name="booking_date"
                    value="<?= $escape($selectedDate) ?>"
                >

                <div class="form-group">
                    <label for="computer-slot">
                        Available computer and time slot
                    </label>

                    <select
                        id="computer-slot"
                        name="computer_slot"
                        required
                    >
                        <option value="">
                            Select an available slot
                        </option>

                        <?php foreach ($availableCombinations as $combination): ?>

                            <option
                                value="<?= (int) $combination['computer_id'] ?>:<?= (int) $combination['slot_id'] ?>"
                            >
                                <?= $escape($combination['computer_name']) ?> —
                                <?= $escape(substr($combination['start_time'], 0, 5)) ?>–<?= $escape(substr($combination['end_time'], 0, 5)) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <small>
                        Only slots currently available for the selected date
                        are listed.
                    </small>
                </div>

                <button type="submit" class="button button-primary">
                    Confirm Booking
                </button>
            </form>

        <?php endif; ?>

    </section>

    <p class="student-back-link">
        <a href="<?= $escape(rtrim(APP_BASE_URL, '/') . '/dashboard.php') ?>">
            ← Back to dashboard
        </a>
    </p>

</section>
