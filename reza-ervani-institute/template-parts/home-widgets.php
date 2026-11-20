<?php
$editorial = $args['editorial'] ?? false;
$content_areas = array('home-2', 'home-3', 'home-4', 'home-5');
$sidebar_areas = array('home-6');

if (!$editorial) {
    $content_areas[] = 'home-video-gallery';
    $sidebar_areas = array_merge(array_column(reza_ervani_editorial_collection_topics(), 'sidebar'), $sidebar_areas);
}

$content_areas = array_filter($content_areas, 'is_active_sidebar');
$sidebar_areas = array_filter($sidebar_areas, 'is_active_sidebar');
$column_areas = array_intersect($content_areas, array('home-3', 'home-4'));
?>
<?php if ($content_areas || $sidebar_areas) : ?>
    <div class="reza-home-widgets<?php echo $content_areas && $sidebar_areas ? ' reza-home-widgets-with-sidebar' : ''; ?><?php echo $editorial ? ' reza-home-widgets-editorial' : ''; ?>">
        <?php if ($content_areas) : ?>
            <div>
                <?php dynamic_sidebar('home-2'); ?>
                <?php if (!$editorial) : ?>
                    <?php dynamic_sidebar('home-video-gallery'); ?>
                <?php endif; ?>
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
        <?php if ($sidebar_areas) : ?>
            <div class="mh-widget-col-1 reza-home-sidebar">
                <?php foreach ($sidebar_areas as $area) : ?>
                    <?php dynamic_sidebar($area); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
