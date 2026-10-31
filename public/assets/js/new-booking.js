(function ($) {
    'use strict';

    // This script enhances only the student booking form; the server remains authoritative for all submitted values.
    if (!$) {
        return;
    }

    const $form = $('#availability-form');
    if (!$form.length) {
        return;
    }

    const endpoint = $form.data('endpoint');
    const $date = $('#booking-date');
    const $slotInputs = $form.find('input[name="slots[]"]');
    const $count = $('#selected-slot-count');
    const $slotSummary = $('#booking-slot-summary');
    const $summary = $('#availability-summary');
    const $message = $('#availability-message');
    const $computerList = $('#available-computers');
    const $holdDate = $('#hold-date');
    const $holdSlots = $('#hold-slot-ids');
    const $continue = $('#continue-booking');

    let activeRequest = null;
    let pollTimer = null;

    // Keep slot IDs as strings because they are form values and are sent directly as GET/POST parameters.
    function selectedSlots() {
        return $slotInputs.filter(':checked').map(function () {
            return String($(this).val());
        }).get();
    }

    // Disable slots that have already started today; the controller repeats this check to prevent forged requests.
    function updatePastSlots() {
        const isToday = $date.val() === String($form.data('today-date'));
        const currentTime = String($form.data('current-time'));

        $slotInputs.each(function () {
            const isPast = isToday && String($(this).data('start-time')) <= currentTime;
            this.disabled = isPast;
            if (isPast) {
                this.checked = false;
            }
            $(this).closest('label')
                .toggleClass('is-past', isPast)
                .toggleClass('is-selected', this.checked);
        });
    }

    // Build empty-state copy with text nodes so error/empty messages are never interpreted as HTML.
    function setEmptyMessage(title, detail) {
        $message.empty().append(
            $('<h4>').text(title),
            $('<p>').text(detail)
        ).prop('hidden', false);
    }

    // Rebuild computer choices only when the available ID set changes, preserving a still-valid radio selection.
    function renderComputers(data, slotIds) {
        const computers = data.computers || [];
        const newIds = computers.map(function (c) {
            return String(c.computer_id);
        }).join(',');

        const currentIds = $computerList.find('input[name="computer_id"]').map(function () {
            return String($(this).val());
        }).get().join(',');

        // If the available computers have not changed, do nothing (preserves user selection & avoids DOM flicker)
        if (currentIds === newIds && $computerList.children().length > 0) {
            return;
        }

        const selectedRadio = $computerList.find('input[name="computer_id"]:checked').val();
        const selectedId = selectedRadio ? String(selectedRadio) : null;

        $computerList.empty();
        $message.prop('hidden', true);

        if (computers.length === 0) {
            $summary.text('No computer is available for every selected slot.');
            setEmptyMessage(
                'No computer is free for every selected slot',
                'Change your date or remove a slot to see other computers.'
            );
            $continue.prop('disabled', true);
            return;
        }

        $summary.text(
            computers.length + ' computer' + (computers.length === 1 ? '' : 's') +
            ' available across ' + slotIds.length + ' selected slot' + (slotIds.length === 1 ? '' : 's') + '.'
        );

        const labels = (data.slots || []).map(function (slot) {
            return String(slot.start_time).slice(0, 5) + '–' + String(slot.end_time).slice(0, 5);
        });

        let selectionStillValid = false;

        computers.forEach(function (computer) {
            const computerIdStr = String(computer.computer_id);
            const isChecked = selectedId && computerIdStr === selectedId;
            if (isChecked) {
                selectionStillValid = true;
            }

            const $label = $('<label>').addClass('available-computer-option');
            const $radio = $('<input>', {
                type: 'radio',
                name: 'computer_id',
                value: computerIdStr,
                required: true
            }).prop('checked', isChecked);

            const $details = $('<span>').append(
                $('<strong>').text(computer.computer_name)
            );

            if (computer.description) {
                $details.append($('<small>').text(computer.description));
            }

            $details.append(
                $('<small>').text('Available for: ' + labels.join(', '))
            );

            $label.append($radio, $details);
            $computerList.append($label);
        });

        $continue.prop('disabled', !selectionStillValid);
    }

    // Fetch availability for the current date/slot set; aborting the previous request prevents stale UI updates.
    function fetchAvailability(isPolling) {
        if (document.hidden && isPolling) {
            return;
        }

        updatePastSlots();
        const slotIds = selectedSlots();
        const date = $date.val();

        if (!isPolling) {
            $count.text(String(slotIds.length));
            $slotSummary.text(slotIds.length === 0
                ? 'Choose time slots'
                : slotIds.length + ' time slot' + (slotIds.length === 1 ? '' : 's') + ' selected');
            $holdDate.val(date);
            $holdSlots.empty();
            slotIds.forEach(function (slotId) {
                $holdSlots.append($('<input>', {
                    type: 'hidden',
                    name: 'slot_ids[]',
                    value: slotId
                }));
            });
        }

        if (slotIds.length === 0) {
            if (!isPolling) {
                $computerList.empty();
                $continue.prop('disabled', true);
                $summary.text('Select one or more slots to see matching computers.');
                setEmptyMessage(
                    'Select your time slots',
                    'Available computers will appear here as you update your selection.'
                );
            }
            return;
        }

        if (!isPolling && $computerList.children().length === 0) {
            $summary.text('Checking availability…');
            $message.prop('hidden', true);
        }

        if (activeRequest !== null) {
            activeRequest.abort();
        }

        activeRequest = $.ajax({
            url: endpoint,
            method: 'GET',
            dataType: 'json',
            data: {
                ajax: 'availability',
                date: date,
                slots: slotIds
            }
        }).done(function (data) {
            renderComputers(data, slotIds);
        }).fail(function (xhr, status) {
            if (status === 'abort') {
                return;
            }
            if (!isPolling) {
                const message = xhr.responseJSON && xhr.responseJSON.error
                    ? xhr.responseJSON.error
                    : 'Availability could not be refreshed. Try again.';
                $summary.text(message);
                setEmptyMessage('Availability unavailable', message);
            }
        }).always(function () {
            activeRequest = null;
        });
    }

    // Poll frequently while the page is visible so a competing hold disables stale computer choices promptly.
    function startPolling() {
        if (pollTimer !== null) {
            clearInterval(pollTimer);
        }
        pollTimer = setInterval(function () {
            fetchAvailability(true);
        }, 1500);
    }

    // When clicking a radio button, simply enable the Continue button.
    // The computer stays selected on this user's screen.
    $computerList.on('change', 'input[name="computer_id"]', function () {
        $continue.prop('disabled', false);
    });

    $form.on('submit', function (event) {
        event.preventDefault();
        fetchAvailability(false);
    });

    $form.on('change', 'input[name="slots[]"], #booking-date', function () {
        $slotInputs.each(function () {
            $(this).closest('label').toggleClass('is-selected', this.checked);
        });
        fetchAvailability(false);
    });

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            fetchAvailability(true);
        }
    });

    if (selectedSlots().length > 0) {
        fetchAvailability(false);
    }
    startPolling();

})(window.jQuery);
