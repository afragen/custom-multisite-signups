<?php

/*
Plugin Name: Custom Multisite Signups
Plugin URI: https://github.com/afragen/custom-multisite-signups
GitHub Plugin URI: https://github.com/afragen/custom-multisite-signups
Description: This plugin adds custom registration data and maybe other things to WP Mulitsite.
Requires at least: 3.4
Tested up to: 3.6
Version: 0.1
Author: Andy Fragen
Author URI: http://stsps.org
License: GNU General Public License v2
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

//Load plugin
new Custom_Multisite_Signups();


class Custom_Multisite_Signups {

	public function __construct() {
		//Add custom fields to registration page
		add_action( 'signup_extra_fields', array( $this, '_signup_extra_fields' ) );
		add_filter( 'wpmu_validate_user_signup', array( $this, '_wpmu_validate_user_signup' ) );
		add_filter( 'add_signup_meta', array( $this, '_add_signup_meta' ) );
		add_action( 'wpmu_activate_user', array( $this, 'insert_meta_on_activation' ), 10, 3 );
		add_action( 'show_user_profile', array( $this, 'show_extra_profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'show_extra_profile_fields' ) );
		add_action( 'personal_options_update', array( $this, 'save_extra_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_extra_profile_fields' ) );

	}

	/**
	* Capitalize strings
	*
	* @param string
	* @return string
	*/
	private function ucname( $string ) {
		$string =ucwords( strtolower( $string ) );
		foreach( array( '-', '\'' ) as $delimiter) {
			if( strpos( $string, $delimiter ) !== false )
				$string = implode( $delimiter, array_map( 'ucfirst', explode( $delimiter, $string ) ) );
		}
		return $string;
	}
	
	/**
	 * Add extra registration fields
	 *
	 * @param array
	 * @return string
	 * @filter returns string
	 */
	public function _signup_extra_fields( $errors ) {
	
		echo  "\n", '<style>', "\n";
		$selectors = '.mu_register #first_name, .mu_register #last_name';
		if( has_filter( 'cms_extra_fields_css_selectors' ) ) {
			echo apply_filters( 'cms_extra_fields_css_selectors', $selectors );
		} else {
			echo $selectors;
		}
		
		echo ' { ';
		$css = 'font-size: 24px; margin: 5px 0; width: 100%;';
		if( has_filter( 'cms_extra_fields_css' ) ) {
			echo apply_filters( 'cms_extra_fields_css', $css );
		} else {
			echo $css;
		}
		echo ' }', "\n", '</style>', "\n";
		
		$first_name = isset( $_REQUEST['first_name'] ) ? (string) $_REQUEST['first_name'] : '';
		$html[] = '<label>First Name</label>';
		$html[] = '<input id="first_name" name="first_name" type="text" value="' . $first_name . '" />';

		$last_name = isset( $_REQUEST['last_name'] ) ? (string) $_REQUEST['last_name'] : '';
		$html[] = '<label>Last Name</label>';
		$html[] = '<input id="last_name" name="last_name" type="text" value="' . $last_name . '" />';
		$html[] = '';
		$html = implode( "\n", $html );

		if( has_filter( 'cms_add_extra_signup_fields' ) ) {
			echo apply_filters( 'cms_add_extra_signup_fields', $html );			
		} else {
			echo $html;
		}

	}

	/**
	* Validate user signup data
	*
	* @param array
	* @return array
	* @filter hook returns array of errors
	*/
	public function _wpmu_validate_user_signup( $result ) {
		
		if( empty( $_POST['first_name'] ) ) {
			$result['errors']->add( 'first_name', __( 'You must include a first name.' ) );
			echo '<p class="error">', $result['errors']->get_error_message('first_name'), '</p>';
		}
		
		if( empty( $_POST['last_name'] ) ) {
			$result['errors']->add( 'last_name', __( 'You must include a last name.' ) );
			echo '<p class="error">', $result['errors']->get_error_message('last_name'), '</p>';
		}
		
		if( preg_match( '/[^-a-zA-Z]/', $_POST['first_name'] ) or preg_match( '/[^-a-zA-Z]/', $_POST['last_name'] ) ) {
			$result['errors']->add( 'name', __( 'Names may only contain letters or hyphens.' ) );
			echo '<p class="error">', $result['errors']->get_error_message('name'), '</p>';
		}

		if( has_filter( 'cms_wpmu_validate_user_signup' ) ) {
			return apply_filters( 'cms_wpmu_validate_user_signup', $result );
		} else {
			return $result;
		}
	}
	
	/**
	 * Add values to wp_signups table
	 *
	 * @param array
	 * @return array
	 * @filter hook returns array of key, value pairs
	 */
	public function _add_signup_meta( $meta = array() ) {

		$fname = sanitize_text_field( $this->ucname( $_POST['first_name'] ) );
		$lname = sanitize_text_field( $this->ucname( $_POST['last_name'] ) );
		
		// create an array of custom meta fields
		$meta['custom_usermeta'] = array(
									'first_name'   => $fname,
									'last_name'    => $lname,
									'display_name' => $fname . ' ' . $lname,
									);
		if( has_filter( 'cms_extra_signup_meta') )
			$meta = $meta['custom_usermeta'] + apply_filters( 'cms_extra_signup_meta', $meta );

		return $meta;

	}

	/**
	* Upon activation loops through wp_signups data to add to user_meta
	*
	* @param ($meta) array of key,value pairs.
	*/
	public function insert_meta_on_activation( $user_id, $email, $meta ) {
	
		// loop through array of custom meta fields
		foreach ( $meta as $key => $value ) {
			// and set each one as a meta field
			update_user_meta( $user_id, $key, $value );
		}
		wp_update_user( array(
						'ID'           => $user_id,
						'display_name' => $meta['display_name'],
						)
					);

	}
	
	/**
	 * Add additional custom field to profile page
	 *
	 * @param - $user
	 * @filter hook returns html
	 */
	public function show_extra_profile_fields ( $user ) {

		echo "\n", '<h3>', _e( 'Extra Profile Info'), '</h3>', "\n", '<table class="form-table"><tr>', "\n";
		
		echo apply_filters( 'cms_show_extra_profile_fields', $user );
		
		echo '</td>', "\n", '</tr>', '</table>', "\n";
		
	}

	/**
	 * Update extra profile fields in admin page.
	 *
	 * @param - $user_id
	 * @filter hook returns array of field names.
	 */
	public function save_extra_profile_fields( $user_id ) {
	
		//if ( ! current_user_can( 'add_users' ) ) { return false; }
		$extra_fields = array();
		$extra_fields = apply_filters( 'cms_save_extra_profile_fields', $extra_fields );
		foreach ( $extra_fields as $extra_field ) {
			update_user_meta( $user_id, $extra_field, $_POST[$extra_field] );
		}
		
	}


} //end class
