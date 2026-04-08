	//	Captcha v2.0.0 Copyright 2015 Jesse Fowler, Fiserv.  All rights reserved.
    function initCaptchaField(el, index) {
        var captchaClassName = 'default', 						// If you want a unique style set the class here, if you want the default set to 'default'. 
        	captchaNumbers = el.attr('rel') - 1,
        	captchaFieldHTML = '',
        	captchaNumberHTML = '',
        	captchaRandWidth = 0,
        	i = 0;
        for (i = 0; i <= captchaNumbers; i++) {
            var randomNumber = Math.floor(Math.random() * 10);
            var randomNumberShift = Math.floor(Math.random() * 10) - 10;
            captchaRandWidth = captchaRandWidth + randomNumberShift;
            captchaFieldHTML = captchaFieldHTML + '<img src="images/captcha/spacer.gif" alt="" height="44" width="30" style="background-image:url(images/captcha/numbers.png); background-position: 0 ' + (-randomNumber * 44 + (randomNumberShift + 5)) + 'px ; margin: 0 ' + (randomNumberShift) + 'px">';
            captchaNumberHTML = captchaNumberHTML + randomNumber.toString();
        } 
        var captchaField = jQuery("<div/>", {
            'class': 'captchaField ' + captchaClassName
        });
        if(captchaClassName == 'default'){
        	captchaField.css('width', ((captchaNumbers * 30) + (captchaRandWidth * 2)) + 206);
        }
        var captchaFieldNumbers = jQuery("<div/>", {
            'class': 'captchaFieldNumbers',
            'html': captchaFieldHTML
        });
        if(captchaClassName == 'default'){
        	captchaFieldNumbers.css('width', ((captchaNumbers * 30) + (captchaRandWidth * 2)) + 40);
        }
        var captchaFieldBoxLeft = jQuery("<div/>", {
            'class': 'captchaFieldBoxLeft',
            'html': ''
        });
        var captchaFieldBoxMid = jQuery("<div/>", {
            'class': 'captchaFieldBoxMid',
            'html': ''
        });
        var captchaFieldBoxMidOverlay = jQuery("<div/>", {
            'class': 'captchaFieldBoxMidOverlay',
            'html': ''
        });
        var captchaFieldBoxMidOverlaySecure = jQuery("<div/>", {
            'class': 'captchaFieldBoxMidOverlaySecure',
            'html': ''
        });
        var captchaFieldBoxRight = jQuery("<div/>", {
            'class': 'captchaFieldBoxRight',
            'html': ''
        });
        var captchaFieldRefresh = jQuery("<a/>", {
            'class': 'captchaFieldRefresh',
            'html': '<img src="images/captcha/spacer.gif" alt="" height="34" width="43" border="0">'
        });
        captchaFieldRefresh.on({
            "click": function() {
                var parent1 = jQuery(this).parent();
                var parent2 = parent1.parent();
                parent2.remove();
                initCaptchaField(el);
            },
            "mouseover": function() {
                jQuery(this).css('background-position', '-43px 0');
            },
            "mouseout": function() {
                jQuery(this).css('background-position', '0 0');
            }
        });
        var captchaNumber = jQuery("<input/>", {
            'id': 'captchaNumber' + (index + 1),
            'name': 'captchaNumber' + (index + 1),
            'class': 'captchaNumber',
            'value': captchaNumberHTML
        });
        el.prop('value', captchaNumberHTML);
        el.after(captchaField);
        captchaField.append(captchaFieldBoxLeft);
        captchaField.append(captchaFieldBoxMid);
        captchaFieldBoxMid.append(captchaFieldBoxMidOverlay);
        captchaFieldBoxMid.append(captchaFieldBoxMidOverlaySecure);
        captchaField.append(captchaFieldBoxRight);
        captchaFieldBoxRight.append(captchaFieldRefresh);
        captchaFieldBoxMid.append(captchaFieldNumbers);
        captchaFieldNumbers.children('img').each(function(index) {
            var currentBackgroundPosition = jQuery(this).css('background-position');
            /*
            var numberEffects = new Fx.Morph(el, {
                duration: 1000,
                transition: Fx.Transitions.Sine.easeOut
            });
            numberEffects.start({
                'background-position': ['0 0', currentBackgroundPosition]
            });
            */
            jQuery(this).css('background-position', currentBackgroundPosition);
            //jQuery(this).animate({'background-position': currentBackgroundPosition}, 1000);
        });
    }

    function initCaptcha() {
        jQuery('div.captchaField').each(function(index) {
            jQuery(this).remove();
        });
        jQuery('input.captchaNumber').each(function(index) {
            jQuery(this).remove();
        });
        jQuery('input.captcha').each(function(index) {
            initCaptchaField(jQuery(this));
        });
    }
    jQuery(document).ready(function(){
		initCaptcha();
	});