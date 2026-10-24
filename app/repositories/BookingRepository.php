<?php

declare(strict_types=1);

final class BookingRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    public function getStudentBookings(int $userId): array
    {
        $statement = $this->pdo->prepare('
            select b.booking_id, b.booking_date, b.status, b.created_at,
                   b.confirmed_at, c.computer_name, ts.start_time, ts.end_time
            from bookings b
            join computers c on c.computer_id = b.computer_id
            join time_slots ts on ts.slot_id = b.slot_id
            where b.user_id = :user_id
            order by b.booking_date desc, ts.start_time desc, b.created_at desc
        ');

        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function getActiveComputers(): array
    {
        return $this->pdo->query('
            select computer_id, computer_name, description
            from computers
            where is_active = true
            order by computer_name
        ')->fetchAll();
    }

    public function getTimeSlots(): array
    {
        return $this->pdo->query('
            select slot_id, start_time, end_time
            from time_slots
            order by start_time
        ')->fetchAll();
    }

    public function getAvailableCombinations(
        string $date,
        string $today,
        string $currentTime,
        array $slotIds = []
    ): array {
        $slotIds = array_values(array_unique(array_filter(
            array_map('intval', $slotIds),
            static fn(int $id): bool => $id > 0
        )));

        $slotFilter = '';

        $parameters = [
            'booking_date' => $date,
            'booking_date_filter' => $date,
            'today' => $today,
            'current_time' => $currentTime,
        ];

        if ($slotIds !== []) {
            $placeholders = [];

            foreach ($slotIds as $index => $slotId) {
                $key = 'slot_' . $index;

                $placeholders[] = ':' . $key;
                $parameters[$key] = $slotId;
            }

            $slotFilter = ' and ts.slot_id in ('
                . implode(', ', $placeholders)
                . ')';
        }

        $statement = $this->pdo->prepare("
            select
                c.computer_id,
                c.computer_name,
                ts.slot_id,
                ts.start_time,
                ts.end_time
            from computers c
            cross join time_slots ts
            where c.is_active = true
              and (
                  cast(:booking_date as date)
                  <> cast(:today as date)
                  or ts.start_time > cast(:current_time as time)
              )
              and not exists (
                  select 1
                  from bookings b
                  where b.computer_id = c.computer_id
                    and b.slot_id = ts.slot_id
                    and b.booking_date = :booking_date_filter
                    and (
                        b.status = 'booked'
                        or (
                            b.status = 'waiting'
                            and b.hold_expires_at > current_timestamp
                        )
                    )
              )
            " . $slotFilter . "
            order by ts.start_time, c.computer_name
        ");

        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function createBookings(
        int $userId,
        string $date,
        array $selections
    ): void {
        if ($selections === []) {
            throw new InvalidArgumentException(
                'Select at least one computer for a time slot.'
            );
        }

        $this->pdo->beginTransaction();

        try {
            foreach ($selections as $selection) {
                $computerId = (int) (
                    $selection['computer_id'] ?? 0
                );

                $slotId = (int) (
                    $selection['slot_id'] ?? 0
                );

                $this->createBooking(
                    $userId,
                    $computerId,
                    $slotId,
                    $date
                );
            }

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function createBooking(
        int $userId,
        int $computerId,
        int $slotId,
        string $date
    ): void {

        $statement = $this->pdo->prepare('
            insert into bookings (
                user_id,
                computer_id,
                slot_id,
                booking_date,
                status,
                confirmed_at
            )
            select
                :user_id,
                c.computer_id,
                ts.slot_id,
                :booking_date,
                \'booked\',
                current_timestamp
            from computers c
            cross join time_slots ts
            where c.computer_id = :computer_id
              and c.is_active = true
              and ts.slot_id = :slot_id
        ');

        $statement->execute([
            'user_id' => $userId,
            'booking_date' => $date,
            'computer_id' => $computerId,
            'slot_id' => $slotId,
        ]);

        if ($statement->rowCount() !== 1) {
            throw new InvalidArgumentException(
                'Choose an active computer and a valid time slot.'
            );
        }
    }

    public function cancelBooking(
        int $userId,
        int $bookingId
    ): bool {
        $statement = $this->pdo->prepare('
            update bookings
            set
                status = \'cancelled\',
                cancelled_at = current_timestamp
            where booking_id = :booking_id
              and user_id = :user_id
              and status in (\'waiting\', \'booked\')
        ');

        $statement->execute([
            'booking_id' => $bookingId,
            'user_id' => $userId,
        ]);

        return $statement->rowCount() === 1;
    }

    public function getStudentStats(int $userId): array
    {
        $statement = $this->pdo->prepare('
            select
                count(*) filter (
                    where status = \'booked\'
                      and booking_date >= current_date
                ) as upcoming,
                count(*) filter (
                    where status = \'booked\'
                ) as total_booked,
                count(*) filter (
                    where status = \'cancelled\'
                ) as cancelled
            from bookings
            where user_id = :user_id
        ');

        $statement->execute(['user_id' => $userId]);

        return $statement->fetch() ?: [
            'upcoming' => 0,
            'total_booked' => 0,
            'cancelled' => 0,
        ];
    }
}