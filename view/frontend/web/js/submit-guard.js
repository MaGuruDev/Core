/**
 * Copyright © MaGuru. All rights reserved.
 * This module is developed for Magento® by MaGuru.
 * Magento® is a trademark of Adobe Inc.
 */
define(['jquery'], function ($) {
    'use strict';

    /**
     * Stops a form from being sent twice and, when asked, confirms it first.
     *
     * Usage: data-mage-init='{"MaGuru_Core/js/submit-guard": {"confirm": "Delete this list?"}}'
     *
     * @param {Object} config
     * @param {String} [config.confirm] Text of the confirmation dialog, omitted for no dialog.
     * @param {HTMLElement} element The form.
     */
    return function (config, element) {
        var form = $(element);

        form.on('submit', function (event) {
            if (form.data('maguruSubmitted')) {
                event.preventDefault();

                return;
            }

            if (config && config.confirm && !window.confirm(config.confirm)) {
                event.preventDefault();

                return;
            }

            form.data('maguruSubmitted', true);
            // Disabled after the event so the button's own value still goes with the request.
            setTimeout(function () {
                form.find('button[type="submit"], input[type="submit"]').prop('disabled', true).addClass('disabled');
            }, 0);
        });
    };
});
