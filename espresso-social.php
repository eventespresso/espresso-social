<?php
/*
Plugin Name: Event Espresso - Social Media
Plugin URI: http://www.eventespresso.com
Description: A social media addon for Event Espresso. Includes includes Facebook and Twitter share buttons.
Version: 1.0
Usage: Add <?php echo espresso_show_social_media($event_id, 'twitter');?> and/or <?php echo espresso_show_social_media($event_id, 'facebook');?> to display  Twitter or Facebook buttons in your event templates.
Example: <p><?php echo espresso_show_social_media($event_id, 'twitter');?> <?php echo espresso_show_social_media($event_id, 'facebook');?></p>
Author: Seth Shoultes
Author URI: http://www.shoultes.net
Copyright 2010  Seth Shoultes  (email : seth@eventespresso.com)

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
	return '1.1';
}
define("ESPRESSO_SOCIAL_VERSION", espresso_social_version() );

//Define the plugin directory and path
define("ESPRESSO_SOCIAL_PLUGINPATH", "/" . plugin_basename( dirname(__FILE__) ) . "/");
define("ESPRESSO_SOCIAL_PLUGINFULLPATH", WP_PLUGIN_DIR . ESPRESSO_SOCIAL_PLUGINPATH  );
define("ESPRESSO_SOCIAL_PLUGINFULLURL", WP_PLUGIN_URL . ESPRESSO_SOCIAL_PLUGINPATH );

//Globals
global $espresso_facebook;
$espresso_facebook = get_option('espresso_facebook_settings');

global $espresso_twitter;
$espresso_twitter = get_option('espresso_twitter_settings');

//Install the plugin
function espresso_social_install(){
	//Install Facebook Options
	$espresso_facebook = array(
					'espresso_facebook_layout' => 'button_count',
					'espresso_facebook_faces' => 'true',
					'espresso_facebook_action' => 'like',
					'espresso_facebook_font' => 'arial',
					'espresso_facebook_colorscheme' => 'light',
					'espresso_facebook_height' => '21',
					'espresso_facebook_width' => '450'
				);
	add_option( 'espresso_facebook_settings', $espresso_facebook );
	
	//Install Twitter Options
	$espresso_twitter = array(
					'espresso_twitter_text' => get_bloginfo('name'),
					'espresso_twitter_username' => 'EventEspresso',
					'espresso_twitter_count_box' => 'none',
					'espresso_twitter_lang' => 'en'
				);
	add_option( 'espresso_twitter_settings', $espresso_twitter );
}
register_activation_hook(__FILE__,'espresso_social_install');

function espresso_social_config_mnu()	{
	global $wpdb, $espresso_twitter, $espresso_facebook;
	
	/*Facebok*/
	function espresso_facebook_updated(){
	return __('Facebook details saved.','event_espresso');
	}
	
	if (isset($_POST['update_facebook'])) {
		$espresso_facebook['espresso_facebook_layout'] = $_POST['espresso_facebook_layout'];
		$espresso_facebook['espresso_facebook_faces'] = $_POST['espresso_facebook_faces'];
		$espresso_facebook['espresso_facebook_action'] = $_POST['espresso_facebook_action'];
		$espresso_facebook['espresso_facebook_font'] = $_POST['espresso_facebook_font'];
		$espresso_facebook['espresso_facebook_colorscheme'] = $_POST['espresso_facebook_colorscheme'];
		$espresso_facebook['espresso_facebook_height'] = $_POST['espresso_facebook_height'];
		$espresso_facebook['espresso_facebook_width'] = $_POST['espresso_facebook_width'];
		
		update_option( 'espresso_facebook_settings', $espresso_facebook);
		add_action( 'admin_notices', 'espresso_facebook_updated');
	}
	$espresso_facebook = get_option('espresso_facebook_settings');
	
	/*Twitter*/
	function espresso_twitter_updated(){
	return __('Twitter details saved.','event_espresso');
	}

	if (isset($_POST['update_twitter'])) {
		$espresso_twitter['espresso_twitter_text'] = stripslashes_deep($_POST['espresso_twitter_text']);
		$espresso_twitter['espresso_twitter_username'] = $_POST['espresso_twitter_username'];
		$espresso_twitter['espresso_twitter_count_box'] = $_POST['espresso_twitter_count_box'];
		$espresso_twitter['espresso_twitter_lang'] = $_POST['espresso_twitter_lang'];
		
		update_option( 'espresso_twitter_settings', $espresso_twitter);
		add_action( 'admin_notices', 'espresso_twitter_updated');
	}

	$espresso_twitter = get_option('espresso_twitter_settings');
