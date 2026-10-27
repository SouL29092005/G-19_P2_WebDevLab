alter table bookings
    add column if not exists booking_group_id uuid,
    add column if not exists booking_group_size integer;

do $$
begin
    if not exists (
        select 1
        from pg_constraint
        where conname = 'valid_booking_group'
          and conrelid = 'bookings'::regclass
    ) then
        alter table bookings
            add constraint valid_booking_group
            check (
                (booking_group_id is null and booking_group_size is null)
                or
                (booking_group_id is not null
                    and booking_group_size is not null
                    and booking_group_size > 0)
            );
    end if;
end;
$$;

create index if not exists idx_bookings_group_id
    on bookings (booking_group_id)
    where booking_group_id is not null;

create unique index if not exists uq_booking_group_slot
    on bookings (booking_group_id, slot_id)
    where booking_group_id is not null;