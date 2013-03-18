jQuery( document ).ready( function ( $ ) {
	
	var base_url = $('#tribe-events-header .tribe-nav-next a').attr('href').slice(0, -8);
	tribe_ev.state.view = 'month';
	
	var has_bar = false;
	
	if($('.tribe-events-calendar').length && $('#tribe-events-bar').length ) {
		has_bar = true;
		$( '.tribe-events-events-dropdown' ).select2({
			minimumResultsForSearch: 9999
		});
	}

	if( tribe_ev.tests.pushstate && !tribe_ev.tests.map_view() ) {		
					
		var params = 'action=tribe_calendar&eventDate=' + $('#tribe-events-header').attr('data-date');

		if( tribe_ev.data.params.length ) 
			params = params + '&' + tribe_ev.data.params;	
		
		history.replaceState({									
			"tribe_params": params
		}, '', location.href);	
		
		$(window).on('popstate', function(event) {		
		
			var state = event.originalEvent.state;				

			if( state ) {			
				tribe_ev.state.do_string = false;
				tribe_ev.state.pushstate = false;	
				tribe_ev.state.popping = true;
				tribe_ev.state.params = state.tribe_params;
				tribe_ev.fn.pre_ajax( function() {
					tribe_events_calendar_ajax_post();	
				});
				
				tribe_ev.fn.set_form( tribe_ev.state.params );	
			} 
		} );
	}

	$( '#tribe-events-content' ).on( 'click', '.tribe-events-sub-nav a', function ( e ) {
		e.preventDefault();
		var $this = $(this);
		tribe_ev.state.date = $this.attr( "data-month" );
		$( '#tribe-bar-date' ).val(tribe_ev.state.date + tribe_ev.fn.get_day());
		if( tribe_ev.state.filter_cats )
			tribe_ev.data.cur_url = $('#tribe-events-header').attr( 'data-baseurl' );
		else
			tribe_ev.data.cur_url = $this.attr( "href" );
		tribe_ev.state.popping = false;
		tribe_ev.fn.pre_ajax( function() { 		
			tribe_events_calendar_ajax_post();	
		});
	} );	

	function tribe_monitor_selects(e) {
		e.preventDefault();				
		tribe_ev.state.date = $( '#tribe-events-events-year' ).val() + '-' + $( '#tribe-events-events-month' ).val();	
		$( '#tribe-bar-date' ).val(tribe_ev.state.date + tribe_ev.fn.get_day());
		if( tribe_ev.state.filter_cats )
			tribe_ev.data.cur_url = $('#tribe-events-header').attr( 'data-baseurl' ) + tribe_ev.state.date + '/';	
		else
			tribe_ev.data.cur_url = base_url + tribe_ev.state.date + '/';		
		tribe_ev.state.popping = false;
		tribe_ev.fn.pre_ajax( function() { 
			tribe_events_calendar_ajax_post();	
		});
	}
	
	if( has_bar ){
		$( '#tribe-events-bar' ).on( 'change', '#tribe-bar-dates select', function ( e ) {
			tribe_monitor_selects(e);
		} );
	} else {
		$( 'body' ).on( 'change', '#tribe-events-events-picker select', function ( e ) {
			tribe_monitor_selects(e);
		} );	
	}

	tribe_ev.fn.snap( '#tribe-events-content', '#tribe-events-content', '#tribe-events-footer .tribe-nav-previous a, #tribe-events-footer .tribe-nav-next a' );
	
	// events bar intercept submit
	
	function tribe_events_bar_calajax_actions(e) {
		if( tribe_events_bar_action != 'change_view' ) {
			e.preventDefault();	
			tribe_ev.state.date = $('#tribe-events-header').attr('data-date');
			if( tribe_ev.state.filter_cats )
				tribe_ev.data.cur_url = $('#tribe-events-header').attr( 'data-baseurl' ) + tribe_ev.state.date + '/';	
			else
				tribe_ev.data.cur_url = tribe_ev.data.initial_url;			
			tribe_ev.state.popping = false;
			tribe_ev.fn.pre_ajax( function() { 
				tribe_events_calendar_ajax_post();
			});		
		}
	}

	$( 'form#tribe-bar-form' ).on( 'submit', function (e) {
		tribe_events_bar_calajax_actions(e);
	} );
	
	$( '.tribe-bar-settings button[name="settingsUpdate"]' ).on( 'click', function (e) {		
		tribe_events_bar_calajax_actions(e);
		tribe_ev.fn.hide_settings();
	} );

	$(tribe_ev.events).on("tribe_ev_runAjax", function() {		
		tribe_events_calendar_ajax_post();		
	});


	function tribe_events_calendar_ajax_post() {	

		tribe_ev.fn.spin_show();
		tribe_ev.state.pushcount = 0;
		
		if( !tribe_ev.state.popping ) {		

			tribe_ev.state.params = {
				action:'tribe_calendar',
				eventDate:tribe_ev.state.date
			};
			
			if( tribe_ev.state.category ) {
				tribe_ev.state.params['tribe_event_category'] = tribe_ev.state.category;
			}
			
			tribe_ev.state.url_params = {};

			$(tribe_ev.events).trigger('tribe_ev_serializeBar');

			tribe_ev.state.params = $.param(tribe_ev.state.params);
			tribe_ev.state.url_params = $.param(tribe_ev.state.url_params);
			
			$(tribe_ev.events).trigger('tribe_ev_collectParams');
			
			if ( tribe_ev.state.pushcount > 0 || tribe_ev.state.filters ) {
				tribe_ev.state.do_string = true;
				tribe_ev.state.pushstate = false;			
			} else {
				tribe_ev.state.do_string = false;
				tribe_ev.state.pushstate = true;
			}
			
			
		} 

		if( tribe_ev.tests.pushstate && !tribe_ev.state.filter_cats ) {
			
			$(tribe_ev.events).triggerAll('tribe_ev_ajaxStart tribe_ev_monthView_AjaxStart');					

			$.post(
				TribeCalendar.ajaxurl,
				tribe_ev.state.params,
				function ( response ) {
					
					tribe_ev.fn.spin_hide();
					tribe_ev.state.initial_load = false;	
					tribe_ev.fn.enable_inputs( '#tribe_events_filters_form', 'input, select' );
					
					if ( response !== '' ) {						
						
						$(tribe_ev.events).triggerAll('tribe_ev_ajaxSuccess tribe_ev_monthView_AjaxSuccess');
						
						tribe_ev.data.ajax_response = {
							'type':'tribe_events_ajax',
							'view':'month',
							'timestamp':new Date().getTime()
						};
						
						var $the_content;
						
						if ($.isFunction(jQuery.parseHTML))
							$the_content = $( $.parseHTML(response) ).contents();							
						else
							$the_content = $( response ).contents();						
						
						$( '#tribe-events-content.tribe-events-calendar' ).html( $the_content );

						var page_title = $the_content.filter("#tribe-events-header").attr('data-title');
						
						if( has_bar ){
							var $date_picker = $the_content.find("#tribe-events-events-picker").contents();
						
							$( '#tribe-bar-dates' ).contents().not('#tribe-bar-date, #tribe-date-storage').remove();
							$( '#tribe-bar-dates' ).append( $date_picker );
							$( '.tribe-events-events-dropdown' ).select2({
								minimumResultsForSearch: 9999
							});
						}
						
						$(document).attr('title', page_title);
						
						if( tribe_ev.state.do_string ) {							
							tribe_ev.data.cur_url = tribe_ev.data.cur_url + '?' + tribe_ev.state.url_params;								
							history.pushState({
								"tribe_date": tribe_ev.state.date,
								"tribe_params": tribe_ev.state.params
							}, page_title, tribe_ev.data.cur_url);															
						}						

						if( tribe_ev.state.pushstate ) {								
							history.pushState({
								"tribe_date": tribe_ev.state.date,
								"tribe_params": tribe_ev.state.params
							}, page_title, tribe_ev.data.cur_url);
						}
					}
				}
			);
				
		} else {			
			if( tribe_ev.state.do_string ) 
				window.location = tribe_ev.data.cur_url + '?' + tribe_ev.state.url_params;													
			else			
				window.location = tribe_ev.data.cur_url;			
		}
	}	
} );