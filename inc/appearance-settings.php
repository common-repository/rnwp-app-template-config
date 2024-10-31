<?php

/*
Class to get reusable variables as all posts, all post types and all taxonomies that shall be used more than once in the settings page
It shall also adjust post type settings with respect to REST API to enable the plugin and the app utilize the required data
*/
class RNWP
{
  public $mkrnwp_existing_settings_options;
  public $offline_per_page;
  public $online_per_page;
  public $current_taxonomies;
  public $current_taxonomies_names;
  public $current_post_types;
  public $current_post_types_names;
  public $query_posts;
  public $all_terms;

  public function __construct()
  {
    add_filter('admin_init', array($this, 'get_variables'), 1);
    add_action('init', array($this, 'adjust_taxonomies_rest'), 9999);
    add_action('init', array($this, 'adjust_maximum_rest'), 9999);
    add_action('init', array($this, 'adjust_cpt_rest'), 9999);
    add_action('admin_enqueue_scripts', array($this, 'mkrnwp_custom_query_styles'));
  }


  // Enqueue css and js files for settings pages
  public function mkrnwp_custom_query_styles($hook)
  {
    if ($hook === 'toplevel_page_rnwp-main') {
      wp_enqueue_style('w3-css', plugin_dir_url(__FILE__) . "../css/w3.css");
      wp_enqueue_style('style-css', plugin_dir_url(__FILE__) . "../css/style.css");
      wp_enqueue_script('custom', plugin_dir_url(__FILE__) . "../js/custom.js", array(), '1.0', true);
    }
  }

  // Show product categories and tags in REST API in case of Woocommerce
  public function adjust_taxonomies_rest()
  {
    global $wp_taxonomies;
    foreach ($wp_taxonomies as $key => $taxonomy) {
      if ($key == 'product_cat' || $key == 'product_tag') {
        $taxonomy->show_in_rest = true;
        $taxonomy->rest_base = $key;
      }
    }
  }
  public function adjust_maximum_rest()
  {
    $this->mkrnwp_existing_settings_options = get_option('mkrnwp_settings_options');
    $this->offline_per_page = intval($this->mkrnwp_existing_settings_options['offline_per_page']);
    $this->online_per_page = intval($this->mkrnwp_existing_settings_options['online_per_page']);

    $args = array(
      'public' => true,
      'show_in_rest'   => true,
    );

    $this->current_taxonomies = get_taxonomies($args, 'objects');
    $this->current_taxonomies_names = get_taxonomies($args, 'names');

    $this->current_post_types = get_post_types($args, 'objects');
    $this->current_post_type_names = get_post_types($args, 'names');

    if ($this->offline_per_page > 100 || $this->online_per_page > 100) {

      foreach ($this->current_taxonomies_names as $key => $name) {
        add_filter('rest_' . $name . '_collection_params', array($this, 'set_offline_per_page'), 10, 2);
      }
      foreach ($this->current_post_type_names as $key => $name) {
        add_filter('rest_' . $name . '_collection_params', array($this, 'set_offline_per_page'), 10, 2);
      }
    }
  }

