<?php

declare(strict_types=1);

final class ComputerRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }


    public function getTimeSlots(): array
    {
        $sql = '
            select
                slot_id,
                start_time,
                end_time
            from time_slots
            order by start_time
        ';

        return $this->pdo->query($sql)->fetchAll();
    }


    public function getAvailabilityForDate(
        string $date,
        array $slotIds,
        string $status
    ): array
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
                    when b.booking_id is null then \'available\'
                    else b.status
                end as status
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
        ';

        $parameters = [
            'booking_date' => $date,
        ];

        if ($slotIds !== []) {
            $slotPlaceholders = [];

            foreach ($slotIds as $index => $slotId) {
                $placeholder = 'slot_id_' . $index;
                $slotPlaceholders[] = ':' . $placeholder;
                $parameters[$placeholder] = $slotId;
            }

            $sql .= ' and ts.slot_id in ('
                . implode(', ', $slotPlaceholders)
                . ')';
        }

        if ($status === 'available') {
            $sql .= ' and b.booking_id is null';
        } elseif (in_array($status, ['waiting', 'booked'], true)) {
            $sql .= ' and b.status = :status';
            $parameters['status'] = $status;
        }

        $sql .= '
            order by
                c.computer_name,
                ts.start_time
        ';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    
    public function getAllComputers(): array
    {
        $sql = '
            select
                computer_id,
                computer_name,
                description,
                is_active,
                created_at
            from computers
            order by computer_name
        ';

        return $this->pdo->query($sql)->fetchAll();
    }


}
