<?php
$content_areas = array_filter(array('home-2', 'home-3', 'home-4', 'home-5'), 'is_active_sidebar');
$column_areas = array_intersect($content_areas, array('home-3', 'home-4'));
$has_sidebar = is_active_sidebar('home-6');

if (!empty($args['editorial'])) {
    add_filter('widget_display_callback', 'reza_ervani_skip_home_slider', 10, 3);
    dynamic_sidebar('home-1');
    remove_filter('widget_display_callback', 'reza_ervani_skip_home_slider', 10);
}
?>
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