  public function adjust_cpt_rest()
  {
    global $wp_post_types;
    // foreach ($wp_post_types as $key => $post_type) {
    //   if ($key == '') {
    //     $post_type->show_in_rest = true;
    //     $post_type->rest_base = $key;
    //   }
    // }
  }
  public function get_variables()
  {
    $args = array(
      'public' => true,
      'show_in_rest'   => true,
    );
    $this->mkrnwp_existing_settings_options = get_option('mkrnwp_settings_options');
    $this->offline_per_page = intval($this->mkrnwp_existing_settings_options['offline_per_page']);
    $this->online_per_page = intval($this->mkrnwp_existing_settings_options['online_per_page']);

    $this->current_taxonomies = get_taxonomies($args, 'objects');
    $this->current_taxonomies_names = get_taxonomies($args, 'names');

    $this->current_post_types = get_post_types($args, 'objects');
    $this->current_post_type_names = get_post_types($args, 'names');

    foreach ($this->current_taxonomies as $key => $taxonomy) {
      if ($taxonomy->rest_base == null) {
        $taxonomy->rest_base = $taxonomy->name;
      }
    }

    foreach ($this->current_post_types as $key => $post_type) {
      if ($post_type->rest_base == null) {
        $post_type->rest_base = $post_type->name;
      }
    }

    //     if ($this->offline_per_page > 100 || $this->online_per_page > 100) {

    //       foreach ($this->current_taxonomies_names as $key => $name) {
    //         add_filter('rest_' . $name . '_collection_params', array($this, 'set_offline_per_page'), 10, 2);
    //       }
    //       foreach ($this->current_post_type_names as $key => $name) {
    //         add_filter('rest_' . $name . '_collection_params', array($this, 'set_offline_per_page'), 10, 2);
    //       }
    //     }
    $query_args = array(
      'post_type' => $this->current_post_type_names,
      'posts_per_page' => -1
    );
    $this->query_posts =
      get_posts($query_args);

    $start = 0;
    $this->all_terms = array();
    foreach ($this->current_taxonomies_names as $name) {

      $current_terms = get_terms(array(
        'taxonomy' => $name,
      ));

      foreach ($current_terms as $key => $term) {
        if (isset($term->term_id)) {
          $this->all_terms[$term->term_id] = $term->name;
          $start += 1;
        }
      }
    }
    $this->existing_ep_options = isset($_POST['excluded_posts']) ? sanitize_text_field($_POST['excluded_posts']) : (isset($this->mkrnwp_existing_settings_options) ? $this->mkrnwp_existing_settings_options['excluded_posts'] : '');

    $this->existing_options_array = explode(",", $this->existing_ep_options);

    $this->excluded_posts = array_filter($this->query_posts, function ($array) {
      $related_id = array_search($array->ID, $this->existing_options_array);

      if ($related_id === false) {
        return false;
      } else {
        return true;
      }
    }, ARRAY_FILTER_USE_BOTH);

    $this->existing_et_options = isset($_POST['excluded_taxes']) ? sanitize_text_field($_POST['excluded_taxes']) : (isset($this->mkrnwp_existing_settings_options) ? $this->mkrnwp_existing_settings_options['excluded_taxes'] : '');
  }

  public function set_offline_per_page($params)
  {
    if (isset($params['per_page'])) {
      $params['per_page']['maximum'] = $this->offline_per_page > $this->online_per_page ? $this->offline_per_page : $this->online_per_page;
    }
    return $params;
  }
}

// Create an instance of our class to kick off the whole thing
$skeleton = new RNWP();


//Main settings form
function mkrnwp_queries_page()
{

  echo "<h1>" . esc_html__('Settings', 'rnwp') . "</h1>";

  echo "<form action='' method='post'>";

  settings_errors();
  do_settings_sections('mkrnwp-main');
  wp_nonce_field("mkrnwp-main-options", "mkrnwp-main-nonce");
  submit_button();
  echo "</form>";
?>
  <div class='w3-panel'>
    <h3>Copy the code and replace the content of userConfig.js file in config folder with the code below:</h3>
  </div>
  <div class="code-container" style='background-color: #eee;' id="code-container">
    <textarea id="object" class="code" style="width:100%;height:800px"></textarea>
  </div>
<?php
}

/*
Below functions shall control plugin settings. This shall be through getting the required settings 
either from post request (if above submit button was clicked) 
or from options table if there are data stored in the database
or a default app value (for the first time after the plugin is installed)
For more details on each setting, please refer to the below link:
https://boostrand.com/configure-your-app-options-userconfig-js-file-and-rnwp-plugin/
*/

//Option to enable the user to add the link to be connected to the app
function mkrnwp_url_input()
{
  global $skeleton;
  //Get the existing settings related to showing categories
  $mkrnwp_existing_settings_options = get_option('mkrnwp_settings_options');
  $url = $mkrnwp_existing_settings_options['url'];
  $website_domain = get_site_url();
  $value = isset($url) ? $url : $website_domain;
  echo "<input id='url' class='wide-input' type='text' name='url' value='" . $value . "' />";
}

function check_proiorities($array)
{

  if ($array['prior_' . $array['endpoint']]) {
    return true;
  } else {
    return false;
  }
}

