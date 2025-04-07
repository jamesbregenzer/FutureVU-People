<?php
// Exit if accessed directly
defined('ABSPATH') || exit;

use Timber\Timber;
use Timber\Site;

if (!class_exists('Timber')) {
    echo 'Timber not loaded';
    exit;
}

$context = Timber::context();
$context['theme_mods'] = get_theme_mods();
$context['site'] = new Site();
$context['footer'] = 'default';
$context['bodyClass'] = 'custom-plugin-page';
$context['vu_close_head_tag_section'] = '';
$context['vu_close_body_tag_section'] = '';

$paged = get_query_var('paged') ? get_query_var('paged') : 1;

$args = array(
    'post_type' => 'person',
    'depth' => 1,
    'posts_per_page' => 12,
    'paged' => $paged,
    'post_status' => array('publish'),
    'meta_key' => 'last_family_name',
    'orderby' => 'meta_value',
    'order' => 'ASC',
);

$wp_query = new WP_Query($args);

// Start output buffer
ob_start();
?>
<style type="text/css">
    .container {
        max-width: 85rem;
        margin: auto;
        padding: 2rem;
    }

    .people-row {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .people-swatch {
        flex: 1 1 calc(25% - 20px);
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 6px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .people-photo img {
        width: 100%;
        height: auto;
        display: block;
    }

    .people-stats {
        padding: 10px;
        font-size: 0.9rem;
    }

    .people-name {
        margin: 0 0 5px;
        font-size: 1rem;
        font-weight: bold;
    }

    .people-title,
    .people-email {
        font-size: 0.85rem;
        color: #333;
    }

    .page-numbers{
        display: flex;
        margin: auto;
        justify-content: center;
    }

    @media (max-width: 980px) {
        .people-swatch {
            flex: 1 1 calc(33.333% - 20px);
        }
    }

    @media (max-width: 600px) {
        .people-swatch {
            flex: 1 1 calc(50% - 20px);
        }
    }

    .pagination {
        margin-top: 2rem;
        text-align: center;
    }

    .pagination a,
    .pagination span {
        display: inline-block;
        margin: 0 5px;
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-radius: 3px;
        text-decoration: none;
        color: #333;
    }

    .pagination .current {
        background-color: #0073aa;
        color: #fff;
        border-color: #0073aa;
    }

    .list-title{
        text-transform: capitalize;
        margin-bottom: 20px;
    }
</style>

<div class="container">
    <article class="primary-content">
        <div class="panel-body">
            <?php $blog_details = get_blog_details(); ?>
            <h2 class="list-title"><?php echo esc_html($blog_details->blogname); ?> Members</h2>

            <div class="people-row">
                <?php if ($wp_query->have_posts()):
                    while ($wp_query->have_posts()):
                        $wp_query->the_post(); ?>
                        <div class="people-swatch">
                            <div class="people-photo">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()): ?>
                                        <?php the_post_thumbnail('img-142-190-grid'); ?>
                                    <?php else: ?>
                                        <img class="media-object img-thumbnail" src="<?php echo plugins_url('images/default-person-pic-150.png', __FILE__); ?>" />
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="people-stats">
                                <h4 class="people-name">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_field('first_given_name'); ?> <?php the_field('middle_name_or_initial'); ?> <?php the_field('last_family_name'); ?>
                                    </a>
                                </h4>
                                <div class="people-title">
                                    <?php if (have_rows('title_and_department')):
                                        while (have_rows('title_and_department')): the_row();
                                            if (get_sub_field('title__position')): ?>
                                                <span class="people-title-position"><?php the_sub_field('title__position'); ?></span>
                                                <?php if (get_sub_field('department__center__office')): ?>
                                                    , <span class="people-title-department"><?php the_sub_field('department__center__office'); ?></span>
                                                <?php endif; ?><br />
                                                <?php break;
                                            endif;
                                        endwhile;
                                    endif; ?>
                                </div>
                                <div class="people-email">
                                    <?php if (get_field('email')): ?>
                                        <a href="mailto:<?php the_field('email'); ?>">
                                            <i class="fa fa-envelope" aria-hidden="true"></i> <?php the_field('email'); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile;
                endif; ?>
            </div>

            <?php
            // Pagination
            $big = 999999999;
            $pagination = paginate_links(array(
                'base'    => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                'format'  => '?paged=%#%',
                'current' => max(1, $paged),
                'total'   => $wp_query->max_num_pages,
                'type'    => 'list'
            ));

            if ($pagination) {
                echo '<div class="pagination">' . $pagination . '</div>';
            }

            wp_reset_postdata();
            ?>
        </div>
    </article>
</div>
<?php
// Store output buffer
$context['plugin_content'] = ob_get_clean();

// Render with your Twig template
Timber::render('plugin-page.twig', $context);
