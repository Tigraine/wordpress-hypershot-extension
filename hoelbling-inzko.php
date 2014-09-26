<?php
/**
 * Plugin Name: Hoelbling-Inzko Modifications
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Extends some stuff for hypershot
 * Version: 1.0
 * Author: Daniel Hoelbling-Inzko
 * Author URI: http://hoelbling-inzko.com
 * License: Copyright Daniel Hoelbling-Inzko
 */

defined('ABSPATH') or die("No script kiddies please!");


function call_hi_options() {
	new hoelblingInzkoOptions();
}

if (is_admin()) {
    add_action( 'load-post.php', 'call_hi_options' );
	add_action( 'load-post-new.php', 'call_hi_options' );
}

class hiOptionReader {
	public static function getHeader($post) {
		return get_post_meta( $post->ID, '_hi_custom_header', true);
	}
	public static function showHeader($post) {
		return get_post_meta( $post->ID, '_hi_show_header', true);
	}
}

class hoelblingInzkoOptions {
	public function __construct() {
		add_action('add_meta_boxes', array($this, 'add_meta_box'));
		add_action('save_post', array($this, 'save'));
	}

	function add_meta_box($post_type) {
		$post_types = array( 'post', 'page', 'album' );

		if ( in_array( $post_type, $post_types )) {
			add_meta_box(
				'hi_custom_header',
				__( 'Hypershot Custom Header', 'hi_custom_header' ),
				array($this, 'render_meta_box_content'),
				$screen
			);
		}
	}

	function render_meta_box_content($post) {
		wp_nonce_field('hi_meta_box', 'hi_meta_box_nonce');

		$value = get_post_meta( $post->ID, '_hi_custom_header', true);

		echo '<label for="hi_custom_header">';
		echo 'Custom Header';
		echo '</label>';
		echo '<textarea class="large-text metadesc" type="text" id="hi_custom_header" name="hi_custom_header" rows="10">' . esc_attr($value) . '</textarea>';
		echo '<label for="hi_header_state">';
		echo 'Display header on page load';
		echo '</label>';
		echo '<select name="hi_show_header" id="hi_show_header">';
		echo '<option value="close">Hidden</option>';
		echo '<option ' . (hiOptionReader::showHeader($post) == 'close' ? '' : ' selected = "selected" ') . ' value="open">Shown</option>';
		echo '</select>';
	}

	function save($post_id) {
		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['hi_meta_box_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['hi_meta_box_nonce'], 'hi_meta_box' ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}

		/* OK, it's safe for us to save the data now. */
		
		// Make sure that it is set.
		if ( ! isset( $_POST['hi_custom_header'] ) && ! isset ( $_POST['hi_show_header'] ) ) {
			return;
		}

		// Sanitize user input.
		$my_data = $_POST['hi_custom_header'];
		$show_header = $_POST['hi_show_header'];

		// Update the meta field in the database.
		update_post_meta( $post_id, '_hi_custom_header', $my_data );
		update_post_meta( $post_id, '_hi_show_header', $show_header );
	}
}
?>