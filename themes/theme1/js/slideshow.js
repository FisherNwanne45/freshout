jQuery( document ).ready(function() {	
	// Slideshow v2.5.0 (c) 2015 Jesse Fowler, Fiserv
	// Requires jQuery, jQuery Mobile, and CSS
	
	jQuery.fn.slideShow = function(options) {
		var settings = jQuery.extend({
            showDuration: 10000,
            transitionSpeed: 1000,
			container: this,
			currentIndex: 0,
			tocActive: 'toc-active',
			captionActive: 'captionActive',
			thumbOpacity: 1,
			hoverSelect: false,
			autoPlay: true,
			TOC: 2, 							// TOC: 0 - Off, 1 - Numbered, 2 - Image Alt, 3 - Thumbnails
			tocThumbnailed: false,
			randomSelect: true,
			hoverPause: false,
			captionTables: true
        }, options );

		var images			= settings.container.find('table>tbody>tr>td>p:first-child img'),
			interval,
			toc 			= [],
			captions		= [],
			afterFirstSlide	= false,
			hold			= false,
			TOCParent		= settings.container.parent();	//settings.container.parent().parent().parent().parent()	
		
		var start = function() { if (settings.autoPlay) {interval = self.setInterval(show, settings.showDuration); } };
		
		var stop = function() { window.clearInterval(interval); };
		
		var show = function(to) {
		
			// Ending animation of the last slide.
			images.removeClass( 'previous' );
			images.eq( settings.currentIndex ).removeClass( 'active' );
			images.eq( settings.currentIndex ).addClass( 'previous' );
			if( !Modernizr.csstransforms ){
				images.eq( settings.currentIndex ).fadeOut(settings.transitionSpeed);
			} 
			if (settings.TOC > 0) { TOCParent.find('.slideshow-container-controls').children('div').eq(settings.currentIndex).removeClass(settings.tocActive); }
			if (settings.captionTables) { 
				jQuery('#caption-container .caption').eq(settings.currentIndex).removeClass(settings.captionActive); 
				// Caption Animation 
				if(!Modernizr.csstransforms){
					jQuery('#caption-container .caption').eq(settings.currentIndex).animate({
						left: -580
					}, (settings.transitionSpeed / 2), "linear", function() {
						hold = false;
					});
				}
			}
			
			// Beginning of the animation of the new slide.
			images.eq( settings.currentIndex = (typeof to != 'undefined' ? to : (settings.currentIndex < images.length - 1 ? settings.currentIndex+1 : 0)) ).addClass( 'active' );
			if( !Modernizr.csstransforms ){
				images.eq(settings.currentIndex).fadeIn(settings.transitionSpeed);
			}
			// Class all of the elements in the slideshow with a unique order.
			for (i=0; i<images.length; i++) {
				images.removeClass( "item-" + i );
			}		
			for (i=settings.currentIndex; i<images.length; i++) {
				images.eq(i).addClass( "item-" + ( i - settings.currentIndex ) );
			}
			for (i=0; i<settings.currentIndex; i++) {
				images.eq(i).addClass( "item-" + ( i + images.length - settings.currentIndex ) );
			}
			
			if (settings.TOC > 0) { TOCParent.find('.slideshow-container-controls').children('div').eq(settings.currentIndex).addClass(settings.tocActive); }
			if (settings.captionTables) { 
				jQuery('#caption-container .caption').eq(settings.currentIndex).addClass(settings.captionActive);
				if(!Modernizr.csstransforms){
					hold = true;		
					jQuery('#caption-container .caption').eq(settings.currentIndex).animate({
						left: 0
					}, (settings.transitionSpeed / 2), "linear", function() {
						hold = false;
					});
				}
			}
		};
		
		var preview = jQuery("<div/>", {
			'class': 'slideshow-container-controls'
		})
		TOCParent.append(preview);
					
		var captionsContainer = jQuery("<div/>", {
			id: 'caption-container'
		})
		settings.container.parent().append(captionsContainer);

		images.each(function(index) {
			/* add caption */
			if (settings.captionTables) { 
				if (jQuery(this).parent().prop("tagName") != "A") { 
					var tableContents = '<div class="caption captionInActive">' + jQuery(this).parent('p').parent().html() + '</div>';
				} else {
					var tableContents = '<div class="caption captionInActive">' + jQuery(this).parent('a').parent('p').parent().html() + '</div>';
				}
				captionsContainer.append(tableContents);
			}
			/* add to table of contents */
			// if(index == 0) { tocPreActive = settings.tocActive }
			if (jQuery(this).prop('alt') != null) { tocAlt = jQuery(this).prop('alt'); } else { tocAlt = "" }
			var imgnum = index + 1;
			if (settings.TOC == 3){
				var tocImg = '<a href="#"><img src="' + jQuery(this).get('src') + '" alt="' + tocAlt + '" title="' + tocAlt + '"></a>';
			} else if (settings.TOC == 2){
				var tocImg = '<a href="#"><span class="numeric-index">' + imgnum + '</span>' + tocAlt + '</a>';
			} else {
				var tocImg = '<a href="#">' + imgnum + '</a>';
			};
			
			var tocDiv = jQuery("<div/>", {
				html: tocImg
			});
			preview.append(tocDiv);
			tocDiv.on({
				click: function(e) {
					if(e) e.preventDefault();
					stop();
					show(index);
					start();
				}, mouseenter: function() {
					jQuery(this).fadeIn(settings.transitionSpeed);
					if (settings.hoverSelect) {
						stop();
						show(index);
					}
				}, mouseleave: function() {
					if(!jQuery(this).hasClass(settings.tocActive)) jQuery(this).fadeTo(settings.transitionSpeed,settings.thumbOpacity);
					if (settings.hoverSelect) {
						start();
					}
				}
			});

			// captionsContainer.inject('mainimg', 'after'); Not sure if this is a requirement.
			//document.id('content1').grab(preview, 'top');
		});
					
		if (settings.captionTables) { 
			captionsContainer.children('.caption').children('p:first-child').remove();		
			captionsContainer.children('.caption').children('*:last-child').addClass('lastchild');
		}

		if (settings.TOC > 0) { preview.css('display', 'block'); } else { preview.css('display', 'none'); }
					
		jQuery('#previous').on({
			click: function(e) {
				if(e) e.preventDefault();
				stop();
				if ((settings.currentIndex - 1) < 0) {
					show(images.length - 1);
				} else {
					show(settings.currentIndex - 1);
				}
				start();
			}
		});
		
		// Swipe previous
		settings.container.add(captionsContainer).on( "swiperight", swiperightHandler );
		
		function swiperightHandler( event ){
			event.stopImmediatePropagation();
			stop();
			if ((settings.currentIndex - 1) < 0) {
				show(images.length - 1);
			} else {
				show(settings.currentIndex - 1);
			}
			start();
		}
				
		jQuery('#next').on({
			click: function(e) {
				if (!hold) {
					if(e) e.preventDefault();
					stop();
					show();
					start();
				}
			}
		});
		
		// Swipe next
		settings.container.add(captionsContainer).on( "swipeleft", swipeleftHandler );
		
		function swipeleftHandler( event ){
			event.stopImmediatePropagation();
			stop();
			show();
			start();
		}
	
		/* control: start/stop on mouseover/mouseout */
		if (settings.hoverPause) {
			settings.container.on({	
				mouseenter: function() { stop(); },
				mouseleave: function() { start(); }
			});
		}	
		if (settings.randomSelect) { 
			var randomSlideNumber = Math.floor(Math.random()*(images.length));
			show(randomSlideNumber); 
		} else {
			show(0);
		}
		start();
	};
	jQuery('#slideshow-container').slideShow({
	    randomSelect: false
	});
	jQuery('#slideshow-secondary-container').slideShow({
	    randomSelect: false,
		captionTables: false
	});
	
	// Linking the TOC between slideshows
	var enableLinkedTOCs = true,
		numberOfTOCs = jQuery('.slideshow-container-controls').length;
	if(enableLinkedTOCs && numberOfTOCs > 1){
		jQuery('#slideshow-container').parent().find('.slideshow-container-controls div').each(function(index){
			jQuery( this ).click(function(){
				jQuery('#slideshow-secondary-container').parent().find('.slideshow-container-controls div').eq(index).click();
			});
		});
	}
	
});
