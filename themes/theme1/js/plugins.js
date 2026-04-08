// Avoid `console` errors in browsers that lack a console.
(function() {
    var method;
    var noop = function () {};
    var methods = [
        'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
        'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
        'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
        'timeStamp', 'trace', 'warn'
    ];
    var length = methods.length;
    var console = (window.console = window.console || {});

    while (length--) {
        method = methods[length];

        // Only stub undefined methods.
        if (!console[method]) {
            console[method] = noop;
        }
    }
}());

// Place any jQuery/helper plugins in here.
function clearText(thefield) {
    if (thefield.defaultValue == thefield.value)
        thefield.value = ""
}
//Function to parse a querystring variable from location.search for accordion
function getQueryVariable(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        if (pair[0] == variable) {
            return unescape(pair[1]);
        }
    }
    return false;
}
//Replaces Subsection Table with a Div Wrapper 
//1.0.0 (c) Fiserv 2015.  All rights reserved.
(function (jQuery) {

    jQuery.fn.tableWrapper = function (options) {

        // This is the easiest way to have default options.
        var settings = jQuery.extend({
            // These are the defaults.
            wrapperClass: "subsection",
        }, options);

        var $this = jQuery(this);

        $this.each(function () {
            //console.warn(jQuery(this));
            var wrapper = jQuery('<div class="' + settings.wrapperClass + '"></div>');
            var tableimg = jQuery(this).css('background-image'),
                subsectionContent = '';
            if (tableimg != 'none') {
                wrapper.css("background-image", tableimg);
            }
            jQuery(this).children("tbody").children("tr").each(function () {
                subsectionContent += '<div class="' + settings.wrapperClass + '-content">';
                jQuery("td:first", this).each(function () {
                    subsectionContent += jQuery(this).html();
                });
                subsectionContent += '</div>';
            });
            wrapper.html(subsectionContent);
            jQuery(this).replaceWith(wrapper);
        });
    }
}(jQuery));
/*Examples
Normal Implementation:
jQuery("table.subsection-table").tableWrapper();

Custom Implementation:
jQuery("table.subsection-table").tableWrapper({
    wrapperClass: "customclass",
});
*/

// Responsive Zoom 2.2.1 Copyright (c) 2014 Fiserv.  All rights reserved.
// Requires Modernizr, jQuery
// Needs to be AFTER any section table/div replacement scripts

