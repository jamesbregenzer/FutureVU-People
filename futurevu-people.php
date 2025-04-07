<?php
/*
 * Plugin Name: FutureVU People
 * Plugin URI: https://vanderbilt.edu/web
 * Description: Create a custom post type VU People. This plug in is for managing Vanderbilt people by site.
 * Version: 1.0
 * Author: Web Comm
 * Author URI: https://vanderbilt.edu/web
 *
 */

add_action('init', 'create_vu_people');

function create_vu_people() {
    $labels = array (
        'name'  =>  _x('People', 'post type general name'),
        'singular_name' =>   _x('People', 'post type singular name'),
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Person',
        'edit' => 'Edit',
        'edit_item' => 'Edit Person',
        'new_item' => 'New Person',
        'view' => 'View',
        'view_item' => 'View Person',
        'search_items' => 'Search Person',
        'not_found' => 'No Person Found',
        'not_found_in_trash' => 'No Person found in Trash',
        'parent' => 'Parent Person',

    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'menu_position' => 5,
        'supports' => array('title', 'thumbnail', 'custom-fields'),
        'taxonomies' => array('post_tag','category'),
        'menu_icon' => 'dashicons-welcome-learn-more',
        'has_archive' => true,
    );

    register_post_type( 'person', $args);
}

add_filter('timber/locations', function($paths) {
    $paths[] = plugin_dir_path(__FILE__) . 'templates';
    return $paths;
});

//force using template
add_filter('template_include', 'include_vu_person_template_function', 1);

function include_vu_person_template_function($template_path) {

    if ( get_post_type() == 'vuperson') {

        if( is_single()) {
            // server theme file from the plugin
            $template_path = plugin_dir_path( __FILE__ ) . '/single-vu_people.php';

        }

        if( is_archive()) {
            $template_path = plugin_dir_path( __FILE__ ) . '/archive-vu_people.php';
        }
    }

    return $template_path;
}

//add image size
add_image_size( 'img-300-200', 300, 200, true );
add_image_size( 'img-300-300', 300, 300, true );
add_image_size( 'img-150-150', 150, 150, true );
add_image_size( 'img-108-144-list', 108, 144, true );
add_image_size( 'img-142-190-grid', 142, 190, true );


//Add Shortcode to support display people by tag

add_shortcode('VUPeople', 'shortcode_display_vu_people_by_tag');

