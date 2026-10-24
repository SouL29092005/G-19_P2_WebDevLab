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

        $date = $_POST['booking_date']
            ?? $_GET['date']
            ?? $today->format('Y-m-d');

        if (
            !is_string($date)
            || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
        ) {
            $date = $today->format('Y-m-d');
        }

        $parsed = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $date,
            $timezone
        );

        if (
            !$parsed
            || $parsed->format('Y-m-d') !== $date
            || $parsed < $today
            || $parsed > $maxDate
        ) {
            $error = 'Choose a valid date from today through the next 3 days.';
            $date = $today->format('Y-m-d');
        }

        $timeSlots = $this->bookings->getTimeSlots();

        $validSlotIds = array_map(
            static fn(array $slot): int => (int) $slot['slot_id'],
            $timeSlots
        );

        $requestedSlots = $_POST['slot_ids']
            ?? $_GET['slots']
            ?? [];

        if (!is_array($requestedSlots)) {
            $requestedSlots = [];
        }

        $selectedSlotIds = array_values(array_unique(array_filter(
            array_map(
                static function ($id): int {
                    return is_scalar($id)
                        && ctype_digit((string) $id)
                            ? (int) $id
                            : 0;
                },
                $requestedSlots
            ),
            static fn(int $id): bool =>
                in_array($id, $validSlotIds, true)
        )));

        if ($date === $today->format('Y-m-d')) {
            $currentTimeForSelection = (
                new DateTimeImmutable('now', $timezone)
            )->format('H:i:s');

            $slotStartTimes = [];

            foreach ($timeSlots as $slot) {
                $slotStartTimes[(int) $slot['slot_id']] =
                    (string) $slot['start_time'];
            }

            $selectedSlotIds = array_values(array_filter(
                $selectedSlotIds,
                static fn(int $id): bool =>
                    isset($slotStartTimes[$id])
                    && $slotStartTimes[$id] > $currentTimeForSelection
            ));
        }

        sort($selectedSlotIds);

        if (
            ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
            && $error === null
        ) {
            if (!$this->validCsrf()) {
                $error = 'Your session expired. Refresh the page and try again.';
            } else {
                $selectedComputers = $_POST['computer_for_slot'] ?? [];

                if (
                    !is_array($selectedComputers)
                    || $selectedSlotIds === []
                ) {
                    $error = 'Select a date, at least one time slot, and a computer for each slot.';
                } else {
                    $selections = [];

                    foreach ($selectedComputers as $slotId => $computerId) {
                        if (
                            !is_scalar($slotId)
                            || !ctype_digit((string) $slotId)
                            || !is_scalar($computerId)
                            || !ctype_digit((string) $computerId)
                        ) {
                            $error = 'One of your computer selections is invalid. Please try again.';
                            break;
                        }

                        $slotId = (int) $slotId;
                        $computerId = (int) $computerId;

                        if (!in_array($slotId, $selectedSlotIds, true)) {
                            $error = 'Please choose computers only for the time slots you selected.';
                            break;
                        }

                        $selections[] = [
                            'slot_id' => $slotId,
                            'computer_id' => $computerId,
                        ];
                    }

                    if (
                        $error === null
                        && count($selections) !== count($selectedSlotIds)
                    ) {
                        $error = 'Choose one available computer for every selected time slot.';
                    }

                    if ($error === null) {
                        try {
                            $currentTime = (
                                new DateTimeImmutable('now', $timezone)
                            )->format('H:i:s');

                            $available = $this->bookings
                                ->getAvailableCombinations(
                                    $date,
                                    $today->format('Y-m-d'),
                                    $currentTime,
                                    $selectedSlotIds
                                );

                            foreach ($selections as $selection) {
                                $found = false;

                                foreach ($available as $combination) {
                                    if (
                                        (int) $combination['slot_id']
                                            === $selection['slot_id']
                                        && (int) $combination['computer_id']
                                            === $selection['computer_id']
                                    ) {
                                        $found = true;
                                        break;
                                    }
                                }

                                if (!$found) {
                                    $error = 'One or more selected computers are no longer available. Review the updated availability and try again.';
                                    break;
                                }
                            }

                            if ($error === null) {
                                $this->bookings->createBookings(
                                    (int) $_SESSION['user']['id'],
                                    $date,
                                    $selections
                                );

                                header(
                                    'Location: '
                                    . rtrim(APP_BASE_URL, '/')
                                    . '/my-bookings.php?success=created',
                                    true,
                                    303
                                );

                                exit;
                            }
                        } catch (PDOException $exception) {
                            if ($exception->getCode() === '23505') {
                                $error = 'A selected slot was just booked by someone else. No bookings were made; please review availability and try again.';
                            } else {
                                throw $exception;
                            }
                        } catch (InvalidArgumentException $exception) {
                            $error = $exception->getMessage();
                        }
                    }
                }
            }
        }

        $currentTime = (
            new DateTimeImmutable('now', $timezone)
        )->format('H:i:s');

        $availableCombinations = $selectedSlotIds === []
            ? []
            : $this->bookings->getAvailableCombinations(
                $date,
                $today->format('Y-m-d'),
                $currentTime,
                $selectedSlotIds
            );

        $computersBySlot = [];

        foreach ($availableCombinations as $combination) {
            $computersBySlot[(int) $combination['slot_id']][] =
                $combination;
        }

        return [
            'pageTitle' => 'New Booking',
            'selectedDate' => $date,
            'minDate' => $today->format('Y-m-d'),
            'maxDate' => $maxDate->format('Y-m-d'),
            'timeSlots' => $timeSlots,
            'selectedSlotIds' => $selectedSlotIds,
            'computersBySlot' => $computersBySlot,
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
        ];
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