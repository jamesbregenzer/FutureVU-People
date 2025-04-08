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

// Start output buffer
ob_start();
?>
<style type="text/css">
    .container {
        max-width: 85rem;
        margin: auto;
        padding: 2rem;
    }
    .pagetitle{
        margin-top: 30px;
        font-size: 30px;
    }
    .single-people-card{
        max-width: 50%;
        margin: auto;
    }
    .people-designations{
        text-align: center;
        display: block;
    }
    .people-title{
        text-align: center;
        font-size: 50px;
        margin-bottom: 50px;
    }
    .people-meta{
        margin-top: 50px;
        margin-left: 0;
        padding-left: 0;
    }

    @media(max-width: 980px){
        .single-people-card{
            max-width: 85%;
            margin: auto;
        }
    }
</style>

    <div class="panel panel-default col-sm-9">
        <div class="container">
            <article class="primary-content col-sm-12">
                <div class="panel-body single-people-card">

                    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                        <?php
                            $shortcode_string  = "";
                        ?>
                        <?php edit_post_link( __( 'Edit', 'vanderbilt_brand' ), '<span class="edit-link">', '</span>' ); ?>

                        <!-- information section 12-->
                        <div class="">
                            <div class="pull-right">
                                <span class="people-designations">
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
                                </span>
                                <h1 class="people-title"><?php the_title(); ?></h1>
                                <?php if (has_post_thumbnail()): ?>
                                <?php 
                                if (has_post_thumbnail()) {
                                    $thumb_id = get_post_thumbnail_id(get_the_ID());
                                    $thumb_caption = wp_get_attachment_caption($thumb_id);

                                    echo '<figure>';
                                    the_post_thumbnail([300, 300], ['class' => 'media-object img-thumbnail']);
                                    if ($thumb_caption) {
                                        echo '<figcaption>' . esc_html($thumb_caption) . '</figcaption>';
                                    }
                                    echo '</figure>';
                                }   ?>
                                <?php else: ?>
                                    <img class="media-object img-thumbnail" src="<?php echo plugins_url('images/default-person-pic.png', __FILE__); ?>" />
                                <?php endif; ?>
                            </div>
                            <div class="lab-person-info">
                                
                                <p>
                                    <?php if(have_rows('title_and_department')): ?>
                                        <?php while(have_rows('title_and_department')):
                                            the_row();

                                            $have_title = 0;
                                            $have_office = 0;

                                            if(get_sub_field('title__position')) {
                                                if($shortcode_string) {
                                                    $shortcode_string  .= get_sub_field('title__position');
                                                }
                                                $have_title = 1;
                                            }

                                            if($have_title && get_sub_field('department__center__office')){
                                                if($shortcode_string ){
                                                    $shortcode_string .= ', ' . get_sub_field('department__center__office') . '<br />';
                                                }
                                                $have_office = 1;
                                            } else if (get_sub_field('department__center__office')) {
                                                $shortcode_string .= get_sub_field('department__center__office') . '<br />';
                                                $have_office = 1;
                                            }

                                            if($have_title && !$have_office){
                                                $shortcode_string .= '<br />';
                                            }
                                        ?>


                                            <?php 
                                                if($shortcode_string )
                                                    echo $shortcode_string;
                                                $shortcode_string = '';
                                            ?>
