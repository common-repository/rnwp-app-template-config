<?php

//Get existing settings
$mkrnwp_existing_settings_options = get_option('mkrnwp_settings_options');

//Prepare for new settings
$mkrnwp_settings_options = array();

//Add the site url to options table
$mkrnwp_settings_options['url'] = isset($_POST['url']) ? esc_url_raw($_POST['url']) : get_site_url();
//Add the chosen post types to options table
$args = array(
  'public' => true,
  'show_in_rest'   => true,
);
$current_post_types = get_post_types($args, 'objects');

foreach ($current_post_types as $key => $post_type) {
  if (isset($_POST[$post_type->rest_base])) {
    $label = isset($_POST[$key . '_name']) ? sanitize_text_field($_POST[$key . '_name']) : $post_type->label;
    $priority =
      isset($_POST['prior_' . $post_type->rest_base]) ? "1" : "";
    $post_types_array[$key] = array('name' => $label, 'endpoint' => $post_type->rest_base, 'prior_' . $post_type->rest_base => $priority);
  }
}
$mkrnwp_settings_options['post_types'] = $post_types_array;

//Add the chosen taxonomies to options table
$args = array(
  'public' => true,
  'show_in_rest'   => true,
);
$current_taxonomies = get_taxonomies($args, 'objects');

$taxonomies_array = array();
foreach ($current_taxonomies as $key => $taxonomy) {
  if (isset($_POST[$taxonomy->rest_base])) {
    $label = isset($_POST[$key . '_name']) ? sanitize_text_field($_POST[$key . '_name']) : $taxonomy->label;
    $related_post_type = isset($_POST['related_type_' . $taxonomy->rest_base]) ? sanitize_key($_POST['related_type_' . $taxonomy->rest_base]) : $taxonomy->object_type[0];
    $priority =
      isset($_POST['prior_' . $taxonomy->rest_base]) ? "1" : "";
    $taxonomies_array[$key] = array('name' => $label, 'endpoint' => $taxonomy->rest_base, 'posttype' => $related_post_type, 'prior_' . $taxonomy->rest_base => $priority);
  }
}

/*
    Below code shall set plugin settings to be stored in the database in the options table
    For more details on each setting, please refer to the below link:
    https://boostrand.com/configure-your-app-options-userconfig-js-file-and-rnwp-plugin/
*/
$mkrnwp_settings_options['taxonomies'] = $taxonomies_array;

$excluded_post_string = isset($_POST['excluded_posts']) ? sanitize_text_field($_POST['excluded_posts']) : "";
$mkrnwp_settings_options['excluded_posts'] = $excluded_post_string;

$excluded_tax_string = isset($_POST['excluded_taxes']) ? sanitize_text_field($_POST['excluded_taxes']) : "";
$mkrnwp_settings_options['excluded_taxes'] = $excluded_tax_string;

$mkrnwp_settings_options['offline_per_page'] = isset($_POST['offline_per_page']) ? sanitize_text_field($_POST['offline_per_page']) : "100";

$mkrnwp_settings_options['online_per_page'] = isset($_POST['online_per_page']) ? sanitize_text_field($_POST['online_per_page']) : "5";

$mkrnwp_settings_options['home_page'] = isset($_POST['home_page']) ? sanitize_key($_POST['home_page']) : "posts";

$mkrnwp_settings_options['about_page'] = isset($_POST['about_page']) ? sanitize_key($_POST['about_page']) : "0";

$mkrnwp_settings_options['show_excerpt'] = sanitize_key($_POST['show_excerpt']);
$mkrnwp_settings_options['excerpt_max_char'] = isset($_POST['excerpt_max_char']) ? sanitize_text_field($_POST['excerpt_max_char']) : "500";

$mkrnwp_settings_options['enable_firebase'] = sanitize_key($_POST['enable_firebase']);

$mkrnwp_settings_options['enable_banner_ads'] = sanitize_key($_POST['enable_banner_ads']);
$mkrnwp_settings_options['banner_ads_key'] = isset($_POST['banner_ads_key']) ? sanitize_text_field($_POST['banner_ads_key']) : "";

$mkrnwp_settings_options['enable_interstitial_ads'] = sanitize_key($_POST['enable_interstitial_ads']);
$mkrnwp_settings_options['interstitial_ads_key'] = isset($_POST['interstitial_ads_key']) ? sanitize_text_field($_POST['interstitial_ads_key']) : "";

