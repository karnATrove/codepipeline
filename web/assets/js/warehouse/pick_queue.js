/**
 * Incoming Scanned/List Products functions.
 */
$(function () {
	// Only run on products scanned page.
	if ($('#pickingStagingQueue').length) {
		$('#pickingStagingQueue').on('change', 'input[type="number"]', function () {
			$(this).attr('readonly','readonly');
			ajaxUpdatePickQueue($(this));
			//$(this).parents('form').submit();
		});
	}
});

/**
 * Update a scanned item via ajax.
 * @param  {[type]} currentElement [description]
 * @return {[type]}                [description]
 */
function ajaxUpdatePickQueue(currentElement) {
	var value = currentElement.val();
	var location = currentElement.parents('td, div.item').data('location');
	var original = currentElement.parents('td, div.item').data('original');
	/*
	var data = $(currentElement).parents('form').serialize();
	data.quantity = value;
	data.original = original;
	*/
	var data = {'quantity': value,'original': original};

	$.ajax({
		url: currentElement.parents('td, div.item').data('url'),
		data: data,
		type: 'POST',
		dataType: 'json',
		success: function (data) {
			if (typeof(data.ajaxCommand) !== 'undefined') {
				handleAjaxResponse(data);
  			} else {
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
			$("td[data-location='"+location+"'] input, , div.item[data-location='"+location+"'] input").attr('readonly',false);
		}
	});
}
