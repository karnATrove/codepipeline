/**
 * Physically post the form to web service.
 * 
 * @param  {[type]}   $form    [description]
 * @param  {Function} callback [description]
 * @return {[type]}            [description]
 */
function postForm( $form, callback ){
 
  /*
   * Get all form values
   */
  var values = getFormValuesToArray($form);
  

  /* Show the progress bar */
  var ajaxOptions = {};
  if ($form.find('.progress-bar').length) {
    $('.progress').show();
    $('.progress .progress-bar').css('width',0);

    // If there is a file field, we want to track it and add more options
    ajaxOptions = {
      async: true,
      processData: false,
      contentType: false,
      data: new FormData($form[0]),
      xhr: function(){
        // get the native XmlHttpRequest object
        var xhr = $.ajaxSettings.xhr() ;
        // set the onprogress event handler
        xhr.upload.onprogress = function(evt){ $('.progress .progress-bar').css('width',(evt.loaded/evt.total*100)+'%'); } ;
        // set the onload event handler
        xhr.upload.onload = function(){ $('.progress').hide(); } ;
        // return the customized object
        return xhr ;
      },
    };
  }
  
  /*
   * Throw the form values to the server!
   */
  $.ajax(jQuery.extend({
    type        : $form.attr( 'method' ),
    url         : $form.attr( 'action' ),
    data        : values,
    cache: false,
    success     : function(data) {
      callback( data, $form );
    },
    error: function (response, desc, err){
      if (response.responseJSON && response.responseJSON.message) {
         alert(response.responseJSON.message);
      }
      else{
         alert(desc);
      }
    }
  },ajaxOptions));
}

/**
 * Post a link
 *
 * @param      {<type>}    $link     The link
 * @param      {Function}  callback  The callback
 */
function ajaxLink($link, ajaxOptions, callback) {
  /*
   * Throw the link to the server!
   */
  $.ajax(jQuery.extend({
    type        : 'GET',
    url         : $link.attr('href'),
    data        : {},
    cache: false,
    success     : function(data) {
      callback( data, $link );
    },
    error: function (response, desc, err){
      if (response.responseJSON && response.responseJSON.message) {
         alert(response.responseJSON.message);
      }
      else{
         alert(desc);
      }
    }
  },ajaxOptions));
}

/**
 * Loop through a form element and obtain all its current values.
 * @param  {[type]} $element [description]
 * @return {[type]}          [description]
 */
function getFormValuesToArray($element) {
  var values = {};
  $.each($element.serializeArray(), function(i, field) {
    values[field.name] = field.value;
  });

  // append buttons
  if ($(document.activeElement).is('button')) {
    values[$(document.activeElement).attr('name')] = $(document.activeElement).text();
  }

  return values;
}


/**
 * jQuery post to form and json the response.
 * 
 * @param  {[type]} ){                $('form.ajaxForm').submit( function( e ){    e.preventDefault();     postForm( $(this), function( response ){      console.log(JSON.stringify(response));     });  }); } [description]
 * @return {[type]}     [description]
 */
$(document).ready(function(){
  /* .ajaxForm submissions */
  $('body').on('submit','form.ajaxForm',function( e ){
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
      postForm( $(this), function( response, $form ){
        if (typeof(response.ajaxCommand) !== 'undefined') {
          handleAjaxResponse(response);
        }
      });
    }
  });

  /* .ajaxLink submissions */
  $('body').on('click','a.ajaxLink',function(e) {
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
      ajaxLink( $(this), {}, function(response, $link) {
        if (typeof(response.ajaxCommand) !== 'undefined') {
          handleAjaxResponse(response);
        }
      })
    }
  });

  // Disable submitting form by enter
  $('body').on('keyup keypress', 'form.no-enter-submit', function(e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13) { 
    e.preventDefault();
    return false;
    }
   });
 
});

/**
 * Variety of handing functions for when ajax returns a response of actions.
 *
 * @param      {<type>}  response  The response
 */
function handleAjaxResponse(response) {
  $(response.ajaxCommand).each(function(i,e) {
    switch(response.ajaxCommand[i].op) {
      case 'replaceWith':
        $(response.ajaxCommand[i].selector).replace(response.ajaxCommand[i].value);
        break;
      case 'html':
        $(response.ajaxCommand[i].selector).html(response.ajaxCommand[i].value);
        break;
      case 'append':
        $(response.ajaxCommand[i].selector).append(response.ajaxCommand[i].value);
        break;
      case 'prepend':
        $(response.ajaxCommand[i].selector).prepend(response.ajaxCommand[i].value);
        break;
      case 'before':
        $(response.ajaxCommand[i].selector).before(response.ajaxCommand[i].value);
        break;
      case 'after':
        $(response.ajaxCommand[i].selector).after(response.ajaxCommand[i].value);
        break;
      case 'remove':
        $(response.ajaxCommand[i].selector).remove();
        break;
      case 'hide':
        $(response.ajaxCommand[i].selector).hide();
        break;
      case 'show':
        $(response.ajaxCommand[i].selector).show();
        break;
      case 'alert':
        alert(response.ajaxCommand[i].value);
        break;
      case 'value':
        $(response.ajaxCommand[i].selector).val(response.ajaxCommand[i].value);
        break;
      case 'attr':
        for(var index in response.ajaxCommand[i].value) { 
          $(response.ajaxCommand[i].selector).attr(index,response.ajaxCommand[i].value[index]);
        }
        break;
      case 'modal':
        $(response.ajaxCommand[i].selector).modal(response.ajaxCommand[i].value);
        break;
      case 'invoke':
        var fn = window[response.ajaxCommand[i].value];
        if (typeof(fn) === 'function') {
          if (typeof(response.ajaxCommand[i].params) !== 'undefined') {
            fn(response.ajaxCommand[i].params);
          } else { 
            fn();
          }
        }
        break;
      case 'log':
        console.log(response.ajaxCommand[i].value);
        break;
      case 'redirect':
        window.location = response.ajaxCommand[i].value;
        break;
    }
  });
}


/**
 * jQuery functions.
 */
(function($) {

  /**
   * Listen to elements for scanner input.
   *
   * @param      {Function}  options  The options
   * @return     {Object}    { description_of_the_return_value }
   */
  $.fn.scannerListener = function(options) {
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
    that.on(options.trigger,function(e) {
      console.log('listening again');
      // Allow 0-9 or - or a-z OR A-Z
      if ((e.which >= 48 && e.which <= 57) || e.which == 45 || (e.which >= 97 && e.which <= 122) || (e.which >= 65 && e.which <= 90)) {
        chars.push(String.fromCharCode(e.which));
      }
      if (pressed == false) {
        setTimeout(function(){
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
        },options.delay);
      }
      pressed = true;
    });

    $.fn.scannerListener.destroy = function() {
      that.unbind(options.trigger);
      console.log('scanner destroyed');
    }

    $('#quick-scan').on('hide.bs.modal', function () {
      $(window).scannerListener.destroy();
    });

    //$('.scan_modes a').on('click', function () {
    //  $(window).scannerListener.destroy();
    //});

    return that;
  };

})(jQuery);