<!--                                            --><?php //the_sub_field('title__position'); ?><!--, --><?php //the_sub_field('department__center__office'); ?>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </p>

                                <ul class="people-meta">
                                    <?php if(get_field('email')): ?>
                                        <li> <i class="fa fa-envelope" aria-hidden="true"></i> : <a href="mailto:<?php the_field('email'); ?>"> <?php the_field('email'); ?> </a></li>
                                    <?php endif; ?>
                                    <?php if(have_rows('phone_numbers')): ?>
                                        <?php while(have_rows('phone_numbers')): the_row(); ?>
                                            <?php if(get_sub_field('number')): ?>
                                            <li> <i class="fa fa-phone" aria-hidden="true"></i> : <a href="tel:<?php the_sub_field('number'); ?>"> <?php the_sub_field('number') ?> </a> </li>
                                            <?php endif; ?>
                                        <?php endwhile; ?>
                                    <?php endif; ?>

                                    <?php if(have_rows('address')): ?>
                                        <?php while(have_rows('address')): the_row(); ?>
                                            <?php if(get_sub_field('building') || get_sub_field('street_address')): ?>
                                                <li> <i class="fa fa-building-o" aria-hidden="true"></i> :
                                                	
                                                	<?php if(get_sub_field('room__suite') || get_sub_field('building')): ?>
                                                        <?php the_sub_field('room__suite'); ?> <?php the_sub_field('building'); ?> <br />
                                                    <?php endif; ?>
                                                
                                                    <?php if(get_sub_field('street_address')): ?>
                                                        <?php the_sub_field('street_address'); ?> <br />
                                                    <?php endif; ?>
                                                    <?php if(get_sub_field('street_address_2')): ?>
                                                        <?php the_sub_field('street_address_2'); ?> <br />
                                                    <?php endif; ?>
                                                    <?php if(get_sub_field('city')): ?>
                                                        <?php the_sub_field('city'); ?>, <?php the_sub_field('state'); ?> - <?php the_sub_field('zip'); ?>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endif; ?>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                                                        
                                    <?php if(get_field('more_information_link') && !empty(get_field('more_information_link'))): ?>
                                        <li>
                                            <i class="fa fa-globe" aria-hidden="true"></i> : <a href="<?php the_field('more_information_link'); ?>" target="_blank"> More Information </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php if(get_field('cv')): ?>
                                        <li>
                                            <i class="fa fa-file-pdf-o" aria-hidden="true"></i> : <a href="<?php the_field('cv'); ?>" target="_blank"> <?php the_title(); ?> - CV </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php if(get_field('lab_website')): ?>
                                        <li>
                                            <i class="fa fa-external-link" aria-hidden="true"></i> : <a href="<?php the_field('lab_website'); ?>" target="_blank"> <?php the_field('lab_website'); ?> </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                </ul>

                                <!-- Show Bio -->
                                <div class="bio-description">
                                    <?php if(get_field('brief_description')): ?>
                                        <?php the_field('brief_description'); ?>
                                    <?php endif; ?>
                                </div>

                                <!-- Show Bio -->
                                <div class="bio-about">
                                    <?php if(get_field('bio__about')): ?>
                                        <?php the_field('bio__about'); ?>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Show Keyword if exists -->
                                <div class="">
                                    <p>
                                    
                                    <?php $index = 0; ?>
                                    <?php if(have_rows('keywords')): ?>
                                        <strong>Keywords: </strong>
                                        <?php while(have_rows('keywords')): the_row(); ?>
                                            <?php if($index == 0): ?>
                                                <?php the_sub_field('keyword'); ?>
                                            <?php else: ?>
                                                , <?php the_sub_field('keyword'); ?>
                                            <?php endif; ?>
                                            <?php $index = $index + 1; ?>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                    </p>
                                    <p>
                                        
                                        <?php $index = 0; ?>
                                        <?php if(have_rows('research_areas')): ?>
                                        	<strong>Research Area: </strong>
                                            <?php while(have_rows('research_areas')): the_row(); ?>
                                                <?php if($index == 0): ?>
                                                    <?php the_sub_field('research_area'); ?>
                                                <?php else: ?>
                                                    , <?php the_sub_field('research_area'); ?>
                                                <?php endif; ?>
                                                <?php $index = $index + 1; ?>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </p>

                                </div>


                            </div>


                        </div>




                        <div class="">
                            <?php //the_content(); ?>
                        </div>




                        <?php if (get_theme_mod('socialsharelinks') == true) { ?>
                            <div class="addthis_sharing_toolbox"></div>
                        <?php } ?>

                        <?php //comments_template(); ?>


                    <?php endwhile; else: ?>

                        <p>Sorry, no people matched your criteria.</p>

                    <?php endif; ?>

                </div>
            </article>
        </div>
    </div>

<?php wp_reset_query();
// Store output buffer
$context['plugin_content'] = ob_get_clean();

// Render with your Twig template
Timber::render('plugin-page.twig', $context);