function debounce(func, wait, immediate) {
    var timeout;
    return function () {
        var context = this, args = arguments;
        var later = function () {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
};

Modernizr.addTest('zoom', function () {
    var test = document.createElement('div');
    if (test.style.zoom === undefined) {
        delete test;
        return false;
    }
    delete test;
    return true;
});

jQuery.fn.responsiveZoom = function (options) {
    var settings = jQuery.extend({
        hideBeforeResized: true
    }, options);

    //console.log('Mobile size detected.');	
    var responsiveZoomers = jQuery(this);
    //console.log(responsiveZoomers.length);
    //console.log('Modernizr.csstransforms: ' + Modernizr.csstransforms);
    responsiveZoomers.each(function () {
        //if ( jQuery( "body" ).hasClass( 'mobile' ) ) {

        // reset zoom before calc.
        if (Modernizr.zoom && !Modernizr.csstransforms) {
            jQuery(this).css("zoom", 1);
            //console.log('Reset the zoom to 1.');
        } else {
            jQuery(this).css("transform-origin", "0 0");
            jQuery(this).css("transform", "scale(1)");
            //console.log('Reset the transform scale to: ' + jQuery( this ).css("transform"));
        }

        // The element being zoomed can't be display:none.
        if (jQuery(this).css("display") === 'none') {
            if (jQuery(this).prop("tagName") == "TABLE") {
                jQuery(this).css("display", "table");
            } else {
                jQuery(this).css("display", "inline");
            }
            var elWidth = jQuery(this).width();
            jQuery(this).css("display", "none");
        } else {
            var elWidth = jQuery(this).width();
        }

        // Widths set as a percentage are set to pixels for proper scaling.
        if (jQuery(this).attr("tagName") == "TABLE") {
            if (!jQuery(this).data("original-width-string")) {
                jQuery(this).data("original-width-string", jQuery(this)[0].style.width);
            }
            if (!jQuery(this).data("original-width")) {
                jQuery(this).data("original-width", elWidth);
                jQuery(this).css("width", elWidth);
                //console.log('Set the width to: ' + elWidth);
            } else {
                jQuery(this).css("width", jQuery(this).data("original-width"));
                //console.log('Reset the width to: ' + jQuery( this ).data("original-width"));
            }
        }

        // Calculates the zoom level.
        if (Modernizr.zoom && !Modernizr.cssgradients) {
            if (!jQuery(this).data("original-position")) {
                jQuery(this).data("original-position", jQuery(this).css("position"));
            }
            jQuery(this).css("position", "absolute").css("visibility", "hidden");
        }
        if (!jQuery(this).parent().hasClass("responsive-zoom-wrapper")) {
            var elParentWidth = jQuery(this).parent().width();
        } else {
            var elParentWidth = jQuery(this).parent().parent().width();
        }
        if (Modernizr.zoom && !Modernizr.cssgradients) {
            jQuery(this).css("position", jQuery(this).data("original-position")).css("visibility", "visible");
        }
        //console.log('elParentWidth: ' + elParentWidth);

        //console.log('elWidth: ' + elWidth + ' / elParentWidth: ' + elParentWidth );
        var elZoom = elParentWidth / elWidth;
        //console.log('elZoom: ' + elZoom);

        // Create a new div to hold the parents height if zoom is not supported.
        if (!jQuery(this).parent().hasClass("responsive-zoom-wrapper")) {
            var responsiveZoomWrapper = jQuery('<div class="responsive-zoom-wrapper"></div>');
            jQuery(this).after(responsiveZoomWrapper);
            responsiveZoomWrapper.append(jQuery(this));
            jQuery(this).parent().css("margin-top", jQuery(this).css("margin-top"));
            jQuery(this).css("margin-top", 0);
            jQuery(this).parent().css("margin-bottom", jQuery(this).css("margin-bottom"));
            jQuery(this).css("margin-bottom", 0);
            //console.log('Created responsiveZoomWrapper');
        }

        // Applies the zoom
        if (elZoom < 1) {
            if (Modernizr.zoom && !Modernizr.csstransforms) {
                jQuery(this).css("zoom", elZoom);
                //console.log('Zoom set to: ' + elZoom);
            } else {
                jQuery(this).css("transform-origin", "0 0");
                jQuery(this).css("transform", "scale(" + elZoom + ")");
                jQuery(this).parent().css("width", jQuery(this).width() * elZoom);
                jQuery(this).parent().css("height", jQuery(this).height() * elZoom);
            }
        } else {
            if (Modernizr.zoom && !Modernizr.csstransforms) {
                jQuery(this).css("zoom", "");
            } else {
                jQuery(this).css("transform-origin", "");
                jQuery(this).css("transform", "");
                if (jQuery(this).parent().hasClass("responsive-zoom-wrapper")) {
                    var parentToRemove = jQuery(this).parent();
                    jQuery(this).css("margin-top", "");
                    jQuery(this).css("margin-bottom", "");
                    parentToRemove.after(jQuery(this));
                    parentToRemove.remove();
                }
            }
            jQuery(this).css("width", jQuery(this).data("original-width-string"));
        }

        if (settings.hideBeforeResized) { jQuery(this).css("opacity", 1); }
    });
};
/* Examples:
jQuery( ".responsivezoom" ).responsiveZoom ({
    hideBeforeResized: false
});
*/

// Expandables v2.4.0 Copyright (c) 2015 Jesse Fowler & Kristen Rogers, Fiserv
// Requires jQuery
// Needs to be after any div/table replacement scripts in document .ready
(function (jQuery) {

    jQuery.fn.fiservExpandablesInit = function (options) {

        // This is the easiest way to have default options.
        var settings = jQuery.extend({
            // These are the defaults.
            defaultClass: 'expandable',
            TOC: false,
            allExpandable: true,
            openFirstExpandable: false,
            scrollToExpanders: false,
            displayedMobileOnly: jQuery('#tabtoexpander'),
            tagBody: false, //tag the body when expander is open / selected
            additionalOffsetTop: 0
        }, options);

        var $this = jQuery(this);

        $this.each(function () {

            var $expandables = jQuery(this),
                expander = []; // Customize with element that is visible only in the mobile view.

            var replacement = jQuery("<div></div>");
            replacement.attr('id', $expandables.attr('id'));
            replacement.addClass(settings.defaultClass);
            var subsectionContent = "";
            $expandables.children("tbody").children("tr").each(function () {
                jQuery("td:first", this).each(function () {
                    //subsectionContent += jQuery(this).html();
                    var clonedTd = jQuery(this).clone(true).children().unwrap();
                    replacement.append(clonedTd);
                });
            });
            //replacement.html(subsectionContent);
            $expandables.before(replacement);
            $expandables.remove();

            // Create click events for expandables.
            var expandable = replacement;
            expander = expandable.children(':first-child');

            // Set initial height
            var adjustedLineHeight = parseFloat(expander.css("line-height")) + parseFloat(expander.css("padding-bottom")) + parseFloat(expander.css("padding-top"));
            expandable.css("height", adjustedLineHeight);

            var tagBody = function () {
                if (settings.tagBody) {
                    if (settings.allExpandable || settings.displayedMobileOnly.css('display') == 'block') {
                        // Sets the prefixed id as a body class.
                        if (expandable.is(jQuery('.' + settings.defaultClass + '[id*=selected-]'))) {
                            jQuery('body').toggleClass(expandable.attr('id'));
                        };
                    } else {
                        // Removes all the prefixed ids that were added to the body.
                        var prefix = "selected-";
                        var classes = jQuery('body')[0].className.split(" ").filter(function (c) {
                            return c.lastIndexOf(prefix, 0) !== 0;
                        });
                        jQuery('body')[0].className = jQuery.trim(classes.join(" "));
                        // Sets the prefixed id as a body class.
                        if (expandable.is(jQuery('.' + settings.defaultClass + '[id*=selected-]'))) {
                            jQuery('body').toggleClass(expandable.attr('id'));
                        };
                    }
                }
            }

            expander.on({
                click: function (e) {
                    tagBody();
                    if (settings.allExpandable || settings.displayedMobileOnly.css('display') == 'block') {
                        if (expandable.hasClass('expanded')) {
                            expandable.removeClass("expanded");
                            expandable.css("height", adjustedLineHeight);
                        } else {
                            expandable.addClass("expanded");
                            expandable.css("height", "auto");
                        }
                    } else {
                        expandable.parent().children('.expanded').removeClass('expanded');
                        expandable.addClass('expanded');
                        expandable.css("height", "auto");
                        if (settings.scrollToExpanders) {
                            if (this && typeof this.scrollIntoView === 'function') {
                                this.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                        }
                    }
                }
            });

            // Create Table of Contents element for the expandables.
            if (settings.TOC) {
                var expandablesTOC = expandable.parent().children('.expandablesTOC');
                //console.warn(expandablesTOC);
                var expandablesLInA = function (obj, toc) {
                    var expandablesTOCli = jQuery('<li></li>'),
                        expandablesTOCaClass = '';
                    if (settings.openFirstExpandable && expandable.is(expandable.parent().children("." + settings.defaultClass).eq(0))) {
                        expandablesTOCaClass = 'active';
                    }
                    var expandablesTOCa = jQuery("<a></a>").html(obj.find('h1,h2,h3,h4,h5,h6,a').eq(0).html()).attr("class", expandablesTOCaClass);
                    if (expandable.attr("id")) {
                        expandablesTOCa.attr("id", settings.defaultClass + "-" + expandable.attr("id"));
                    }

                    if (obj.find('h1,h2,h3,h4,h5,h6,a').eq(0).attr('href')) {
                        expandablesTOCa.attr('href', obj.find('h1,h2,h3,h4,h5,h6,a').eq(0).attr("href"));
                    } else {
                        expandablesTOCa.on({
                            click: function (e) {
                                tagBody();
                                if (!settings.allExpandable) {
                                    jQuery(this).parent().parent().find('li a.active').removeClass('active');
                                    jQuery(this).addClass('active');
                                } else {
                                    jQuery(this).toggleClass('active');
                                }
                                if (settings.allExpandable) {
                                    if (expandable.hasClass("expanded")) {
                                        expandable.removeClass("expanded");
                                    } else {
                                        expandable.addClass("expanded");
                                    }
                                } else {
                                    expandable.parent().children('.expanded').removeClass("expanded");
                                    expandable.addClass("expanded");
                                }
                            }
                        });
                    }
                    expandablesTOCli.append(expandablesTOCa);
                    toc.append(expandablesTOCli);
                }
                if (expandablesTOC.length) {
                    expandablesLInA(expandable, expandablesTOC.eq(0));
                } else {
                    expandablesTOC = jQuery("<ul></ul>").attr("class", "expandablesTOC");
                    if (settings.openFirstExpandable) {
                        expandable.addClass("expanded");
                        expandable.css("height", "");
                    }
                    expandablesLInA(jQuery(this), expandablesTOC);
                    expandable.parent().prepend(expandablesTOC);
                }
            }

        });

        // Expand from querystring v1.2.0 Copyright (c) Jesse Fowler & Kristen Rogers, Fiserv
        // Requires Querystring parser and the getParameterByName() function.
        // Querystring should be page.aspx?expand=idname

        // Querystring parser
        function getParameterByName(name) {
            name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
            var regexS = "[\\?&]" + name + "=([^&#]*)",
              regex = new RegExp(regexS),
              results = regex.exec(window.location.search);
            if (results == null)
                return "";
            else
                return decodeURIComponent(results[1].replace(/\+/g, " "));
        }

        function fiservExpanderInit() {
            var expand = getParameterByName('expand'),
                offsetTopExpander = 0;
            if (expand) {
                jQuery("." + settings.defaultClass).each(function (el, index) {  // This doesn't appear to be returning anything to offsetTopExpander - Jesse
                    if (jQuery(this).attr('id') === expand) {
                        offsetTopExpander = jQuery(this).offset().top;
                        offsetTopExpander = offsetTopExpander - parseInt(settings.additionalOffsetTop); /*+(55*index)*/
                        if (settings.TOC) {
                            jQuery("#" + settings.defaultClass + "-" + jQuery(this).attr("id")).click();
                        } else {
                            jQuery(this).addClass("expanded");
                            jQuery(this).css("height", "auto");
                        }
                    }
                });

                //delay scrollTo to allow for expander to expand - avoid short page
                function scrollToExpander(e) {
                    window.scrollTo(0, offsetTopExpander);
                }
                setTimeout(scrollToExpander, 500)
            }
        }
        fiservExpanderInit();

        return $this;
    }
}(jQuery));
/*Examples
Normal Implementation:
jQuery("table.subsection-table").fiservExpandablesInit();

Custom Implementation:
jQuery("table.subsection-table").fiservExpandablesInit({
    wrapperClass: "customclass",
});
*/

//Smooth Scroll 	
jQuery(function () {
    jQuery('a[href*=#]:not([href=#])').click(function () {
        if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
            var target = jQuery(this.hash);
            target = target.length ? target : jQuery('[name=' + this.hash.slice(1) + ']');
            if (target.length) {
                jQuery('html,body').animate({
                    scrollTop: target.offset().top
                }, 850, 'swing');
                return false;
            }
        }
    });
});
// Scroll Trigger 1.1.0 (c) Kristen Rogers & Jesse Fowler, Fiserv 2015.  All rights reserved.
// Requires jQuery and CSS.
(function (jQuery) {

    jQuery.fn.scrollTrigger = function (options) {

        var settings = jQuery.extend({
            triggerClass: "scroll-active",
            scrollMin: 0,
            resetOnScrollUp: true,
            target: this
        }, options);

        var $this = this,
            height = $(window).scrollTop(),
            scrollMinProvided = true,
            targetProvided = true;

        if (settings.scrollMin == 0) {
            scrollMinProvided = false;
        }
        if (settings.target === this) {
            targetProvided = false;
        }

        $this.each(function (index) {
            if (!scrollMinProvided) {
                settings.scrollMin = jQuery(this).offset().top - (jQuery(window).innerHeight() * 1);
            }
            if (height >= settings.scrollMin) {
                if (targetProvided) {
                    settings.target.addClass(settings.triggerClass);
                } else {
                    jQuery(this).addClass(settings.triggerClass);
                }
            } else if (height < settings.scrollMin && settings.resetOnScrollUp) {
                if (targetProvided) {
                    settings.target.removeClass(settings.triggerClass);
                } else {
                    jQuery(this).removeClass(settings.triggerClass);
                }
            }
        });
        return $this;
    }
}(jQuery));
/*Examples
Normal Implementation:
jQuery("#header").scrollTrigger();

Custom Implementation:
jQuery("#gototop").scrollTrigger({
    triggerClass: "customclass",
}); */

// Responsive Site Notice 3.1.0 Copyright 2015 Jesse Fowler, Fiserv.  All rights reserved.
// Requires jQuery, CSS and notice article
(function (jQuery) {

    jQuery.fn.responsiveSiteNotice = function (options) {

        var settings = $.extend({
            reqLength: 15,
            fixedPosition: false,
            delay: 100
        }, options);

        this.each(function () {
            var $notice = jQuery(this),
                $noticeHtml = $notice.find('.noticeHtml'),
                uniqueName = $notice.attr('id') + "NoticeText";
            if ($noticeHtml.html().length > settings.reqLength) {

                var noticeCloser = jQuery('<div class="noticecloser"></div>');

                var noticeCloserSession = jQuery('<div class="noticeclosersession"></div>');

                var firstTable = $notice.find('.noticeHtml>table>tbody>tr>td');
                if (firstTable.length) {
                    noticeCloserSession.prependTo(firstTable);
                    noticeCloser.prependTo(firstTable);
                } else {
                    noticeCloserSession.prependTo($noticeHtml);
                    noticeCloser.prependTo($noticeHtml);
                }

                var bypassNotice = localStorage.getItem(uniqueName),
                    noticeHtmlNow = $noticeHtml.html();
                if (bypassNotice) {
                    sessionStorage.setItem(uniqueName, bypassNotice);
                }
                var bypassNoticeSession = sessionStorage.getItem(uniqueName);

                if (settings.fixedPosition) {
                    var newId = $notice.prop('id') + '-clone';
                    $notice.clone().prop('id', newId).prependTo(jQuery('body'));
                }
                function noticeOpen() {
                    $notice.addClass('active');
                    jQuery('body').addClass('noticeactive');
                }
                function noticeClose() {
                    $notice.removeClass('active');
                    jQuery('body').removeClass('noticeactive');
                }
                if (bypassNotice != noticeHtmlNow && bypassNoticeSession != noticeHtmlNow) {
                    setTimeout(noticeOpen, settings.delay);
                    localStorage.removeItem(uniqueName);
                    sessionStorage.removeItem(uniqueName);
                } else if (bypassNoticeSession != noticeHtmlNow) {
                    setTimeout(noticeOpen, settings.delay);
                    localStorage.removeItem(uniqueName);
                    sessionStorage.removeItem(uniqueName);
                }

                noticeCloser.on('click', function () {
                    localStorage.setItem(uniqueName, noticeHtmlNow);
                    sessionStorage.setItem(uniqueName, noticeHtmlNow);
                    noticeClose();
                });

                noticeCloserSession.on('click', function () {
                    sessionStorage.setItem(uniqueName, noticeHtmlNow);
                    noticeClose();
                });

            } else if ($noticeHtml.html().length < settings.reqLength) {
                localStorage.removeItem(uniqueName);
                sessionStorage.removeItem(uniqueName);
            }
        });

        return this;

    };

}(jQuery));

/* Usage example:
jQuery( ".notice" ).responsiveSiteNotice({
    fixedPosition: true
}); 
*/