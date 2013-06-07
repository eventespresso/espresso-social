<?php
/*
  Plugin Name: Event Espresso - Social Media
  Plugin URI: http://www.eventespresso.com
  Description: A social media addon for Event Espresso. Includes includes Facebook and Twitter share buttons.
  Version: 1.1.5.1
  Usage: Add <?php echo espresso_show_social_media($event_id, 'twitter');?> and/or <?php echo espresso_show_social_media($event_id, 'facebook');?> to display  Twitter or Facebook buttons in your event templates.
  Example: <p><?php echo espresso_show_social_media($event_id, 'twitter');?> <?php echo espresso_show_social_media($event_id, 'facebook');?></p>
  Author: Event Espresso
  Author URI: http://www.eventespresso.com
  Copyright 2013  Event Espresso  (email : support@eventespresso.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


//Define the version of the plugin
function espresso_social_version() {
	return '1.1.5.1';
}

//Update notifications
add_action('action_hook_espresso_social_update_api', 'ee_social_load_pue_update');
function ee_social_load_pue_update() {
	global $org_options, $espresso_check_for_updates;
	if ( $espresso_check_for_updates == false )
		return;

	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php')) { //include the file
		require(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php' );
		$api_key = $org_options['site_license_key'];
		$host_server_url = 'http://eventespresso.com';
		$plugin_slug = 'espresso-social';
		$options = array(
			'apikey' => $api_key,
			'lang_domain' => 'event_espresso',
			'checkPeriod' => '24',
			'option_key' => 'site_license_key',
			'options_page_slug' => 'event_espresso'
		);
		$check_for_updates = new PluginUpdateEngineChecker($host_server_url, $plugin_slug, $options); //initiate the class and start the plugin update engine!
	}
}
if (is_admin()) {
	add_action('plugins_loaded', 'espresso_social_load_admin_file');
}

/**
 * load admin file
 * @author Sidney Harrell
 * @since 1.1.4
 * conditionally loads the admin screen based on the current version of Event Espresso
 * this will be obsolete after we drop support for 3.1.x
 */
function espresso_social_load_admin_file() {
	if ( function_exists('espresso_version') && espresso_version() >= '3.2' ){
		require_once('social_admin.php');
	} else {
		require_once('social_admin_classic.php');
	}
}

define("ESPRESSO_SOCIAL_VERSION", espresso_social_version());

//Define the plugin directory and path
define("ESPRESSO_SOCIAL_PLUGINPATH", "/" . plugin_basename(dirname(__FILE__)) . "/");
define("ESPRESSO_SOCIAL_PLUGINFULLPATH", WP_PLUGIN_DIR . ESPRESSO_SOCIAL_PLUGINPATH);
define("ESPRESSO_SOCIAL_PLUGINFULLURL", WP_PLUGIN_URL . ESPRESSO_SOCIAL_PLUGINPATH);

//Globals
global $espresso_facebook;
$espresso_facebook = get_option('espresso_facebook_settings');

global $espresso_twitter;
$espresso_twitter = get_option('espresso_twitter_settings');

global $espresso_google;
$espresso_google = get_option('espresso_google_settings');

global $espresso_org_options;
$espresso_org_options = get_option('events_organization_settings');

global $espresso_events_page;
$espresso_events_page = get_post( $espresso_org_options['event_page_id'] );

/**
 * espresso social install
 * @since 1.1
 * install the plugin
 */
function espresso_social_install() {
	// Install Facebook Options
	$espresso_facebook = array(
			'espresso_facebook_layout' => 'button_count',
			'espresso_facebook_faces' => 'true',
			'espresso_facebook_action' => 'like',
			'espresso_facebook_font' => 'arial',
			'espresso_facebook_colorscheme' => 'light',
			'espresso_facebook_height' => '21',
			'espresso_facebook_width' => '450'
	);
	add_option('espresso_facebook_settings', $espresso_facebook);

	// Install Twitter Options
	$espresso_twitter = array(
			'espresso_twitter_text' => get_bloginfo('name'),
			'espresso_twitter_username' => 'EventEspresso',
			'espresso_twitter_count_box' => 'none',
			'espresso_twitter_lang' => 'en'
	);
	add_option('espresso_twitter_settings', $espresso_twitter);

	// Install  google+1 options
	$espresso_google = array(
			'espresso_google_button_size' => 'small',
			'espresso_google_url' => '',
			'espresso_google_annotation' => 'bubble'
	);
	update_option('espresso_google_settings', $espresso_google);
}

register_activation_hook(__FILE__, 'espresso_social_install');


/* * **********************
 * 	Facebook Button 	*
 * ********************** */

/**
 * espresso social insert to head
 * @author Dean Robinson
 * @author Chris Reynolds
 * @since 1.1.5
 * this will add FB scripts needed and a style for the comment box that pops up.
 * opengraph support added by Chris
 */
