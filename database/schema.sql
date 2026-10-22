
-- 1. USERS

create table users (
    user_id bigint generated always as identity primary key,
    full_name varchar(100) not null,
    email varchar(255) not null unique,
    password_hash text not null,
    role varchar(20) not null default 'student' 
    check (role in ('student', 'admin')),
    created_at timestamptz not null default current_timestamp
);


-- 2. COMPUTERS

create table computers (
    computer_id bigint generated always as identity primary key,
    computer_name varchar(50) not null unique, description text,
    is_active boolean not null default true,
    created_at timestamptz not null default current_timestamp
);


-- 3. TIME SLOTS
-- Stores the 28 fixed 30-minute slots from 08:00 to 22:00.

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


-- 4. BOOKINGS

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

    created_at timestamptz not null default current_timestamp,

    confirmed_at timestamptz,

    cancelled_at timestamptz,

    constraint valid_hold_expiration
        check (
            (status = 'waiting' and hold_expires_at is not null)
            or
            (status <> 'waiting' and hold_expires_at is null)
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


-- 5. BOOKING LOOKUP INDEXES

create index idx_bookings_date_slot
    on bookings (booking_date, slot_id);

create index idx_bookings_user_date
    on bookings (user_id, booking_date);

create index idx_bookings_hold_expiration
    on bookings (hold_expires_at)
    where status = 'waiting';


-- 6. PREVENT CONFLICTING RESERVATIONS

create unique index uq_active_computer_slot_booking
    on bookings (booking_date, computer_id, slot_id)
    where status in ('waiting', 'booked');

