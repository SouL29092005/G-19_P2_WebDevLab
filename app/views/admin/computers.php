<?php

declare(strict_types=1);

$computers = $computers ?? [];

?>

<section class="admin-computers">
    <h2>Manage Laboratory Computers</h2>

    <p>
        View all laboratory computers, including inactive ones.
    </p>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Computer Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($computers === []): ?>
                    <tr>
                        <td colspan="5">
                            No computers have been added yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($computers as $computer): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars(
                                    (string) $computer['computer_id'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    (string) $computer['computer_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    (string) (
                                        $computer['description'] ?? ''
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <?php if ($computer['is_active']): ?>
                                    <span class="status-active">
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span class="status-inactive">
                                        Inactive
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    (string) $computer['created_at'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
