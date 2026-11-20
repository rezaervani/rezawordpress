<?php
$show_widgets = $args['show_widgets'] ?? true;
$collections = $args['collections'] ?? array();
$content_areas = $show_widgets ? array_filter(array('home-2', 'home-3', 'home-4', 'home-5'), 'is_active_sidebar') : array();
$column_areas = array_intersect($content_areas, array('home-3', 'home-4'));
$has_content = $content_areas || $collections;
$has_sidebar = $show_widgets && is_active_sidebar('home-6');
?>
<?php if ($has_content || $has_sidebar) : ?>
    <div class="reza-home-widgets<?php echo $has_content && $has_sidebar ? ' reza-home-widgets-with-sidebar' : ''; ?><?php echo $collections ? ' reza-home-widgets-with-collections' : ''; ?>">
        <?php if ($has_content) : ?>
            <div>
                <?php foreach ($collections as $collection) : ?>
                    <?php get_template_part('template-parts/editorial', 'collection', $collection); ?>
                <?php endforeach; ?>
                <?php if ($content_areas) : ?>
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
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($has_sidebar) : ?>
            <div class="mh-widget-col-1 reza-home-sidebar">
                <?php dynamic_sidebar('home-6'); ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