function shortcode_display_vu_people_by_tag( $atts)
{

    $shortcode_attributes = shortcode_atts(array(
        'tag' => '',
        'style' => 'list',
        'title' => 'show',
    ), $atts);

    wp_reset_query();

    //query people post by tags
    $args = array(
        'post_type' => 'person',
        'depth' => 1,
        'posts_per_page' => -1,
        'post_status' => array('publish'),
        'meta_key' => 'last_family_name',
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'tag_slug__in' => $shortcode_attributes['tag'],

    );

    $wp_query = new WP_Query($args);

    $shortcode_string = '';

    if ($wp_query->have_posts()) {
        if($shortcode_attributes['style'] == 'grid'){
	        
	        if($shortcode_attributes['title'] != 'show'){
		        $shortcode_string = '';
	        } else {
		        $shortcode_string = '<h2>' . $shortcode_attributes['tag'] . '</h2>';
	        }
	        
            

            $shortcode_string .= '<div class="row">';
            while ($wp_query->have_posts()) {
                $wp_query->the_post();

                $shortcode_string .= '<div class="col-xs-6 col-sm-4 col-md-3 people-swatch">';

                $shortcode_string .= '<div class="people-photo">';
                $shortcode_string .= '<a href="' . get_the_permalink() . '">';
                if (has_post_thumbnail()) {
                    $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), "img-142-190-grid" );
                    $shortcode_string .= '<img src="' . $thumbnail[0] . '" alt="image_thumb" />';
                } else {
                    //$shortcode_string .= '<img src="' . get_stylesheet_directory_uri() . '/default-person-pic-150.png' . '"/>';
                    $shortcode_string .= '<img src="' . plugins_url('images/default-person-pic-150.png', __FILE__) . '"/>';
                }
                $shortcode_string .= '</a>';
                $shortcode_string .= '</div>';

                $shortcode_string .= '<div class="people-stats">';

                $shortcode_string .= '<strong class="people-name">';
                $shortcode_string .= '<a href="' . get_the_permalink() . '">';
                $shortcode_string .= get_field('first_given_name') . ' ' . get_field('middle_name_or_initial') . ' ' . get_field('last_family_name');
                
                if(get_field('suffix__credentials') && !empty(get_field('suffix__credentials'))) {
                    $shortcode_string .= ', ' . get_field('suffix__credentials');
                }

                $shortcode_string .= '</a>';
                $shortcode_string .= '</strong>';

                $shortcode_string .= '<div class="people-title">';

                if (have_rows('title_and_department')) {
                    while (have_rows('title_and_department')) {
                        the_row();

                        $have_title = 0;
                        $have_office = 0;

                        if (get_sub_field('title__position') !== '') {
                            $shortcode_string .= '<span class="people-title-position">' . get_sub_field('title__position').'</span>';
                            $have_title = 1;
                        }

                        if ($have_title && get_sub_field('department__center__office') !== ''){
                            $shortcode_string .= ', ' . '<span class="people-title-department">' . get_sub_field('department__center__office') . '</span><br />';
                            $have_office = 1;
                        } elseif (get_sub_field('department__center__office') !== '') {
                            $shortcode_string .= '<span class="people-title-department">' . get_sub_field('department__center__office') . '</span><br />';
                            $have_office = 1;
                        }

                        if ($have_title && !$have_office){
                            $shortcode_string .= '<br />';
                        }

                        if ($have_title || $have_office){
                            //we only want to show one title/office
                            break;
                        }

                    }
                }
                $shortcode_string .= '</div>';

                $shortcode_string .= '<div class="people-email">';
                if (get_field('email')) {
                    $shortcode_string .= '<a href="mailto:' . get_field('email') . '">' . '<i class="fa fa-envelope" aria-hidden="true"></i> '  . '</a>';
                }

                //*
                if (have_rows('phone_numbers')) {
                    while (have_rows('phone_numbers')) {
                        the_row();
                        if (get_sub_field('number')) {
                            $shortcode_string .= ' <a style="margin-left: 10px;" href="tel: ' . get_sub_field('number') . '">' . '<i class="fa fa-phone" aria-hidden="true"></i> ' . '</a>';
                            break;
                        }

                    }
                }
                //*/
                $shortcode_string .= '</div>';


                $shortcode_string .= '</div>'; //close of people-stats div

                $shortcode_string .= '</div>'; //close of people-swatch

            }

            $shortcode_string .= '</div>'; //close of row div
        } else {
            //default is list style
            if($shortcode_attributes['title'] != 'show'){
		        $shortcode_string = '';
	        } else {
		        $shortcode_string = '<h2>' . $shortcode_attributes['tag'] . '</h2>';
	        }

            while ($wp_query->have_posts()) {
                $wp_query->the_post();

                $shortcode_string = $shortcode_string . '<div class="media"> ';
                $shortcode_string .= '<div class="media-left">';
                $shortcode_string .= '<a href="' . get_the_permalink() . '">';
                if (has_post_thumbnail()) {
                    $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), "img-108-144-list" );
                    $shortcode_string .= '<img src="' . $thumbnail[0] . '" alt="image_thumb" />';
                } else {
                    //$shortcode_string .= '<img src="' . get_stylesheet_directory_uri() . '/default-person-pic-150.png' . '"/>';
                    $shortcode_string .= '<img src="' . plugins_url('images/default-person-pic-150.png', __FILE__) . '"/>';
                }
                $shortcode_string .= '</a>';
                $shortcode_string .= '</div>';

                $shortcode_string .= '<div class="media-body">';
                $shortcode_string .= '<strong class="media-heading">';
                $shortcode_string .= '<a href="' . get_the_permalink() . '">';
                $shortcode_string .= get_field('first_given_name') . ' ' . get_field('middle_name_or_initial') . ' ' . get_field('last_family_name');
                
                if(get_field('suffix__credentials') && !empty(get_field('suffix__credentials'))) {
                    $shortcode_string .= ', ' . get_field('suffix__credentials');
                }

                $shortcode_string .= '</a>';
                $shortcode_string .= '</strong>';

                $shortcode_string .= '<p class="person-title">';

                if (have_rows('title_and_department')) {
                    while (have_rows('title_and_department')) {
                        the_row();

                        $shortcode_string .= get_sub_field('title__position');

                        if (get_sub_field('title__position') !== '' && get_sub_field('department__center__office') !== '') {
                            $shortcode_string .= ', ';
                        }

                        $shortcode_string .= get_sub_field('department__center__office');

                        $shortcode_string .= '<br />';
                    }
                }
                $shortcode_string .= '</p>';

                if (get_field('brief_description')) {
                    $shortcode_string .= '<p>';
                    $shortcode_string .= get_field('brief_description');
                    $shortcode_string .= '</p>';
                }

                $shortcode_string .= '<ul>';

                if(have_rows('address')){
                    $shortcode_string .= '<li>';
                    while(have_rows('address')){
                        the_row();
                        if(get_sub_field('building') || get_sub_field('street_address')) {
                            $shortcode_string .= '<p>';
                            $shortcode_string .= '<i class="fa fa-building-o" aria-hidden="true"></i>: ';

                            if(get_sub_field('room__suite') && !empty(get_sub_field('room__suite')) || get_sub_field('building') && !empty(get_sub_field('building'))) {
                                $shortcode_string .= get_sub_field('room__suite') . ' ' . get_sub_field('building') . '<br />';
                            }
                            
                            if(get_sub_field('street_address')){
                                $shortcode_string .= get_sub_field('street_address') . '<br />';
                            }
                            if(get_sub_field('street_address_2')){
                                $shortcode_string .= get_sub_field('street_address_2') . '<br />';
                            }
                            if(get_sub_field('city')){
                                $shortcode_string .= get_sub_field('city') . ', ' . get_sub_field('state') . ' - ' . get_sub_field('zip');
                            }
                            $shortcode_string .= '</p>';
                        }
                    }

                    $shortcode_string .= '</li>';
                }

                if (get_field('email')) {
                    $shortcode_string .= '<li> <i class="fa fa-envelope" aria-hidden="true"></i>: <a href="mailto:' . get_field('email') . '"> ' . get_field('email') . '</a></li>';
                }
                if (have_rows('phone_numbers')) {
                    while (have_rows('phone_numbers')) {
                        the_row();
                        if (get_sub_field('number')) {
                            $shortcode_string .= '<li> <i class="fa fa-phone" aria-hidden="true"></i>: <a href="tel: ' . get_sub_field('number') . '"> ' . get_sub_field('number') . '</a></li>';
                        }
                    }
                }
                if (get_field('more_information_link')) {
                    $shortcode_string .= '<li> <i class="fa fa-info" aria-hidden="true"></i>: <a href="' . get_field('more_information_link') . '" target="_blank"> ' . get_field('more_information_link') . '</a></li>';
                }
                if (get_field('lab_website')) {
	                $shortcode_string .= '<li> <i class="fa fa-external-link" aria-hidden="true"></i>: <a href="' . get_field('lab_website') . '" target="_blank"> ' . get_field('lab_website') . '</a></li>';
                }
                $shortcode_string .= '</ul>';

                $shortcode_string .= '</div>';
                $shortcode_string .= '<hr />';
                $shortcode_string .= '</div>';

            }
        }

        wp_reset_query();

    } else {
        $shortcode_string = '<p>There is no people with ' . $shortcode_attributes['tag'] . ' tag</p>';
    }

    return $shortcode_string;
}