//Option to enable the user to choose which post types to show in the app
function mkrnwp_post_types()
{
  global $skeleton;
  $current_post_types = $skeleton->current_post_types;
  //Get the existing settings related to showing tags
  $mkrnwp_existing_settings_options =
    get_option('mkrnwp_settings_options');

  $post_types = $mkrnwp_existing_settings_options['post_types'];

  if (isset($post_types)) {
    $is_any_post_priority = array_filter($post_types, "check_proiorities", ARRAY_FILTER_USE_BOTH);
  } else {
    $is_any_post_priority = array();
  }

  $i = 0;
  $type = "post";
  foreach ($current_post_types as $key => $post_type) {
    if ($post_type->rest_controller_class != 'WP_REST_Attachments_Controller') {
      //Show the option chosen either from the options table or from the post request if the form was submitted
      $checked = (isset($post_types[$key]) ? 'checked' : '');

      $priority_checked = ((isset($post_types[$key]['prior_' . $post_type->rest_base]) && $post_types[$key]['prior_' . $post_type->rest_base] != "") || (count($is_any_post_priority) == 0 && $i == 0)) ?
        'checked' : '';

      $value = (isset($post_types[$key]) ? $post_types[$key]['name'] : $post_type->label);
      echo "<div>
      <label for='checkpost" . $i . "' >Enable " . $post_type->label . " </label><input id='checkpost" . $i . "' class='checkpt' type='checkbox' name='" . $post_type->rest_base . "' value='1' " . $checked . "/>
      <label for='post" . $i . "' >Enter " . $key . " screen name in the app. </label><input id='post" . $i . "' class='restpt' type='text' name='" . $key . "_name' value='" . esc_attr($value) . "' />
      <label for='prior_" . $i . "' >Show first</label><input type='checkbox' id='prior_" . $i . "' class='priorpt' name='prior_" . $post_type->rest_base . "' value='1' " . $priority_checked . " onclick='adjustPriority(" . $i . ")' data-start='0'/>
      </div><hr>";
      $i += 1;
    }
  }
  echo "</div>";
}

