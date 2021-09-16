<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class ThemeMXBasicTemplate {

	public $post_type = 'page';

	public $post_id = 0;

	public $is_single = false;

	public $expand_javascript_vars = [];

	public function __construct( $args = [] )
	{

		$this->is_single = is_single();

		// Set post type
		$this->set_post_type( $args['post_type'] ?? NULL );

		// Set post id
		$this->set_post_id( $args['post_id'] ?? NULL );

	}

	/*
	* Set post type
	*/
	public function set_post_type( $post_type = NULL )
	{

		if( get_post_type() AND $post_type !== NULL ) {

			$this->post_type = $post_type;

		} else {

			$this->post_type = get_post_type();

		}

	}

	/*
	* Set post id
	*/
	public function set_post_id( $post_id = NULL )
	{

		if( get_the_ID() AND $post_id !== NULL ) {

			$this->post_id = $post_id;

		} else {

			$this->post_id = get_the_ID();

		}

	}

	/*
	* Set Global JS variable
	*/
	public function mx_global_javascript_vars()
	{
		$script = '<script>';

			$script .= 'window.theme_mx_data = {';

				$script .= '"post_type":"' 	. $this->post_type . '",';

				$script .= '"is_single":"' 	. $this->is_single . '",';				

				$script .= '"post_id":"' 	. $this->post_id . '",';

				$script .= '"ajax_url":"' 	. admin_url( "admin-ajax.php" ) . '",';

				$script .= '"nonce":"' 	. wp_create_nonce( 'theme_mx_get_content_nonce' ) . '"';			

			$script .= '};';

		$script .= '</script>';

		return $script;
	}

	/*
	* Expand Global JS variable
	*/
	public function mx_expand_javascript_vars()
	{
		$script = '<script> // Expand Global JS variable. </script>';

		array_push( $this->expand_javascript_vars, $script );

	}
		public function mx_each_expande_js_var()
		{

			foreach ( $this->expand_javascript_vars as $key => $value ) {
				
				echo $value;

			}

		}


	/*
	* Render template
	*/
	public function render()
	{
		// Set Global JS variable
		echo $this->mx_global_javascript_vars();

		// Expand Global JS variable
		// $this->mx_expand_javascript_vars();
		$this->mx_each_expande_js_var();

		// Display app container
		echo '<div id="app"></div>';

	}

}

/*
* Set AJAX actions
*/
if ( ! function_exists( 'get_post_content_func' ) ) :

	add_action( 'wp_ajax_mx_get_post_content', 'get_post_content_func' );

	add_action( 'wp_ajax_nopriv_mx_get_post_content', 'get_post_content_func' );

	function get_post_content_func() {

		// Check out if POST nonce is not empty
		if( empty( $_POST['nonce'] ) ) wp_die( '0' );

		// Check out if nonce's matched
		if( wp_verify_nonce( $_POST['nonce'], 'theme_mx_get_content_nonce' ) ) {

			$post_type = 'post';

			if( isset( $_POST['post_type'] ) ) {

				$post_type = sanitize_text_field( $_POST['post_type'] );

			}

			$post_id = 0;

			if( isset( $_POST['post_id'] ) ) {

				$post_id = sanitize_text_field( $_POST['post_id'] );

			}

			global $wpdb;

			$posts_table = $wpdb->prefix . 'posts';

			$sql_str = "SELECT ID, post_title, post_date, post_title, post_content, post_excerpt
				FROM $posts_table
				WHERE post_type = '$post_type'
					AND post_status = 'publish'
					AND ID = $post_id";

			$result = $wpdb->get_row( $sql_str, ARRAY_A );			

			if( $result ) {

				// post thumbnails ...
				$result['thumbnails'] = [];

				$the_thumbnail_full = get_the_post_thumbnail_url( $post_id );

					if( $the_thumbnail_full ) {

						// full
						$result['thumbnails']['full'] = $the_thumbnail_full;
						
						// thumbnail
						$the_thumbnail_thumbnail = get_the_post_thumbnail_url( $post_id, 'thumbnail' );

						$result['thumbnails']['thumbnail'] = $the_thumbnail_thumbnail;

						// medium
						$the_thumbnail_medium = get_the_post_thumbnail_url( $post_id, 'medium' );

						$result['thumbnails']['medium'] = $the_thumbnail_medium;

						// large
						$the_thumbnail_large = get_the_post_thumbnail_url( $post_id, 'large' );

						$result['thumbnails']['large'] = $the_thumbnail_large;

					}

				// ... post thumbnails

				// the permalink ...
				$url = get_the_permalink( $post_id );

				if( $url ) {

					$result['permalink'] = $url;

				}
				// ... the permalink

				// categories ...
				$categories = get_the_category( $post_id );

				if( $categories ) {

					$result['categories'] = $categories;

				}
				// ... categories

				// tags ...
				$tags = get_the_tags( $post_id );

				if( $tags ) {

					$result['tags'] = $tags;

				}
				// ... tags

				// excerpt ...
				$result['post_excerpt'] = get_the_excerpt( $post_id );
				// ... excerpt

				// time ...
				$result['get_the_time'] = get_the_time( 'U', $post_id );

				$result['get_the_modified_time'] = esc_attr( get_the_modified_time( 'U', $post_id ) );
					
				// %1$s
				$result['post_date_date_w3c'] = esc_attr( get_the_date( DATE_W3C, $post_id ) );

				// %2$s
				$result['get_the_date'] = esc_html( get_the_date( '', $post_id ) );

				// %3$s
				$result['get_the_modified_date_date_w3c'] = esc_attr( get_the_modified_date( DATE_W3C, $post_id ) );

				// %4$s
				$result['get_the_modified_date'] = esc_html( get_the_modified_date( '', $post_id ) );	
				// ... time

				echo json_encode( $result );

			} else {

				echo 'error';

			}

		}

		wp_die();

	}

endif;