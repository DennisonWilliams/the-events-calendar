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

		public static function init(){
			// Start list template
			add_filter( 'tribe_events_list_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// List view buttons
			add_filter( 'tribe_events_list_the_view_buttons', array( __CLASS__, 'the_view_buttons' ), 1, 1 );
	
			// Start list loop
			add_filter( 'tribe_events_list_before_loop', array( __CLASS__, 'before_loop' ), 1, 1 );
			add_filter( 'tribe_events_list_inside_before_loop', array( __CLASS__, 'inside_before_loop' ), 1, 1 );
	
			// Event start date
			add_filter( 'tribe_events_list_the_start_date', array( __CLASS__, 'the_start_date' ), 1, 1 );
	
			// Event title
			add_filter( 'tribe_events_list_the_title', array( __CLASS__, 'the_title' ), 1, 1 );

			// Event content
			add_filter( 'tribe_events_list_before_the_content', array( __CLASS__, 'before_the_content' ), 1, 1 );
			add_filter( 'tribe_events_list_the_content', array( __CLASS__, 'the_content' ), 1, 1 );
			add_filter( 'tribe_events_list_after_the_content', array( __CLASS__, 'after_the_content' ), 1, 1 );
	
			// Event meta
			add_filter( 'tribe_events_list_before_the_meta', array( __CLASS__, 'before_the_meta' ), 1, 1 );
			add_filter( 'tribe_events_list_the_meta', array( __CLASS__, 'the_meta' ), 1, 1 );
			add_filter( 'tribe_events_list_after_the_meta', array( __CLASS__, 'after_the_meta' ), 1, 1 );
	
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
		public function before_template( $post_id ){
			$html = '<div id="tribe-events-content" class="tribe-events-list">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_template');
		}
		// List View Buttons
		public function the_view_buttons( $post_id ){
			$html = '';
			if(!tribe_is_day())
				$html .= '<div id="tribe-events-calendar-header" class="clearfix">';
				$html .= '<span class="tribe-events-calendar-buttons">';
				$html .= '<a class="tribe-events-button-on" href="'. tribe_get_listview_link() .'">'. __( 'Event List', 'tribe-events-calendar' ) .'</a>';
				$html .= '<a class="tribe-events-button-off" href="'. tribe_get_gridview_link() .'">'. __( 'Calendar', 'tribe-events-calendar' ) .'</a>';
				$html .= '</span><!-- .tribe-events-calendar-buttons -->';
				$html .= '</div><!-- #tribe-events-calendar-header -->';			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_view_buttons');
		}
		// Start List Loop
		public function before_loop( $post_id ){
			$html = '<div class="tribe-events-loop hfeed">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_loop');
		}
		public function inside_before_loop( $post_id ){
			
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
			
			$html = '<div id="post-'. get_the_ID() .'" class="'. $class_string .'">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_inside_before_loop');
		}
		// Event Start Date
		public function the_start_date( $post_id ){
			$html = '';
			if (tribe_is_new_event_day() && !tribe_is_day())
				$html .= '<h3><abbr class="tribe-events-abbr updated published dtstart" title="'. tribe_get_start_date( null, false, TribeDateUtils::DBDATEFORMAT ) .'">'. tribe_get_start_date() .'</abbr></h3>';
				
			if (tribe_is_day() && $this->first) {
				$this->first = false;
				$html .= '<h3><abbr class="tribe-events-abbr updated published dtstart" title="'. tribe_get_start_date( null, false, TribeDateUtils::DBDATEFORMAT ) .'">'. tribe_event_format_date( strtotime( get_query_var( 'eventDate' ) ), false ) .'</abbr></h3>';
			}
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_start_date');
		}
		// Event Title
		public function the_title( $post_id ){
			$html = '<h2 class="entry-title summary"><a class="url" href="'. tribe_get_event_link() .'" title="'. get_the_title( $post_id ) .'" rel="bookmark">'. get_the_title( $post_id ) .'</a></h2>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_title');
		}
		// Event Content
		public function before_the_content( $post_id ){
			$html = '<div class="entry-content description">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_the_content');
		}
		public function the_content( $post_id ){
			$html = '';
			if (has_excerpt())
				$html .= '<p>'. TribeEvents::truncate($post_id->post_excerpt) .'</p>';
			else
				$html .= '<p>'. TribeEvents::truncate(get_the_content(), 80) .'</p>';	
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_content');
		}
		public function after_the_content( $post_id ){
			$html = '</div><!-- .entry-content -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_the_content');
		}
		// Event Meta
		public function before_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_the_meta');
		}
		public function the_meta( $post_id ){
			ob_start();
?>
			<div class="tribe-events-event-meta">
				<dl>
				<?php if ( tribe_is_multiday() || !tribe_get_all_day() ) : // Get our event dates ?>
					
					<dt><?php _e( 'Start:', 'tribe-events-calendar' ); ?></dt>
					<dd class="updated published dtstart">
						<abbr class="tribe-events-abbr" title="<?php echo tribe_get_start_date( null, false, TribeDateUtils::DBDATEFORMAT ); ?>"><?php echo tribe_get_start_date(); ?></abbr>	
					</dd><!-- .dtstart -->
					
					<dt><?php _e( 'End:', 'tribe-events-calendar' ); ?></dt>
					<dd class="dtend">
						<abbr class="tribe-events-abbr" title="<?php echo tribe_get_end_date( null, false, TribeDateUtils::DBDATEFORMAT ); ?>"><?php echo tribe_get_end_date(); ?></abbr>	
					</dd><!-- .dtend -->

				<?php else: ?>
					
					<dt><?php _e( 'Date:', 'tribe-events-calendar' ); ?></dt>
					<dd class="updated published dtstart">
						<abbr class="tribe-events-abbr" title="<?php echo tribe_get_start_date( null, false, TribeDateUtils::DBDATEFORMAT ); ?>"><?php echo tribe_get_start_date(); ?></abbr>
					</dd><!-- .dtstart -->	
					
				<?php endif; ?>
				
				<?php if ( tribe_get_venue() ) { // Get our venue ?>
					<dt><?php _e( 'Venue:', 'tribe-events-calendar' ); ?></dt>
					<dd class="vcard fn org">
						<?php if( class_exists( 'TribeEventsPro' ) ) :
							tribe_get_venue_link( $post_id, class_exists( 'TribeEventsPro' ) );
						else :
							echo tribe_get_venue( $post_id );
						endif; ?>
					</dd><!-- .fn .org -->
				<?php } ?>
				
				<?php if ( tribe_get_phone() ) { // Get our event phone number ?>
					<dt><?php _e( 'Phone:', 'tribe-events-calendar' ); ?></dt>
					<dd class="vcard tel">
						<?php echo tribe_get_phone(); ?>
					</dd><!-- .tel -->
				<?php } ?>
				
				<?php if ( tribe_address_exists( $post_id ) ) { // Get our event address ?>
					<dt><?php _e( 'Address: ', 'tribe-events-calendar' ); ?><br />
						<?php if( get_post_meta( $post_id, '_EventShowMapLink', true ) == 'true' ) : ?>
							 <a class="tribe-events-gmap" href="<?php echo tribe_get_map_link(); ?>" title="Click to view this event's Google Map" target="_blank"><?php _e( 'Google Map', 'tribe-events-calendar' ); ?></a>
						<?php endif; ?>
					</dt>
					<dd class="location">
						<?php echo tribe_get_full_address( $post_id ); ?>
					</dd><!-- .location -->
				<?php } ?>
				
				<?php if ( tribe_get_cost() ) { // Get our event cost ?>
					<dt><?php _e( 'Cost:', 'tribe-events-calendar' ); ?></dt>
					<dd class="tribe-events-event-cost">
						<?php echo tribe_get_cost(); ?>
					</dd><!-- .tribe-events-event-cost -->
				<?php } ?>
				
				</dl>
			</div><!-- .tribe-events-event-meta -->
<?php
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_meta');
		}
		public function after_the_meta( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_the_meta');
		}
		// End List Loop
		public function inside_after_loop( $post_id ){
			$html = '</div><!-- .hentry .vevent -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_inside_after_loop');
		}
		public function after_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_loop');
		}
		// Event Notices
		public function notices( $notices = array(), $post_id ) {
			$html = '';
			if(!empty($notices))	
				$html .= '<div class="event-notices">' . implode('<br />', $notices) . '</div><!-- .event-notices -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_notices');
		}
		// List Pagination
		public function before_pagination( $post_id ){
			$html = '</div><!-- .tribe-events-loop -->';
			$html .= '<div class="tribe-events-loop-nav">';
			$html .= '<h3 class="tribe-visuallyhidden">'. __( 'Events loops navigation', 'tribe-events-calendar' ) .'</h3>';
			$html .= '<ul>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_pagination');
		}
		public function pagination( $post_id ){
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
		public function after_pagination( $post_id ){
			$html = '</ul></div><!-- .tribe-events-loop-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_pagination');
		}
		// End List Template
		public function after_template( $hasPosts = false, $post_id ){
			$html = '';
			if (!empty($hasPosts) && function_exists('tribe_get_ical_link')) // iCal Import
				$html .= '<a class="tribe-events-ical" title="'. __( 'iCal Import', 'tribe-events-calendar' ) .'" href="'. tribe_get_ical_link() .'">'. __( 'iCal Import', 'tribe-events-calendar' ) .'</a>';
				
			$html .= '</div><!-- #tribe-events-content -->';
			$html .= '<div class="tribe-clear"></div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_template');		
		}
	}
	Tribe_Events_List_Template::init();
}