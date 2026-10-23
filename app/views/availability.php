<?php

declare(strict_types=1);

?>

<section class="availability-page">
    <div class="availability-heading">
        <h2>Computer Availability</h2>
        <p>
            Select a date to check which laboratory computers
            are available.
        </p>
    </div>

    <?php if ($errorMessage !== null): ?>
        <p class="form-error" role="alert">
            <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="GET" action="<?= htmlspecialchars(rtrim(APP_BASE_URL, '/') . '/availability', ENT_QUOTES, 'UTF-8'
    ) ?>" class="date-filter">
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

        <button type="submit" class="button button-primary">
            Check Availability
        </button>
    </form>

    <?php if ($availability === []): ?>
        <p class="empty-state">
            No active computers or time slots were found.
        </p>
    <?php else: ?>
        <?php
        $currentComputerId = null;
        ?>

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
                        <h3>
                            <?= htmlspecialchars(
                                $slot['computer_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </h3>

                        <?php if (!empty($slot['description'])): ?>
                            <p class="computer-description">
                                <?= htmlspecialchars(
                                    $slot['description'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </p>
                        <?php endif; ?>

                        <div class="slot-list">
                    <?php
                }

                $isAvailable = in_array(
                    $slot['is_available'],
                    [true, 1, '1', 't', 'true'],
                    true
                );

                $startTime = substr($slot['start_time'], 0, 5);
                $endTime = substr($slot['end_time'], 0, 5);
                ?>

                <div class="slot-item">
                    <span class="slot-time">
                        <?= htmlspecialchars($startTime, ENT_QUOTES, 'UTF-8') ?>
                        –
                        <?= htmlspecialchars($endTime, ENT_QUOTES, 'UTF-8') ?>
                    </span>

                    <span class="slot-status <?= $isAvailable
                        ? 'status-available'
                        : 'status-unavailable' ?>">
                        <?= $isAvailable ? 'Available' : 'Unavailable' ?>
                    </span>
                </div>
            <?php endforeach; ?>

            </div>
            </section>
    <?php endif; ?>
</section>
