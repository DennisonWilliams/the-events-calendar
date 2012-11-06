jQuery(document).ready(function($) {

	// Global Tooltips
	if( $('.tribe-events-calendar').length || $('.tribe-events-grid').length || $('.tribe-events-list').length || $('.tribe-events-single').length ) {
		function tribe_event_tooltips() {
			$('.tribe-events-calendar, .tribe-events-grid, .tribe-events-list, .tribe-events-single').delegate('div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring', 'mouseenter', function() {
				// Week View Tooltips
				if( $('body').hasClass('tribe-events-week') ) {
					var bottomPad = $(this).outerHeight() + 5;
				} else if( $('body').hasClass('events-gridview') ) { // Cal View Tooltips
					var bottomPad = $(this).find('a').outerHeight() + 18;
				} else if( $('body').is('.single-tribe_events, .events-list') ) { // Single/List View Recurring Tooltips
					var bottomPad = $(this).outerHeight() + 12;
				}
				// Widget Tooltips
				if( $(this).parents('.tribe-events-calendar-widget').length ) {
					var bottomPad = $(this).outerHeight() - 6;
				}
				$(this).find('.tribe-events-tooltip').css('bottom', bottomPad).show();
			}).delegate('div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring', 'mouseleave', function() {
				if ($.browser.msie && $.browser.version <= 9) {
					$(this).find('.tribe-events-tooltip').hide()
				} else {
					$(this).find('.tribe-events-tooltip').stop(true,false).fadeOut(200);
				}
			});
		}
		tribe_event_tooltips();
	}

});