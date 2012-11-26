var tribe_list_paged = 1;

jQuery( document ).ready( function ( $ ) {	
	
	var tribe_is_paged = tribe_get_url_param('tribe_paged');		
	
	if( tribe_is_paged ) {
		tribe_list_paged = tribe_is_paged;
	} 

	if( typeof GeoLoc === 'undefined' ) 
		var GeoLoc = {"map_view":""};	

	if( tribe_has_pushstate && !GeoLoc.map_view ) {

		// let's fix any browser that fires popstate on first load incorrectly

		var popped = ('state' in window.history), initialURL = location.href;

		$(window).bind('popstate', function(event) {
			var initialPop = !popped && location.href == initialURL;
			popped = true;

			// if it was an inital load, let's get out of here

			if ( initialPop ) return;

			// this really is popstate, let's fire the ajax but not overwrite our history

			if( event.state ) {				
				tribe_do_string = false;
				tribe_pushstate = false;	
				tribe_popping = true;
				tribe_params = event.state.tribe_params;
				tribe_url_params = event.state.tribe_url_params;
				tribe_pre_ajax_tests( function() {
					tribe_events_list_ajax_post( '', tribe_pushstate, tribe_do_string, tribe_popping, tribe_params, tribe_url_params );	
				});
			}
		} );
		
	}


		// events bar intercept submit

		$( '#tribe-events-list-view' ).on( 'click', 'a#tribe_paged_next', function ( e ) {
			e.preventDefault();
			tribe_list_paged++;	
			tribe_pre_ajax_tests( function() { 
				tribe_events_list_ajax_post( tribe_cur_url );
			});
		} );

		$( '#tribe-events-list-view' ).on( 'click', 'a#tribe_paged_prev', function ( e ) {
			e.preventDefault();
			tribe_list_paged--;
			tribe_pre_ajax_tests( function() {
				tribe_events_list_ajax_post( tribe_cur_url );
			});
		} );

		// if advanced filters active intercept submit

		if ( $( '#tribe_events_filters_form' ).length ) {
			$( 'form#tribe_events_filters_form' ).bind( 'submit', function ( e ) {
				if ( tribe_events_bar_action != 'change_view' ) {
					e.preventDefault();	
					tribe_list_paged = 1;
					tribe_pre_ajax_tests( function() {
						tribe_events_list_ajax_post( tribe_cur_url );
					});
				}
			} );
		}
		
		// event bar datepicker monitoring 

		$('#tribe-bar-date').bind( 'change', function (e) {		

			e.preventDefault();
			tribe_list_paged = 1;
			tribe_pre_ajax_tests( function() {
				tribe_events_list_ajax_post( tribe_cur_url );
			});

		} );

		$( 'form#tribe-events-bar-form' ).bind( 'submit', function ( e ) {

			if ( tribe_events_bar_action != 'change_view' ) {
				e.preventDefault();
				tribe_list_paged = 1;
				tribe_pre_ajax_tests( function() {
					tribe_events_list_ajax_post( tribe_cur_url );
				});
			}
		} );


		function tribe_events_list_ajax_post( tribe_href_target, tribe_pushstate, tribe_do_string, tribe_popping, tribe_params, tribe_url_params ) {

			$( '.ajax-loading' ).show();
			
			
			
			if( !tribe_popping ) {
				
				tribe_hash_string = $( '#tribe-events-list-hash' ).val();

				tribe_params = {
					action     :'tribe_list',
					tribe_paged:tribe_list_paged					
				};
				
				tribe_url_params = {
					action     :'tribe_list',
					tribe_paged:tribe_list_paged					
				};							
				
				if( tribe_hash_string.length ) {
					tribe_params['hash'] = tribe_hash_string;
				}				
				
				// add any set values from event bar to params. want to use serialize but due to ie bug we are stuck with second

				$( 'form#tribe-events-bar-form :input[value!=""]' ).each( function () {
					var $this = $( this );
					if( $this.val().length && $this.attr('name') != 'submit-bar' ) {
						tribe_params[$this.attr('name')] = $this.val();
						tribe_url_params[$this.attr('name')] = $this.val();						
					}			
				} );
				
				tribe_params = $.param(tribe_params);
				tribe_url_params = $.param(tribe_url_params);

				// check if advanced filters plugin is active

				if( $('#tribe_events_filters_form').length ) {

					// serialize any set values and add to params

					tribe_filter_params = $('form#tribe_events_filters_form :input[value!=""]').serialize();
					if( tribe_filter_params.length ) {
						tribe_params = tribe_params + '&' + tribe_filter_params;
						tribe_url_params = tribe_url_params + '&' + tribe_filter_params;
					}					
				} 			
				
				tribe_pushstate = false;
				tribe_do_string = true;				
							
			}
			
			if( tribe_has_pushstate ) {

				$.post(
					TribeList.ajaxurl,
					tribe_params,
					function ( response ) {
						$( "#ajax-loading" ).hide();

						if ( response.success ) {

							tribe_list_paged = response.tribe_paged;

							$( '#tribe-events-list-hash' ).val( response.hash );

							$( '#tribe-events-list-view' ).html( response.html );

							if ( response.max_pages > tribe_list_paged ) {
								$( 'a#tribe_paged_next' ).show();
							} else {
								$( 'a#tribe_paged_next' ).hide();
							}
							if ( tribe_list_paged > 1 ) {
								$( 'a#tribe_paged_prev' ).show();
							} else {
								$( 'a#tribe_paged_prev' ).hide();
							}
							
							if( tribe_do_string ) {
								tribe_href_target = tribe_href_target + '?' + tribe_url_params;								
								history.pushState({									
									"tribe_params": tribe_params,
									"tribe_url_params": tribe_url_params
								}, '', tribe_href_target);															
							}						

							if( tribe_pushstate ) {																
								history.pushState({									
									"tribe_params": tribe_params,
									"tribe_url_params": tribe_url_params
								}, '', tribe_href_target);
							}							
						}
					}
				);
			} else {
			
				if( tribe_do_string ) {
					tribe_href_target = tribe_href_target + '?' + tribe_url_params;													
				}
				window.location = tribe_href_target;			
			}
		} 
		
} );