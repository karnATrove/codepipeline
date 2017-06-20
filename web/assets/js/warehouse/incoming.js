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
		form.submit();
	});

	$('#number_per_page_sel').change(function () {
		var form = $('#search_form');
		form.submit();
	});

});