$mkrnwp_settings_options['enable_rewarded_ads'] = sanitize_key($_POST['enable_rewarded_ads']);
$mkrnwp_settings_options['rewarded_ads_key'] = isset($_POST['rewarded_ads_key']) ? sanitize_text_field($_POST['rewarded_ads_key']) : "";

$mkrnwp_settings_options['enable_featured_image'] = sanitize_key($_POST['enable_featured_image']);

$mkrnwp_settings_options['enable_fa_installed'] = sanitize_key($_POST['enable_fa_installed']);

$mkrnwp_settings_options['scheduled_enabled'] = sanitize_key($_POST['scheduled_enabled']);
$mkrnwp_settings_options['notification_frequency'] =
  isset($_POST['notification_frequency']) ? sanitize_text_field($_POST['notification_frequency']) : "We miss you!";
$mkrnwp_settings_options['notification_title'] = isset($_POST['notification_title']) ? sanitize_text_field($_POST['notification_title']) : "We miss you!";
$mkrnwp_settings_options['notification_message'] = isset($_POST['notification_message']) ? sanitize_text_field($_POST['notification_message']) : "We miss you!";

$mkrnwp_settings_options['text_direction'] = isset($_POST['text_direction']) ? sanitize_key($_POST['text_direction']) : "ltr";

$mkrnwp_settings_options['phone'] = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : "";
$mkrnwp_settings_options['address'] = isset($_POST['address']) ? sanitize_text_field($_POST['address']) : "";
$mkrnwp_settings_options['mail'] = isset($_POST['mail']) ? sanitize_email($_POST['mail']) : "";

$social_array = ['facebook' => '', 'youtube' => '', 'instagram' => '', 'twitter' => '', 'pinterest' => '', 'facebook-messenger' => '', 'whatsapp' => '', 'telegram' => '', 'snapchat' => '', 'linkedin' => '', 'reddit' => '', 'behance' => '', 'github-circle' => '', 'wordpress' => '', 'quora' => '', 'wechat' => '', 'tumblr' => '', 'qqchat' => ''];
foreach ($social_array as $icon => $link) {
  if ($_POST[$icon] != "") {
    $mkrnwp_settings_options['social'][$icon] = $icon == "whatsapp" ? sanitize_text_field($_POST[$icon]) : esc_url_raw($_POST[$icon]);
  }
}

$mkrnwp_settings_options['home_screen_name'] = isset($_POST['home_screen_name']) ? sanitize_text_field($_POST['home_screen_name']) : "Home";
$mkrnwp_settings_options['contact_screen_name'] = isset($_POST['contact_screen_name']) ? sanitize_text_field($_POST['contact_screen_name']) : "Contact Us";
$mkrnwp_settings_options['about_screen_name'] = isset($_POST['about_screen_name']) ? sanitize_text_field($_POST['about_screen_name']) : "About Us";
$mkrnwp_settings_options['setting_screen_name'] = isset($_POST['setting_screen_name']) ? sanitize_text_field($_POST['setting_screen_name']) : "Settings";
$mkrnwp_settings_options['search_screen_name'] = isset($_POST['search_screen_name']) ? sanitize_text_field($_POST['search_screen_name']) : "Search";
$mkrnwp_settings_options['saved_screen_name'] = isset($_POST['saved_screen_name']) ? sanitize_text_field($_POST['saved_screen_name']) : "Saved";


