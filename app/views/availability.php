<?php

declare(strict_types=1);

?>

<section class="availability-page">
    <div class="availability-heading">
        <h2>Computer Availability</h2>
        <p>
            Filter laboratory computers by reservation date, time slot, and
            availability status.
        </p>
    </div>

    <?php if ($errorMessage !== null): ?>
        <p class="form-error" role="alert">
            <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form
        method="GET"
        action="<?= htmlspecialchars(rtrim(APP_BASE_URL, '/') . '/availability', ENT_QUOTES, 'UTF-8') ?>"
        class="date-filter"
        id="availability-filter-form"
        data-endpoint="<?= htmlspecialchars(rtrim(APP_BASE_URL, '/') . '/availability', ENT_QUOTES, 'UTF-8') ?>"
    >
        <div class="filter-field">
            <label for="booking-date">Reservation date</label>

            <input
                type="date"
                id="booking-date"
                name="date"
                value="<?= htmlspecialchars($selectedDate, ENT_QUOTES, 'UTF-8') ?>"
                min="<?= htmlspecialchars($minDate, ENT_QUOTES, 'UTF-8') ?>"
                max="<?= htmlspecialchars($maxDate, ENT_QUOTES, 'UTF-8') ?>"
                required
            >
        </div>

        <div class="filter-field">
            <label for="availability-status">Status</label>

            <select id="availability-status" name="status">
                <?php foreach ($statusOptions as $statusOption): ?>
                    <option
                        value="<?= htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8') ?>"
                        <?= $selectedStatus === $statusOption ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars(
                            ucfirst($statusOption),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <details class="filter-slots">
            <summary>Time slots <span>(select multiple)</span></summary>
            <div class="time-slot-menu">
                <p class="filter-hint">
                    Select one or more slots. Leave all unchecked to include
                    every time slot.
                </p>
                <div class="time-slot-options">
                    <?php foreach ($timeSlots as $timeSlot): ?>
                        <?php
                        $slotId = (int) $timeSlot['slot_id'];
                        $startTime = substr($timeSlot['start_time'], 0, 5);
                        $endTime = substr($timeSlot['end_time'], 0, 5);
                        ?>
                        <label class="time-slot-option">
                            <input
                                type="checkbox"
                                name="slot_ids[]"
                                value="<?= $slotId ?>"
                                <?= in_array(
                                    $slotId,
                                    $selectedSlotIds,
                                    true
                                ) ? 'checked' : '' ?>
                            >
                            <span><?= htmlspecialchars(
                                $startTime . ' – ' . $endTime,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </details>

        <div class="filter-actions">
            <button type="submit" class="button button-primary">
                Apply Filters
            </button>
        </div>
    </form>

    <p id="availability-updated" class="availability-updated" role="status">
        Availability refreshes automatically every 5 seconds.
    </p>

    <div id="availability-results">
        <?php if ($availability === []): ?>
            <p class="empty-state">
                No computers match the selected filters.
            </p>
        <?php else: ?>
            <?php $currentComputerId = null; ?>

            <div class="computer-list">
                <?php foreach ($availability as $slot): ?>
                    <?php
                    if ($currentComputerId !== $slot['computer_id']) {
                        if ($currentComputerId !== null) {
                            ?>
                            </div>
                            </section>
                            <?php
                        }

                        $currentComputerId = $slot['computer_id'];
                        ?>
                        <section class="computer-card">
                            <h3><?= htmlspecialchars($slot['computer_name'], ENT_QUOTES, 'UTF-8') ?></h3>

                            <?php if (!empty($slot['description'])): ?>
                                <p class="computer-description">
                                    <?= htmlspecialchars($slot['description'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            <?php endif; ?>

                            <div class="slot-list">
                        <?php
                    }

                    $startTime = substr($slot['start_time'], 0, 5);
                    $endTime = substr($slot['end_time'], 0, 5);
                    $status = $slot['status'];
                    ?>

                    <div class="slot-item">
                        <span class="slot-time">
                            <?= htmlspecialchars($startTime, ENT_QUOTES, 'UTF-8') ?>
                            –
                            <?= htmlspecialchars($endTime, ENT_QUOTES, 'UTF-8') ?>
                        </span>

                        <span class="slot-status status-<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                <?php endforeach; ?>

                </div>
                </section>
            </div>
        <?php endif; ?>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= htmlspecialchars(rtrim(APP_BASE_URL, '/') . '/assets/js/availability.js', ENT_QUOTES, 'UTF-8') ?>" defer></script>
