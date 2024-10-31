<?php
add_action('rest_api_init', 'mkrnwp_rest_api_for_search_filter_add_filters');
/**
 * Register search query API route and entry point
 */
function mkrnwp_rest_api_for_search_filter_add_filters()
{
    // Register new route for search queries
    register_rest_route('search/v1', 'search', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'mkrnwp_search_callback'
    ));
}

/**
 * Generate results for the /wp-json/search/v1/search route.
 *
 * @param WP_REST_Request $request Request infrormation.
 */
function mkrnwp_search_callback(WP_REST_Request $request)
{

    // Get API query parameters
    $parameters = $request->get_query_params();
    // Default query parameters
    $supported_post_types = get_post_types(array('exclude_from_search' => false), 'names');
    $args = array('posts_per_page' => 10, 'paged' => 0, 'post_type' => $supported_post_types);

    // Allowed posts per page (posts_per_page) value is from 1 to 10000
    if (isset($parameters['per_page']) && ((int)$parameters['per_page'] >= 1 && (int)$parameters['per_page'] <= 10000)) {
        $args['per_page'] = intval($parameters['per_page']);
    }

    if (isset($parameters['paged']) &&  (int) $parameters['paged'] >= 0) {
        $args['paged'] = intval($parameters['paged']);
    }

    $id_array = array();
    for ($x = 0; $x < 1000; $x++) {
        if (null !==  $parameters['id' . $x]) {
            array_push($id_array, $parameters['id' . $x]);
        } else {
            break;
        }
    }
    $args['post__in'] = $id_array;


    // Search query term
    $args['s'] = $parameters['keyword'];

    // Run search query
    $search_query = new WP_Query($args);

    // Collect results and preare response	    
    $posts = array();
    while ($search_query->have_posts()) {
        $search_query->the_post();
        array_push($posts, array(
            'id' => get_the_id(),
            'title' => array(
                'rendered' => get_the_title()
            ),
            'content' => array(
                'rendered' => get_the_content()
            ),
            'excerpt' => array('rendered' => get_the_excerpt()),
            'featured_media' => get_post_thumbnail_id(),
            '_embedded' => array('wp:featuredmedia' => array(array('source_url' => get_the_post_thumbnail_url())))
        ));
    }

    // Return search results or error if nothing found.
    if (!empty($posts)) {
        return $posts;
    } else {
        return new WP_Error('No results', 'Nothing found');
    }
}
