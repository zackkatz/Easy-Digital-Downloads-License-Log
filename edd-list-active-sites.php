<?php
/*
Plugin Name: Easy Digital Downloads - License Log Dashboard Widget
Plugin URL: http://gravityview.co
Description: List active sites
Version: 1.1
Author: Katz Web Services, Inc.
Author URI: http://katz.co
Contributors: katzwebservices
*/


/**
 * Add a widget to the dashboard.
 *
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */
function edd_sl_license_add_dashboard_widget() {

	if( !current_user_can( 'manage_options' ) || !function_exists( 'edd_software_licensing' ) ) {
		return;
	}

	wp_add_dashboard_widget(
	     'edd_sl_license_logs',         // Widget slug.
	     __('EDD License Logs', 'edd-license-logs'),         // Title.
	     'edd_sl_license_logs_dashboard_widget' // Display function.
    );

}

add_action( 'wp_dashboard_setup', 'edd_sl_license_add_dashboard_widget' );

/**
 * Create the function to output the contents of our Dashboard Widget.
 */
function edd_sl_license_logs_dashboard_widget() {

	$logs = edd_software_licensing()->get_license_logs('');

	if( $logs ) {

		$html = '
		<table class="post-table widefat" style="border:none;">
			<thead>
				<tr>
					<th scope="col">Site</th>
					<th>WP Version</th>
				</tr>
			</thead>
			<tbody>';

		foreach ( $logs as $log ) {

			// Get the title of the action
			preg_match('/log-license-(.+?)-[0-9]+/ism', $log->post_name, $matches);
			$action = $matches[1];

			$data = json_decode( get_post_field( 'post_content', $log->ID ) );

			list($wp_info, $site_url) = explode(';', $data->HTTP_USER_AGENT);
			list($wordpress, $version ) = explode( '/', $wp_info );

			$html .= '
			<tr>
				<td>
					<a href="'.esc_attr( $site_url ) .'" target="_blank">'.esc_html( $site_url ) . '</a>
					<div class="description">'.esc_html( ucfirst($action) ). ' on '.esc_html( date_i18n( get_option( 'date_format' ), $data->REQUEST_TIME ) . ' ' . date_i18n( get_option( 'time_format' ), $data->REQUEST_TIME ) ).'
					</div>
				</td>
				<td>'.esc_html( $version ) . '</td>
			</tr>';
		}

		$html .= '
			</tbody>
		</table>';
	} else {
		$html .= '<tr><td><p>' . __( 'There are no log entries', 'edd-license-logs' ).'</td></tr>';
	}

	echo $html;
}