$mkrnwp_settings_options['no_internet'] = isset($_POST['no_internet']) ? sanitize_text_field($_POST['no_internet']) : "There is no internet connection.";
$mkrnwp_settings_options['background_alt_light'] = isset($_POST['online_search']) ? sanitize_text_field($_POST['online_search']) : "Online Search (More flexible)";
$mkrnwp_settings_options['no_results'] = isset($_POST['no_results']) ? sanitize_text_field($_POST['no_results']) : "No search results found.";
$mkrnwp_settings_options['enable_dark_mode'] = isset($_POST['enable_dark_mode']) ? sanitize_text_field($_POST['enable_dark_mode']) : "Enable dark mode";
$mkrnwp_settings_options['enable_notifications'] = isset($_POST['enable_notifications']) ? sanitize_text_field($_POST['enable_notifications']) : "Enable notifications";
$mkrnwp_settings_options['saved_offline'] = isset($_POST['saved_offline']) ? sanitize_text_field($_POST['saved_offline']) : "Enable offline mode";
$mkrnwp_settings_options['check_updates'] = isset($_POST['check_updates']) ? sanitize_text_field($_POST['check_updates']) : "Check updates";
$mkrnwp_settings_options['downloading_text'] = isset($_POST['downloading_text']) ? sanitize_text_field($_POST['downloading_text']) : "Downloading...";
$mkrnwp_settings_options['done_text'] = isset($_POST['done_text']) ? sanitize_text_field($_POST['done_text']) : "Done.";
$mkrnwp_settings_options['see_product_page'] = isset($_POST['see_product_page']) ? sanitize_text_field($_POST['see_product_page']) : "See Product Page";
$mkrnwp_settings_options['download_alert_title'] = isset($_POST['download_alert_title']) ? sanitize_text_field($_POST['download_alert_title']) : "Download data";
$mkrnwp_settings_options['download_alert_message'] = isset($_POST['download_alert_message']) ? sanitize_text_field($_POST['download_alert_message']) : "Do you want to download the data?";
$mkrnwp_settings_options['delete_alert_title'] = isset($_POST['delete_alert_title']) ? sanitize_text_field($_POST['delete_alert_title']) : "Delete data";
$mkrnwp_settings_options['delete_alert_message'] = isset($_POST['delete_alert_message']) ? sanitize_text_field($_POST['delete_alert_message']) : "Do you want to delete the data?";
$mkrnwp_settings_options['alert_yes'] = isset($_POST['alert_yes']) ? sanitize_text_field($_POST['alert_yes']) : "Yes";
$mkrnwp_settings_options['alert_no'] = isset($_POST['alert_no']) ? sanitize_text_field($_POST['alert_no']) : "No";

$mkrnwp_settings_options['background_light'] = isset($_POST['background_light']) ? sanitize_hex_color($_POST['background_light']) : "#f4eeff";
$mkrnwp_settings_options['text_light'] = isset($_POST['text_light']) ? sanitize_hex_color($_POST['text_light']) : "#424874";
$mkrnwp_settings_options['text_alt_light'] = isset($_POST['text_alt_light']) ? sanitize_hex_color($_POST['text_alt_light']) : "#a6b1e1";
$mkrnwp_settings_options['container_light'] = isset($_POST['container_light']) ? sanitize_hex_color($_POST['container_light']) : "#c3cafd";
$mkrnwp_settings_options['switch_thumb_light'] = isset($_POST['switch_thumb_light']) ? sanitize_hex_color($_POST['switch_thumb_light']) : "#424874";
$mkrnwp_settings_options['switch_on_light'] = isset($_POST['switch_on_light']) ? sanitize_hex_color($_POST['switch_on_light']) : "#a6b1e1";
$mkrnwp_settings_options['switch_off_light'] = isset($_POST['switch_off_light']) ? sanitize_hex_color($_POST['switch_off_light']) : "#dcd6f7";

$mkrnwp_settings_options['background_dark'] = isset($_POST['background_dark']) ? sanitize_hex_color($_POST['background_dark']) : "#424874";
$mkrnwp_settings_options['text_dark'] = isset($_POST['text_dark']) ? sanitize_hex_color($_POST['text_dark']) : "#f4eeff";
$mkrnwp_settings_options['text_alt_dark'] = isset($_POST['text_alt_dark']) ? sanitize_hex_color($_POST['text_alt_dark']) : "#dcd6f7";
$mkrnwp_settings_options['container_dark'] = isset($_POST['container_dark']) ? sanitize_hex_color($_POST['container_dark']) : "#5d659f";
$mkrnwp_settings_options['switch_thumb_dark'] = isset($_POST['switch_thumb_dark']) ? sanitize_hex_color($_POST['switch_thumb_dark']) : "#a6b1e1";
$mkrnwp_settings_options['switch_on_dark'] = isset($_POST['switch_on_dark']) ? sanitize_hex_color($_POST['switch_on_dark']) : "#dcd6f7";
$mkrnwp_settings_options['switch_off_dark'] = isset($_POST['switch_off_dark']) ? sanitize_hex_color($_POST['switch_off_dark']) : "#f4eeff";




// $mkrnwp_settings_options['delete_plugin_data'] = isset($_POST['delete_plugin_data']) ? "1" : '';




//Send the new settings array to the database to update the settings option
if (!$mkrnwp_existing_settings_options) {
  add_option('mkrnwp_settings_options', $mkrnwp_settings_options);
} else {
  update_option('mkrnwp_settings_options', $mkrnwp_settings_options);
}
