$(document).ready(function() {

	 //$('#scan').attr('readonly','readonly');
	 destroyScanner(); // Unbind previous listener
	 
	 // Only run if on popup scan mode
	 if ($('#form_scanIncoming').length) {
		 active_scanner = $(window).scannerListener({'element_val':'#form_scanIncoming','trigger':'keypress.incomingscanner','delay':300});

		 $('#form_scanIncoming').unbind('change.scanincoming');
		 $('#form_scanIncoming').on('change.scanincoming',function() {
		 	if ($(this).val().length >= 2) {
				delay(function() {
					$('#quick-scan .loading').show();
					$('#scan-incoming-form').submit();
				}, 300);
			}
		 });

		 // Bind on change of location
		 $('body').unbind('change.scanStock'); // required 
		 $('body').on('change.scanStock', '.incomingScannedProducts select, .incomingScannedProducts input', function() {
		 	$('#quick-scan .loading').show();
			$('#scan-incoming-product-form').submit();
		 });
	};
});