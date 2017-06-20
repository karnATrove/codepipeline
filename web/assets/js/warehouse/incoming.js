$(function () {
	$('#incoming_eta').datetimepicker({
		'format':'YYYY-MM-DD'
	});
	$('#incoming_scheduled').datetimepicker({
		'format':'YYYY-MM-DD h:mm:ss a',
		'sideBySide':true
	});

	$('#is_complete_sel').change(function () {
		var form = $('#search_form');
		postForm(form, function (response, $form) {
			if (typeof(response.ajaxCommand) !== 'undefined') {
				handleAjaxResponse(response);
			}
		});
	});

});