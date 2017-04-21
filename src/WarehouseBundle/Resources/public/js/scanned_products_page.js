$(function () {
	$('#scanned_form_wrap').on('change', 'select', function () {
		ajaxUpdateIncomingScan($(this));
	});

	$('#scanned_form_wrap').on('change', 'input[type="number"]', function () {
		ajaxUpdateIncomingScan($(this));
	});
});

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



