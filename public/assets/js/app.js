(function () {
    'use strict';

    const root = document.documentElement;
    const toastRegion = document.getElementById('toast-region');

    function setTheme(theme) {
        root.dataset.theme = theme;
        try {
            localStorage.setItem('lab-theme', theme);
        } catch (error) {
            // The selected theme still applies for this page when storage is unavailable.
        }

        document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
            const dark = theme === 'dark';
            button.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
            button.innerHTML = dark
                ? '<i class="fa-regular fa-sun" aria-hidden="true"></i>'
                : '<i class="fa-regular fa-moon" aria-hidden="true"></i>';
        });
    }

    document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            setTheme(root.dataset.theme === 'dark' ? 'light' : 'dark');
        });
    });
    setTheme(root.dataset.theme === 'dark' ? 'dark' : 'light');

    const navToggle = document.getElementById('nav-toggle');
    const navigation = document.getElementById('primary-navigation');
    if (navToggle && navigation) {
        navToggle.addEventListener('click', function () {
            const expanded = navToggle.getAttribute('aria-expanded') === 'true';
            navToggle.setAttribute('aria-expanded', String(!expanded));
            navToggle.setAttribute('aria-label', expanded ? 'Open navigation' : 'Close navigation');
            navigation.classList.toggle('is-open', !expanded);
        });
        navigation.addEventListener('click', function (event) {
            if (event.target.closest('a')) {
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.setAttribute('aria-label', 'Open navigation');
                navigation.classList.remove('is-open');
            }
        });
    }

    const accountToggle = document.getElementById('account-menu-toggle');
    const accountMenu = document.getElementById('account-menu');
    if (accountToggle && accountMenu) {
        function closeAccountMenu() {
            accountMenu.hidden = true;
            accountToggle.setAttribute('aria-expanded', 'false');
        }

        accountToggle.addEventListener('click', function () {
            const open = accountToggle.getAttribute('aria-expanded') === 'true';
            accountToggle.setAttribute('aria-expanded', String(!open));
            accountMenu.hidden = open;
        });
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.account-menu')) {
                closeAccountMenu();
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeAccountMenu();
                accountToggle.focus();
            }
        });
    }

    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        const input = document.getElementById(button.getAttribute('aria-controls'));
        if (!input) {
            return;
        }
        button.addEventListener('click', function () {
            const showPassword = input.type === 'password';
            input.type = showPassword ? 'text' : 'password';
            button.textContent = showPassword ? 'Hide' : 'Show';
            button.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
            input.focus();
        });
    });

    const scrollProgress = document.getElementById('scroll-progress');
    const backToTop = document.getElementById('back-to-top');
    function updateScrollControls() {
        const scrollable = document.documentElement.scrollHeight - window.innerHeight;
        const progress = scrollable > 0 ? (window.scrollY / scrollable) * 100 : 0;
        if (scrollProgress) {
            scrollProgress.style.width = progress + '%';
        }
        if (backToTop) {
            backToTop.classList.toggle('is-visible', window.scrollY > 420);
        }
    }
    window.addEventListener('scroll', updateScrollControls, { passive: true });
    updateScrollControls();
    if (backToTop) {
        backToTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const revealObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        document.querySelectorAll('[data-reveal]').forEach(function (element) {
            revealObserver.observe(element);
        });
    } else {
        document.querySelectorAll('[data-reveal]').forEach(function (element) {
            element.classList.add('is-revealed');
        });
    }

    document.querySelectorAll('[data-count-up]').forEach(function (element) {
        const target = Number(element.dataset.countUp);
        if (!Number.isFinite(target) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            if (Number.isFinite(target)) {
                element.textContent = String(target);
            }
            return;
        }
        const duration = 850;
        const start = performance.now();
        function tick(now) {
            const ratio = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - ratio, 3);
            element.textContent = String(Math.round(target * eased));
            if (ratio < 1) {
                window.requestAnimationFrame(tick);
            }
        }
        window.requestAnimationFrame(tick);
    });

    function showToast(message, kind) {
        if (!toastRegion || !message) {
            return;
        }
        const toast = document.createElement('div');
        toast.className = 'toast toast-' + kind;
        toast.setAttribute('role', kind === 'error' ? 'alert' : 'status');
        const icon = document.createElement('i');
        icon.className = kind === 'error'
            ? 'fa-solid fa-circle-exclamation'
            : 'fa-solid fa-circle-check';
        icon.setAttribute('aria-hidden', 'true');
        const text = document.createElement('span');
        text.textContent = message;
        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'toast-close icon-button';
        close.setAttribute('aria-label', 'Dismiss notification');
        close.innerHTML = '<i class="fa-solid fa-xmark" aria-hidden="true"></i>';
        close.addEventListener('click', function () {
            toast.remove();
        });
        toast.append(icon, text, close);
        toastRegion.appendChild(toast);
        window.setTimeout(function () {
            toast.classList.add('is-leaving');
            window.setTimeout(function () {
                toast.remove();
            }, 220);
        }, kind === 'error' ? 8000 : 5200);
    }

    document.querySelectorAll('[data-toast]').forEach(function (message) {
        showToast(message.textContent.trim(), message.dataset.toast || 'success');
        message.hidden = true;
    });

    document.querySelectorAll('form').forEach(function (form) {
        if (form.matches('[data-validate]')) {
            form.noValidate = true;
        }
        form.addEventListener('submit', function (event) {
            const invalid = form.querySelector(':invalid');
            if (invalid) {
                event.preventDefault();
                invalid.setAttribute('aria-invalid', 'true');
                const field = invalid.closest('.form-group, .login-field, .filter-field');
                if (field && !field.querySelector('.field-validation-message')) {
                    const message = document.createElement('small');
                    message.className = 'field-validation-message';
                    message.textContent = invalid.validationMessage || 'Please check this field.';
                    field.appendChild(message);
                }
                invalid.focus();
                invalid.reportValidity();
            }
        });
        form.addEventListener('input', function (event) {
            if (event.target.matches('input, textarea, select') && event.target.checkValidity()) {
                event.target.removeAttribute('aria-invalid');
                const field = event.target.closest('.form-group, .login-field, .filter-field');
                const message = field && field.querySelector('.field-validation-message');
                if (message) {
                    message.remove();
                }
            }
        });
        form.addEventListener('change', function (event) {
            if (event.target.matches('input, textarea, select') && event.target.checkValidity()) {
                event.target.removeAttribute('aria-invalid');
            }
        });
    });

    document.querySelectorAll('[data-confirm-message]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.confirmed === 'true') {
                return;
            }
            event.preventDefault();
            const message = form.dataset.confirmMessage;
            if (window.Swal) {
                window.Swal.fire({
                    title: 'Are you sure?',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Continue',
                    cancelButtonText: 'Keep it',
                    reverseButtons: true,
                    customClass: { popup: 'lab-modal' }
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.dataset.confirmed = 'true';
                        form.requestSubmit();
                    }
                });
            } else if (window.confirm(message)) {
                form.dataset.confirmed = 'true';
                form.requestSubmit();
            }
        });
    });

    if (window.flatpickr) {
        document.querySelectorAll('input[type="date"]').forEach(function (input) {
            window.flatpickr(input, {
                dateFormat: 'Y-m-d',
                minDate: input.min || null,
                maxDate: input.max || null,
                disableMobile: false,
                allowInput: true
            });
        });
    }

    document.querySelectorAll('[data-toggle-target]').forEach(function (button) {
        const target = document.getElementById(button.dataset.toggleTarget);
        if (!target) {
            return;
        }
        button.addEventListener('click', function () {
            const visible = target.hidden;
            target.hidden = !visible;
            document.querySelectorAll('[data-toggle-target="' + button.dataset.toggleTarget + '"]').forEach(function (toggle) {
                toggle.setAttribute('aria-expanded', String(visible));
            });
            if (visible) {
                target.querySelector('input, textarea, select')?.focus();
            }
        });
    });

    document.querySelectorAll('[data-countdown]').forEach(function (counter) {
        let seconds = Math.max(0, Number(counter.dataset.countdown) || 0);
        const confirmButton = document.getElementById('confirm-booking-button');
        function renderCountdown() {
            if (seconds <= 0) {
                counter.textContent = 'Expired';
                if (confirmButton) {
                    confirmButton.disabled = true;
                }
                return;
            }
            counter.textContent = Math.floor(seconds / 60) + ':' + String(seconds % 60).padStart(2, '0');
            seconds -= 1;
            window.setTimeout(renderCountdown, 1000);
        }
        renderCountdown();
    });

    function tableRows(table) {
        return Array.from(table.querySelectorAll('tbody tr')).filter(function (row) {
            return !row.classList.contains('empty-row');
        });
    }

    document.querySelectorAll('[data-table-tools]').forEach(function (toolbar) {
        const table = document.getElementById(toolbar.dataset.tableTarget);
        if (!table) {
            return;
        }
        const search = toolbar.querySelector('[data-table-search]');
        const pageSize = toolbar.querySelector('[data-table-page-size]');
        const pagination = toolbar.parentElement.querySelector('[data-table-pagination]');
        let currentPage = 1;
        let sortColumn = -1;
        let sortDirection = 1;
        let activeFilter = 'all';
        const allRows = tableRows(table);
        const emptyState = toolbar.parentElement.querySelector('[data-table-empty]');

        function rowMatchesFilter(row) {
            if (activeFilter === 'all') {
                return true;
            }
            const status = row.dataset.status || '';
            const displayStatus = row.dataset.displayStatus || status;
            const bookingDate = row.dataset.bookingDate || '';
            const today = new Date();
            const todayString = toolbar.dataset.today || [
                today.getFullYear(),
                String(today.getMonth() + 1).padStart(2, '0'),
                String(today.getDate()).padStart(2, '0')
            ].join('-');
            if (activeFilter === 'cancelled') {
                return status === 'cancelled';
            }
            if (activeFilter === 'upcoming') {
                return ['booked', 'waiting'].includes(status)
                    && displayStatus !== 'expired'
                    && bookingDate >= todayString;
            }
            if (activeFilter === 'past') {
                return ['booked', 'waiting'].includes(status)
                    && (bookingDate < todayString || displayStatus === 'expired');
            }
            if (activeFilter === 'expired') {
                return displayStatus === 'expired';
            }
            return status === activeFilter;
        }

        function renderTable() {
            const query = search ? search.value.trim().toLocaleLowerCase() : '';
            const filtered = allRows.filter(function (row) {
                return rowMatchesFilter(row) && row.textContent.toLocaleLowerCase().includes(query);
            });
            if (emptyState) {
                emptyState.hidden = allRows.length === 0 || filtered.length !== 0;
            }
            const limit = pageSize ? Number(pageSize.value) : 10;
            const pageCount = Math.max(1, Math.ceil(filtered.length / limit));
            currentPage = Math.min(currentPage, pageCount);
            const start = (currentPage - 1) * limit;
            const shown = new Set(filtered.slice(start, start + limit));
            allRows.forEach(function (row) {
                row.hidden = !shown.has(row);
            });

            if (pagination) {
                pagination.innerHTML = '';
                const summary = document.createElement('span');
                summary.textContent = filtered.length
                    ? 'Showing ' + (start + 1) + '–' + Math.min(start + limit, filtered.length) + ' of ' + filtered.length
                    : 'No matching records';
                const previous = document.createElement('button');
                previous.type = 'button';
                previous.className = 'button button-secondary button-small';
                previous.textContent = 'Previous';
                previous.disabled = currentPage <= 1;
                previous.addEventListener('click', function () {
                    currentPage -= 1;
                    renderTable();
                });
                const next = document.createElement('button');
                next.type = 'button';
                next.className = 'button button-secondary button-small';
                next.textContent = 'Next';
                next.disabled = currentPage >= pageCount;
                next.addEventListener('click', function () {
                    currentPage += 1;
                    renderTable();
                });
                pagination.append(summary, previous, next);
            }
        }

        if (search) {
            search.addEventListener('input', function () {
                currentPage = 1;
                renderTable();
            });
        }
        if (pageSize) {
            pageSize.addEventListener('change', function () {
                currentPage = 1;
                renderTable();
            });
        }
        toolbar.querySelectorAll('[data-filter-value]').forEach(function (button) {
            button.addEventListener('click', function () {
                activeFilter = button.dataset.filterValue || 'all';
                currentPage = 1;
                toolbar.querySelectorAll('[data-filter-value]').forEach(function (filter) {
                    filter.classList.toggle('is-active', filter === button);
                    filter.setAttribute('aria-pressed', String(filter === button));
                });
                renderTable();
            });
        });
        table.querySelectorAll('thead th[data-sortable]').forEach(function (heading, index) {
            heading.tabIndex = 0;
            heading.setAttribute('role', 'button');
            heading.addEventListener('click', sort);
            heading.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    sort();
                }
            });
            function sort() {
                if (sortColumn === index) {
                    sortDirection *= -1;
                } else {
                    sortColumn = index;
                    sortDirection = 1;
                }
                const body = table.querySelector('tbody');
                allRows.sort(function (a, b) {
                    const left = (a.cells[index]?.textContent || '').trim();
                    const right = (b.cells[index]?.textContent || '').trim();
                    const leftNumber = Number(left);
                    const rightNumber = Number(right);
                    const comparison = left !== '' && right !== '' && Number.isFinite(leftNumber) && Number.isFinite(rightNumber)
                        ? leftNumber - rightNumber
                        : left.localeCompare(right, undefined, { numeric: true, sensitivity: 'base' });
                    return comparison * sortDirection;
                }).forEach(function (row) {
                    body.appendChild(row);
                });
                currentPage = 1;
                renderTable();
            }
        });
        renderTable();
    });

    document.querySelectorAll('[data-table-export]').forEach(function (button) {
        button.addEventListener('click', function () {
            const table = document.getElementById(button.dataset.tableExport);
            if (!table) {
                return;
            }
            const rows = Array.from(table.querySelectorAll('tr')).filter(function (row) {
                return row.offsetParent !== null;
            });
            const csv = rows.map(function (row) {
                return Array.from(row.cells).map(function (cell) {
                    return '"' + cell.innerText.trim().replace(/"/g, '""') + '"';
                }).join(',');
            }).join('\r\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = (button.dataset.filename || 'labspace-export') + '.csv';
            link.click();
            URL.revokeObjectURL(link.href);
        });
    });

    document.querySelectorAll('[data-booking-calendar]').forEach(function (calendar) {
        const monthLabel = calendar.querySelector('[data-calendar-month]');
        const grid = calendar.querySelector('[data-calendar-grid]');
        const items = Array.from(document.querySelectorAll('[data-calendar-item]'));
        const monthControl = calendar.querySelectorAll('[data-calendar-shift]');
        if (!monthLabel || !grid) {
            return;
        }
        let month = new Date();
        month = new Date(month.getFullYear(), month.getMonth(), 1);
        function renderCalendar() {
            const year = month.getFullYear();
            const monthIndex = month.getMonth();
            monthLabel.textContent = month.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
            grid.innerHTML = '';
            ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].forEach(function (name) {
                const weekday = document.createElement('span');
                weekday.className = 'calendar-weekday';
                weekday.textContent = name;
                grid.appendChild(weekday);
            });
            const offset = (new Date(year, monthIndex, 1).getDay() + 6) % 7;
            const days = new Date(year, monthIndex + 1, 0).getDate();
            for (let index = 0; index < offset; index += 1) {
                const blank = document.createElement('span');
                blank.className = 'calendar-day is-outside';
                grid.appendChild(blank);
            }
            for (let day = 1; day <= days; day += 1) {
                const dayDate = [
                    year,
                    String(monthIndex + 1).padStart(2, '0'),
                    String(day).padStart(2, '0')
                ].join('-');
                const dayItems = items.filter(function (item) {
                    return item.dataset.calendarDate === dayDate;
                });
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'calendar-day' + (dayItems.length ? ' has-booking' : '');
                button.textContent = String(day);
                button.setAttribute('aria-label', dayDate + (dayItems.length ? ', ' + dayItems.length + ' booking(s)' : ''));
                if (dayItems.length) {
                    const dot = document.createElement('span');
                    dot.className = 'calendar-dot';
                    dot.setAttribute('aria-hidden', 'true');
                    button.appendChild(dot);
                    button.title = dayItems.length + ' booking(s)';
                }
                button.addEventListener('click', function () {
                    const table = document.getElementById('student-bookings-table');
                    if (table) {
                        table.querySelectorAll('tbody tr[data-booking-date]').forEach(function (row) {
                            row.hidden = dayItems.length ? row.dataset.bookingDate !== dayDate : false;
                        });
                    }
                });
                grid.appendChild(button);
            }
        }
        monthControl.forEach(function (button) {
            button.addEventListener('click', function () {
                month.setMonth(month.getMonth() + Number(button.dataset.calendarShift));
                renderCalendar();
            });
        });
        renderCalendar();
    });

    document.querySelectorAll('[data-booking-stepper]').forEach(function (stepper) {
        const form = document.getElementById('availability-form');
        const holdForm = document.getElementById('hold-form');
        if (!form) {
            return;
        }
        function updateStep() {
            const slotsSelected = form.querySelectorAll('input[name="slots[]"]:checked').length > 0;
            const computerSelected = holdForm && holdForm.querySelector('input[name="computer_id"]:checked');
            const activeStep = computerSelected ? 3 : (slotsSelected ? 2 : 1);
            stepper.querySelectorAll('[data-step]').forEach(function (step) {
                const number = Number(step.dataset.step);
                step.classList.toggle('is-current', number === activeStep);
                step.classList.toggle('is-complete', number < activeStep);
                if (number === activeStep) {
                    step.setAttribute('aria-current', 'step');
                } else {
                    step.removeAttribute('aria-current');
                }
            });
        }
        form.addEventListener('change', updateStep);
        if (holdForm) {
            holdForm.addEventListener('change', updateStep);
        }
        updateStep();
    });

    const bookingForm = document.getElementById('availability-form');
    const bookingHoldForm = document.getElementById('hold-form');
    if (bookingForm) {
        const dateInput = bookingForm.querySelector('#booking-date');
        const dateSummary = document.getElementById('summary-date');
        const slotsSummary = document.getElementById('summary-slots');
        const computerSummary = document.getElementById('summary-computer');

        function updateBookingSummary() {
            if (dateInput && dateSummary && dateInput.value) {
                const parts = dateInput.value.split('-').map(Number);
                const localDate = new Date(parts[0], parts[1] - 1, parts[2]);
                dateSummary.textContent = localDate.toLocaleDateString(undefined, {
                    weekday: 'short',
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            }
            if (slotsSummary) {
                const labels = Array.from(bookingForm.querySelectorAll('input[name="slots[]"]:checked'))
                    .map(function (input) {
                        return input.closest('label').querySelector('span').textContent.trim();
                    });
                slotsSummary.textContent = labels.length ? labels.join(', ') : 'Not selected';
            }
            if (computerSummary && bookingHoldForm) {
                const selected = bookingHoldForm.querySelector('input[name="computer_id"]:checked');
                computerSummary.textContent = selected
                    ? selected.closest('label').querySelector('strong').textContent.trim()
                    : 'Choose a computer';
            }
        }

        bookingForm.addEventListener('change', updateBookingSummary);
        if (bookingHoldForm) {
            bookingHoldForm.addEventListener('change', updateBookingSummary);
        }
        updateBookingSummary();
    }

    if (window.Chart) {
        document.querySelectorAll('[data-sample-chart]').forEach(function (canvas) {
            // TODO: backend endpoint needed for real admin booking trends and computer utilization.
            const type = canvas.dataset.sampleChart;
            const isUsage = type === 'usage';
            new window.Chart(canvas, {
                type: isUsage ? 'doughnut' : 'line',
                data: isUsage
                    ? {
                        labels: ['Station A', 'Station B', 'Station C', 'Other'],
                        datasets: [{
                            data: [34, 28, 22, 16],
                            backgroundColor: ['#6c63ff', '#31b7a4', '#78a7ff', '#d5d9e8'],
                            borderWidth: 0,
                            hoverOffset: 6
                        }]
                    }
                    : {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            label: 'Sample reservations',
                            data: [12, 19, 15, 24, 21, 29, 25],
                            borderColor: '#6c63ff',
                            backgroundColor: 'rgba(108, 99, 255, .12)',
                            fill: true,
                            tension: .38,
                            pointRadius: 3
                        }]
                    },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: isUsage, position: 'bottom' } },
                    scales: isUsage ? {} : {
                        y: { beginAtZero: true, grid: { color: 'rgba(120, 130, 160, .12)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    }
})();
