/**
 * jQuery upload v1.2
 * http://www.ponxu.com
 * https://github.com/ponxu/upload-jquery
 * @author xwz
 */
(function($) {
	var noop = function(){ return true; };
	var frameCount = 0;
	
	$.uploadDefault = {
		url: '',
		fileName: 'filedata',
		dataType: 'json',
		params: {},
		onSend: noop,
		onSubmit: noop,
		onComplate: noop
	};

	$.upload = function(options) {
		var opts = $.extend(jQuery.uploadDefault, options);
		if (opts.url === '') {
			return;
		}
		
		var canSend = opts.onSend();
		if (!canSend) {
			return;
		}
		
		var frameName = 'upload_frame_' + (frameCount++);
		var iframe = $('<iframe style="position:absolute;top:-9999px" />').attr('name', frameName);
		var form = $('<form method="post" style="display:none;" enctype="multipart/form-data" />').attr('name', 'form_' + frameName);
		form.attr("target", frameName).attr('action', opts.url);
		
		var formHtml = '<input type="file" name="' + opts.fileName + '" onchange="onChooseFile(this)">';
		for (var key in opts.params) {
			formHtml += '<input type="hidden" name="' + key + '" value="' + opts.params[key] + '">';
		}
		form.append(formHtml);

		iframe.appendTo("body");
		form.appendTo("body");
		
		form.submit(opts.onSubmit);
		
		iframe.load(function() {
			var contents = $(this).contents().get(0);
			var data = $(contents).find('body').text();
			if ('json' == opts.dataType) {
				data = window.eval('(' + data + ')');
			}
			opts.onComplate(data);
			setTimeout(function() {
				iframe.remove();
				form.remove();
			}, 5000);
		});
		
		var fileInput = $('input[type=file][name=' + opts.fileName + ']', form);
		fileInput.click();
	};
})(jQuery);

var onChooseFile = function(fileInputDOM) {
	var form = $(fileInputDOM).parent();
	form.submit();
};