//Option to enable the user to choose whether to show authors in search or not
function mkrnwp_taxonomies()
{
  global $skeleton;

  $mkrnwp_existing_settings_options =
    get_option('mkrnwp_settings_options');
  $taxonomies = $mkrnwp_existing_settings_options['taxonomies'];
  $current_post_types
    = $skeleton->current_post_types;
  $current_taxonomies
    = $skeleton->current_taxonomies;

  if (isset($taxonomies)) {
    $is_any_tax_priority = array_filter($taxonomies, function ($array) {

      if ($array['prior_' . $array['endpoint']]) {
        return true;
      } else {
        return false;
      }
    }, ARRAY_FILTER_USE_BOTH);
  } else {
    $is_any_tax_priority = array();
  }
  $cpt_length = count($current_post_types);
  echo "<div>";
  $i = 0;
  $type = "tax";
  foreach ($current_taxonomies as $key => $taxonomy) {
    //Show the option chosen either from the options table or from the post request if the form was submitted
    $checked = (isset($taxonomies[$key]) ? 'checked' : '');
    $value = (isset($taxonomies[$key]) ? $taxonomies[$key]['name'] : $taxonomy->label);
    $priority_checked = ((isset($taxonomies[$key]['prior_' . $taxonomy->rest_base]) && $taxonomies[$key]['prior_' . $taxonomy->rest_base] != "") || (count($is_any_tax_priority) == 0 && $i == 0)) ?
      'checked' : '';

    $new_count = $i + $cpt_length;

    echo "<div><label for='checktax" . $i . "' >Enable " . $taxonomy->label . " </label><input class='checktax' type='checkbox' id='checktax" . $i . "' name='" . $taxonomy->rest_base . "' value='1' " . $checked . "/>
      <label for='" . $key . "' >Enter " . $key . " screen name in the app. </label><input id='tax" . $i . "' class='resttax' type='text' name='" . $key . "_name' value='" . esc_attr($value) . "' />
      <label for='prior_" . $new_count . "' >Show first</label><input type='checkbox' id='prior_" . $new_count . "' class='priortax' name='prior_" . $taxonomy->rest_base . "' value='1' " . $priority_checked . " onclick='adjustPriority(" . $new_count . ")' data-start='" . $cpt_length . "' />";

    //Post types related to the taxonomy to be considered in the app
    $related_post_types = $taxonomy->object_type;
    echo "<select id='related_type_" . $taxonomy->rest_base . "' name='related_type_" . $taxonomy->rest_base . "' class='related_types'>";
    foreach ($related_post_types as $key => $post_type) {
      $selected = $taxonomies[$taxonomy->name]['posttype'] == $current_post_types[$post_type]->rest_base ? 'selected' : '';
      echo "<option value='" . $current_post_types[$post_type]->rest_base . "'" . $selected . " >" . $current_post_types[$post_type]->label . "</option>";
    }

    echo  "</select>";
    echo "</div><hr>";
    $i += 1;
  }

  echo "</div>";
}
// Get the post types to be excluded either from post request or from options table or a preliminary value
function mkrnwp_excluded_posts()
{
  global $skeleton;
  $query_posts = $skeleton->query_posts;
  $excluded_posts = $skeleton->excluded_posts;
  $existing_ep_options = $skeleton->existing_ep_options;

  $type = "posts";
?>
  <div class="dropdown">
    <input type="hidden" name="excluded_posts" id="excluded_posts" value="<?php echo esc_html($existing_ep_options); ?>">
    <button class="dropbtn" id="dropbtnposts">Choose posts to exclude</button><span id="excluded_posts_name">Excluded Posts
      <?php
      foreach ($excluded_posts as $key => $excluded_post) {
      ?>
        <span class="w3-tag w3-red w3-round w3-margin"><?php echo $excluded_post->post_title; ?>
          <span id="dismiss-<?php echo $excluded_post->ID; ?>" class="dashicons dashicons-dismiss" onclick="excludeDismiss(<?php echo $excluded_post->ID; ?>, '<?php echo $type; ?>')">
          </span></span>
      <?php
      }
      ?>
    </span>

    <div id="myDropdownPosts" class="dropdown-content">
      <input type="text" class="wide-input" placeholder="Search.." id="myInputPosts" onkeyup="filterPosts('Posts')" data-type='Posts'>
      <?php
      foreach ($query_posts as $key => $query_post) {
        echo "<a onclick='addExcludedPost(" . $query_post->ID . ")' id='postexl" . $query_post->ID . "' data-type='posts'>" . $query_post->post_title . "</a>";
      }
      ?>
    </div>
  </div>

<?php
}
// Get the taxonomies to be excluded either from post request or from options table or a preliminary value
function mkrnwp_excluded_taxes()
{
  global $skeleton;
  $all_terms = $skeleton->all_terms;
  $existing_et_options = $skeleton->existing_et_options;

  $type = "taxes";
  $existing_et_array = explode(",", $existing_et_options);
?>
  <div class="dropdown">
    <input type="hidden" name="excluded_taxes" id="excluded_taxes" value="<?php echo esc_html($existing_et_options); ?>">
    <button class="dropbtn" id="dropbtntaxes">Choose taxonomies to exclude</button><span id="excluded_taxes_name">Excluded Taxonomies
      <?php
      foreach ($all_terms as $id => $name) {
        $is_excluded = array_search($id, $existing_et_array);
        if ($is_excluded !== false) {
      ?>
          <span class="w3-tag w3-red w3-round w3-margin"><?php echo $name; ?>
            <span id="dismiss-<?php echo $id; ?>" class="dashicons dashicons-dismiss" onclick="excludeDismiss(<?php echo $id; ?>, '<?php echo $type; ?>')">
            </span></span>
      <?php
        }
      }
      ?>
    </span>

    <div id="myDropdownTaxes" class="dropdown-content">
      <input type="text" placeholder="Search.." id="myInputTaxes" onkeyup="filterPosts('Taxes')" data-type='Taxes'>
      <?php
      foreach ($all_terms as $id => $name) {
        echo "<a onclick='addExcludedTax(" . $id . ")' id='taxexcl" . $id . "' data-type='taxes'>" . $name . "</a>";
      }
      ?>
    </div>
  </div>

<?php
}
function mkrnwp_offline_per_page()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = get_option('mkrnwp_settings_options');
  $value = isset($_POST['offline_per_page']) ? sanitize_text_field($_POST['offline_per_page']) : (isset($mkrnwp_existing_settings_options['offline_per_page']) ? $mkrnwp_existing_settings_options['offline_per_page'] : "100");
  echo "<input id='offline_per_page' class='wide-input' type='text' name='offline_per_page' value='" . esc_attr($value) . "' />";
}

function mkrnwp_online_per_page()
{
  global $skeleton;
  $mkrnwp_existing_settings_options =
    get_option('mkrnwp_settings_options');
  $value = isset($_POST['online_per_page']) ? sanitize_text_field($_POST['online_per_page']) : (isset($mkrnwp_existing_settings_options['online_per_page']) ? $mkrnwp_existing_settings_options['online_per_page'] : "5");
  echo "<input id='online_per_page' class='wide-input' type='text' name='online_per_page' value='" . esc_attr($value) . "' />";
}

function mkrnwp_home_page()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $home_page = isset($_POST['home_page']) ? sanitize_key($_POST['home_page']) : (isset($mkrnwp_existing_settings_options['home_page']) ? $mkrnwp_existing_settings_options['home_page'] : "posts");

  $post_selected = $home_page == 'posts' ? "selected" : "";
  $tax_selected =
    $home_page == 'taxes' ? "selected" : "";
  echo "<select id='home_page' name='home_page'><option value='posts' " . $post_selected . " >Posts</option><option value='taxes' " . $tax_selected . " >Taxonomies</option></select>";
}

