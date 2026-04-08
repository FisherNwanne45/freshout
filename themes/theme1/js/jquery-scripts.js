//Plugins are located in plugins.js. All calls to those plugins are in this file

jQuery(document).ready(function () {
    // selectmenu 
    //requires jquery-ui
    //$("#emailTo").selectmenu({
    //    appendTo: "#contact-select",
    //});
	// Anchor link class taging
	jQuery('a').each(function(){
		if (!this.href) {
			jQuery(this).addClass('anchor');
		}
	});

    //Replaces Subsection Table with a Div Wrapper 
	jQuery("table.Subsection-Table").tableWrapper();

	jQuery("table.Subsection-Callout-Table").tableWrapper({
	    wrapperClass: "subsection-callout",
	});

	jQuery("table.Promo-Home").tableWrapper({
	    wrapperClass: "inner-promo",
	});
	jQuery("table.Promo-Home-Small").tableWrapper({
	    wrapperClass: "inner-promo",
	});
	jQuery("table.News-Home").tableWrapper({
	    wrapperClass: "inner",
	});
    
    // Responsive Zoom
	function onWinResize() {
	    var windowSize = jQuery(window).width();

	    //// Set page width maximums and minimums
	    pageWidth = parseFloat(windowSize);
	    if (pageWidth < 1024) {
	        try {
	            jQuery("body").addClass("mobile");
	            jQuery("body").removeClass("desktop");
	        } catch (err) { }
	    } else {
	        try {
	            jQuery("body").removeClass("mobile");
	            jQuery("body").addClass("desktop");
	        } catch (err) { }
	    }

	    //Applies the zoom to an element with the specified classes
	    //Example:
	    //jQuery(".responsivezoom").responsiveZoom();

	    jQuery(".responsivezoom").responsiveZoom();
	    jQuery(".Table-Style").responsiveZoom();
	    jQuery(".Table-Product").responsiveZoom();

	    onWinResizeInitalized = true;
	}

    // Initializer - Calls Responsive Zoom
	onWinResize();
	var windowWidth = jQuery(window).width();
	var onWinResizer = debounce(function () {
	    if (jQuery(window).width() != windowWidth) {
	        onWinResize();
	        windowWidth = jQuery(window).width();
	    }
	}, 500);

	jQuery(window).on('resize', onWinResizer);
	
	// Responsive Nav
	//
	jQuery("#menuopen").click(function() {
		jQuery("body").toggleClass("opennav"); 
		jQuery("body").removeClass("openob"); //Hide login     
	});

	jQuery("ul.panelnav li").click(function() {
		jQuery(this).toggleClass("active");      
		//jQuery(this).siblings().removeClass("active"); //closes other tabs
	});

	jQuery("#login-button").click(function() { // Login Show/Hide
		jQuery("body").toggleClass("openob");
		jQuery("body").removeClass("opennav"); //Hide Responsive Nav      
	});

    // Expandables
	jQuery('.Expandable').fiservExpandablesInit();

    //Remove unwanted spaces
    jQuery("#noticeHtml>p:last-of-type, .content p, #news p, .feature-promo p, #hero p").filter(function () {
	    return jQuery.trim(jQuery(this).html()) == '&nbsp;';
	}).remove();

    // Personalization 2.1.0 Copyright (c) 2015 Jesse Fowler, Fiserv.
    // Requires jQuery and jQuery Cookie plugin
    // Modified from original to change #greeting1 to class based for cms implementation

    // Customized greeting
	if (jQuery('.greeting1')) {
	    function initGreeting() {

	        var greeting = "";

	        // This array holds the "friendly" day names
	        var day_names = new Array(7)
	        day_names[0] = "Sunday"
	        day_names[1] = "Monday"
	        day_names[2] = "Tuesday"
	        day_names[3] = "Wednesday"
	        day_names[4] = "Thursday"
	        day_names[5] = "Friday"
	        day_names[6] = "Saturday"

	        // This array holds the "friendly" month names
	        var month_names = new Array(12)
	        month_names[0] = "January"
	        month_names[1] = "February"
	        month_names[2] = "March"
	        month_names[3] = "April"
	        month_names[4] = "May"
	        month_names[5] = "June"
	        month_names[6] = "July"
	        month_names[7] = "August"
	        month_names[8] = "September"
	        month_names[9] = "October"
	        month_names[10] = "November"
	        month_names[11] = "December"

	        // Get the current date
	        date_now = new Date()

	        // Figure out the friendly day name
	        day_value = date_now.getDay()
	        date_text = day_names[day_value]

	        // Figure out the friendly month name
	        month_value = date_now.getMonth()
	        date_text += " " + month_names[month_value]

	        // Add the day of the month
	        date_text += " " + date_now.getDate()

	        // Add the year
	        date_text += ", " + date_now.getFullYear()

	        // Get the minutes in the hour
	        minute_value = date_now.getMinutes()
	        if (minute_value < 10) {
	            minute_value = "0" + minute_value
	        }

	        // Get the hour value and use it to customize the greeting
	        hour_value = date_now.getHours()
	        if (hour_value == 0) {
	            greeting = "Good morning, "
	            time_text = " at " + (hour_value + 12) + ":" + minute_value + " AM"
	        }
	        else if (hour_value < 12) {
	            greeting = "Good morning,"
	            time_text = " at " + hour_value + ":" + minute_value + " AM"
	        }
	        else if (hour_value == 12) {
	            greeting = "Good afternoon,"
	            time_text = " at " + hour_value + ":" + minute_value + " PM"
	        }
	        else if (hour_value < 17) {
	            greeting = "Good afternoon,"
	            time_text = " at " + (hour_value - 12) + ":" + minute_value + " PM"
	        }
	        else {
	            greeting = "Good evening,"
	            time_text = " at " + (hour_value - 12) + ":" + minute_value + " PM"
	        }
	        var fullGreeting1 = greeting;  //add time + time_text
	        var fullGreeting2 = " It's " + date_text;  //add time + time_text
	        jQuery('.greeting1').html(fullGreeting1);
	        jQuery('#greeting2').html(fullGreeting2);

	    }
	    initGreeting();
	}

	var personalizationEnable = true;
	var personalizeMyFinancial = jQuery.cookie('personalizeMyFinancial', 'true');
    //if (jQuery.cookie('personalizeMyFinancial')) {
    //$('personalizeLine').setStyle('display', 'block');
    //}
	if (jQuery('#personalizationPopupxy') && personalizationEnable && jQuery.cookie('personalizeMyFinancial')) {
	    var personalizationFirstName = jQuery.cookie('personalizationFirstName'),
			spans = jQuery('span.firstname');

	    // Personalization popup 
	    var initializepersonalization = function () {
	        jQuery('#personalizationPopupxy').addClass('active');
	        jQuery('#personalizationName').focus();
	    };

	    // Name personalization
	    var personalizationInitialize = function () {
	        personalizationFirstName = jQuery.cookie('personalizationFirstName');
	        // console.log( personalizationFirstName );
	        if (spans != '') {
	            //console.warn(spans);
	            if (!personalizationFirstName) {
	                initializepersonalization();
	            } else {
	                jQuery('#personalizationPopupxy').removeClass('active');
	            }
	            spans.each(function () {
	                var firstNameElement = jQuery('<a href="javascript:void(0)" class="personalizationSetting" style="cursor:pointer"></a>');
	                firstNameElement.on("click", function () {
	                    initializepersonalization();
	                });
	                if (!personalizationFirstName) {
	                    var linkHtml = jQuery(this).html();
	                    firstNameElement.html(linkHtml);
	                } else if (personalizationFirstName != 'Skipped') {
	                    firstNameElement.html(personalizationFirstName + " ");
	                } else {
	                    var linkHtml = jQuery(this).html();
	                    firstNameElement.html(linkHtml);
	                }
	                jQuery(this).html('');
	                jQuery(this).append(firstNameElement);
	            });
	        } else {
	            //alert('Spans found.');
	        }
	    };
	    personalizationInitialize();

	    var personalizationClose = jQuery('.personalizationClose');
	    personalizationClose.each(function (index) {
	        jQuery(this).on("click", function () {
	            jQuery.cookie('personalizationFirstName', 'Skipped');
	            jQuery('#personalizationPopupxy').removeClass('active');
	        });
	    });
	    var personalizationPopupClosePerm = jQuery('.personalizationPopupClosePerm');
	    personalizationPopupClosePerm.each(function (index) {
	        jQuery(this).on("click", function () {
	            jQuery.cookie('personalizationFirstName', 'Skipped', { expires: 90 });
	            jQuery('#personalizationPopupxy').removeClass('active');
	        });
	    });
	    if (jQuery('#personalizationForm')) {
	        jQuery('#personalizationForm').on("submit", function (e) {
	            e.preventDefault();
	            // Update personalization name.
	            jQuery.cookie('personalizationFirstName', jQuery('#personalizationName').prop('value'), { expires: 365 });
	            personalizationInitialize();
	            jQuery('#personalizationPopupxy').removeClass('active');
	        });
	    }
	    var personalizationOpen = jQuery('.personalizationSet');
	    personalizationOpen.each(function (index) {
	        jQuery(this).on("click", function () {
	            jQuery.cookie('personalizationFirstName', null);
	            jQuery.cookie('personalizationFirstName');
	            initializepersonalization();
	        });
	    });
	}

    //Form Formatting

    //Functional key codes for number and math fields
	var acceptableKeys = [8, 13, 16, 17, 37, 38, 39, 40, 46];
    //Only allow numbers
	function keepOnlyNumbers(input) {
	    onlyNumbers = "";
	    var numberRegex = /[0-9]/ig;
	    var oldValue = input.value.match(numberRegex) || "";

	    for (i = 0; i < oldValue.length; i++) {
	        onlyNumbers += oldValue[i];
	    }
	    return onlyNumbers;
	}

    //Phone number bind
	jQuery('input.tel').bind("propertychange change keydown paste", function (e) {
	    var format = true;
	    for (k = 0; k < acceptableKeys.length; k++) {
	        if (e.keyCode === acceptableKeys[k]) {
	            format = false;
	        }
	    }
	    if (format === true) {
	        formatTel(this);
	    }
	});

    //Format Phone numbers
	function formatTel(input) {
	    var oldValue = keepOnlyNumbers(input);
	    var newValue = "";
	    for (i = 0; i < oldValue.length; i++) {
	        if (oldValue.length <= 10) {
	            if (i == 3) {
	                newValue = "(" + newValue + ") ";
	            } else if (i == 6) {
	                newValue += "-";
	            }
	        } else {
	            if (i == 1) {
	                newValue += "-";
	            } else if (i == 4) {
	                newValue += "-";
	            } else if (i == 7) {
	                newValue += "-";
	            }
	        }
	        newValue += oldValue[i];
	    }
	    input.value = newValue;
	    return false;
	}
	    jQuery(document).ready(function ($) {
	        $('a[rel^=lightcase]').lightcase({
	            attr: 'rel'
	        });
	    });
    
});

jQuery( window ).scroll(function() {   
		
	jQuery("#gototop").scrollTrigger({
	    triggerClass: "gototopactive",
	    scrollMin: 350
	});
   
});

jQuery(window).load(function () {

    jQuery(".notice").responsiveSiteNotice();

});
