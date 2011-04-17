/*
|=======================================================|
|  Simple-PHP5-Framework                                |
|  Requires == MySQL 5.3+                               |
|  Requires == PHP 5.3.0+                               |
|  Requires == Memcached 1.4.5+                         |
|  https://github.com/simpleframe/Simple-PHP-Framework  |
|  (c) 2010 by NoName                                   |
|  Free to use and modifiy and share                    |
|  Not for Sale                                         |
|=======================================================|
*/

function toggleChecks(formElem,masterElem) {
	if (masterElem.checked) { checked=true; } else { checked=false; }
	for(s=0; s<$('#'+formElem).raw().elements.length; s++) {
		if ($('#'+formElem).raw().elements[s].type=="checkbox") {
			$('#'+formElem).raw().elements[s].checked=checked;
		}
	}
}

//Lightbox stuff
var lightbox = {
	init: function (image, size) {
		if (image.naturalWidth === undefined) {
			var tmp = document.createElement('img');
			tmp.style.visibility = 'hidden';
			tmp.src = image.src;
			image.naturalWidth = tmp.width;
			delete tmp;
		}
		if (image.naturalWidth > size) {
			lightbox.box(image);
		}
	},
	ajax: function (url) {
			ajax.get( url ,function(response){
				$('#lightbox_ajax').show().listen('click',lightbox.unbox).raw().innerHTML =response;
			});
		$('#curtain').show().listen('click',lightbox.unbox);
	},
	frame: function (url) {
			$('#lightbox_frame').show().listen('click',lightbox.unbox).raw().innerHTML = '<iframe src=" '+ url + '" width="100%" height="100%"></iframe>';
			$('#curtain').show().listen('click',lightbox.unbox);
	},
	box: function (image) {
		if(image.parentNode.tagName.toUpperCase() != 'A') {
			$('#lightbox').show().listen('click',lightbox.unbox).raw().innerHTML = '<img src="' + image.src + '" />';
			$('#curtain').show().listen('click',lightbox.unbox);
		}
	},
	unbox: function (data) {
		$('#curtain').hide();
		$('#lightbox').hide().raw().innerHTML = '';
		$('#lightbox_ajax').hide().raw().innerHTML = '';
		$('#lightbox_frame').hide().raw().innerHTML = '';
	}
};


/* Still some issues
function caps_check(e) {
	if (e === undefined) {
		e = window.event;
	}
	if (e.which === undefined) {
		e.which = e.keyCode;
	}
	if (e.which > 47 && e.which < 58) {
		return;
	}
	if ((e.which > 64 && e.which <  91 && !e.shiftKey) || (e.which > 96 && e.which < 123 && e.shiftKey)) {
		$('#capslock').show();
	}
}
*/

function hexify(str) {
   str = str.replace(/rgb\(|\)/g, "").split(",");
   str[0] = parseInt(str[0], 10).toString(16).toLowerCase();
   str[1] = parseInt(str[1], 10).toString(16).toLowerCase();
   str[2] = parseInt(str[2], 10).toString(16).toLowerCase();
   str[0] = (str[0].length == 1) ? '0' + str[0] : str[0];
   str[1] = (str[1].length == 1) ? '0' + str[1] : str[1];
   str[2] = (str[2].length == 1) ? '0' + str[2] : str[2];
   return (str.join(""));
}

function resize(id) {
	var textarea = document.getElementById(id);
	if (textarea.scrollHeight > textarea.clientHeight) {
		textarea.style.overflowY = 'hidden';
		textarea.style.height = textarea.scrollHeight + textarea.style.fontSize + 'px';
	}
}


