<?php

	if ( !class_exists( 'TribeEventsTickets' ) ) {
		abstract class TribeEventsTickets {

			// All TribeEventsTickets api consumers. It's static, so it's shared across childs.
			protected static $active_modules = array();

			public $className;
			private $parentPath;
			private $parentUrl;

			// prevent re-doing the metabox by different childs
			private static $done_metabox = false;

			/* API Definition */
			/* Child classes must implement all this functions */

			public $pluginName;
			protected $pluginPath;
			protected $pluginUrl;

			abstract function get_event_reports_link( $event_id );
			abstract function get_ticket_reports_link( $event_id, $ticket_id );


			abstract function save_ticket( $event_id, $ticket, $raw_data = array() );

			abstract function get_tickets( $tickets = array(), $event_id );

			abstract function get_ticket( $event_id, $ticket_id );

			abstract function delete_ticket( $event_id, $ticket_id );

			abstract function do_metabox_advanced_options( $event_id, $ticket_id );

			abstract static function get_instance();


			function __construct() {

				// As this is an abstract class, we want to know which child
				// instantiated it
				$this->className = get_class( $this );

				$this->parentPath = trailingslashit( dirname( dirname( __FILE__ ) ) );
				$this->parentUrl  = trailingslashit( plugins_url( '', $this->parentPath ) );

				// Register all TribeEventsTickets api consumers
				self::$active_modules[$this->className] = $this->pluginName;

				add_action( 'tribe_events_event_save', array( $this,
				                                              'save_tickets' ), 10, 1 );

				add_filter( 'tribe_events_tickets_modules', array( $this,
				                                                   'modules' ) );
				add_filter( 'tribe_events_tickets_get_tickets', array( $this,
				                                                       'get_tickets' ), 10, 2 );
				add_action( 'tribe_events_tickets_metabox_advanced', array( $this,
				                                                            'do_metabox_advanced_options' ), 10, 2 );

				/* Admin AJAX actions */

				add_action( 'wp_ajax_tribe-ticket-add-' . $this->className, array( $this,
				                                                                   'ajax_handler_ticket_add' ) );
				add_action( 'wp_ajax_tribe-ticket-delete-' . $this->className, array( $this,
				                                                                      'ajax_handler_ticket_delete' ) );
				add_action( 'wp_ajax_tribe-ticket-edit-' . $this->className, array( $this,
				                                                                    'ajax_handler_ticket_edit' ) );


			}

			public final function do_meta_box( $post_id ) {

				if ( !self::$done_metabox ) {

					$tickets = $this->get_event_tickets( $post_id );

					include $this->parentPath . 'admin-views/tickets-meta-box.php';

					self::$done_metabox = true;
				}

			}

			/* AJAX Handlers */

			public final function ajax_handler_ticket_add() {

				if ( !isset( $_POST["formdata"] ) ) $this->ajax_error( 'Bad post' );
				if ( !isset( $_POST["post_ID"] ) ) $this->ajax_error( 'Bad post' );

				$data    = wp_parse_args( $_POST["formdata"] );
				$post_id = $_POST["post_ID"];

				if ( !isset( $data["ticket_provider"] ) || !$this->module_is_valid( $data["ticket_provider"] ) ) $this->ajax_error( 'Bad module' );

				$ticket = new TribeEventsTicketObject();

				$ticket->ID             = isset( $data["ticket_id"] ) ? $data["ticket_id"] : NULL;
				$ticket->name           = isset( $data["ticket_name"] ) ? $data["ticket_name"] : NULL;
				$ticket->description    = isset( $data["ticket_description"] ) ? $data["ticket_description"] : NULL;
				$ticket->price          = isset( $data["ticket_price"] ) ? $data["ticket_price"] : NULL;
				$ticket->provider_class = $this->className;

				/* Pass the control to the child object */
				$return = $this->save_ticket( $post_id, $ticket, $data );

				//If saved OK, let's create a tickets list markup to return
				if ( $return ) {

					$tickets = $this->get_event_tickets( $post_id );
					$return  = $this->get_ticket_list_markup( $tickets );

				}


				$this->ajax_ok( $return );
			}

			public final function ajax_handler_ticket_delete() {

				if ( !isset( $_POST["post_ID"] ) ) $this->ajax_error( 'Bad post' );
				if ( !isset( $_POST["ticket_id"] ) ) $this->ajax_error( 'Bad post' );

				$post_id   = $_POST["post_ID"];
				$ticket_id = $_POST["ticket_id"];

				/* Pass the control to the child object */
				$return = $this->delete_ticket( $post_id, $ticket_id );

				//If deleted OK, let's create a tickets list markup to return
				if ( $return ) {

					$tickets = $this->get_event_tickets( $post_id );
					$return  = $this->get_ticket_list_markup( $tickets );

				}


				$this->ajax_ok( $return );
			}

			public final function ajax_handler_ticket_edit() {

				if ( !isset( $_POST["post_ID"] ) ) $this->ajax_error( 'Bad post' );
				if ( !isset( $_POST["ticket_id"] ) ) $this->ajax_error( 'Bad post' );

				$post_id   = $_POST["post_ID"];
				$ticket_id = $_POST["ticket_id"];

				$return = get_object_vars( $this->get_ticket( $post_id, $ticket_id ) );

				ob_start();
				$this->do_metabox_advanced_options( $post_id, $ticket_id );
				$extra = ob_get_contents();
				ob_end_clean();

				$return["advanced_fields"] = $extra;

				$this->ajax_ok( $return );
			}

			protected final function ajax_error( $message = "" ) {
				header( 'Content-type: application/json' );
				echo json_encode( array( "success" => false,
				                         "message" => $message ) );
				exit;
			}

			protected final function ajax_ok( $data ) {
				$return = array();
				if ( is_object( $data ) ) {
					$return = get_object_vars( $data );
				} elseif ( is_array( $data ) || is_string( $data ) ) {
					$return = $data;
				} elseif ( is_bool( $data ) && !$data ) {
					$this->ajax_error( "Something went wrong" );
				}

				header( 'Content-type: application/json' );
				echo json_encode( array( "success" => true,
				                         "data"    => $return ) );
				exit;
			}

			/* \AJAX Handlers */

			/*  Helpers */

			private function module_is_valid( $module ) {
				return array_key_exists( $module, self::$active_modules );
			}

			private function ticket_list_markup( $tickets = array() ) {
				if ( !empty( $tickets ) ):
					include $this->parentPath . 'admin-views/tickets-list.php';
				endif;
			}

			private function get_ticket_list_markup( $tickets = array() ) {

				ob_start();
				$this->ticket_list_markup( $tickets );
				$return = ob_get_contents();
				ob_end_clean();

				return $return;
			}

			protected function tr_class() {
				echo "ticket_advanced ticket_advanced_" . $this->className;
			}

			public function modules() {
				return self::$active_modules;
			}

			public function get_event_tickets( $event_id ) {

				return apply_filters( 'tribe_events_tickets_get_tickets', array(), $event_id );
			}

			/* \Helpers */

		}

	}