<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['user']['role'])) {
    header(
        'Location: ' . rtrim(APP_BASE_URL, '/') . '/login.php',
        true,
        303
    );

    exit;
}

if ($_SESSION['user']['role'] !== 'student') {
    header(
        'Location: ' . rtrim(APP_BASE_URL, '/') . '/admin/',
        true,
        303
    );

    exit;
}

require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/controllers/StudentController.php';

try {
    $controller = new StudentController(
        new BookingRepository($pdo)
    );

    $data = $controller->newBooking();

    extract($data, EXTR_SKIP);
    $pageTitle = $data['pageTitle'];
} catch (Throwable $e) {
    error_log('New booking failed: ' . $e->getMessage());

    http_response_code(500);
    $error = 'Booking is unavailable right now. Please try again later.';
    $selectedSlotIds = [];
    $timeSlots = [];
    $computersBySlot = [];
    $selectedDate = date('Y-m-d');
    $minDate = date('Y-m-d');
    $maxDate = date('Y-m-d', strtotime('+3 days'));
    $csrfToken = '';
}

$escape = static fn($value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$selectedSlotIds = is_array($selectedSlotIds ?? null)
    ? $selectedSlotIds
    : [];
$selectedSlotLookup = array_fill_keys($selectedSlotIds, true);
$postedComputers = is_array($_POST['computer_for_slot'] ?? null)
    ? $_POST['computer_for_slot']
    : [];

$baseUrl = rtrim(APP_BASE_URL, '/');

require APP_PATH . '/views/layouts/header.php';

?>
<section class="student-page">
    <div class="student-page-heading">
        <div>
            <p class="eyebrow">STUDENT PORTAL</p>
            <h2>New Booking</h2>
            <p>
                Choose a date, select one or more 30-minute
                time slots, then choose an available computer
                for each slot.
            </p>
        </div>

        <a
            class="button button-secondary"
            href="<?= $escape($baseUrl . '/my-bookings.php') ?>"
        >
            My Bookings
        </a>
    </div>

    <?php if ($error !== null): ?>
        <div class="alert alert-error" role="alert">
            <?= $escape($error) ?>
        </div>
    <?php endif; ?>

    <section class="student-panel booking-form-panel">
        <form
            method="get"
            action="<?= $escape($baseUrl . '/new-booking.php') ?>"
            class="booking-date-form booking-search-form"
        >
            <div class="form-group">
                <label for="booking-date">
                    1. Select booking date
                </label>

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
                <legend>2. Select time slots</legend>

                <p class="field-hint">
                    Select as many 30-minute slots as you need.
                    Lab hours are 8:00 AM–10:00 PM.
                </p>

                <details class="time-slot-dropdown">
                    <summary class="time-slot-dropdown-summary">
                        <span>
                            <?= count($selectedSlotIds) === 0
                                ? 'Choose time slots'
                                : count($selectedSlotIds) . ' time slot' . (count($selectedSlotIds) === 1 ? '' : 's') . ' selected' ?>
                        </span>
                        <span aria-hidden="true">⌄</span>
                    </summary>

                    <div class="time-slot-menu">
                        <?php foreach ($timeSlots as $slot): ?>
                            <?php
                            $slotId = (int) $slot['slot_id'];
                            $slotStart = substr((string) $slot['start_time'], 0, 5);
                            $slotEnd = substr((string) $slot['end_time'], 0, 5);
                            $isSelected = isset($selectedSlotLookup[$slotId]);
                            $isPastSlot = $selectedDate === date('Y-m-d')
                                && $slotStart <= date('H:i');
                            ?>

                            <label class="time-slot-option<?= $isSelected ? ' is-selected' : '' ?><?= $isPastSlot ? ' is-past' : '' ?>">
                                <input
                                    type="checkbox"
                                    name="slots[]"
                                    value="<?= $slotId ?>"
                                    <?= $isSelected ? 'checked' : '' ?>
                                    <?= $isPastSlot ? 'disabled' : '' ?>
                                >
                                <span><?= $escape($slotStart) ?>–<?= $escape($slotEnd) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </details>
            </fieldset>

            <button
                type="submit"
                class="button button-primary"
            >
                Find Available Computers
            </button>
        </form>

        <div class="booking-rules">
            <strong>Booking rules:</strong>
            You can book from today through the next 3 days.
            Each time slot is 30 minutes. Choose one computer
            for each selected time slot. All selected bookings
            are confirmed together; if any slot becomes
            unavailable, none of the selections will be booked.
        </div>

        <?php if ($selectedSlotIds !== []): ?>
            <div class="availability-results">
                <div class="student-panel-heading">
                    <div>
                        <h3>
                            3. Choose a computer for each time slot
                        </h3>

                        <p>
                            <?= count($selectedSlotIds) ?>
                            time slot<?= count($selectedSlotIds) === 1 ? '' : 's' ?>
                            selected for
                            <?= $escape(date(
                                'D, d M Y',
                                strtotime($selectedDate)
                            )) ?>.
                        </p>
                    </div>
                </div>

                <form
                    method="post"
                    action="<?= $escape($baseUrl . '/new-booking.php') ?>"
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

                    <?php foreach ($selectedSlotIds as $slotId): ?>
                        <input
                            type="hidden"
                            name="slot_ids[]"
                            value="<?= $slotId ?>"
                        >
                    <?php endforeach; ?>

                    <div class="selected-slot-list">
                        <?php foreach ($selectedSlotIds as $slotId): ?>
                            <?php
                            $slotDetails = null;

                            foreach ($timeSlots as $slot) {
                                if ((int) $slot['slot_id'] === $slotId) {
                                    $slotDetails = $slot;
                                    break;
                                }
                            }

                            $availableComputers =
                                $computersBySlot[$slotId] ?? [];
                            ?>

                            <section class="slot-computer-group">
                                <div class="slot-computer-heading">
                                    <h4>
                                        <?= $escape(
                                            $slotDetails
                                                ? substr(
                                                    (string) $slotDetails['start_time'],
                                                    0,
                                                    5
                                                )
                                                : ''
                                        ) ?>–<?= $escape(
                                            $slotDetails
                                                ? substr(
                                                    (string) $slotDetails['end_time'],
                                                    0,
                                                    5
                                                )
                                                : ''
                                        ) ?>
                                    </h4>

                                    <span>
                                        <?= count($availableComputers) ?>
                                        computer<?= count($availableComputers) === 1 ? '' : 's' ?>
                                        available
                                    </span>
                                </div>

                                <?php if ($availableComputers === []): ?>
                                    <p class="no-computers-message">
                                        No computers are available for
                                        this time slot. Go back and
                                        deselect it or choose another time.
                                    </p>
                                <?php else: ?>
                                    <div class="available-computer-grid">
                                        <?php foreach ($availableComputers as $computer): ?>
                                            <?php
                                            $computerId = (int) $computer['computer_id'];
                                            ?>

                                            <label class="available-computer-option">
                                                <input
                                                    type="radio"
                                                    name="computer_for_slot[<?= $slotId ?>]"
                                                    value="<?= $computerId ?>"
                                                    <?= isset($postedComputers[$slotId])
                                                        && (string) $postedComputers[$slotId]
                                                            === (string) $computerId
                                                        ? 'checked'
                                                        : '' ?>
                                                    required
                                                >

                                                <span>
                                                    <strong>
                                                        <?= $escape($computer['computer_name']) ?>
                                                    </strong>

                                                    <?php if (!empty($computer['description'])): ?>
                                                        <small>
                                                            <?= $escape($computer['description']) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </section>
                        <?php endforeach; ?>
                    </div>

                    <?php
                    $allSlotsHaveComputers = true;

                    foreach ($selectedSlotIds as $slotId) {
                        if (empty($computersBySlot[$slotId])) {
                            $allSlotsHaveComputers = false;
                            break;
                        }
                    }
                    ?>

                    <?php if ($allSlotsHaveComputers): ?>
                        <button
                            type="submit"
                            class="button button-primary"
                        >
                            Confirm All Selected Bookings
                        </button>
                    <?php else: ?>
                        <p class="field-hint">
                            You can confirm after removing any time
                            slots that have no available computers.
                        </p>
                    <?php endif; ?>
                </form>
            </div>
        <?php else: ?>
            <div class="student-empty booking-prompt">
                <h3>Select your date and time slots</h3>

                <p>
                    Available computers will appear here after
                    you select one or more time slots and click
                    “Find Available Computers”.
                </p>
            </div>
        <?php endif; ?>
    </section>

    <p class="student-back-link">
        <a href="<?= $escape($baseUrl . '/dashboard.php') ?>">
            ← Back to dashboard
        </a>
    </p>
</section>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>