function espresso_social_insert_to_head() {
	global $espresso_events_page, $event_meta;
	ob_start();

	// only do opengraph stuff if espresso_get_event exists
	if ( function_exists('espresso_get_event') ) {
		$event_id = str_replace('ee=', '', $_SERVER['QUERY_STRING']); // a hack to get the event id from the query string
		if ( empty( $event_id ) ) return; //get out this isn't an ee page.
		$event = espresso_get_event( $event_id );

		//one more sanity check
		if ( empty( $event ) ) return; //get out we don't have an event.

		if ( array_key_exists( 'event_thumbnail_url', $event_meta ) ) {
			$event_thumbnail_url = $event_meta['event_thumbnail_url'];
		} else {
			$event_thumbnail_url = null;
		}
		?>
		<!-- facebook open graph -->
		<!-- added by espresso-social -->
		<meta property="og:title" content="<?php echo $event->event_name; ?>"/>
		<meta property="og:description" content="<?php echo wp_strip_all_tags( $event->event_desc, $remove_breaks = true ); ?>"/>
		<meta property="og:url" content="<?php the_permalink( $espresso_events_page->ID ); echo '?ee=' . $event_id; ?>"/>
		<?php if ( $event_thumbnail_url ) { ?>
			<meta property="og:image" content="<?php echo $event_thumbnail_url; ?>"/>
		<?php } ?>
		<meta property="og:type" content="website"/>
		<meta property="og:site_name" content="<?php bloginfo('name'); ?>"/>
		<!-- end event espresso facebook opengraph -->
	<?php } ?>

	<style>
	.facebook-button div span iframe {
	min-width:450px;
	}
	</style>

	<div id="fb-root"></div>

	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=534432793275671";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>
	<?php

	$espresso_fb_scripts = ob_get_clean();
	echo $espresso_fb_scripts;
}
add_action ('wp_head', 'espresso_social_insert_to_head');


/**
 * espresso facebook button
 * @author Chris Reynolds
 * @author Dean Robinson
 * @since 1.0
 * controls the display and output of the facebook button
 * Override this function using the Custom Files Addon (http://eventespresso.com/product/espresso-custom-files/)
 */
if (!function_exists('espresso_facebook_button')) {

	function espresso_facebook_button($event_id) {
		global $espresso_facebook, $espresso_events_page;

		$permalink = get_permalink( $espresso_events_page->ID ); // get the id of the event-registration page
		if ( is_ssl() ) {
			$permalink = str_replace('http://', 'https://', $permalink); // replaces http with https if we're using ssl
		}
		$registration_url = $permalink . '?ee=' . $event_id; // this may break if they aren't using pretty permalinks
		// new button

			$button = '<div style="overflow:visible;" class="fb-like"';
			$button .= ' data-href="' . urldecode($registration_url) . '"';
			$button .= ' data-send="false"';
			$button .= ' data-width="' . $espresso_facebook['espresso_facebook_width'] . '"';
			$button .= ' data-show-faces="' . $espresso_facebook['espresso_facebook_faces'] . '"';
			$button .= ' data-layout="' .$espresso_facebook['espresso_facebook_layout']. '"';
			$button .= ' data-font="' .$espresso_facebook['espresso_facebook_font']. '"';
			$button .= ' data-colorscheme="' .$espresso_facebook['espresso_facebook_colorscheme']. '"';


			$button .= '></div>';

		return $button;
	}

}


/* * **********************
 * 	Twitter Button 		*
 * ********************** */

/**
 * espresso twitter button
 * @author Chris Reynolds
 * @since 1.0
 * controls the display and output of the twitter button
 * rewritten in 1.1.4
 * Override this function using the Custom Files Addon (http://eventespresso.com/product/espresso-custom-files/)
 */
if (!function_exists('espresso_twitter_button')) {

	function espresso_twitter_button($event_id) {
		global $espresso_twitter;

		//Build the URl to the page
		$registration_url = espresso_reg_url($event_id); //get_option('siteurl') . '/?ee='. $event_id;

		// new button
		if (is_ssl()) {
			$button = '<a href="https://twitter.com/share"';
		} else {
			$button = '<a href="http://twitter.com/share"';
		}
		$button .= 'class="twitter-share-button" data-url="' . $registration_url;
		$button .= '" data-count="' . $espresso_twitter['espresso_twitter_count_box'];
		$button .= '" data-via="' . $espresso_twitter['espresso_twitter_username'];
		$button .= '" data-lang="' . $espresso_twitter['espresso_twitter_lang'];
		if (is_ssl()) {
			$button .= '">Tweet</a><script type="text/javascript" src="https://platform.twitter.com/widgets.js"></script>';
		} else {
			$button .= '">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';
		}
		// all done!
		return $button;
	}

}

