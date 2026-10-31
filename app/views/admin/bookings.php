<?php

declare(strict_types=1);

$baseUrl = rtrim(APP_BASE_URL, '/');

?>
<section class="admin-panel">
    <div class="admin-panel-header">
        <h2>All Bookings</h2>
        <a href="<?= htmlspecialchars($baseUrl . '/admin', ENT_QUOTES, 'UTF-8') ?>" class="button">← Back to Dashboard</a>
    </div>

    <?php if (empty($bookings)): ?>
        <p>No bookings found.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Email</th>
                    <th>Computer</th>
                    <th>Date</th>
                    <th>Time Slot</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $booking['booking_id'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $booking['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $booking['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $booking['computer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $booking['booking_date'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?= htmlspecialchars(substr((string) $booking['start_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?>
                            –
                            <?= htmlspecialchars(substr((string) $booking['end_time'], 0, 5), ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td>
                            <span class="status-<?= htmlspecialchars((string) $booking['status'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars(ucfirst((string) $booking['status']), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars(substr((string) $booking['created_at'], 0, 16), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

