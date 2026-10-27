<?php

declare(strict_types=1);

$escape = static fn($value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$baseUrl = rtrim(APP_BASE_URL, '/');
$availableComputers = is_array($availableComputers ?? null)
    ? $availableComputers
    : [];
$timeSlots = is_array($timeSlots ?? null) ? $timeSlots : [];
$selectedSlotIds = is_array($selectedSlotIds ?? null)
    ? $selectedSlotIds
    : [];
$selectedLookup = array_fill_keys($selectedSlotIds, true);
$bookingToday = (new DateTimeImmutable('today', new DateTimeZone(APP_TIMEZONE)))->format('Y-m-d');
$bookingNow = (new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE)))->format('H:i:s');
$selectedLabels = [];

foreach ($timeSlots as $slot) {
    if (isset($selectedLookup[(int) $slot['slot_id']])) {
        $selectedLabels[] = substr((string) $slot['start_time'], 0, 5)
            . '–'
            . substr((string) $slot['end_time'], 0, 5);
    }
}

?>

<section class="student-page">
    <div class="student-page-heading">
        <div>
            <p class="eyebrow">STUDENT PORTAL</p>
            <h2>New Booking</h2>
            <p>Select one computer that is free for every selected slot.</p>
        </div>

        <a class="button button-secondary" href="<?= $escape($baseUrl . '/my-bookings.php') ?>">
            My Bookings
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

    <section class="student-panel booking-form-panel">
        <form
            method="get"
            action="<?= $escape($baseUrl . '/new-booking.php') ?>"
            class="booking-date-form multi-slot-form"
            id="availability-form"
            data-endpoint="<?= $escape($baseUrl . '/new-booking.php') ?>"
            data-today-date="<?= $escape($bookingToday) ?>"
            data-current-time="<?= $escape($bookingNow) ?>"
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

            <fieldset class="time-slot-picker">
                <legend>Choose time slots</legend>
                <p class="field-hint">Select one or more 30-minute slots. Lab hours are 8:00 AM–10:00 PM.</p>

                <details class="time-slot-dropdown" id="booking-slot-dropdown">
                    <summary class="time-slot-dropdown-summary">
                        <span id="booking-slot-summary"><?= count($selectedSlotIds) === 0 ? 'Choose time slots' : count($selectedSlotIds) . ' time slot' . (count($selectedSlotIds) === 1 ? '' : 's') . ' selected' ?></span>
                        <span aria-hidden="true">⌄</span>
                    </summary>
                    <div class="time-slot-menu time-slot-options">
                    <?php foreach ($timeSlots as $slot): ?>
                        <?php
                        $slotId = (int) $slot['slot_id'];
                        $start = substr((string) $slot['start_time'], 0, 5);
                        $end = substr((string) $slot['end_time'], 0, 5);
                        $past = $selectedDate === $bookingToday
                            && (string) $slot['start_time'] <= $bookingNow;
                        ?>
                        <label class="time-slot-option<?= isset($selectedLookup[$slotId]) ? ' is-selected' : '' ?><?= $past ? ' is-past' : '' ?>">
                            <input
                                type="checkbox"
                                name="slots[]"
                                value="<?= $slotId ?>"
                                data-start-time="<?= $escape((string) $slot['start_time']) ?>"
                                <?= isset($selectedLookup[$slotId]) ? 'checked' : '' ?>
                                <?= $past ? 'disabled' : '' ?>
                            >
                            <span><?= $escape($start) ?>–<?= $escape($end) ?></span>
                        </label>
                    <?php endforeach; ?>
                    </div>
                </details>
            </fieldset>

            <div class="booking-selection-summary" aria-live="polite">
                <strong id="selected-slot-count"><?= count($selectedSlotIds) ?></strong>
                <span>time slots selected</span>
            </div>

            <button type="submit" class="button button-secondary">
                Find computers
            </button>
        </form>

        <div class="booking-rules">
            <strong>Booking window:</strong> today through the next 3 days.
            Your selected slots are held together for 2 minutes after you continue.
        </div>

        <section class="availability-results" aria-live="polite">
            <div class="student-panel-heading">
                <div>
                    <h3>Computers available for all selected slots</h3>
                    <p id="availability-summary">
                        <?php if ($selectedSlotIds === []): ?>
                            Select one or more slots to see matching computers.
                        <?php else: ?>
                            <?= count($availableComputers) ?> computer<?= count($availableComputers) === 1 ? '' : 's' ?> available across <?= count($selectedSlotIds) ?> selected slot<?= count($selectedSlotIds) === 1 ? '' : 's' ?>.
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div id="availability-message" class="student-empty booking-prompt" <?= $selectedSlotIds !== [] && $availableComputers !== [] ? 'hidden' : '' ?>>
                <?php if ($selectedSlotIds === []): ?>
                    <h4>Select your time slots</h4>
                    <p>Available computers will appear here as you update your selection.</p>
                <?php elseif ($availableComputers === []): ?>
                    <h4>No computer is free for every selected slot</h4>
                    <p>Change your date or remove a slot to see other computers.</p>
                <?php endif; ?>
            </div>

            <form
                method="post"
                action="<?= $escape($baseUrl . '/new-booking.php') ?>"
                id="hold-form"
                class="new-booking-form"
            >
                <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                <input type="hidden" name="action" value="hold">
                <input type="hidden" name="booking_date" id="hold-date" value="<?= $escape($selectedDate) ?>">
                <div id="hold-slot-ids">
                    <?php foreach ($selectedSlotIds as $slotId): ?>
                        <input type="hidden" name="slot_ids[]" value="<?= (int) $slotId ?>">
                    <?php endforeach; ?>
                </div>

                <div id="available-computers" class="available-computer-grid">
                    <?php foreach ($availableComputers as $computer): ?>
                        <label class="available-computer-option">
                            <input type="radio" name="computer_id" value="<?= (int) $computer['computer_id'] ?>" required>
                            <span>
                                <strong><?= $escape($computer['computer_name']) ?></strong>
                                <?php if (!empty($computer['description'])): ?>
                                    <small><?= $escape($computer['description']) ?></small>
                                <?php endif; ?>
                                <small>Available for: <?= $escape(implode(', ', $selectedLabels)) ?></small>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <button
                    type="submit"
                    id="continue-booking"
                    class="button button-primary"
                    <?= $availableComputers === [] ? 'disabled' : '' ?>
                >
                    Continue to Confirmation
                </button>
            </form>
        </section>
    </section>

    <p class="student-back-link">
        <a href="<?= $escape($baseUrl . '/dashboard.php') ?>">← Back to dashboard</a>
    </p>
</section>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= $escape($baseUrl . '/assets/js/new-booking.js') ?>" defer></script>
