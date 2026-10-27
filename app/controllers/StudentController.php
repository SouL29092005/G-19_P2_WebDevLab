<?php

declare(strict_types=1);

require_once APP_PATH . '/repositories/BookingRepository.php';

final class StudentController
{
    public function __construct(
        private readonly BookingRepository $bookings
    ) {}

    public function dashboard(): array
    {
        $userId = (int) $_SESSION['user']['id'];

        $all = $this->bookings->getStudentBookings($userId);

        $upcoming = array_values(array_filter(
            $all,
            static fn(array $b): bool =>
                $b['status'] === 'booked'
                && $b['booking_date'] >= date('Y-m-d')
        ));

        usort(
            $upcoming,
            static fn(array $a, array $b): int =>
                [$a['booking_date'], $a['start_time']]
                <=> [$b['booking_date'], $b['start_time']]
        );

        return [
            'pageTitle' => 'Student Dashboard',
            'stats' => $this->bookings->getStudentStats($userId),
            'upcomingBookings' => array_slice($upcoming, 0, 5),
            'csrfToken' => $this->csrfToken(),
        ];
    }

    public function myBookings(): array
    {
        $error = null;
        $success = null;

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!$this->validCsrf()) {
                $error = 'Your session expired. Refresh the page and try again.';
            } else {
                $action = $_POST['action'] ?? '';
                $bookingId = $_POST['booking_id'] ?? '';

                if (
                    $action !== 'cancel'
                    || !is_string($bookingId)
                    || !ctype_digit($bookingId)
                ) {
                    $error = 'Invalid booking request.';
                } elseif (
                    $this->bookings->cancelBooking(
                        (int) $_SESSION['user']['id'],
                        (int) $bookingId
                    )
                ) {
                    header(
                        'Location: '
                        . rtrim(APP_BASE_URL, '/')
                        . '/my-bookings.php?success=cancelled',
                        true,
                        303
                    );

                    exit;
                } else {
                    $error = 'This booking could not be cancelled. It may already be cancelled.';
                }
            }
        }

        if (($_GET['success'] ?? '') === 'cancelled') {
            $success = 'Booking cancelled successfully.';
        } elseif (($_GET['success'] ?? '') === 'hold-cancelled') {
            $success = 'The temporary reservation was released.';
        } elseif (
            ($_GET['success'] ?? '') === 'created'
            && is_string($_GET['group'] ?? null)
            && $this->isUuid($_GET['group'])
        ) {
            $confirmed = $this->bookings->getConfirmedBookingGroup(
                (int) $_SESSION['user']['id'],
                $_GET['group']
            );

            if ($confirmed !== []) {
                $slots = array_map(
                    static fn(array $booking): string =>
                        substr((string) $booking['start_time'], 0, 5)
                        . '–'
                        . substr((string) $booking['end_time'], 0, 5),
                    $confirmed
                );
                $success = 'Booking confirmed: '
                    . $confirmed[0]['computer_name']
                    . ' on '
                    . date('d M Y', strtotime($confirmed[0]['booking_date']))
                    . ' for '
                    . implode(', ', $slots)
                    . '.';
            }
        }

        return [
            'pageTitle' => 'My Bookings',
            'bookings' => $this->bookings->getStudentBookings(
                (int) $_SESSION['user']['id']
            ),
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'success' => $success,
        ];
    }

    public function newBooking(): array
    {
        $error = null;
        $timezone = new DateTimeZone(APP_TIMEZONE);
        $today = new DateTimeImmutable('today', $timezone);
        $maxDate = $today->modify('+3 days');
        $todayString = $today->format('Y-m-d');
        $maxDateString = $maxDate->format('Y-m-d');
        $dateInput = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
            ? ($_POST['booking_date'] ?? null)
            : ($_GET['date'] ?? $todayString);
        $date = is_string($dateInput) ? $dateInput : $todayString;

        if (!$this->isDateInWindow($date, $today, $maxDate, $timezone)) {
            $error = 'Choose a valid date from today through the next 3 days.';
            $date = $todayString;
        }

        $timeSlots = $this->bookings->getTimeSlots();
        $validSlotIds = array_map(
            static fn(array $slot): int => (int) $slot['slot_id'],
            $timeSlots
        );
        $selectedSlotIds = [];
        $requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $requestedSlots = $requestMethod === 'POST'
            ? ($_POST['slot_ids'] ?? null)
            : ($_GET['slots'] ?? []);

        try {
            $selectedSlotIds = $this->parseSlotIds(
                $requestedSlots,
                $validSlotIds
            );

            if ($date === $todayString && $selectedSlotIds !== []) {
                $now = new DateTimeImmutable('now', $timezone);
                $slotsById = [];
                foreach ($timeSlots as $slot) {
                    $slotsById[(int) $slot['slot_id']] = (string) $slot['start_time'];
                }

                foreach ($selectedSlotIds as $slotId) {
                    if ($slotsById[$slotId] <= $now->format('H:i:s')) {
                        throw new InvalidArgumentException(
                            'One or more selected slots have already started. Update your selection.'
                        );
                    }
                }
            }
        } catch (InvalidArgumentException $exception) {
            $error = $exception->getMessage();
            $selectedSlotIds = [];
        }

        if ($requestMethod === 'POST') {
            if (!$this->validCsrf()) {
                $error = 'Your session expired. Refresh the page and try again.';
            } elseif (($_POST['action'] ?? '') !== 'hold') {
                $error = 'Invalid booking request.';
            } elseif ($error === null) {
                $computerInput = $_POST['computer_id'] ?? null;

                if (
                    !is_string($computerInput)
                    || !ctype_digit($computerInput)
                    || (int) $computerInput < 1
                    || $selectedSlotIds === []
                ) {
                    $error = 'Select one computer and at least one valid time slot.';
                } else {
                    try {
                        $groupId = $this->bookings->createHoldGroup(
                            (int) $_SESSION['user']['id'],
                            $date,
                            (int) $computerInput,
                            $selectedSlotIds
                        );

                        if ($groupId === false) {
                            $error = 'That computer is no longer available for every selected slot. No slots were held; update your selection.';
                        } else {
                            header(
                                'Location: '
                                . rtrim(APP_BASE_URL, '/')
                                . '/confirm-booking.php?group='
                                . rawurlencode($groupId),
                                true,
                                303
                            );
                            exit;
                        }
                    } catch (PDOException $exception) {
                        if ($exception->getCode() === '42703') {
                            error_log('Booking group database migration is missing: ' . $exception->getMessage());
                            $error = 'Booking setup needs an update. Apply database/migrations/20261027_add_booking_group.sql, then try again.';
                        } else {
                            throw $exception;
                        }
                    } catch (InvalidArgumentException $exception) {
                        $error = $exception->getMessage();
                    }
                }
            }
        }

        $currentTime = (new DateTimeImmutable('now', $timezone))->format('H:i:s');
        $availableComputers = $selectedSlotIds === []
            ? []
            : $this->bookings->getAvailableComputersAcrossSlots(
                $date,
                $todayString,
                $currentTime,
                $selectedSlotIds
            );

        return [
            'pageTitle' => 'New Booking',
            'selectedDate' => $date,
            'minDate' => $todayString,
            'maxDate' => $maxDateString,
            'timeSlots' => $timeSlots,
            'selectedSlotIds' => $selectedSlotIds,
            'availableComputers' => $availableComputers,
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'success' => ($_GET['success'] ?? '') === 'hold-cancelled'
                ? 'The temporary reservation was released.'
                : null,
        ];
    }

    public function availability(): array
    {
        $timezone = new DateTimeZone(APP_TIMEZONE);
        $today = new DateTimeImmutable('today', $timezone);
        $maxDate = $today->modify('+3 days');
        $date = $_GET['date'] ?? null;

        if (
            !is_string($date)
            || !$this->isDateInWindow($date, $today, $maxDate, $timezone)
        ) {
            throw new InvalidArgumentException(
                'Choose a valid date from today through the next 3 days.'
            );
        }

        $slots = $this->parseSlotIds(
            $_GET['slots'] ?? [],
            array_map(
                static fn(array $slot): int => (int) $slot['slot_id'],
                $this->bookings->getTimeSlots()
            )
        );

        if ($slots === []) {
            return ['computers' => [], 'slotIds' => []];
        }

        $now = new DateTimeImmutable('now', $timezone);
        if ($date === $today->format('Y-m-d')) {
            $slotById = [];
            foreach ($this->bookings->getTimeSlots() as $slot) {
                $slotById[(int) $slot['slot_id']] = (string) $slot['start_time'];
            }
            foreach ($slots as $slotId) {
                if ($slotById[$slotId] <= $now->format('H:i:s')) {
                    throw new InvalidArgumentException(
                        'One or more selected slots have already started.'
                    );
                }
            }
        }

        return [
            'computers' => $this->bookings->getAvailableComputersAcrossSlots(
                $date,
                $today->format('Y-m-d'),
                $now->format('H:i:s'),
                $slots
            ),
            'slotIds' => $slots,
            'slots' => array_values(array_filter(
                $this->bookings->getTimeSlots(),
                static fn(array $slot): bool =>
                    in_array((int) $slot['slot_id'], $slots, true)
            )),
        ];
    }

    public function confirmBooking(): array
    {
        $error = null;
        $groupInput = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
            ? ($_POST['group_id'] ?? null)
            : ($_GET['group'] ?? null);
        $groupId = is_string($groupInput) && $this->isUuid($groupInput)
            ? $groupInput
            : null;
        $userId = (int) $_SESSION['user']['id'];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!$this->validCsrf()) {
                $error = 'Your session expired. Refresh the page and try again.';
            } elseif ($groupId === null) {
                $error = 'This temporary reservation is invalid or has expired.';
            } else {
                $action = $_POST['action'] ?? '';

                if ($action === 'cancel') {
                    $this->bookings->releaseWaitingBookingGroup($userId, $groupId);
                    header(
                        'Location: '
                        . rtrim(APP_BASE_URL, '/')
                        . '/new-booking.php?success=hold-cancelled',
                        true,
                        303
                    );
                    exit;
                }

                if ($action !== 'confirm') {
                    $error = 'Invalid confirmation request.';
                } elseif ($this->bookings->confirmBookingGroup($userId, $groupId)) {
                    header(
                        'Location: '
                        . rtrim(APP_BASE_URL, '/')
                        . '/my-bookings.php?success=created&group='
                        . rawurlencode($groupId),
                        true,
                        303
                    );
                    exit;
                } else {
                    $error = 'This hold expired or is incomplete. No bookings were confirmed; please select your slots again.';
                }
            }
        }

        $group = $groupId === null
            ? []
            : $this->bookings->getWaitingBookingGroup($userId, $groupId);

        if ($group === [] && $error === null) {
            $error = 'This temporary reservation is unavailable or has expired.';
        }

        return [
            'pageTitle' => 'Confirm Booking',
            'groupId' => $groupId,
            'bookingGroup' => $group,
            'remainingSeconds' => $group === []
                ? 0
                : max(0, strtotime((string) $group[0]['hold_expires_at']) - time()),
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
        ];
    }

    private function isDateInWindow(
        string $date,
        DateTimeImmutable $today,
        DateTimeImmutable $maxDate,
        DateTimeZone $timezone
    ): bool {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date, $timezone);

        return $parsed !== false
            && $parsed->format('Y-m-d') === $date
            && $parsed >= $today
            && $parsed <= $maxDate;
    }

    private function parseSlotIds($input, array $validSlotIds): array
    {
        if (!is_array($input)) {
            throw new InvalidArgumentException('Choose valid time slots.');
        }

        $slotIds = [];
        foreach ($input as $id) {
            if (
                !is_scalar($id)
                || !ctype_digit((string) $id)
                || (int) $id < 1
                || !in_array((int) $id, $validSlotIds, true)
                || in_array((int) $id, $slotIds, true)
            ) {
                throw new InvalidArgumentException(
                    'One or more selected time slots are invalid.'
                );
            }

            $slotIds[] = (int) $id;
        }

        sort($slotIds);

        return $slotIds;
    }

    private function isUuid(string $value): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value
        ) === 1;
    }

    private function csrfToken(): string
    {
        if (
            !isset($_SESSION['csrf_token'])
            || !is_string($_SESSION['csrf_token'])
        ) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    private function validCsrf(): bool
    {
        $submitted = $_POST['csrf_token'] ?? null;

        return is_string($submitted)
            && isset($_SESSION['csrf_token'])
            && is_string($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $submitted);
    }
}