function mkrnwp_about_page()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $query_posts = $skeleton->query_posts;
  $current_post_types = $skeleton->current_post_types;

  $about_page = isset($_POST['about_page']) ? sanitize_key($_POST['about_page']) : (isset($mkrnwp_existing_settings_options['about_page']) ? $mkrnwp_existing_settings_options['about_page'] : "0");

  $none_selected = $about_page == '0' ? "selected" : "";

  echo "<select id='about_page' name='about_page'><option value='0' " . $none_selected . " >None</option>";
  foreach ($query_posts as $key => $query_post) {
    $selected =
      $about_page == $query_post->ID ? "selected" : "";
    $post_type = $query_post->post_type;
    $endpoint = $current_post_types[$post_type]->rest_base;
    echo "<option value='" . $query_post->ID . "' " . $selected . " data-endpoint='" . $endpoint . "'>" . $query_post->post_title . "</option>";
  }
  echo " </select>";
}

function mkrnwp_show_excerpt()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $show_excerpt =
    isset($_POST['submit']) ? sanitize_key($_POST['show_excerpt']) : (isset($mkrnwp_existing_settings_options['show_excerpt']) ? $mkrnwp_existing_settings_options['show_excerpt'] : "");

  $checked = (@$show_excerpt == "1" ? 'checked' : '');
  echo "<input type='checkbox' name='show_excerpt' id='show_excerpt' value='1' " . $checked . "/>";
}

function mkrnwp_excerpt_max_char()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $value = isset($_POST['excerpt_max_char']) ? sanitize_text_field($_POST['excerpt_max_char']) : (isset($mkrnwp_existing_settings_options['excerpt_max_char']) ? $mkrnwp_existing_settings_options['excerpt_max_char'] : "500");

  echo "<input type='text' name='excerpt_max_char' id='excerpt_max_char' value='" . esc_attr($value) . "' />";
}

function mkrnwp_enable_firebase()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $enable_firebase =
    isset($_POST['submit']) ? sanitize_key($_POST['enable_firebase']) : (isset($mkrnwp_existing_settings_options['enable_firebase']) ? $mkrnwp_existing_settings_options['enable_firebase'] : "");

  $checked = (@$enable_firebase == "1" ? 'checked' : '');
  echo "<input type='checkbox' name='enable_firebase' id='enable_firebase' value='1' " . $checked . "/>";
}

function mkrnwp_enable_banner_ads()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $enable_banner_ads =
    isset($_POST['submit']) ? sanitize_key($_POST['enable_banner_ads']) : (isset($mkrnwp_existing_settings_options['enable_banner_ads']) ? $mkrnwp_existing_settings_options['enable_banner_ads'] : "");

  $checked = (@$enable_banner_ads == "1" ? 'checked' : '');
  echo "<input type='checkbox' name='enable_banner_ads' id='enable_banner_ads' value='1' " . $checked . "/>";
}

function mkrnwp_banner_ads_key()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $value = isset($_POST['banner_ads_key']) ? sanitize_text_field($_POST['banner_ads_key']) : (isset($mkrnwp_existing_settings_options['banner_ads_key']) ? $mkrnwp_existing_settings_options['banner_ads_key'] : "");

  echo "<input type='text' class='wide-input' name='banner_ads_key' id='banner_ads_key' value='" . esc_attr($value) . "' />";
}


function mkrnwp_enable_interstitial_ads()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $enable_interstitial_ads =
    isset($_POST['submit']) ? sanitize_key($_POST['enable_interstitial_ads']) : (isset($mkrnwp_existing_settings_options['enable_interstitial_ads']) ? $mkrnwp_existing_settings_options['enable_interstitial_ads'] : "");

  $checked = (@$enable_interstitial_ads == "1" ? 'checked' : '');
  echo "<input type='checkbox' name='enable_interstitial_ads' id='enable_interstitial_ads' value='1' " . $checked . "/>";
}

function mkrnwp_interstitial_ads_key()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $value = isset($_POST['interstitial_ads_key']) ? sanitize_text_field($_POST['interstitial_ads_key']) : (isset($mkrnwp_existing_settings_options['interstitial_ads_key']) ? $mkrnwp_existing_settings_options['interstitial_ads_key'] : "");

  echo "<input type='text' class='wide-input' name='interstitial_ads_key' id='interstitial_ads_key' value='" . esc_attr($value) . "' />";
}