//Twitter button short code
//Example usage:
//[ESPRESSO_TWITTER text="Test Event" user_name="EventEspresso" count_box="vertical"]
//[ESPRESSO_TWITTER]
//Shortcode parameters:
// text - Default Tweet text
// count_box - Count box position: none (default) | horizontal | vertical
// user_name - Screen name of the user to attribute the Tweet to
// url - URL of the page to share
// lang - The language for the Tweet Button. Set it to the two letter ISO-639-1 language code (http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes). Default is English (en)
//For more information, check out http://dev.twitter.com/pages/tweet_button_faq
/* if (!function_exists('espresso_twitter_button_shortcode')) {
  //Override this function using the Custom Files Addon (http://eventespresso.com/download/add-ons/custom-files-addon/)
  function espresso_twitter_button_shortcode ($atts){
  //global $wpdb, $org_options;

  //Get the shortcode parameters
  extract(shortcode_atts(array('text' => __('Register for','event_espresso'), 'count_box' => 'none', 'lang' => 'en', 'user_name' => '', 'url' => ''), $atts));

  $text = "{$text}"; //Default Tweet text
  $count_box = "{$count_box}"; //Count box position
  $user_name = "{$user_name}"; //Screen name of the user to attribute the Tweet to
  $url = "{$url}"; //URL of the page to share
  $lang = "{$lang}"; //The language for the Tweet Button

  //Build the URL if none is provided
  $url != "" ? 'data-url="' . $url . '"' : '';

  $button = '<a href="http://twitter.com/share" class="twitter-share-button" ' . $url . ' data-text="' . $text . '" data-count="' . $count_box . '" data-via="' . $user_name . '" data-lang="' . $lang . '">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';

  //return "{$user_name}";
  return $button;

  }
  }
  add_shortcode('ESPRESSO_TWITTER', 'espresso_twitter_button_shortcode');
 */
/* * ****************
 * Google+1 button *
 * ***************** */

/**
 * espresso Google+ button
 * @author Chris Reynolds
 * @since 1.0
 * controls the display and output of the G+ button
 * Override this function using the Custom Files Addon (http://eventespresso.com/product/espresso-custom-files/)
 */
if (!function_exists('espresso_google_button')) {

	function espresso_google_button($event_id) {
		global $espresso_google;

		$registration_url = espresso_reg_url($event_id); //get_option('siteurl') . '/?ee='. $event_id;
		$g_button = '<div class="g-plusone" href="' . $registration_url . '" data-href="' . $registration_url . '" data-size="' . $espresso_google['espresso_google_button_size'] . '"></div>';
		$g_button .= '<script type="text/javascript">
  		(function() {
    		var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true; ';
		if (is_ssl()) {
			$g_button .= 'po.src = \'https://apis.google.com/js/plusone.js\';';
		} else {
			$g_button .= 'po.src = \'http://apis.google.com/js/plusone.js\';'; // only load https address if we're using ssl on the page
		}
		$g_button .= 'var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);
  		})();
			</script>';

		return $g_button;
		;
	}

}

/**
 * social media buttons
 * @since 1.1
 * returns the button data for espresso_social_display_buttons
 * @see espresso_social_display_buttons
 */
if (!function_exists('espresso_social_media_buttons')) {

	function espresso_social_media_buttons($event_id, $type = '') {
		switch ($type) {
			case 'twitter':
				if (function_exists('espresso_twitter_button')) {
					return espresso_twitter_button($event_id);
				}
				break;
			case 'facebook':
				if (function_exists('espresso_facebook_button')) {
					return espresso_facebook_button($event_id);
				}
				break;
			case 'google':
				if (function_exists('espresso_google_button')) {
					return espresso_google_button($event_id);
				}
				break;
			default:
				break;
		}
	}

}

/**
 * display buttons
 * gets the options for the buttons and spits them out onto the page
 * @since 1.1
 */
function espresso_social_display_buttons($event_id) {
	/*
	  fetching the options here so I can output the alignment of each button and apply some conditional styling based on the orientation of the button
	 */
	$espresso_social_twitter = get_option('espresso_twitter_settings');
	$espresso_social_google = get_option('espresso_google_settings');
	$espresso_social_facebook = get_option('espresso_facebook_settings');
	//echo $event_id;
	echo '<div class="ee-social-media-buttons">';
	if (espresso_social_media_buttons($event_id, 'twitter')) {
		echo '<span class="twitter-button ee-social-media-button ' . $espresso_social_twitter['espresso_twitter_count_box'] . '">' . espresso_social_media_buttons($event_id, 'twitter') . '</span>';
	}
	if (espresso_social_media_buttons($event_id, 'google')) {
		echo '<div class="google-button ee-social-media-button ' . $espresso_social_google['espresso_google_button_size'] . '">' . espresso_social_media_buttons($event_id, 'google') . '</div>';
	}
	if (espresso_social_media_buttons($event_id, 'facebook')) {
		echo '<span class="facebook-button ee-social-media-button ' . $espresso_social_facebook['espresso_facebook_layout'] . '">' . espresso_social_media_buttons($event_id, 'facebook') . '</span>';
	} // moving facebook to the last position to, hopefully, fix cb #587
	echo '</div>';
	echo '<div style="clear: both;"></div>';
	return;
}

add_action('action_hook_espresso_social_display_buttons', 'espresso_social_display_buttons', 10, 1);