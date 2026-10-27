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

    public function getAvailableComputersAcrossSlots(
        string $date,
        string $today,
        string $currentTime,
        array $slotIds
    ): array {
        $slotIds = array_values(array_map('intval', $slotIds));

        if ($slotIds === [] || count($slotIds) !== count(array_unique($slotIds))) {
            return [];
        }

        $placeholders = [];
        $parameters = [
            'booking_date_today' => $date,
            'booking_date_conflict' => $date,
            'today' => $today,
            'current_time' => $currentTime,
            'slot_count' => count($slotIds),
        ];

        foreach ($slotIds as $index => $slotId) {
            $key = 'selected_slot_' . $index;
            $placeholders[] = ':' . $key;
            $parameters[$key] = $slotId;
        }

        $statement = $this->pdo->prepare('
            select c.computer_id, c.computer_name, c.description
            from computers c
            cross join unnest(array[' . implode(', ', $placeholders) . ']::integer[])
                as selected(slot_id)
            join time_slots ts on ts.slot_id = selected.slot_id
            where c.is_active = true
              and (
                  cast(:booking_date_today as date) <> cast(:today as date)
                  or ts.start_time > cast(:current_time as time)
              )
              and not exists (
                  select 1
                  from bookings b
                  where b.computer_id = c.computer_id
                    and b.slot_id = ts.slot_id
                    and b.booking_date = cast(:booking_date_conflict as date)
                    and (
                        b.status = \'booked\'
                        or (
                            b.status = \'waiting\'
                            and b.hold_expires_at > current_timestamp
                        )
                    )
              )
            group by c.computer_id, c.computer_name, c.description
            having count(*) = :slot_count
            order by c.computer_name
        ');

        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function createHoldGroup(
        int $userId,
        string $date,
        int $computerId,
        array $slotIds
    ): string|false {
        $slotIds = array_values(array_map('intval', $slotIds));

        if (
            $userId < 1
            || $computerId < 1
            || $slotIds === []
            || count($slotIds) !== count(array_unique($slotIds))
        ) {
            throw new InvalidArgumentException('Choose a valid computer and at least one time slot.');
        }

        $uuidBytes = random_bytes(16);
        $uuidBytes[6] = chr((ord($uuidBytes[6]) & 0x0f) | 0x40);
        $uuidBytes[8] = chr((ord($uuidBytes[8]) & 0x3f) | 0x80);
        $uuidHex = bin2hex($uuidBytes);
        $groupId = sprintf(
            '%s-%s-%s-%s-%s',
            substr($uuidHex, 0, 8),
            substr($uuidHex, 8, 4),
            substr($uuidHex, 12, 4),
            substr($uuidHex, 16, 4),
            substr($uuidHex, 20, 12)
        );

        $this->pdo->beginTransaction();

        try {
            $expireParameters = [
                'computer_id' => $computerId,
                'booking_date' => $date,
            ];
            $expireSlotParams = [];

            foreach ($slotIds as $index => $slotId) {
                $key = 'expire_slot_' . $index;
                $expireSlotParams[] = ':' . $key;
                $expireParameters[$key] = $slotId;
            }

            $expire = $this->pdo->prepare('
                update bookings
                set status = \'cancelled\',
                    hold_expires_at = null,
                    cancelled_at = current_timestamp
                where booking_date = :booking_date
                  and computer_id = :computer_id
                  and slot_id in (' . implode(', ', $expireSlotParams) . ')
                  and status = \'waiting\'
                  and hold_expires_at <= current_timestamp
            ');
            $expire->execute($expireParameters);

            $values = [];
            $insertParameters = [
                'user_id' => $userId,
                'computer_id' => $computerId,
                'booking_date' => $date,
                'booking_date_conflict' => $date,
                'booking_group_id' => $groupId,
                'booking_group_size' => count($slotIds),
            ];

            foreach ($slotIds as $index => $slotId) {
                $key = 'insert_slot_' . $index;
                $values[] = '(cast(:' . $key . ' as integer))';
                $insertParameters[$key] = $slotId;
            }

            $insert = $this->pdo->prepare('
                insert into bookings (
                    user_id,
                    computer_id,
                    slot_id,
                    booking_date,
                    status,
                    hold_expires_at,
                    booking_group_id,
                    booking_group_size
                )
                select :user_id,
                       c.computer_id,
                       selected.slot_id,
                       cast(:booking_date as date),
                       \'waiting\',
                       current_timestamp + interval \'2 minutes\',
                       cast(:booking_group_id as uuid),
                       :booking_group_size
                from computers c
                cross join (values ' . implode(', ', $values) . ') as selected(slot_id)
                join time_slots ts on ts.slot_id = selected.slot_id
                where c.computer_id = :computer_id
                  and c.is_active = true
                                    and not exists (
                                            select 1
                                            from bookings existing
                                            where existing.booking_date = cast(:booking_date_conflict as date)
                                                and existing.computer_id = c.computer_id
                                                and existing.slot_id = selected.slot_id
                                                and (
                                                        existing.status = \'booked\'
                                                        or (
                                                                existing.status = \'waiting\'
                                                                and existing.hold_expires_at > current_timestamp
                                                        )
                                                )
                                    )
                on conflict (booking_date, computer_id, slot_id)
                    where status in (\'waiting\', \'booked\')
                do nothing
                returning booking_id
            ');
            $insert->execute($insertParameters);
            $inserted = $insert->fetchAll();

            if (count($inserted) !== count($slotIds)) {
                $this->pdo->rollBack();
                return false;
            }

            $this->pdo->commit();

            return $groupId;
        } catch (PDOException $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            if ($exception->getCode() === '23505') {
                return false;
            }

            throw $exception;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function getWaitingBookingGroup(int $userId, string $groupId): array
    {
        $statement = $this->pdo->prepare('
            select b.booking_group_size, b.booking_date, b.hold_expires_at,
                   c.computer_name, ts.start_time, ts.end_time
            from bookings b
            join computers c on c.computer_id = b.computer_id
            join time_slots ts on ts.slot_id = b.slot_id
            where b.booking_group_id = cast(:group_id as uuid)
              and b.user_id = :user_id
              and b.status = \'waiting\'
                            and b.hold_expires_at > current_timestamp
            order by ts.start_time
        ');
        $statement->execute([
            'group_id' => $groupId,
            'user_id' => $userId,
        ]);
        $rows = $statement->fetchAll();

        if ($rows === []) {
            $this->releaseWaitingBookingGroup($userId, $groupId);
            return [];
        }

        $expectedCount = (int) $rows[0]['booking_group_size'];
        $validGroup = true;

        foreach ($rows as $row) {
            if (
                (int) $row['booking_group_size'] !== $expectedCount
                || (string) $row['booking_date'] !== (string) $rows[0]['booking_date']
                || (string) $row['computer_name'] !== (string) $rows[0]['computer_name']
                || (string) $row['hold_expires_at'] !== (string) $rows[0]['hold_expires_at']
            ) {
                $validGroup = false;
                break;
            }
        }

        if (
            $expectedCount !== count($rows)
            || !$validGroup
        ) {
            $this->releaseWaitingBookingGroup($userId, $groupId);
            return [];
        }

        return $rows;
    }

    public function confirmBookingGroup(int $userId, string $groupId): bool
    {
        $this->pdo->beginTransaction();

        try {
            $select = $this->pdo->prepare('
                select booking_id,
                       booking_group_size,
                       hold_expires_at,
                       case
                           when hold_expires_at > current_timestamp then 1
                           else 0
                       end as is_unexpired
                from bookings
                where booking_group_id = cast(:group_id as uuid)
                  and user_id = :user_id
                  and status = \'waiting\'
                for update
            ');
            $select->execute([
                'group_id' => $groupId,
                'user_id' => $userId,
            ]);
            $rows = $select->fetchAll();
            $isUnexpired = $rows !== []
                && (int) $rows[0]['is_unexpired'] === 1;
            $expectedCount = $rows === []
                ? 0
                : (int) $rows[0]['booking_group_size'];
            $consistentGroup = $rows !== [];

            foreach ($rows as $row) {
                if (
                    (int) $row['booking_group_size'] !== $expectedCount
                    || (string) $row['hold_expires_at']
                        !== (string) $rows[0]['hold_expires_at']
                ) {
                    $consistentGroup = false;
                    break;
                }
            }

            if (
                $rows === []
                || !$consistentGroup
                || count($rows) !== $expectedCount
                || !$isUnexpired
            ) {
                $this->pdo->rollBack();
                $this->releaseWaitingBookingGroup($userId, $groupId);
                return false;
            }

            $update = $this->pdo->prepare('
                update bookings
                set status = \'booked\',
                    hold_expires_at = null,
                    confirmed_at = current_timestamp
                where booking_group_id = cast(:group_id as uuid)
                  and user_id = :user_id
                  and status = \'waiting\'
                  and hold_expires_at > current_timestamp
            ');
            $update->execute([
                'group_id' => $groupId,
                'user_id' => $userId,
            ]);

            if ($update->rowCount() !== $expectedCount) {
                $this->pdo->rollBack();
                return false;
            }

            $this->pdo->commit();

            return true;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function releaseWaitingBookingGroup(int $userId, string $groupId): bool
    {
        $statement = $this->pdo->prepare('
            update bookings
            set status = \'cancelled\',
                hold_expires_at = null,
                cancelled_at = current_timestamp
            where booking_group_id = cast(:group_id as uuid)
              and user_id = :user_id
              and status = \'waiting\'
        ');
        $statement->execute([
            'group_id' => $groupId,
            'user_id' => $userId,
        ]);

        return $statement->rowCount() > 0;
    }

    public function getConfirmedBookingGroup(int $userId, string $groupId): array
    {
        $statement = $this->pdo->prepare('
            select b.booking_group_size, b.booking_date,
                   c.computer_name, ts.start_time, ts.end_time
            from bookings b
            join computers c on c.computer_id = b.computer_id
            join time_slots ts on ts.slot_id = b.slot_id
            where b.booking_group_id = cast(:group_id as uuid)
              and b.user_id = :user_id
              and b.status = \'booked\'
            order by ts.start_time
        ');
        $statement->execute([
            'group_id' => $groupId,
            'user_id' => $userId,
        ]);
        $rows = $statement->fetchAll();

        if (
            $rows === []
            || count($rows) !== (int) $rows[0]['booking_group_size']
        ) {
            return [];
        }

        return $rows;
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

    public function cancelBooking(
        int $userId,
        int $bookingId
    ): bool {
        $statement = $this->pdo->prepare('
            with target as (
                select booking_id, status, booking_group_id
                from bookings
                where booking_id = :lookup_booking_id
                  and user_id = :lookup_user_id
                  and status in (\'waiting\', \'booked\')
            )
            update bookings b
            set
                status = \'cancelled\',
                hold_expires_at = null,
                confirmed_at = null,
                cancelled_at = current_timestamp
            from target t
            where b.user_id = :owner_user_id
              and b.status in (\'waiting\', \'booked\')
              and (
                  (
                      t.status = \'waiting\'
                      and (
                          (t.booking_group_id is not null
                              and b.booking_group_id = t.booking_group_id)
                          or
                          (t.booking_group_id is null
                              and b.booking_id = t.booking_id)
                      )
                  )
                  or (t.status = \'booked\' and b.booking_id = t.booking_id)
              )
        ');

        $statement->execute([
            'lookup_booking_id' => $bookingId,
            'lookup_user_id' => $userId,
            'owner_user_id' => $userId,
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