?>
<div id="configure_organization_form" class="wrap meta-box-sortables ui-sortable">
  <div id="icon-options-event" class="icon32"> </div>
  <h2>
    <?php _e('Event Espresso - Social Media Settings','event_espresso'); ?>
  </h2>
  <div id="event_espresso-col-left">
  <form class="espresso_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
      <ul id="event_espresso-sortables">
        <li>
          <div class="box-mid-head">
            <h2 class="fugue f-wrench">
              <?php _e('Facebook Settings','event_espresso'); ?>
            </h2>
          </div>
          <div class="box-mid-body" id="toggle2">
            <div class="padding">
              <ul>
              	<li>
                  <label for="espresso_facebook_layout">
                    <?php _e('Layout Style:','event_espresso'); ?>
                  </label>
                  <?php
						$values=array(
							array('id'=>'button_count','text'=> __('Button Count','event_espresso')),					
							array('id'=>'standard','text'=> __('Standard','event_espresso')),
							array('id'=>'box_count','text'=> __('Box Count','event_espresso'))
						);				
							echo select_input('espresso_facebook_layout', $values, $espresso_facebook['espresso_facebook_layout']);
					?>
                </li>
                <li>
                  <label for="espresso_facebook_faces">
                    <?php _e('Show Faces:','event_espresso'); ?>
                  </label>
                  <?php
						$values=array(					
							array('id'=>'true','text'=> __('Yes','event_espresso')),
							array('id'=>'false','text'=> __('No','event_espresso'))
						);				
							echo select_input('espresso_facebook_faces', $values, $espresso_facebook['espresso_facebook_faces']);
					?>
                </li>
                
                <li>
                  <label for="espresso_facebook_font">
                    <?php _e('Font:','event_espresso'); ?>
                  </label>
                 <?php
						$values=array(					
							array('id'=>'arial','text'=> __('arial','event_espresso')),
							array('id'=>'lucida grande','text'=> __('lucida grande','event_espresso')),
							array('id'=>'segoe ui','text'=> __('segoe ui','event_espresso')),
							array('id'=>'tahoma','text'=> __('tahoma','event_espresso')),
							array('id'=>'trebuchet ms','text'=> __('trebuchet ms','event_espresso')),
							array('id'=>'verdana','text'=> __('verdana','event_espresso'))
						);				
							echo select_input('espresso_facebook_font', $values, $espresso_facebook['espresso_facebook_font']);
					?>
                </li>
                 <li>
                  <label for="espresso_facebook_colorscheme">
                    <?php _e('Color Scheme:','event_espresso'); ?>
                  </label>
                 <?php
						$values=array(					
							array('id'=>'light','text'=> __('Light','event_espresso')),
							array('id'=>'dark','text'=> __('Dark','event_espresso'))
						);				
							echo select_input('espresso_facebook_colorscheme', $values, $espresso_facebook['espresso_facebook_colorscheme']);
					?>
                </li>
                <li>
                  <label for="espresso_facebook_height">
                    <?php _e('Height:','event_espresso'); ?>
                  </label>
                   <input type="text" name="espresso_facebook_height" size="100" maxlength="100" value="<?php echo $espresso_facebook['espresso_facebook_height'];?>" />
                </li>
                 <li>
                  <label for="espresso_facebook_width">
                    <?php _e('Width:','event_espresso'); ?>
                  </label>
                   <input type="text" name="espresso_facebook_width" size="100" maxlength="100" value="<?php echo $espresso_facebook['espresso_facebook_width'];?>" />
                </li>
                <li><input type="hidden" name="update_facebook" value="update" />
      <p>
        <input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Facebook Options', 'event_espresso'); ?>" id="save_facebook_settings" />
      </p></li>
              </ul>
            </div>
          </div>
        </li>
        </ul>
         
        </form>
        
    <form class="espresso_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
      <ul id="event_espresso-sortables">
        <li>
          <div class="box-mid-head">
            <h2 class="fugue f-wrench">
              <?php _e('Twitter Settings','event_espresso'); ?>
            </h2>
          </div>
          <div class="box-mid-body" id="toggle2">
            <div class="padding">
              <ul>
              	<li>
                  <label for="espresso_twitter_username">
                    <?php _e('Twitter Username:','event_espresso'); ?>
                  </label>
                  <input type="text" name="espresso_twitter_username" size="30" maxlength="20" value="<?php echo $espresso_twitter['espresso_twitter_username'];?>" />
                </li>
                <li>
                  <label for="espresso_twitter_text">
                    <?php _e('Default Twitter Text:','event_espresso'); ?>
                  </label>
                  <input type="text" name="espresso_twitter_text" size="100" maxlength="100" value="<?php echo stripslashes_deep($espresso_twitter['espresso_twitter_text']);?>" />
                </li>
                
                <li>
                  <label for="espresso_twitter_count_box">
                    <?php _e('Count Box Position:','event_espresso'); ?>
                  </label>
                 <?php
						$values=array(					
							array('id'=>'none','text'=> __('None','event_espresso')),
							array('id'=>'horizontal','text'=> __('Horizontal','event_espresso')),
							array('id'=>'vertical','text'=> __('Vertical','event_espresso'))
						);				
							echo select_input('espresso_twitter_count_box', $values, $espresso_twitter['espresso_twitter_count_box']);
					?>
                </li>
                <li>
                  <label for="espresso_twitter_lang">
                    <?php _e('The language for the Tweet Button:','event_espresso'); ?>
                  </label>
                   <?php
						$values=array(					
							array('id'=>'en','text'=> __('English','event_espresso')),
							array('id'=>'da','text'=> __('Danish','event_espresso')),
							array('id'=>'dl','text'=> __('Dutch','event_espresso')),
							array('id'=>'fr','text'=> __('French','event_espresso')),
							array('id'=>'de','text'=> __('German','event_espresso')),
							array('id'=>'es','text'=> __('Spanish','event_espresso'))
						);				
							echo select_input('espresso_twitter_lang', $values, $espresso_twitter['espresso_twitter_lang']);
					?>
                </li>
                <li> <input type="hidden" name="update_twitter" value="update" />
      <p>
        <input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Twitter Options', 'event_espresso'); ?>" id="save_twitter_settings" />
      </p></li>
              </ul>
            </div>
          </div>
        </li>
        </ul>
        
        </form>
        </div>
        </div><?php event_espresso_display_right_column ();?>