function mkrnwp_enable_rewarded_ads()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $enable_rewarded_ads =
    isset($_POST['submit']) ? sanitize_key($_POST['enable_rewarded_ads']) : (isset($mkrnwp_existing_settings_options['enable_rewarded_ads']) ? $mkrnwp_existing_settings_options['enable_rewarded_ads'] : "");

  $checked = (@$enable_rewarded_ads == "1" ? 'checked' : '');
  echo "<input type='checkbox' name='enable_rewarded_ads' id='enable_rewarded_ads' value='1' " . $checked . "/>";
}

function mkrnwp_rewarded_ads_key()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $value = isset($_POST['rewarded_ads_key']) ? sanitize_text_field($_POST['rewarded_ads_key']) : (isset($mkrnwp_existing_settings_options['rewarded_ads_key']) ? $mkrnwp_existing_settings_options['rewarded_ads_key'] : "");

  echo "<input type='text' class='wide-input' name='rewarded_ads_key' id='rewarded_ads_key' value='" . esc_attr($value) . "' />";
}

function mkrnwp_featured_image()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $enable_featured_image =
    isset($_POST['submit']) ? sanitize_key($_POST['enable_featured_image']) : (isset($mkrnwp_existing_settings_options['enable_featured_image']) ? $mkrnwp_existing_settings_options['enable_featured_image'] : "");

  $checked = (@$enable_featured_image == "1" ? 'checked' : '');
  echo "<input type='checkbox' name='enable_featured_image' id='enable_featured_image' value='1' " . $checked . "/>";
}

function mkrnwp_fa_installed()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $enable_fa_installed =
    isset($_POST['submit']) ? sanitize_text_field($_POST['enable_fa_installed']) : (isset($mkrnwp_existing_settings_options['enable_fa_installed']) ? $mkrnwp_existing_settings_options['enable_fa_installed'] : "");

  $checked = (@$enable_fa_installed == "1" ? 'checked' : '');
  echo "<input type='checkbox' name='enable_fa_installed' id='enable_fa_installed' value='1' " . $checked . "/><p><a href='https://wordpress.org/plugins/fusion-web-app/'>You can download Fusion Web App plugin from this link.</a></p>";
}

function mkrnwp_scheduled_enabled()
{
  global $mkrnwp_existing_settings_options;
  $scheduled_enabled =
    isset($_POST['submit']) ? sanitize_key($_POST['scheduled_enabled']) : (isset($mkrnwp_existing_settings_options['scheduled_enabled']) ? $mkrnwp_existing_settings_options['scheduled_enabled'] : "");

  $checked = (@$scheduled_enabled == "1" ? 'checked' : '');
  echo "<input type='checkbox' name='scheduled_enabled' id='scheduled_enabled' value='1' " . $checked . "/>";
}

function mkrnwp_notification_frequency()
{
  global $mkrnwp_existing_settings_options;
  $value = isset($_POST['notification_frequency']) ? sanitize_text_field($_POST['notification_frequency']) : (isset($mkrnwp_existing_settings_options['notification_frequency']) ? $mkrnwp_existing_settings_options['notification_frequency'] : "1");

  echo "<input type='text' name='notification_frequency' id='notification_frequency' value='" . esc_attr($value) . "' /><span> days</span>";
}

function mkrnwp_notification_title()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $value = isset($_POST['notification_title']) ? sanitize_text_field($_POST['notification_title']) : (isset($mkrnwp_existing_settings_options['notification_title']) ? $mkrnwp_existing_settings_options['notification_title'] : "We miss you!");

  echo "<input type='text' name='notification_title' id='notification_title' value='" . esc_attr($value) . "' />";
}

function mkrnwp_notification_message()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $value = isset($_POST['notification_message']) ? sanitize_text_field($_POST['notification_message']) : (isset($mkrnwp_existing_settings_options['notification_message']) ? $mkrnwp_existing_settings_options['notification_message'] : "Check our new data");

  echo "<input type='text' class='wide-input' name='notification_message' id='notification_message' value='" . esc_attr($value) . "' />";
}

function mkrnwp_text_direction()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $text_direction = isset($_POST['text_direction']) ? sanitize_key($_POST['text_direction']) : (isset($mkrnwp_existing_settings_options['text_direction']) ? $mkrnwp_existing_settings_options['text_direction'] : "ltr");

  $ltr_selected = $text_direction == 'ltr' ? "selected" : "";
  $rtl_selected =
    $text_direction == 'rtl' ? "selected" : "";
  echo "<select id='text_direction' name='text_direction'><option value='ltr' " . $ltr_selected . " >Left to Right (for most languages)</option><option value='rtl' " . $rtl_selected . " >Right to Left (for languages as Arabic, Urdu, ...etc.)</option></select>";
}

