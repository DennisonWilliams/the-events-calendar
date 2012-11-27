<?php
/**
 * @for Events List Template
 * This file contains the hook logic required to create an effective event list view.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_List_Template')){
	class Tribe_Events_List_Template extends Tribe_Template_Factory {

		private $first = true;
		static $loop_increment = 0;
		static $prev_event_month = null;
		static $prev_event_year = null;

		public static function init(){
			// Start list template
			add_filter( 'tribe_events_list_before_template', array( __CLASS__, 'before_template' ), 1, 1 );
	
			// Page Title
			add_filter( 'tribe_events_list_the_title', array( __CLASS__, 'the_title' ), 1, 1 );

			// Start list loop
			add_filter( 'tribe_events_list_before_loop', array( __CLASS__, 'before_loop' ), 1, 1 );
			add_filter( 'tribe_events_list_inside_before_loop', array( __CLASS__, 'inside_before_loop' ), 1, 1 );
		
			// Event featured image
			add_filter( 'tribe_events_list_the_event_image', array( __CLASS__, 'the_event_image' ), 1, 1 );

			// Event details start
			add_filter( 'tribe_events_list_before_the_event_details', array( __CLASS__, 'before_the_event_details' ), 1, 1 );

			// Event title
			add_filter( 'tribe_events_list_the_event_title', array( __CLASS__, 'the_event_title' ), 1, 1 );

			// Event content
			add_filter( 'tribe_events_list_before_the_content', array( __CLASS__, 'before_the_content' ), 1, 1 );
			add_filter( 'tribe_events_list_the_content', array( __CLASS__, 'the_content' ), 1, 1 );
			add_filter( 'tribe_events_list_after_the_content', array( __CLASS__, 'after_the_content' ), 1, 1 );
	
			// Event meta
			add_filter( 'tribe_events_list_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_list_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_list_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );

			// Event details end
			add_filter( 'tribe_events_list_after_the_event_details', array( __CLASS__, 'after_the_event_details' ), 1, 1 );			
	
			// End list loop
			add_filter( 'tribe_events_list_inside_after_loop', array( __CLASS__, 'inside_after_loop' ), 1, 1 );
			add_filter( 'tribe_events_list_after_loop', array( __CLASS__, 'after_loop' ), 1, 1 );
	
			// Event notices
			add_filter( 'tribe_events_list_notices', array( __CLASS__, 'notices' ), 1, 2 );

			// List pagination
			add_filter( 'tribe_events_list_before_pagination', array( __CLASS__, 'before_pagination' ), 1, 1 );
			add_filter( 'tribe_events_list_pagination', array( __CLASS__, 'pagination' ), 1, 1 );
			add_filter( 'tribe_events_list_after_pagination', array( __CLASS__, 'after_pagination' ), 1, 1 );

			// End list template
			add_filter( 'tribe_events_list_after_template', array( __CLASS__, 'after_template' ), 1, 2 );
		}
		// Start List Template
		public static function before_template( $post_id ){
			$html = '<div id="tribe-events-content" class="tribe-events-list">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_template');
		}
		public static function the_title( $post_id ){
			$html = sprintf( '<h2 class="tribe-events-page-title">%s</h2>',
				tribe_get_events_title()
				);
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_title');
		}
		// Start List Loop
		public static function before_loop( $post_id ){
			$html = '<div class="tribe-events-loop hfeed">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_loop');
		}
		public static function inside_before_loop( $post_id ){
			global $wp_query;
			// Get our wrapper classes (for event categories, organizer, venue, and defaults)
			$tribe_string_classes = '';
			$tribe_cat_ids = tribe_get_event_cat_ids( $post_id ); 
			foreach( $tribe_cat_ids as $tribe_cat_id ) { 
				$tribe_string_classes .= 'tribe-events-category-'. $tribe_cat_id .' '; 
			}
			$tribe_string_wp_classes = '';
			$allClasses = get_post_class(); 
			foreach ($allClasses as $class) { 
				$tribe_string_wp_classes .= $class . ' '; 
			}
			$tribe_classes_default = 'hentry vevent '. $tribe_string_wp_classes;
			$tribe_classes_venue = tribe_get_venue_id() ? 'tribe-events-venue-'. tribe_get_venue_id() : '';
			$tribe_classes_organizer = tribe_get_organizer_id() ? 'tribe-events-organizer-'. tribe_get_organizer_id() : '';
			$tribe_classes_categories = $tribe_string_classes;
			$class_string = $tribe_classes_default .' '. $tribe_classes_venue .' '. $tribe_classes_organizer .' '. $tribe_classes_categories;
			
			// added first class for css
			if( ( self::$loop_increment == 0 ) && !tribe_is_day() ){
				$class_string .= ' tribe-first';
			}
			
			// added last class for css
			if( self::$loop_increment == count($wp_query->posts)-1 ){
				$class_string .= ' tribe-last';
			}


			/* Month and year separators */

			$show_separators = apply_filters( 'tribe_events_list_show_separators', true );

			if ( $show_separators ) {
				if ( tribe_get_start_date( $post_id, false, 'Y' ) != date( 'Y' ) && self::$prev_event_year != tribe_get_start_date( $post_id, false, 'Y' ) ) {
					echo sprintf( "<span class='tribe_list_separator_year'>%s</span>", tribe_get_start_date( $post_id, false, 'Y' ) );
				}

				if ( self::$prev_event_month != tribe_get_start_date( $post_id, false, 'm' ) ) {
					echo sprintf( "<span class='tribe_list_separator_month'>%s</span>", tribe_get_start_date( $post_id, false, 'M' ) );
				}

				self::$prev_event_year  = tribe_get_start_date( $post_id, false, 'Y' );
				self::$prev_event_month = tribe_get_start_date( $post_id, false, 'm' );
			}

			$html = '<div id="post-' . get_the_ID() . '" class="' . $class_string . ' tribe-clearfix">';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_list_inside_before_loop' );
		}

		// Event Image
		public static function the_event_image( $post_id ){
			$html ='';
			if ( tribe_event_featured_image() ) {
				$html .= tribe_event_featured_image(null, 'large');
			}
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_event_image');

		}
		// Event Details Begin
		public static function before_the_event_details ( $post_id ){
			$html = '<div class="tribe-events-event-details">';
			if ( tribe_get_cost() ) { // Get our event cost 
				$html .=	'<div class="tribe-events-event-cost"><span>'. tribe_get_cost() .'</span></div>';
			 } 				
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_the_event_details'); 
		}							
		// Event Title
		public static function the_event_title( $post_id ){
			$html = '<h2 class="entry-title summary"><a class="url" href="'. tribe_get_event_link() .'" title="'. get_the_title( $post_id ) .'" rel="bookmark">'. get_the_title( $post_id ) .'</a></h2>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_event_title');
		}
		// Event Meta
		public static function before_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_the_meta');
		}
		public static function the_meta( $post_id ){
			ob_start();
		?>
			<div class="tribe-events-event-meta">
				<h3 class="updated published time-details">
					<?php
					global $post;
					if ( !empty( $post->distance ) ) { ?>
						<strong>[<?php echo tribe_get_distance_with_unit( $post->distance ); ?>]</strong>
					<?php } ?>
					<?php echo tribe_events_event_schedule_details(), tribe_events_event_recurring_info_tooltip(); ?>
				</h3>
				<?php tribe_display_meta( 'tribe_list_venue_name_address' ); ?>
			</div><!-- .tribe-events-event-meta -->
<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_meta');
		}
		public static function after_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_the_meta');
		}			
		// Event Content
		public static function before_the_content( $post_id ){
			$html = '<div class="entry-content description">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_the_content');
		}
		public static function the_content( $post_id ){
			$html = '';
			if (has_excerpt())
				$html .= '<p>'. get_the_excerpt() .'</p>';
			else
				$html .= '<p>'. TribeEvents::truncate(get_the_content(), 80) .'</p>';	
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_content');
		}
		public static function after_the_content( $post_id ){
			$html = '</div><!-- .entry-content -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_the_content');
		}		
		// Event Details End
		public static function after_the_event_details ( $post_id ){
			$html = '</div><!-- .tribe-events-event-details -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_the_event_details'); 
		}	

		// End List Loop
		public static function inside_after_loop( $post_id ){

			// internal increment to keep track of position within the loop
			self::$loop_increment++;

			$html = '</div><!-- .hentry .vevent -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_inside_after_loop');
		}
		public static function after_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_loop');
		}
		// Event Notices
		public static function notices( $notices = array(), $post_id ) {
			$html = '';
			if(!empty($notices))	
				$html .= '<div class="event-notices">' . implode('<br />', $notices) . '</div><!-- .event-notices -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_notices');
		}
		// List Pagination
		public static function before_pagination( $post_id ){
			$html = '</div><!-- .tribe-events-loop -->';
			$html .= '<div class="tribe-events-loop-nav">';
			$html .= '<h3 class="tribe-visuallyhidden">'. __( 'Events loops navigation', 'tribe-events-calendar' ) .'</h3>';
			$html .= '<ul>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_pagination');
		}
		public static function pagination( $post_id ){
			// Display Previous Page Navigation
			$html = '<li class="tribe-nav-previous">';
			if(tribe_is_upcoming() && get_previous_posts_link())
				$html .= get_previous_posts_link( __( '&laquo; Previous Events', 'tribe-events-calendar' ) );
			elseif(tribe_is_upcoming() && !get_previous_posts_link())
				$html .= '<a href="'. tribe_get_past_link() .'" rel="prev">'. __( '&laquo; Previous Events', 'tribe-events-calendar' ) .'</a>';
			elseif(tribe_is_past() && get_next_posts_link()) 
				$html .= get_next_posts_link( __( '&laquo; Previous Events', 'tribe-events-calendar' ) );
			$html .= '</li><!-- .tribe-nav-previous -->';
			// Display Next Page Navigation
			$html .= '<li class="tribe-nav-next">';
			if(tribe_is_upcoming() && get_next_posts_link())
				$html .= get_next_posts_link( __( 'Next Events &raquo;', 'tribe-events-calendar' ) );
			elseif(tribe_is_past() && get_previous_posts_link())
				$html .= get_previous_posts_link( __( 'Next Events &raquo;', 'tribe-events-calendar' ) );
			elseif(tribe_is_past() && !get_previous_posts_link()) 
				$html .= '<a href="'. tribe_get_upcoming_link() .'" rel="next">'. __( 'Next Events &raquo;', 'tribe-events-calendar' ) .'</a>';
			$html .= '</li><!-- .tribe-nav-next -->';	
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_pagination');
		}
		public static function after_pagination( $post_id ){
			$html = '</ul></div><!-- .tribe-events-loop-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_pagination');
		}
		// End List Template
		public static function after_template( $hasPosts = false, $post_id ){
			$html = '';
			if (!empty($hasPosts) && function_exists('tribe_get_ical_link')) // iCal Import
				$html .= '<a class="tribe-events-ical tribe-events-button-grey" title="'. __( 'iCal Import', 'tribe-events-calendar' ) .'" href="'. tribe_get_ical_link() .'">'. __( 'iCal Import', 'tribe-events-calendar' ) .'</a>';
				
			$html .= '</div><!-- #tribe-events-content -->';
			$html .= '<div class="tribe-clear"></div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_template');		
		}
	}
	Tribe_Events_List_Template::init();
}
