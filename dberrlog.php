<?php /*
Plugin Name: Dashboard ErrorLog
Description: This Plugin add a display of your PHP error log on the dashboard.
Plugin URI: http://federalproductions.com
Version: 1.1
Author: Ted Thompson and (links to) Steve Taylor
Author URI: http://sltaylor.co.uk/
*/

	function slt_PHPErrorsWidget() {

/****************************************************************************/
/* Set Values for your server below                                         */
/****************************************************************************/
	
		$local_folder = ''; //may be needed if site is in a subfolder
		
/****************************************************************************/		
/* DO NOT EDIT BELOW THIS LINE (unless you know what you're doing of course)*/
/****************************************************************************/
		
		$filename = '/' . ini_get('error_log'); // If log does not appear, enter the filename for your error log file here.
		$root = $_SERVER['DOCUMENT_ROOT'] . $local_folder; // Build local document root and add website subfolder.
		$parsed_url = parse_url(site_url());
		$path = $parsed_url['path'];
		$logfile = $root . $path . $filename; // Build full path and filename
		$displayErrorsLimit = 100; // The maximum number of errors to display in the widget
		$errorLengthLimit = 300; // The maximum number of characters to display for each error
		$fileCleared = false;
		$userCanClearLog = current_user_can( 'manage_options' );
		// Clear file?
		if ( $userCanClearLog && isset( $_GET["slt-php-errors"] ) && $_GET["slt-php-errors"]=="clear" ) {
			$handle = fopen( $logfile, "w" );
			fclose( $handle );
			$fileCleared = true;
		}
		// Read file
		if ( file_exists( $logfile ) ) {
			$errors = file( $logfile );
			$errors = array_reverse( $errors );
			if ( $fileCleared ) echo '<p><em>File cleared.</em></p>';
			if ( $errors ) {
				echo '<p>'.count( $errors ).' error';
				if ( $errors != 1 ) echo 's';
				echo '.';
				if ( $userCanClearLog ) echo ' [ <b><a href="' . admin_url() . '?slt-php-errors=clear" onclick="return confirm(\'Are you sure?\');">CLEAR LOG FILE</a></b> ]';
				echo '</p>';
				echo '<div id="slt-php-errors" style="height:250px;overflow:scroll;padding:2px;background-color:#faf9f7;border:1px solid #ccc;">';
				echo '<ol style="padding:0;margin:0;">';
				$i = 0;
				foreach ( $errors as $error ) {
					echo '<li style="padding:2px 4px 6px;border-bottom:1px solid #ececec;">';
					$errorOutput = preg_replace( '/\[([^\]]+)\]/', '<b>[$1]</b>', $error, 1 );
					if ( strlen( $errorOutput ) > $errorLengthLimit ) {
						echo substr( $errorOutput, 0, $errorLengthLimit ).' [...]';
					} else {
						echo $errorOutput;
					}
					echo '</li>';
					$i++;
					if ( $i > $displayErrorsLimit ) {
						echo '<li style="padding:2px;border-bottom:2px solid #ccc;"><em>More than '.$displayErrorsLimit.' errors in log...</em></li>';
						break;
					}
				}
				echo '</ol></div>';
			} else {
				echo '<p>No errors currently logged.</p>';
			}
		} else {
			echo '<p><em>There was a problem reading the error log file.</em></p>'; 
		}
		echo '<p>Filepath:<strong>' . $logfile . '</strong></p>';
	}

	// Add widgets
	function slt_dashboardWidgets() {
		if ( current_user_can( 'manage_options' ) ){
			wp_add_dashboard_widget( 'slt-php-errors', 'PHP errors', 'slt_PHPErrorsWidget' );
		}
	}
	add_action( 'wp_dashboard_setup', 'slt_dashboardWidgets' );
?>
