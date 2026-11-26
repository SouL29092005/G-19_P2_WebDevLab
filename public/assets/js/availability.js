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
    const $search = $('#availability-search');
    let availabilityRequest = null;
    let requestVersion = 0;
    let changeTimer = null;

    function renderAvailability(rows) {
        if (!Array.isArray(rows) || rows.length === 0) {
            $results.empty().append(
                $('<p>').addClass('empty-state').text('No computers match the selected filters.')
            );
            return;
        }

        const $computerList = $('<div>').addClass('computer-list');
        let currentComputerId = null;
        let $tileGrid = null;

        rows.forEach(function (row) {
            const computerId = String(row.computer_id);
            if (computerId !== currentComputerId) {
                const $card = $('<section>')
                    .addClass('computer-card')
                    .attr('data-computer-name', row.computer_name || '');
                const $header = $('<div>').addClass('computer-card-header');
                $header.append(
                    $('<span>').addClass('computer-icon').attr('aria-hidden', 'true').append(
                        $('<i>').addClass('fa-solid fa-desktop')
                    ),
                    $('<div>').append($('<h3>').text(row.computer_name || 'Computer'))
                );
                if (row.description) {
                    $header.find('div').append(
                        $('<p>').addClass('computer-description').text(row.description)
                    );
                }
                $tileGrid = $('<div>').addClass('pc-tile-grid');
                $card.append($header, $tileGrid);
                $computerList.append($card);
                currentComputerId = computerId;
            }

            const status = ['available', 'waiting', 'booked'].includes(row.status)
                ? row.status
                : 'available';
            const startTime = String(row.start_time).slice(0, 5);
            const endTime = String(row.end_time).slice(0, 5);
            const stateLabel = status.charAt(0).toUpperCase() + status.slice(1);
            const tooltip = status === 'booked'
                ? 'Booked until ' + endTime
                : (status === 'waiting' ? 'Temporary hold for this slot, through ' + endTime : 'Available for this slot');
            const ariaLabel = String(row.computer_name || 'Computer') + ', ' + startTime + ' to ' + endTime + ', ' + status;
            const $tile = $('<div>')
                .addClass('pc-tile status-' + status)
                .attr('title', tooltip)
                .attr('aria-label', ariaLabel);

            $tile.append(
                $('<span>').addClass('slot-time').text(startTime + '–' + endTime),
                $('<span>').addClass('slot-status status-' + status).text(stateLabel)
            );
            $tileGrid.append($tile);
        });

        $results.empty().append($computerList);
        applySearch();
    }

    function applySearch() {
        const query = String($search.val() || '').trim().toLocaleLowerCase();
        const $cards = $results.find('.computer-card');
        let visible = 0;
        $cards.each(function () {
            const matches = !query || $(this).text().toLocaleLowerCase().includes(query);
            $(this).toggle(matches);
            if (matches) {
                visible += 1;
            }
        });
        $results.find('.availability-search-empty').remove();
        if ($cards.length && visible === 0) {
            $results.append(
                $('<p>').addClass('empty-state availability-search-empty').text('No computer matches your search.')
            );
        }
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
        $results.addClass('is-refreshing').attr('aria-busy', 'true');

        request.done(function (response) {
            if (thisRequestVersion !== requestVersion) {
                return;
            }
            if (!response || response.success !== true) {
                $updated.text(response && response.error
                    ? response.error
                    : 'Availability could not be refreshed.');
                return;
            }
            renderAvailability(response.availability);
            $updated.text(
                'Live · updated at ' + new Date(response.updated_at).toLocaleTimeString()
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
            $results.removeClass('is-refreshing').removeAttr('aria-busy');
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

    $search.on('input', applySearch);

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            refreshAvailability();
        }
    });

    refreshAvailability();
    window.setInterval(refreshAvailability, 5000);
})(window.jQuery);
