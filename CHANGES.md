# UI redesign notes

## Phase 1: Existing application map

This is a server-rendered PHP application. `public/index.php` is the front
controller for `/`, `/login`, `/availability`, and the protected admin routes.
The direct student endpoints are `public/dashboard.php`,
`public/new-booking.php`, `public/confirm-booking.php`, and
`public/my-bookings.php`. `public/.htaccess` sends clean paths to the front
controller while allowing real PHP files to be served directly.

### Route and data flow

- The front controller loads `config/app.php`, starts the shared session,
  resolves a method/path pair, invokes the relevant controller/repository, and
  renders the shared header, selected view, and footer.
- Login posts `email`, `password`, and `csrf_token` to `/login.php`. Logout is
  the existing `/login.php?action=logout` link. The session contains the
  authenticated user's id, name, email, and role.
- `/availability` reads `date`, `slot_ids[]`, and `status`; its existing
  `ajax=availability` GET response refreshes those filters every five seconds.
- The student booking discovery form uses GET `date` and `slots[]`. Its
  existing `new-booking.php?ajax=availability` poll returns matching computers
  for `date` and `slots[]`. The separate POST hold form sends `csrf_token`,
  `action=hold`, `booking_date`, `slot_ids[]`, and `computer_id`.
- The confirmation page uses `group` on GET and `group_id`, `action`,
  `csrf_token` on POST. The existing actions are `confirm` and `cancel`.
- Student booking cancellation posts `action=cancel`, `booking_id`, and
  `csrf_token` to `/my-bookings.php`.
- Admin routes are `/admin/`, `/admin/computers`, `/admin/users`, and
  `/admin/bookings`. Computer creation posts `computer_name` and
  `description`; user create/delete actions use `action`, `full_name`, `email`,
  `password`, `role`, or `user_id`, plus `csrf_token`.
- `StudentController` supplies student stats, upcoming bookings, booking
  history, slot availability, temporary hold details, and flash-message text.
  `ComputerController` supplies public availability rows, slot filters, and
  status options. Admin controllers supply computer/user rows and form state;
  admin bookings use `BookingRepository::getAllBookings()`.
- `admin/dashboard.php` currently receives no statistics or chart series.
  Any dashboard visualization without backend data must be explicitly labelled
  as sample/demo data and marked as needing a backend endpoint.

### Existing frontend behavior

- `availability.js` serializes the public filters and polls the current
  endpoint; `new-booking.js` validates past slots for presentation, polls
  availability, retains a valid computer selection, and creates the required
  hidden slot inputs before the hold POST.
- Server-rendered page data is the no-JavaScript fallback. The controllers
  remain authoritative for validation, availability, ownership, CSRF, booking
  holds, and confirmation.
- Existing views escape dynamic text with `htmlspecialchars`; all redesigned
  templates must continue that pattern and retain every output variable,
  conditional, loop, form name, hidden field, and endpoint contract.

## Assumptions and constraints

- The requested MySQL stack conflicts with the checked-in application: its
  schema and connection bootstrap use PostgreSQL (`pgsql:`), and booking
  transactions use PostgreSQL-specific SQL. Backend and database files will
  remain unchanged; setup documentation will describe the actual PostgreSQL
  requirement rather than claim this app runs on MySQL.
- `APP_BASE_URL` is currently `http://lab-reservation.local:8080`. The
  redesign will use that existing URL helper for links and assets rather than
  changing application configuration or route behavior.
- No admin aggregate/chart endpoint exists. Any visual demo series will be
  labelled sample-only with a `TODO: backend endpoint needed` note in code.
- External fonts/icons/chart/date-picker enhancements are progressive
  enhancements; core navigation, form controls, and server-rendered data must
  remain usable if a CDN is unavailable.

## UI implementation

### Shared design system and shell

- Replaced the shared stylesheet with a token-based light/dark theme, responsive
  spacing and typography, elevated surfaces, status colors, visible focus
  treatment, reduced-motion handling, and responsive table/card layouts.
- Updated the shared header and footer with role-aware navigation, a mobile
  menu, account dropdown, early theme selection from `localStorage`, toast
  region, scroll progress, and back-to-top control.
- Added `public/assets/js/app.js` for theme persistence, password visibility,
  reveal/count-up effects, inline form feedback, confirmation dialogs, table
  search/sort/pagination/CSV export, the student booking calendar, and chart
  rendering. Booking/calendar/table enhancements remain client-side.
- Used CDN assets: DM Sans and Manrope (Google Fonts), Font Awesome 6.5.2,
  jQuery 3.7.1, Flatpickr, SweetAlert2 11, and Chart.js 4.4.3. Core templates
  continue to render without the enhancements.

### Redesigned views

- Home: animated gradient hero, truthful schedule details, feature cards,
  booking steps, and calls to action.
- Login: split layout, accessible password visibility control, native/client
  validation, and server-rendered error feedback.
- Availability: server-rendered visual computer/slot map, status legend, search,
  update state, and accessible status tooltips; the existing polling URL and
  filter names are unchanged.
- Student: animated dashboard stats, upcoming sessions, booking stepper and
  live summary, group-slot selection, confirmation countdown, searchable and
  filterable booking history, calendar, and cancellation confirmation.
- Booking history/admin ledger: booked sessions whose scheduled end time has
  passed are presented as **Expired** and can be filtered separately. This is
  derived in the templates using `APP_TIMEZONE`; the stored booking status and
  backend logic remain unchanged.
- Admin: management dashboard, responsive/searchable/sortable/paginated tables,
  CSV export, account/computer forms, and reservation ledger. Since the current
  admin dashboard route has no analytics data, charts are clearly marked
  sample-only. `public/assets/js/app.js` includes the
  `TODO: backend endpoint needed` note for replacing those samples.

### Compatibility and verification

- No controllers, repositories, middleware, schema, public routes, session
  logic, or database configuration were changed.
- Existing POST/GET names, hidden CSRF/group/action inputs, and AJAX endpoints
  remain in place. Dynamic output in the redesigned templates is escaped.
- `README.md` now documents the no-build frontend runtime and clarifies that
  the current backend requires PostgreSQL, not XAMPP MySQL.
- PHP syntax checks passed for all redesigned templates. JavaScript syntax
  checks passed. The landing page, login page, and database-backed public
  availability view rendered in local XAMPP; the availability view returned
  12 computers and 336 slot tiles. Login theme persistence, password reveal,
  and mobile navigation were exercised with no browser console errors.
- Authenticated student/admin booking flows still require sign-in and were not
  submitted during this UI-only validation; their server-side logic and
  endpoint contracts were left unchanged.

## Remaining backend-dependent work

- Supply a protected admin analytics endpoint (booking counts, trends, and
  computer utilization) before replacing the labelled demo chart series.
- The database driver/configuration remain PostgreSQL-specific by design; a
  MySQL migration is outside this frontend-only change.
