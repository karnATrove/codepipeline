/**
 * Incoming Scanned/List Products functions.
 */
$(function () {
	var scanned_form_wrap = $('#scanned_form_wrap');
	// Only run on products scanned page.
	if (scanned_form_wrap.length) {

		//if location gets update
		scanned_form_wrap.on('change', 'select', function () {
			ajaxUpdateIncomingScan($(this));
		});
		//if qty gets update
		scanned_form_wrap.on('change', 'input[type="number"]', function () {
			ajaxUpdateIncomingScan($(this));
		});

		scanned_form_wrap.on('focus', 'input#form_new', function() {
			$(this).data('lastvalue',$(this).val());
			$(this).val('');
		});
		scanned_form_wrap.on('blur', 'input#form_new', function() {
			if ($(this).val() == '' && $(this).data('lastvalue') && $(this).data('lastvalue').length > 0) {
				$(this).val($(this).data('lastvalue'));
			}
		});

		destroyScanner(); // Unbind previous listener
		active_scanner = $(window).scannerListener({'element_val':'#scanned_form_wrap input#form_new','trigger':'keypress.incomingscanner','delay':300});

		scanned_form_wrap.unbind('change.scanincoming');
		scanned_form_wrap.on('change.scanincoming', 'input#form_new', function() {
			if ($(this).val().length >= 2) {
				delay(function() {
					//$('#quick-scan .loading').show();
					//$('#scan-incoming-form').submit();
				}, 300);
			}
		});
	}
});

/**
 * Update a scanned item via ajax.
 * @param  {[type]} currentElement [description]
 * @return {[type]}                [description]
 */
function ajaxUpdateIncomingScan(currentElement) {
	var type = currentElement.data('type');
	var value = currentElement.val();
	var data = null;
	if (type === 'location') {
		data = {'location': value};
	} else {
		data = {'quantity': value};
	}
	$.ajax({
		url: currentElement.data('url'),
		data: data,
		type: 'POST',
		dataType: 'json',
		success: function (data) {
			if (data.error) {
				if (data.error !== null) {
					new Noty({
						theme: 'relax',
						type: 'error',
						layout: 'topLeft',
						text: data.error,
						timeout: 2000
					}).show();
				} else {
					alert("Unknown error");
				}
				location.reload();
			} else {
				new Noty({
					theme: 'relax',
					type: 'success',
					layout: 'topLeft',
					text: data.message,
					timeout: 2000
				}).show();
			}
		},
		error: function (response, desc, err) {
			if (response.responseJSON && response.responseJSON.message) {
				alert(response.responseJSON.message);
			}
			else {
				alert(desc);
			}
		},
		complete: function () {
		}
	});
}



