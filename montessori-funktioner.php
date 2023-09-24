<?php

/**
 * Plugin Name: Skärgårdens Montessori funktioner
 * Plugin URI: https://github.com/unlikelyobjects/montessori-funktioner
 * Description: Skräddarsydda funktioner för skargardensmontessori.se
 * Version: 1.0
 */


/**
 * Remove Edit Link from pages
 *
 */
add_filter('edit_post_link', '__return_false');


/**
 * Add footer menu
 *
 */

function mont_footer_menu()
{
    register_nav_menu('mont-footer-menu', __('Sidfoten'));
}
add_action('init', 'mont_footer_menu');


/**
 * Customize the footer
 *
 */

remove_action('genesis_footer', 'genesis_do_footer');
add_action('genesis_footer', 'mont_custom_footer');
function mont_custom_footer()
{

    echo '<h3>Skärgårdens Montessoriförskola</h3>';

    echo '<div class="footer-flex-row">';

    // Custom fields from Inställningar options panel
    echo '<p class="footer-flex-row-item">' . get_field('adress', 'option') . '</p><br />';

    echo '<p class="footer-flex-row-item">';
    if (have_rows('telefonnummer', 'option')) :
        while (have_rows('telefonnummer', 'option')) : the_row();
            $label = get_sub_field('etikett') . ' ';
            $number = get_sub_field('telnr');
            echo $label . ' <a href="tel:' . $number . '">' . $number . '</a><br />';
        endwhile;
    endif;
    echo '‬</p>';

    echo '<div class="footer-flex-row-item">';
    wp_nav_menu(array(
        'menu' => 'Sidfoten',
        'fallback_cb' => false // Do not fall back to wp_page_menu()
    ));
    echo '</div><br />';

    if (have_rows('andra_lankar', 'option')) :
        echo '<p class="footer-flex-row-item">';
        while (have_rows('andra_lankar', 'option')) : the_row();
            echo '<a href="' . get_sub_field('lank') . '">' . get_sub_field('etikett') . '</a>';
        endwhile;
        echo '</p>';
    endif;

    echo '</div>';
}


/**
 * Restrict content based on URI segment
 *
 */

add_action('genesis_before', 'restrict_content');

// Check if user is logged in when accessing /kunskapsbas
function restrict_content()
{
    // Get the URL segments
    $uriSegments = explode("/", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

    // Check the first segment
    if ($uriSegments[1] == 'kunskapsbas') {

        // Check if user is logged in
        if (!is_user_logged_in()) {

            // User is not logged in. Meta refresh and redirect to login modal
            echo '
        <meta http-equiv="refresh" content="0; url = /#login" />';

            // Prevent page content from loading
            exit();
        } else {
            // User is logged in; do nothing.
        }
    }
}


/**
 * Create child pages navigation in sidebar.
 * List is added before the sidebar widget area.
 *
 */

add_action('genesis_before_sidebar_widget_area', 'mont_list_child_pages', 5);

function mont_list_child_pages()
{

    global $post; // global variable $post

    if (is_page() && $post->post_parent) {
        $children = wp_list_pages('sort_column=menu_order&title_li=&child_of=' . $post->post_parent . '&echo=0');
    } else {
        $children = wp_list_pages('sort_column=menu_order&title_li=&child_of=' . $post->ID . '&echo=0');
    }
    if ($children) {
        echo '<ul class="subpage_nav">';
        echo $children;
        echo '</ul>';
    }
}


/**
 * Create custom post type: Matsedel
 *
 */

function matsedel_init()
{
    register_post_type(
        'matsedel',
        array(
            'labels' => array(
                'name' => __('Matsedlar'),
                'singular_name' => __('Matsedel')
            ),
            'public' => true,
            'has_archive' => false,
            'query_var' => true,
        )
    );
}
add_action('init', 'matsedel_init');


// /**
// * Delete old Matsedel posts
// *
// **/

// /* Add monthly interval to the schedules (since WP doesnt provide it from the start) */
// add_filter('cron_schedules', 'cron_add_monthly');
// function cron_add_monthly($schedules)
// {
// $schedules['monthly'] = array(
// 'interval' => 2419200,
// 'display' => __('Once per month')
// );
// return $schedules;
// }

// /* Add the scheduling if it doesnt already exist */
// add_action('wp', 'setup_schedule');
// function setup_schedule()
// {
// if (!wp_next_scheduled('monthly_pruning')) {
// wp_schedule_event(time(), 'monthly', 'monthly_pruning');
// }
// }

// /* Add the function that takes care of removing all rows with post_type=post that are older than 30 days */
// add_action('monthly_pruning', 'remove_old_posts');
// function remove_old_posts()
// {
// global $wpdb;
// $wpdb->query($wpdb->prepare("DELETE FROM wp_posts WHERE post_type='matsedel' AND post_date < DATE_SUB(NOW(), INTERVAL 30 DAY);")); // }

// Prevent Author indexing
add_action(
    'template_redirect',
    function () {
        if (isset($_GET['author']) || is_author()) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
        }
    },
    1
);
add_filter('author_link', function () {
    return '#';
}, 99);
add_filter('the_author_posts_link', '__return_empty_string', 99);
