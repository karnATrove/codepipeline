/**
 * Keeps control of active scanner
 * @type {[type]}
 */
var active_scanner = null;

$(function () {
    /**
     * Prevent following links when scan interface init.
     */
    $('.scan-trigger').on('click', function (e) {
        e.preventDefault();
    });

    /**
     * Main scan change.
     */
    $('#scan').unbind('change.defaultscan');
    $('#scan').on('change.defaultscan', function () {
        $(this).val($(this).val().trim());
        if ($(this).val().length >= 2) {
            delay(function () {
                $('#quick-scan .loading').show();
                $('#scan-form').submit();
            }, 400);
        }
    });

    /**
     * Bootstrap modal appears.
     */
    $('#quick-scan').on('shown.bs.modal', function () {
        //$('#scan').focus();
        initDefaultScanner();
    });

    /**
     * Bootstrap modal disappears.
     */
    $('#quick-scan').on('hide.bs.modal', function () {
        $('#scan').val('');
        $('#scan-result').empty();
    });

    /**
     * Changing mode
     */
    $('.scan_modes .btn-app').unbind('click');
    $('.scan_modes .btn-app').on('click', function (e) {
        e.preventDefault();

        // Buttons
        $('.scan_modes .btn-app').removeClass('active');
        $(this).addClass('active');
        $('input.scan_mode').val($(this).data('mode'));
        $('#scan-result').empty();

        // What to do with new button
        switch ($(this).data('mode')) {
            case 'stock':
                $('#default_scan').hide();
                $('#stock_mode').show();
                $('#quick-scan #form_incoming').trigger('change');
                break;
            default:
                $('#default_scan').show();
                $('#stock_mode').hide();
                destroyScanner();
                initDefaultScanner();
                $('#scan').trigger('change');
                break;
        }
    });

    /**
     * Incoming mode
     */
    $('#quick-scan #form_incoming').on('change', function () {
        if ($(this).find(':selected').val().length >= 1) {
            $('#quick-scan .loading').show();
            $('#scan-form').submit();
        }
    });

});

/**
 * Initiate default scan listener
 * Toggles on and off between different modes
 * @return {[type]} [description]
 */
function initDefaultScanner() {
    console.log('listening');

    active_scanner = $(window).scannerListener({
        'element_val': '#scan',
        'trigger': 'keypress.defaultscan',
        'delay': 200
    });
}

function destroyScanner() {
    console.log('destroying');
    if (active_scanner !== null) {
        $(window).unbind('keypress');
        //active_scanner.destroy();
        active_scanner = null;
    }
}

/**
 * Delay execution
 *
 * @return     {function}  { description_of_the_return_value }
 */
var delay = (function () {
    var timer = 0;
    return function (callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
    };
})();