function mkrnwp_phone()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $value = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : (isset($mkrnwp_existing_settings_options['phone']) ? $mkrnwp_existing_settings_options['phone'] : "");

  echo "<input type='text' class='wide-input' name='phone' id='phone' value='" . esc_html($value) . "' />";
}

function mkrnwp_address()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $value = isset($_POST['address']) ? sanitize_text_field($_POST['address']) : (isset($mkrnwp_existing_settings_options['address']) ? $mkrnwp_existing_settings_options['address'] : "");

  echo "<input type='text' class='wide-input' name='address' id='address' value='" . esc_attr($value) . "' />";
}

function mkrnwp_mail()
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $value = isset($_POST['mail']) ? sanitize_email($_POST['mail']) : (isset($mkrnwp_existing_settings_options['mail']) ? $mkrnwp_existing_settings_options['mail'] : "");

  echo "<input type='email' class='wide-input' name='mail' id='mail' value='" . esc_attr($value) . "' />";
}

function adjust_social_array($key)
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  if (isset($_POST['submit']) && $_POST[$key] != "") {
    if ($key == 'whatsapp') {
      return sanitize_text_field($_POST[$key]);
    } else {
      return esc_url_raw($_POST[$key]);
    }
  } else if (!isset($_POST['submit']) && isset($mkrnwp_existing_settings_options['social'][$key])) {
    return $mkrnwp_existing_settings_options['social'][$key];
  } else {
    return "";
  }
}

function mkrnwp_social()
{
  $social_array = ['facebook' => '', 'youtube' => '', 'instagram' => '', 'twitter' => '', 'pinterest' => '', 'facebook-messenger' => '', 'whatsapp' => '', 'telegram' => '', 'snapchat' => '', 'linkedin' => '', 'reddit' => '', 'behance' => '', 'github-circle' => '', 'wordpress' => '', 'quora' => '', 'wechat' => '', 'tumblr' => '', 'qqchat' => '', 'vimeo' => '', 'codepen' => ''];
  // $value = isset($_POST['social']) ? $_POST['social'] : (isset($mkrnwp_existing_settings_options['social']) ? $mkrnwp_existing_settings_options['social'] : $social_array);
  echo "<div class='w3-container'>";
  foreach ($social_array as $icon => $link) {
    $value = adjust_social_array($icon);
    if ($icon == 'whatsapp') {
      echo "<div class='w3-quarter'><input type='text' name='" . $icon . "' id='" . $icon . "' value='" . esc_attr($value) . "' class='social' placeholder='whatsapp incl. country code' /></div>";
    } else {
      echo "<div class='w3-quarter'><input type='text' name='" . $icon . "' id='" . $icon . "' value='" . esc_url_raw($value) . "' class='social' placeholder='" . $icon . "' /></div>";
    }
  }
  echo "</div>";
}

function mkrnwp_add_text_input($input, $initial_value)
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $value = isset($_POST[$input]) ? sanitize_text_field($_POST[$input]) : (isset($mkrnwp_existing_settings_options[$input]) ? $mkrnwp_existing_settings_options[$input] : $initial_value);

  echo "<input type='text' class='wide-input' name='" . $input . "' id='" . $input . "' value='" . esc_attr($value) . "' />";
}

function mkrnwp_home_screen_name()
{
  return mkrnwp_add_text_input('home_screen_name', "Home");
}

function mkrnwp_contact_screen_name()
{
  return mkrnwp_add_text_input('contact_screen_name', 'Contact us');
}
function mkrnwp_about_screen_name()
{
  return mkrnwp_add_text_input('about_screen_name', 'About Us');
}
function mkrnwp_setting_screen_name()
{
  return mkrnwp_add_text_input('setting_screen_name', 'Settings');
}
function mkrnwp_search_screen_name()
{
  return mkrnwp_add_text_input('search_screen_name', 'Search');
}
function mkrnwp_saved_screen_name()
{
  return mkrnwp_add_text_input('saved_screen_name', 'Saved');
}

function mkrnwp_no_internet()
{
  return mkrnwp_add_text_input('no_internet', 'There is no internet connection.');
}

function mkrnwp_online_search()
{
  return mkrnwp_add_text_input('online_search', 'Online Search (More flexible)');
}

function mkrnwp_no_results()
{
  return mkrnwp_add_text_input('no_results', 'No search results found.');
}

