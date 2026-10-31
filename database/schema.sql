
-- Users hold login identity and role; password_hash must contain a password_hash() result, never plaintext.

create table users (
    user_id bigint generated always as identity primary key,
    full_name varchar(100) not null,
    email varchar(255) not null unique,
    password_hash text not null,
    role varchar(20) not null default 'student' 
    check (role in ('student', 'admin')),
    created_at timestamptz not null default current_timestamp
);


-- Computers may be deactivated without deleting historical booking references.

create table computers (
    computer_id bigint generated always as identity primary key,
    computer_name varchar(50) not null unique, description text,
    is_active boolean not null default true,
    created_at timestamptz not null default current_timestamp
);


-- Stores the fixed lab schedule. Seed data creates 28 consecutive 30-minute slots from 08:00 through 22:00.

create table time_slots (
    slot_id integer generated always as identity primary key,
    start_time time not null,
    end_time time not null,

    constraint valid_slot_duration
        check (end_time > start_time),

    constraint unique_slot_start
        unique (start_time),

    constraint unique_slot_end
        unique (end_time)
);


-- Each row represents one computer/date/slot; multi-slot reservations share booking_group_id and group_size.

create table bookings (
    booking_id bigint generated always as identity primary key,

    user_id bigint not null
        references users(user_id),

    computer_id bigint not null
        references computers(computer_id),

    slot_id integer not null
        references time_slots(slot_id),

    booking_date date not null,

    status varchar(20) not null default 'waiting'
        check (
            status in ('waiting', 'booked', 'cancelled')
        ),

    hold_expires_at timestamptz,

    booking_group_id uuid,

    booking_group_size integer,

    created_at timestamptz not null default current_timestamp,

    confirmed_at timestamptz,

    cancelled_at timestamptz,

    constraint valid_hold_expiration
        check (
            (status = 'waiting' and hold_expires_at is not null)
            or
            (status <> 'waiting' and hold_expires_at is null)
        ),

    constraint valid_booking_group
        check (
            (booking_group_id is null and booking_group_size is null)
            or
            (booking_group_id is not null
                and booking_group_size is not null
                and booking_group_size > 0)
        ),

    constraint valid_confirmation
        check (
            (status = 'booked' and confirmed_at is not null)
            or
            (status <> 'booked' and confirmed_at is null)
        ),

    constraint valid_cancellation
        check (
            (status = 'cancelled' and cancelled_at is not null)
            or
            (status <> 'cancelled' and cancelled_at is null)
        )
);


-- Lookup indexes support student history, date/slot availability, expiry cleanup, and group validation.

create index idx_bookings_date_slot
    on bookings (booking_date, slot_id);

create index idx_bookings_user_date
    on bookings (user_id, booking_date);

create index idx_bookings_hold_expiration
    on bookings (hold_expires_at)
    where status = 'waiting';

create index idx_bookings_group_id
    on bookings (booking_group_id)
    where booking_group_id is not null;

create unique index uq_booking_group_slot
    on bookings (booking_group_id, slot_id)
    where booking_group_id is not null;


-- Partial uniqueness makes waiting and booked rows mutually exclusive for a computer/date/slot.
-- Cancelled rows leave the active index, allowing that slot to be reserved again.

create unique index uq_active_computer_slot_booking
    on bookings (booking_date, computer_id, slot_id)
    where status in ('waiting', 'booked');

