<?php

/*
Plugin Name:       Custom Multisite Signups Extras
Requires at least: 3.4
Tested up to:      3.6
Version:           0.1.0
Author:            Andy Fragen
Author URI:        http://thefragens.com
License:           GNU General Public License v2
License URI:       http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Description: This plugin adds custom registration data using hooks to the <a href="https://github.com/afragen/custom-multisite-signups">Custom Multisite Signups</a> plugin.
*/

class Custom_Mulitsite_Signups_Extras {

	public function __construct() {
		add_filter( 'cms_add_extra_signup_fields', array( $this, 'my_extra_fields' ) );
		add_filter( 'cms_extra_fields_css_selectors', array( $this, 'my_extra_fields_selectors' ) );
		add_filter( 'cms_extra_fields_css', array( $this, 'my_extra_fields_css' ) );
		add_filter( 'cms_wpmu_validate_user_signup', array( $this, 'my_validate_user_signup' ) );
		add_action( 'cms_extra_signup_meta', array( $this, 'my_cms_extra_signup_meta' ) );
		add_filter( 'cms_show_extra_profile_fields', array( $this, 'my_show_extra_profile_fields' ) );
		add_action( 'cms_save_extra_profile_fields', array( $this, 'my_save_extra_profile_fields' ) );
	}

	public function my_extra_fields( $html ) {
		$extra_field1 = isset( $_REQUEST['extra_field1'] ) ? (string) $_REQUEST['extra_field1'] : '';
		$extra_fields[] = '<label>Extra Field</label>';
		$extra_fields[] = '<input id="extra_field1" name="extra_field1" type="text" value="' . $extra_field1 . '" />';

		$html .=  implode( "\n", $extra_fields );
		echo $html;
}

	public function my_extra_fields_selectors( $selectors ) {
		$selectors .= ', .mu_register #extra_field1';
		return $selectors;
	}

	public function my_extra_fields_css( $css ) {
		$css .= ' /* CSS comment */ ';
		return $css;
	}

	public function my_validate_user_signup( $result ) {
		if ( empty( $_POST['extra_field1'] ) ) {
			$result['errors']->add( 'last_name', __( 'You must include an extra field.' ) );
			echo '<p class="error">', $result['errors']->get_error_message('extra_field1'), '</p>';
		}

		return $result;
	}

	public function my_cms_extra_signup_meta() {
		return array(
				'extra_field1' => sanitize_text_field( $_POST['extra_field1'] ),
				);
	}

	public function my_show_extra_profile_fields( $user ) {

		$html[] = '<th><label for="extra_field1" id="extra_field1">' . __( 'Extra Field 1' ) . '</label></th>';
		$html[] = '<td>';
		$html[] = '<input type="text" name="extra_field1" id="extra_field1" value="';
		$html[] = esc_attr( get_the_author_meta( 'extra_field1', $user->ID ) );
		$html[] = '" class="regular-text" /><br />';
		$html[] = '<span class="description">Please enter your Extra Field 1 data.</span>';
		$html[] = '';
		$html = implode( " ", $html );

		return $html;

	}

	public function my_save_extra_profile_fields() {
		return array( 'extra_field1' );
	}

}