function mkrnwp_enable_dark_mode()
{
  return mkrnwp_add_text_input('enable_dark_mode', 'Enable dark mode');
}

function mkrnwp_enable_notifications()
{
  return mkrnwp_add_text_input('enable_notifications', 'Enable notifications');
}


function mkrnwp_saved_offline()
{
  return mkrnwp_add_text_input('saved_offline', 'Enable offline mode');
}

function mkrnwp_check_updates()
{
  return mkrnwp_add_text_input('check_updates', 'Check updates');
}


function mkrnwp_downloading_text()
{
  return mkrnwp_add_text_input('downloading_text', 'Downloading...');
}

function mkrnwp_done_text()
{
  return mkrnwp_add_text_input('done_text', 'Done.');
}

function mkrnwp_see_product_page()
{
  return mkrnwp_add_text_input('see_product_page', 'See Product Page');
}

function mkrnwp_download_alert_title()
{
  return mkrnwp_add_text_input('download_alert_title', 'Download data');
}


function mkrnwp_download_alert_message()
{
  return mkrnwp_add_text_input('download_alert_message', 'Do you want to download the data?');
}


function mkrnwp_delete_alert_title()
{
  return mkrnwp_add_text_input('delete_alert_title', 'Delete data');
}

function mkrnwp_delete_alert_message()
{
  return mkrnwp_add_text_input('delete_alert_message', 'Do you want to delete the data?');
}


function mkrnwp_alert_yes()
{
  return mkrnwp_add_text_input('alert_yes', 'Yes');
}


function mkrnwp_alert_no()
{
  return mkrnwp_add_text_input('alert_no', 'No');
}

function mkrnwp_add_color_input($input, $initial_value)
{
  global $skeleton;
  $mkrnwp_existing_settings_options = $skeleton->mkrnwp_existing_settings_options;
  $value = isset($_POST[$input]) ? sanitize_hex_color($_POST[$input]) : (isset($mkrnwp_existing_settings_options[$input]) ? $mkrnwp_existing_settings_options[$input] : $initial_value);

  echo "<input type='color' name='" . $input . "' id='" . $input . "' value='" . esc_attr($value) . "' />";
}

function mkrnwp_background_light()
{
  return mkrnwp_add_color_input('background_light', '#f4eeff');
}

function mkrnwp_text_light()
{
  return mkrnwp_add_color_input('text_light', '#424874');
}

function mkrnwp_text_alt_light()
{
  return mkrnwp_add_color_input('text_alt_light', '#a6b1e1');
}


function mkrnwp_container_light()
{
  return mkrnwp_add_color_input('container_light', '#c3cafd');
}

function mkrnwp_switch_thumb_light()
{
  return mkrnwp_add_color_input('switch_thumb_light', '#424874');
}

function mkrnwp_switch_on_light()
{
  return mkrnwp_add_color_input('switch_on_light', '#a6b1e1');
}

function mkrnwp_switch_off_light()
{
  return mkrnwp_add_color_input('switch_off_light', '#dcd6f7');
}

function mkrnwp_background_dark()
{
  return mkrnwp_add_color_input('background_dark', '#424874');
}


function mkrnwp_text_dark()
{
  return mkrnwp_add_color_input('text_dark', '#f4eeff');
}

function mkrnwp_text_alt_dark()
{
  return mkrnwp_add_color_input('text_alt_dark', '#dcd6f7');
}

function mkrnwp_container_dark()
{
  return mkrnwp_add_color_input('container_dark', '#5d659f');
}

function mkrnwp_switch_thumb_dark()
{
  return mkrnwp_add_color_input('switch_thumb_dark', '#a6b1e1');
}

function mkrnwp_switch_on_dark()
{
  return mkrnwp_add_color_input('switch_on_dark', '#dcd6f7');
}

function mkrnwp_switch_off_dark()
{
  return mkrnwp_add_color_input('switch_off_dark', '#f4eeff');
}



//Option to enable the user to delete plugin data if the plugin was uninstalled
function mkrnwp_delete_all_plugin_data()
{
  //Get the existing settings related to deleting the data
  $mkapis_existing_settings_options = get_option('mkrnwp_settings_options');
  $delete_data = $mkapis_existing_settings_options['delete_plugin_data'];

  //Show the option chosen either from the options table or from the post request if the form was submitted
  $checked = (@$delete_data === "1" ? 'checked' : '');
  echo "<input type='checkbox' name='delete_plugin_data' value='1' " . $checked . "/>";
}
