<?php

declare(strict_types=1);

$baseUrl = rtrim(APP_BASE_URL, '/');

?>
<section class="availability-page">
    <div class="availability-heading">
        <div>
            <p class="eyebrow">LIVE LAB MAP</p>
            <h2>Find your next open seat.</h2>
            <p>Explore computer availability by date, time, and reservation status.</p>
        </div>
        <div class="availability-legend" aria-label="Availability legend">
            <span class="legend-item"><i class="legend-dot" aria-hidden="true"></i> Available</span>
            <span class="legend-item"><i class="legend-dot waiting" aria-hidden="true"></i> Temporary hold</span>
            <span class="legend-item"><i class="legend-dot booked" aria-hidden="true"></i> Booked</span>
        </div>
    </div>

    <?php if ($errorMessage !== null): ?>
        <p class="form-error" role="alert" data-toast="error"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="GET" action="<?= htmlspecialchars($baseUrl . '/availability', ENT_QUOTES, 'UTF-8') ?>" class="date-filter" id="availability-filter-form" data-endpoint="<?= htmlspecialchars($baseUrl . '/availability', ENT_QUOTES, 'UTF-8') ?>" data-validate>
        <div class="filter-field">
            <label for="booking-date">Reservation date</label>
            <input type="date" id="booking-date" name="date" value="<?= htmlspecialchars($selectedDate, ENT_QUOTES, 'UTF-8') ?>" min="<?= htmlspecialchars($minDate, ENT_QUOTES, 'UTF-8') ?>" max="<?= htmlspecialchars($maxDate, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="filter-field">
            <label for="availability-status">Availability status</label>
            <select id="availability-status" name="status">
                <?php foreach ($statusOptions as $statusOption): ?>
                    <option value="<?= htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedStatus === $statusOption ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($statusOption), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <details class="filter-slots">
            <summary>Time slots <span>(select any)</span></summary>
            <div class="time-slot-menu">
                <p class="filter-hint">Leave all unchecked to include every slot.</p>
                <div class="time-slot-options">
                    <?php foreach ($timeSlots as $timeSlot): ?>
                        <?php
                        $slotId = (int) $timeSlot['slot_id'];
                        $startTime = substr($timeSlot['start_time'], 0, 5);
                        $endTime = substr($timeSlot['end_time'], 0, 5);
                        ?>
                        <label class="time-slot-option">
                            <input type="checkbox" name="slot_ids[]" value="<?= $slotId ?>" <?= in_array($slotId, $selectedSlotIds, true) ? 'checked' : '' ?>>
                            <span><?= htmlspecialchars($startTime . ' – ' . $endTime, ENT_QUOTES, 'UTF-8') ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </details>

        <div class="filter-actions">
            <button type="submit" class="button button-primary"><i class="fa-solid fa-sliders" aria-hidden="true"></i> Apply filters</button>
        </div>
    </form>

    <div class="availability-results-heading">
        <div>
            <h3>Computer map</h3>
            <p id="availability-updated" class="availability-updated" role="status">Live availability refreshes every 5 seconds.</p>
        </div>
        <label class="table-search-wrap availability-search-wrap">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <span class="sr-only">Search computers</span>
            <input class="table-search" type="search" id="availability-search" placeholder="Search computers">
        </label>
    </div>

    <!-- Server-render the first result set so availability remains useful without JavaScript. -->
    <div id="availability-results" aria-live="polite">
        <?php if ($availability === []): ?>
            <p class="empty-state">No computers match the selected filters.</p>
        <?php else: ?>
            <?php $currentComputerId = null; ?>
            <div class="computer-list">
                <?php foreach ($availability as $slot): ?>
                    <?php if ($currentComputerId !== $slot['computer_id']): ?>
                        <?php if ($currentComputerId !== null): ?>
                            </div></section>
                        <?php endif; ?>
                        <?php $currentComputerId = $slot['computer_id']; ?>
                        <section class="computer-card" data-computer-name="<?= htmlspecialchars($slot['computer_name'], ENT_QUOTES, 'UTF-8') ?>">
                            <div class="computer-card-header">
                                <span class="computer-icon" aria-hidden="true"><i class="fa-solid fa-desktop"></i></span>
                                <div>
                                    <h3><?= htmlspecialchars($slot['computer_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                    <?php if (!empty($slot['description'])): ?>
                                        <p class="computer-description"><?= htmlspecialchars($slot['description'], ENT_QUOTES, 'UTF-8') ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="pc-tile-grid">
                    <?php endif; ?>

                    <?php
                    $startTime = substr($slot['start_time'], 0, 5);
                    $endTime = substr($slot['end_time'], 0, 5);
                    $status = $slot['status'];
                    $busyTitle = $status === 'booked'
                        ? 'Booked until ' . $endTime
                        : ($status === 'waiting' ? 'Temporary hold for this slot, through ' . $endTime : 'Available for this slot');
                    ?>
                    <div class="pc-tile status-<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($busyTitle, ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= htmlspecialchars($slot['computer_name'] . ', ' . $startTime . ' to ' . $endTime . ', ' . $status . ($status === 'booked' ? ', booked until ' . $endTime : ''), ENT_QUOTES, 'UTF-8') ?>">
                        <span class="slot-time"><?= htmlspecialchars($startTime . '–' . $endTime, ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="slot-status status-<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endforeach; ?>
                </div></section>
            </div>
        <?php endif; ?>
    </div>
</section>

<script defer src="<?= htmlspecialchars($baseUrl . '/assets/js/availability.js?v=' . filemtime(PUBLIC_PATH . '/assets/js/availability.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
