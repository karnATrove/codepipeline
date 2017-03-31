$(document).ready(function() {
	// Bind on change of location
	$('body').unbind('change.scannedStock'); // required 
	$('body').on('change.scannedStock', '.incomingScannedProductsPage select, .incomingScannedProductsPage input', function() {
		$('#scanned_form_wrap .loading').show();
		$('#incoming-product-page-form').submit();
	});
});