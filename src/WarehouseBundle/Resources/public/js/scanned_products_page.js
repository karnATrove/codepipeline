$(function () {

	$('.incoming-location-update').change(function () {
		var value = $(this).val();
		var data = {'location': value};
		incomingUpdate($(this), data, 'location');
	});

	$('.incoming-qty-update').keypress(function (e) {
		e.preventDefault();
		if (e.which === 13) {
			//todo move to next

		}
	});

	$('.incoming-qty-update').change(function () {
		var value = $(this).val();
		var data = {'quantity': value};
		incomingUpdate($(this), data, 'quantity');
	});
});

function incomingUpdate(currentElement, data, type) {
	// var loading = $('.loading');
	// loading.show();
	$.ajax({
		url: currentElement.data('url'),
		data: data,
		type: 'POST',
		dataType: 'json',
		success: function (data) {
			if (data.error) {
				if(data.error!==null){
					alert(data.error);
				}else{
					alert("Unknown error");
				}
				location.reload();
			}else{
				new Noty({
					theme: 'relax',
					type: 'success',
					layout: 'topRight',
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
			// loading.hide();
		}

	});
}

