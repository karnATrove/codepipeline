$(function () {

	/* .ajaxForm submissions */
	$('body').on('submit', 'form.ajaxForm', function (e) {
		e.preventDefault();

		// handle confirm if data-confirm exists
		var confirm = true;
		if ($(document.activeElement).is('button')) {
			if ($(document.activeElement).data('confirm')) {
				confirm = window.confirm($(document.activeElement).data('confirm'));
			}
		}

		if (confirm) {
			$(this).closest('.loading').show();
			postForm($(this), function (response, $form) {
				if (typeof(response.ajaxCommand) !== 'undefined') {
					handleAjaxResponse(response);
				}
			});
		}
	});

	/* .ajaxLink submissions */
	$('body').on('click', 'a.ajaxLink', function (e) {
		e.preventDefault();

		// handle confirm if data-confirm exists
		var confirm = true;
		if ($(this).data('confirm')) {
			confirm = window.confirm($(this).data('confirm'));
		}

		if (confirm) {
			// Show loading if necessary
			if ($('#quick-scan').is(':visible')) {
				$('#quick-scan .loading').show();
			}

			// Process the link
			ajaxLink($(this), {}, function (response, $link) {
				if (typeof(response.ajaxCommand) !== 'undefined') {
					handleAjaxResponse(response);
				}
			});
		}
	});

	// Disable submitting form by enter
	$('body').on('keyup keypress', 'form.no-enter-submit', function (e) {
		var keyCode = e.keyCode || e.which;
		if (keyCode === 13) {
			e.preventDefault();
			return false;
		}
	});

});




/**
 * jQuery functions.
 */
(function ($) {

	/**
	 * Listen to elements for scanner input.
	 *
	 * @param      {Function}  options  The options
	 * @return     {Object}    { description_of_the_return_value }
	 */
	$.fn.scannerListener = function (options) {
		var that = this;

		options = $.extend({
			delay: 500,
			charlimit: 4,
			element_val: '#scan',
			ignore: '#scan',
			trigger: 'keypress.scanner'
		}, options);

		/**
		 * Listen for a scanner input
		 */
		var pressed = false;
		var chars = [];
		that.on(options.trigger, function (e) {
			console.log('listening again');
			// Allow 0-9 or - or a-z OR A-Z
			if ((e.which >= 48 && e.which <= 57) || e.which === 45 || (e.which >= 97 && e.which <= 122) || (e.which >= 65 && e.which <= 90)) {
				chars.push(String.fromCharCode(e.which));
			}
			if (pressed === false) {
				setTimeout(function () {
					if (chars.length >= options.charlimit) {
						var barcode = chars.join("");
						console.log("Barcode Scanned: " + barcode);
						// assign value to some input (or do whatever you want)
						console.log(options.element_val);
						//if (options.element_val != '#scan' && !$('#scan').is(':focus')) {
						$(options.element_val).val(barcode).trigger('change');
						//}
					}
					chars = [];
					pressed = false;
				}, options.delay);
			}
			pressed = true;
		});

		$.fn.scannerListener.destroy = function () {
			that.unbind(options.trigger);
			console.log('scanner destroyed');
		};

		$('#quick-scan').on('hide.bs.modal', function () {
			$(window).scannerListener.destroy();
		});

		return that;
	};

})(jQuery);