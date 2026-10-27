(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    const $form = $('#availability-filter-form');

    if (!$form.length) {
        return;
    }

    const endpoint = $form.data('endpoint');
    const $results = $('#availability-results');
    const $updated = $('#availability-updated');
    let availabilityRequest = null;
    let requestVersion = 0;
    let changeTimer = null;

    function renderAvailability(rows) {
        if (!Array.isArray(rows) || rows.length === 0) {
            $results.empty().append(
                $('<p>').addClass('empty-state').text(
                    'No computers match the selected filters.'
                )
            );
            return;
        }

        const $computerList = $('<div>').addClass('computer-list');
        let currentComputerId = null;
        let $slotList = null;

        rows.forEach(function (row) {
            const computerId = String(row.computer_id);

            if (computerId !== currentComputerId) {
                const $card = $('<section>').addClass('computer-card');
                $card.append($('<h3>').text(row.computer_name));

                if (row.description) {
                    $card.append(
                        $('<p>').addClass('computer-description').text(row.description)
                    );
                }

                $slotList = $('<div>').addClass('slot-list');
                $card.append($slotList);
                $computerList.append($card);
                currentComputerId = computerId;
            }

            const status = ['available', 'waiting', 'booked'].includes(row.status)
                ? row.status
                : 'available';
            const startTime = String(row.start_time).slice(0, 5);
            const endTime = String(row.end_time).slice(0, 5);
            const $slotItem = $('<div>').addClass('slot-item');
            const $slotStatus = $('<span>')
                .addClass('slot-status')
                .addClass('status-' + status)
                .text(status.charAt(0).toUpperCase() + status.slice(1));

            $slotItem.append(
                $('<span>').addClass('slot-time').text(startTime + ' – ' + endTime),
                $slotStatus
            );
            $slotList.append($slotItem);
        });

        $results.empty().append($computerList);
    }

    function refreshAvailability() {
        if (document.hidden) {
            return;
        }

        if (availabilityRequest !== null) {
            availabilityRequest.abort();
        }

        const thisRequestVersion = ++requestVersion;
        const request = $.ajax({
            url: endpoint,
            method: 'GET',
            dataType: 'json',
            data: $form.serialize() + '&ajax=availability'
        });

        availabilityRequest = request;

        request.done(function (response) {
            if (thisRequestVersion !== requestVersion) {
                return;
            }

            if (!response || response.success !== true) {
                $updated.text(
                    response && response.error
                        ? response.error
                        : 'Availability could not be refreshed.'
                );
                return;
            }

            renderAvailability(response.availability);
            $updated.text(
                'Availability updated at '
                + new Date(response.updated_at).toLocaleTimeString()
                + '.'
            );
        });

        request.fail(function (xhr, status) {
            if (status === 'abort' || thisRequestVersion !== requestVersion) {
                return;
            }

            const message = xhr.responseJSON && xhr.responseJSON.error
                ? xhr.responseJSON.error
                : 'Unable to refresh availability. Showing the most recent results.';
            $updated.text(message);
        });

        request.always(function () {
            if (availabilityRequest === request) {
                availabilityRequest = null;
            }
        });
    }

    $form.on('submit', function (event) {
        event.preventDefault();
        refreshAvailability();
    });

    $form.on('change', 'input, select', function () {
        window.clearTimeout(changeTimer);
        changeTimer = window.setTimeout(refreshAvailability, 200);
    });

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            refreshAvailability();
        }
    });

    refreshAvailability();
    window.setInterval(refreshAvailability, 5000);
})(window.jQuery);