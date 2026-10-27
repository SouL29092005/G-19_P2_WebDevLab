(function ($) {
    'use strict';

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

    function selectedSlots() {
        return $slotInputs.filter(':checked').map(function () {
            return String($(this).val());
        }).get();
    }

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

    function setEmptyMessage(title, detail) {
        $message.empty().append(
            $('<h4>').text(title),
            $('<p>').text(detail)
        ).prop('hidden', false);
    }

    function renderComputers(data, slotIds) {
        const labels = (data.slots || []).map(function (slot) {
            return String(slot.start_time).slice(0, 5)
                + '–'
                + String(slot.end_time).slice(0, 5);
        });

        $computerList.empty();
        $message.prop('hidden', true);

        if (!data.computers || data.computers.length === 0) {
            $summary.text('No computer is available for every selected slot.');
            setEmptyMessage(
                'No computer is free for every selected slot',
                'Change your date or remove a slot to see other computers.'
            );
            return;
        }

        $summary.text(
            data.computers.length
            + ' computer'
            + (data.computers.length === 1 ? '' : 's')
            + ' available across '
            + slotIds.length
            + ' selected slot'
            + (slotIds.length === 1 ? '' : 's')
            + '.'
        );

        data.computers.forEach(function (computer) {
            const $label = $('<label>').addClass('available-computer-option');
            const $radio = $('<input>', {
                type: 'radio',
                name: 'computer_id',
                value: String(computer.computer_id),
                required: true
            });
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
    }

    function updateAvailability() {
        updatePastSlots();
        const slotIds = selectedSlots();
        const date = $date.val();

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

        $computerList.empty();
        $continue.prop('disabled', true);

        if (slotIds.length === 0) {
            $summary.text('Select one or more slots to see matching computers.');
            setEmptyMessage(
                'Select your time slots',
                'Available computers will appear here as you update your selection.'
            );
            return;
        }

        $summary.text('Checking availability…');
        $message.prop('hidden', true);

        $.ajax({
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
        }).fail(function (response) {
            const message = response.responseJSON && response.responseJSON.error
                ? response.responseJSON.error
                : 'Availability could not be refreshed. Try again.';
            $summary.text(message);
            setEmptyMessage('Availability unavailable', message);
        });
    }

    $form.on('submit', function (event) {
        event.preventDefault();
        updateAvailability();
    });

    $form.on('change', 'input[name="slots[]"], #booking-date', function () {
        $slotInputs.each(function () {
            $(this).closest('label').toggleClass('is-selected', this.checked);
        });
        updateAvailability();
    });

    $computerList.on('change', 'input[name="computer_id"]', function () {
        $continue.prop('disabled', false);
    });
})(window.jQuery);
