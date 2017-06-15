$(function () {
	$('#incoming_eta').datetimepicker({
		'format':'YYYY-MM-DD'
	});
	$('#incoming_scheduled').datetimepicker({
		'format':'YYYY-MM-DD h:mm:ss a',
		'sideBySide':true
	});
});