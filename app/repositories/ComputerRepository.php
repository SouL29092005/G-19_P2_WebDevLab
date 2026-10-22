<?php

declare(strict_types=1);

final class ComputerRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }


    public function getAvailabilityForDate(string $date): array
    {
        $sql = '
            select
                c.computer_id,
                c.computer_name,
                c.description,
                ts.slot_id,
                ts.start_time,
                ts.end_time,
                case
                    when b.booking_id is null then true
                    else false
                end as is_available
            from computers c
            cross join time_slots ts
            left join bookings b
                on b.computer_id = c.computer_id
                and b.slot_id = ts.slot_id
                and b.booking_date = :booking_date
                and (
                    b.status = \'booked\'
                    or (
                        b.status = \'waiting\'
                        and b.hold_expires_at > current_timestamp
                    )
                )
            where c.is_active = true
            order by
                c.computer_name,
                ts.start_time
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'booking_date' => $date,
        ]);

        return $statement->fetchAll();
    }
}
