<?php 
/**
 * Calendar Single Event
 * This file contains one event in the calendar grid
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/calendar/single-event.php
 * *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
?>

<?php 

global $post;
$day = tribe_events_get_current_calendar_day();
$event_id = "{$post->ID}-{$day['daynum']}";
$start = tribe_get_start_date( $post, FALSE, 'U' );
$end = tribe_get_end_date( $post, FALSE, 'U' );

?>

<div id="tribe-events-event-<?php echo $event_id ?>" class="<?php tribe_events_the_calendar_single_event_classes() ?>">
	<h3 class="entry-title summary"><a href="<?php tribe_event_link( $post ); ?>"><?php the_title() ?></a></h3>
	<div id="tribe-events-tooltip-<?php echo $event_id; ?>" class="tribe-events-tooltip">
		<h4 class="entry-title summary"><a href="<?php tribe_event_link( $post ); ?>"><?php the_title() ?></a></h4>
		<div class="tribe-events-event-body">
			<div class="duration">
				<abbr class="tribe-events-abbr updated published distort" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), $start ); ?>">
					<?php // move to template tag: tribe_events_the_start_time() ?>
					<?php if ( !empty( $start ) ) : ?>
						<?php echo date_i18n( get_option( 'date_format', 'F j, Y' ), $start ); ?>
					<?php endif; ?>
					<?php if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', TRUE ) ) : ?>
						<?php echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), $start ); ?>
					<?php endif; ?>
				</abbr><!-- .dtstart -->
				<abbr class="tribe-events-abbr tend" title="<?php echo date_i18n( get_option( 'date_format', 'Y-m-d' ), $end ); ?>">
					<?php // move to template tag: tribe_events_the_end_time() ?>
					<?php if ( !empty( $end )  && $start !== $end ) : ?>
						-
						<?php if ( date_i18n( 'Y-m-d', $start ) == date_i18n( 'Y-m-d', $end ) ) : ?>
							<?php $time_format = get_option( 'time_format', 'g:i a' ); ?>
							<?php if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', TRUE ) ) : ?>
								<?php echo date_i18n( $time_format, $end ); ?>
							<?php endif; ?>
						<?php else : ?>
							<?php echo date_i18n( get_option( 'date_format', 'F j, Y' ), $end ); ?>
							<?php if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', TRUE ) ) : ?>
								<?php echo date_i18n( get_option( 'time_format', 'g:i a' ), $end ) . '<br />'; ?>
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
				</abbr><!-- .dtend -->
			</div><!-- .duration -->

			<?php if (has_post_thumbnail() && '' != get_the_post_thumbnail( $post->ID ) ) : ?>
				<div class="tribe-events-event-thumb"><?php echo the_post_thumbnail(array(90,90));?></div>
			<?php endif; ?>

			<p class="entry-summary description">
				<?php echo get_the_excerpt() ?>
			</p><!-- .entry-summary -->
			<p><a href="<?php echo tribe_get_event_link(); ?>" class="tribe-events-read-more"><?php  echo __('Find out more', 'tribe-events-calendar'); ?> &raquo;</a></p>
		</div><!-- .tribe-events-event-body -->
		<span class="tribe-events-arrow"></span>
	</div><!-- .tribe-events-tooltip -->
</div><!-- #tribe-events-event-# -->
