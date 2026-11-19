<?php /* Template Name: Homepage */ ?>
<?php
get_header();

$content_areas = array_filter(array('home-2', 'home-3', 'home-4', 'home-5'), 'is_active_sidebar');
$column_areas = array_intersect($content_areas, array('home-3', 'home-4'));
$has_sidebar = is_active_sidebar('home-6');
?>
<div class="mh-wrapper mh-home mh-clearfix">
    <?php dynamic_sidebar('home-1'); ?>
    <main id="main-content" class="mh-home-content">
        <?php if ($content_areas || $has_sidebar) : ?>
            <div class="reza-home-widgets<?php echo $content_areas && $has_sidebar ? ' reza-home-widgets-with-sidebar' : ''; ?>">
                <?php if ($content_areas) : ?>
                    <div>
                        <?php dynamic_sidebar('home-2'); ?>
                        <?php if ($column_areas) : ?>
                            <div class="reza-home-widget-columns">
                                <?php foreach ($column_areas as $area) : ?>
                                    <div class="mh-widget-col-1">
                                        <?php dynamic_sidebar($area); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php dynamic_sidebar('home-5'); ?>
                    </div>
                <?php endif; ?>
                <?php if ($has_sidebar) : ?>
                    <div class="mh-widget-col-1 reza-home-sidebar">
                        <?php dynamic_sidebar('home-6'); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (get_theme_mod('reza_ervani_show_home_categories', false)) : ?>
            <?php get_template_part('template-parts/home', 'categories'); ?>
        <?php endif; ?>
    </main>
</div>
<?php get_footer(); ?>
