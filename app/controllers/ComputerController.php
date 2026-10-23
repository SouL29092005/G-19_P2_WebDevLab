<?php

declare(strict_types=1);

require_once APP_PATH . '/repositories/ComputerRepository.php';

final class ComputerController
{
    public function __construct(
        private readonly ComputerRepository $computerRepository
    ) {
    }

    public function index(): array
    {
        $timezone = new DateTimeZone(APP_TIMEZONE);
        $today = new DateTimeImmutable('today', $timezone);
        $maxDate = $today->modify('+3 days');

        $requestedDate = $_GET['date'] ?? $today->format('Y-m-d');
        $errorMessages = [];

        if (!is_string($requestedDate)) {
            $requestedDate = '';
        }

        $selectedDate = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $requestedDate,
            $timezone
        );

        $dateErrors = DateTimeImmutable::getLastErrors();

        $validDate = $selectedDate !== false
            && $selectedDate->format('Y-m-d') === $requestedDate
            && (
                $dateErrors === false
                || (
                    $dateErrors['warning_count'] === 0
                    && $dateErrors['error_count'] === 0
                )
            );

        if (!$validDate) {
            $errorMessages[] = 'Please select a valid date.';
            $selectedDate = $today;
        } elseif ($selectedDate < $today || $selectedDate > $maxDate) {
            $errorMessages[] = 'Please select a date within the next 3 days.';
            $selectedDate = $today;
        }

        $date = $selectedDate->format('Y-m-d');
        $timeSlots = $this->computerRepository->getTimeSlots();
        $slotIds = array_map(
            static fn (array $slot): int => (int) $slot['slot_id'],
            $timeSlots
        );

        $requestedSlotIds = $_GET['slot_ids'] ?? [];
        $selectedSlotIds = [];

        if (!is_array($requestedSlotIds)) {
            $errorMessages[] = 'Please select a valid time slot.';
        } else {
            foreach ($requestedSlotIds as $requestedSlotId) {
                if (
                    !is_string($requestedSlotId)
                    || !ctype_digit($requestedSlotId)
                    || !in_array((int) $requestedSlotId, $slotIds, true)
                ) {
                    $errorMessages[] = 'Please select valid time slots.';
                    $selectedSlotIds = [];
                    break;
                }

                $selectedSlotIds[] = (int) $requestedSlotId;
            }
        }

        $selectedSlotIds = array_values(array_unique($selectedSlotIds));

        $statusOptions = ['all', 'available', 'waiting', 'booked'];
        $requestedStatus = $_GET['status'] ?? 'all';

        if (
            !is_string($requestedStatus)
            || !in_array($requestedStatus, $statusOptions, true)
        ) {
            $errorMessages[] = 'Please select a valid availability status.';
            $selectedStatus = 'all';
        } else {
            $selectedStatus = $requestedStatus;
        }

        return [
            'pageTitle' => 'Computer Availability',
            'selectedDate' => $date,
            'minDate' => $today->format('Y-m-d'),
            'maxDate' => $maxDate->format('Y-m-d'),
            'timeSlots' => $timeSlots,
            'selectedSlotIds' => $selectedSlotIds,
            'statusOptions' => $statusOptions,
            'selectedStatus' => $selectedStatus,
            'availability' => $this->computerRepository
                ->getAvailabilityForDate(
                    $date,
                    $selectedSlotIds,
                    $selectedStatus
                ),
            'errorMessage' => $errorMessages === []
                ? null
                : implode(' ', $errorMessages),
        ];
    }
}