<?php
}

/************************
* 	Facebook Button 	*
************************/
if (!function_exists('espresso_facebook_button')) {
	function espresso_facebook_button ($event_id){
		//OVerride this function using the Custom Files Addon (http://eventespresso.com/download/add-ons/custom-files-addon/)
		global $org_options, $espresso_facebook;
		
		//Build the URl to the page
		$registration_url = get_option('siteurl') . '/?ee='. $event_id;
	
		$button = '<iframe src="http://www.facebook.com/plugins/like.php?href='.$registration_url.'&amp;layout=' . $espresso_facebook['espresso_facebook_layout'] . '&amp;show_faces=' . $espresso_facebook['espresso_facebook_faces'] . '&amp;width=' . $espresso_facebook['espresso_facebook_width'] . '&amp;action=' . $espresso_facebook['espresso_facebook_action'] . '&amp;font=' . $espresso_facebook['espresso_facebook_font'] . '&amp;colorscheme=' . $espresso_facebook['espresso_facebook_colorscheme'] . '&amp;height=' . $espresso_facebook['espresso_facebook_height'] . '" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:' . $espresso_facebook['espresso_facebook_width'] . 'px; height:' . $espresso_facebook['espresso_facebook_height'] . 'px;" allowTransparency="true"></iframe>';
		
		return $button;
	
	}
}

/************************
* 	Twitter Button 		*
************************/
if (!function_exists('espresso_twitter_button')) {
	//OVerride this function using the Custom Files Addon (http://eventespresso.com/download/add-ons/custom-files-addon/)
	function espresso_twitter_button ($event_id){
		global $wpdb, $org_options, $espresso_twitter;
		
		//Build the URl to the page
		$registration_url = get_option('siteurl') . '/?ee='. $event_id;
		
		$button = '<a href="http://twitter.com/share" class="twitter-share-button" data-url="' . $registration_url . '" data-text="' . $espresso_twitter['espresso_twitter_text'] . '" data-count="' . $espresso_twitter['espresso_twitter_count_box'] . '" data-via="' . $espresso_twitter['espresso_twitter_username'] . '" data-lang="' . $espresso_twitter['espresso_twitter_lang'] . '">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';
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
if (!function_exists('espresso_twitter_button_shortcode')) { 
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