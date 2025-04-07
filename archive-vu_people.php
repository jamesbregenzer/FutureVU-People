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
$context['site'] = new \Timber\Site();
$context['footer'] = 'default'; // or whatever your footer style is
$context['bodyClass'] = 'custom-plugin-page';
$context['vu_close_head_tag_section'] = ''; // or actual content
$context['vu_close_body_tag_section'] = ''; // or actual content

// Start output buffer
ob_start();
?>
<style type="text/css">
    /**
     * @todo move css to an asset fiele later
     */
    .container{
        max-width: 85rem;
        margin: auto;
    }
    .people-row{
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 20px;
    }
    .people-row .people-photo img{
        max-width: 100%;
    }
    .people-row .people-swatch{
        width: 23%;
    }
    .people-row .people-stats{
        background: #EEE;
        padding: 10px;
    }
    @media(max-width: 980px){
        .people-row .people-swatch{
            width: 30%;
        }
    }
    @media(max-width: 600px){
        .people-row .people-swatch{
            width: 45%;
        }
    }
</style>
<div class="container">
        <article class="primary-content col-sm-12">
            <div class="panel-body">
                <?php $blog_details = get_blog_details(); ?>
                <h2> <?php echo esc_html($blog_details->blogname); ?> Members </h2>
                <?php
                $args = array(
                    'post_type' => 'person',
                    'depth' => 1,
                    'posts_per_page' => -1,
                    'post_status' => array('publish'),
                    'meta_key' => 'last_family_name',
                    'orderby' => 'meta_value',
                    'order' => 'ASC',
                );
                $wp_query = new WP_Query($args);
                ?>
                <div class="people-row">
                    <?php if($wp_query->have_posts()):
                        while($wp_query->have_posts()):
                            $wp_query->the_post();
                    ?>
                        <div class="col-xs-6 col-sm-4 col-md-3 people-swatch">
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
                                    <?php if(have_rows('title_and_department')): ?>
                                        <?php while(have_rows('title_and_department')): the_row(); ?>
                                            <?php if(get_sub_field('title__position')): ?>
                                                <span class="people-title-position"><?php the_sub_field('title__position'); ?></span>
                                                <?php if(get_sub_field('department__center__office')): ?>
                                                    , <span class="people-title-department"><?php the_sub_field('department__center__office'); ?></span>
                                                <?php endif; ?>
                                                <br />
                                                <?php break; ?>
                                            <?php endif; ?>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="people-email">
                                    <?php if(get_field('email')): ?>
                                        <a href="mailto:<?php the_field('email'); ?>">
                                            <i class="fa fa-envelope" aria-hidden="true"></i> <?php the_field('email'); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; endif; ?>
                </div>
                <?php wp_reset_postdata(); ?>
        </article>
    </div>
</div>
<?php
// Store output buffer
$context['plugin_content'] = ob_get_clean();

// Render your twig template that extends the theme
Timber::render('plugin-page.twig', $context);
