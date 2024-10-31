<?php

/**
 * Adding the setting pages for both the appearance
 * and the custom search queries in the admin panel
 */


function mkrnwp_add_menu_page()
{
  //Adding plugin menu page
  add_menu_page(__('RN App Settings', 'rnwp'), __('RN App Settings', 'rnwp'), 'manage_options', 'rnwp-main', 'mkrnwp_queries_page', 'dashicons-welcome-widgets-menus', 90);

  //Adding plugin menu page
  add_action('admin_init', 'mkrnwp_appearance_settings');
}
add_action('admin_menu', 'mkrnwp_add_menu_page');

//Add sections and fields for the submenu pages
function mkrnwp_appearance_settings()
{
  //Make sure the form is submitted
  if (isset($_GET['page']) && $_GET['page'] === "rnwp-main" && isset($_POST['submit'])) {

    //Protection against CSRF
    if (!isset($_POST['mkrnwp-main-nonce']) || !wp_verify_nonce($_POST['mkrnwp-main-nonce'], "mkrnwp-main-options")) {
      return;
    }

    //Admin validation
    if (!current_user_can('manage_options')) {
      return;
    }

    require_once("rnwp-options.php");
  }
  //Adding section and fields for the settings page
  add_settings_section('main', '', 'mkrnwp_appearance_section', 'mkrnwp-main');
  add_settings_field('url', __('Website URL', 'rnwp'), 'mkrnwp_url_input', 'mkrnwp-main', 'main');

  add_settings_section('structure', 'App Structure', 'mkrnwp_structure', 'mkrnwp-main');
  add_settings_field('post_types', __('Post Types', 'rnwp'), 'mkrnwp_post_types', 'mkrnwp-main', 'structure');
  add_settings_field('taxonomies', __('Taxonomies', 'rnwp'), 'mkrnwp_taxonomies', 'mkrnwp-main', 'structure');
  add_settings_field('excluded_posts', __('Excluded Posts', 'rnwp'), 'mkrnwp_excluded_posts', 'mkrnwp-main', 'structure');
  add_settings_field('excluded_taxes', __('Excluded Taxonomies', 'rnwp'), 'mkrnwp_excluded_taxes', 'mkrnwp-main', 'structure');
  add_settings_field('offline_per_page', __('Number of posts to download when activating offline mode', 'rnwp'), 'mkrnwp_offline_per_page', 'mkrnwp-main', 'structure');
  add_settings_field('online_per_page', __('Posts per page(fetch with scroll down)', 'rnwp'), 'mkrnwp_online_per_page', 'mkrnwp-main', 'structure');
  add_settings_field('home_page', __('Type of application\'s home page (Will show the post type or taxonomy that has priority)', 'rnwp'), 'mkrnwp_home_page', 'mkrnwp-main', 'structure');
  add_settings_field('about_page', __('Select the page to be considered as about Page', 'rnwp'), 'mkrnwp_about_page', 'mkrnwp-main', 'structure');
  add_settings_field('show_excerpt', __('Show excerpt in post thumbnails (otherwise it will show the whole post)', 'rnwp'), 'mkrnwp_show_excerpt', 'mkrnwp-main', 'structure');
  add_settings_field('excerpt_max_char', __('Maximum characters to be shown in a post thumbnail in the app', 'rnwp'), 'mkrnwp_excerpt_max_char', 'mkrnwp-main', 'structure');
  add_settings_field('featured_image', __('Do you want the app to show featured images for your posts?', 'rnwp'), 'mkrnwp_featured_image', 'mkrnwp-main', 'structure');
  add_settings_field('text_direction', __('Text direction', 'rnwp'), 'mkrnwp_text_direction', 'mkrnwp-main', 'structure');

  add_settings_section('notifications', 'Notifications', 'mkrnwp_notifications', 'mkrnwp-main');
  add_settings_field('enable_firebase', __('Did you enable Firebase for your application?', 'rnwp'), 'mkrnwp_enable_firebase', 'mkrnwp-main', 'notifications');
  add_settings_field('fa_installed', __('Is Fusion Web App plugin installed (for push notifications)?', 'rnwp'), 'mkrnwp_fa_installed', 'mkrnwp-main', 'notifications');
  add_settings_field('scheduled_enabled', __('App would send scheduled notifications from time to time?', 'rnwp'), 'mkrnwp_scheduled_enabled', 'mkrnwp-main', 'notifications');
  add_settings_field('notification_frequency', __('Notification frequency. Scheduled notifications to be shown every how many days?', 'rnwp'), 'mkrnwp_notification_frequency', 'mkrnwp-main', 'notifications');
  add_settings_field('notification_title', __('Scheduled notification message title', 'rnwp'), 'mkrnwp_notification_title', 'mkrnwp-main', 'notifications');
  add_settings_field('notification_message', __('Scheduled notification message body', 'rnwp'), 'mkrnwp_notification_message', 'mkrnwp-main', 'notifications');

  add_settings_section('contacts', 'Contact Details', 'mkrnwp_app_contact_details', 'mkrnwp-main');
  add_settings_field('phone', __('Business Phone number (if applicable)', 'rnwp'), 'mkrnwp_phone', 'mkrnwp-main', 'contacts');
  add_settings_field('address', __('Business Address (if applicable)', 'rnwp'), 'mkrnwp_address', 'mkrnwp-main', 'contacts');
  add_settings_field('mail', __('Business Email (if applicable)', 'rnwp'), 'mkrnwp_mail', 'mkrnwp-main', 'contacts');
  add_settings_field('social', __('Social Links', 'rnwp'), 'mkrnwp_social', 'mkrnwp-main', 'contacts');

  add_settings_section('ads', 'Ad Settings', 'mkrnwp_ads', 'mkrnwp-main');
  add_settings_field('enable_banner_ads', __('Enable Admob Banner ads', 'rnwp'), 'mkrnwp_enable_banner_ads', 'mkrnwp-main', 'ads');
  add_settings_field('banner_ads_key', __('Admob Banner ads key', 'rnwp'), 'mkrnwp_banner_ads_key', 'mkrnwp-main', 'ads');
  add_settings_field('enable_interstitial_ads', __('Enable Admob interstitial ads', 'rnwp'), 'mkrnwp_enable_interstitial_ads', 'mkrnwp-main', 'ads');
  add_settings_field('interstitial_ads_key', __('Admob Interstitial ads key', 'rnwp'), 'mkrnwp_interstitial_ads_key', 'mkrnwp-main', 'ads');
  add_settings_field('enable_rewarded_ads', __('Enable Admob rewarded ads', 'rnwp'), 'mkrnwp_enable_rewarded_ads', 'mkrnwp-main', 'ads');
  add_settings_field('rewarded_ads_key', __('Admob Rewarded ads key', 'rnwp'), 'mkrnwp_rewarded_ads_key', 'mkrnwp-main', 'ads');

  add_settings_section('screen-names', 'Other Screen Names', 'mkrnwp_screen_names', 'mkrnwp-main');
  add_settings_field('home_screen_name', __('Home Screen', 'rnwp'), 'mkrnwp_home_screen_name', 'mkrnwp-main', 'screen-names');
  add_settings_field('contact_screen_name', __('Contact Screen', 'rnwp'), 'mkrnwp_contact_screen_name', 'mkrnwp-main', 'screen-names');
  add_settings_field('about_screen_name', __('About Screen', 'rnwp'), 'mkrnwp_about_screen_name', 'mkrnwp-main', 'screen-names');
  add_settings_field('setting_screen_name', __('Setting Screen', 'rnwp'), 'mkrnwp_setting_screen_name', 'mkrnwp-main', 'screen-names');
  add_settings_field('search_screen_name', __('Search Screen', 'rnwp'), 'mkrnwp_search_screen_name', 'mkrnwp-main', 'screen-names');
  add_settings_field('saved_screen_name', __('Saved Screen', 'rnwp'), 'mkrnwp_saved_screen_name', 'mkrnwp-main', 'screen-names');

  add_settings_section('app-text', 'Other texts in the app', 'mkrnwp_app_text', 'mkrnwp-main');
  add_settings_field('no_internet', __('No internet connection', 'rnwp'), 'mkrnwp_no_internet', 'mkrnwp-main', 'app-text');
  add_settings_field('online_search', __('Online search button option', 'rnwp'), 'mkrnwp_online_search', 'mkrnwp-main', 'app-text');
  add_settings_field('no_results', __('No search results', 'rnwp'), 'mkrnwp_no_results', 'mkrnwp-main', 'app-text');
  add_settings_field('enable_dark_mode', __('Dark mode option', 'rnwp'), 'mkrnwp_enable_dark_mode', 'mkrnwp-main', 'app-text');
  add_settings_field('enable_notifications', __('Notification option', 'rnwp'), 'mkrnwp_enable_notifications', 'mkrnwp-main', 'app-text');
  add_settings_field('saved_offline', __('Offline mode option', 'rnwp'), 'mkrnwp_saved_offline', 'mkrnwp-main', 'app-text');
  add_settings_field('check_updates', __('Check updates button', 'rnwp'), 'mkrnwp_check_updates', 'mkrnwp-main', 'app-text');
  add_settings_field('downloading_text', __('Downloading text', 'rnwp'), 'mkrnwp_downloading_text', 'mkrnwp-main', 'app-text');
  add_settings_field('done_text', __('Download done text', 'rnwp'), 'mkrnwp_done_text', 'mkrnwp-main', 'app-text');
  add_settings_field('see_product_page', __('See Product Page (for Woocommerce if user wishes to continue purchase he will be redirected to web page)', 'rnwp'), 'mkrnwp_see_product_page', 'mkrnwp-main', 'app-text');
  add_settings_field('download_alert_title', __('Download data alert title', 'rnwp'), 'mkrnwp_download_alert_title', 'mkrnwp-main', 'app-text');
  add_settings_field('download_alert_message', __('Download data alert title', 'rnwp'), 'mkrnwp_download_alert_message', 'mkrnwp-main', 'app-text');
  add_settings_field('delete_alert_title', __('Delete data alert title', 'rnwp'), 'mkrnwp_delete_alert_title', 'mkrnwp-main', 'app-text');
  add_settings_field('delete_alert_message', __('Delete data alert title', 'rnwp'), 'mkrnwp_delete_alert_message', 'mkrnwp-main', 'app-text');
  add_settings_field('alert_yes', __('Button title to accept alert', 'rnwp'), 'mkrnwp_alert_yes', 'mkrnwp-main', 'app-text');
  add_settings_field('alert_no', __('Button title to dismiss alert', 'rnwp'), 'mkrnwp_alert_no', 'mkrnwp-main', 'app-text');

  add_settings_section('light-mode-colors', 'Light mode colors', 'mkrnwp_light_mode_colors', 'mkrnwp-main');
  add_settings_field('background_light', __('Background', 'rnwp'), 'mkrnwp_background_light', 'mkrnwp-main', 'light-mode-colors');
  add_settings_field('text_light', __('Text', 'rnwp'), 'mkrnwp_text_light', 'mkrnwp-main', 'light-mode-colors');
  add_settings_field('text_alt_light', __('text Alt', 'rnwp'), 'mkrnwp_text_alt_light', 'mkrnwp-main', 'light-mode-colors');
  add_settings_field('container_light', __('container', 'rnwp'), 'mkrnwp_container_light', 'mkrnwp-main', 'light-mode-colors');
  add_settings_field('switch_thumb_light', __('Switch thumb', 'rnwp'), 'mkrnwp_switch_thumb_light', 'mkrnwp-main', 'light-mode-colors');
  add_settings_field('switch_on_light', __('Switch Button on', 'rnwp'), 'mkrnwp_switch_on_light', 'mkrnwp-main', 'light-mode-colors');
  add_settings_field('switch_off_light', __('Switch Button off', 'rnwp'), 'mkrnwp_switch_off_light', 'mkrnwp-main', 'light-mode-colors');

  add_settings_section('dark-mode-colors', 'dark mode colors', 'mkrnwp_dark_mode_colors', 'mkrnwp-main');
  add_settings_field('background_dark', __('Background', 'rnwp'), 'mkrnwp_background_dark', 'mkrnwp-main', 'dark-mode-colors');
  add_settings_field('text_dark', __('Text', 'rnwp'), 'mkrnwp_text_dark', 'mkrnwp-main', 'dark-mode-colors');
  add_settings_field('text_alt_dark', __('text Alt', 'rnwp'), 'mkrnwp_text_alt_dark', 'mkrnwp-main', 'dark-mode-colors');
  add_settings_field('container_dark', __('container', 'rnwp'), 'mkrnwp_container_dark', 'mkrnwp-main', 'dark-mode-colors');
  add_settings_field('switch_thumb_dark', __('Switch thumb', 'rnwp'), 'mkrnwp_switch_thumb_dark', 'mkrnwp-main', 'dark-mode-colors');
  add_settings_field('switch_on_dark', __('Switch Button on', 'rnwp'), 'mkrnwp_switch_on_dark', 'mkrnwp-main', 'dark-mode-colors');
  add_settings_field('switch_off_dark', __('Switch Button off', 'rnwp'), 'mkrnwp_switch_off_dark', 'mkrnwp-main', 'dark-mode-colors');

  // add_settings_field('delete-data', __('Delete plugin data when plugin is uninstalled', 'archive-pages-in-search'), 'mkrnwp_delete_all_plugin_data', 'mkrnwp-main', 'appearance');
}


//Insert new rnwp config sections
function mkrnwp_appearance_section()
{
  echo "<h4 class='w3-text-red'><a href='https://boostrand.gumroad.com/l/rnwp'>Haven't checked out the RNWP template yet? Click here.</a></h4>";
}

function mkrnwp_structure()
{
}

function mkrnwp_screen_names()
{
}

function mkrnwp_app_contact_details()
{
}


function mkrnwp_notifications()
{
}

function mkrnwp_ads()
{
}

function mkrnwp_app_text()
{
}

function mkrnwp_light_mode_colors()
{
}

function mkrnwp_dark_mode_colors()
{
}

require_once("appearance-settings.php");
