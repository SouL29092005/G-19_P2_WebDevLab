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
        $errorMessage = null;

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
            $errorMessage = 'Please select a valid date.';
            $selectedDate = $today;
        } elseif ($selectedDate < $today || $selectedDate > $maxDate) {
            $errorMessage = 'Please select a date within the next 3 days.';
            $selectedDate = $today;
        }

        $date = $selectedDate->format('Y-m-d');

        return [
            'pageTitle' => 'Computer Availability',
            'selectedDate' => $date,
            'minDate' => $today->format('Y-m-d'),
            'maxDate' => $maxDate->format('Y-m-d'),
            'availability' => $this->computerRepository
                ->getAvailabilityForDate($date),
            'errorMessage' => $errorMessage,
        ];
    }
}
