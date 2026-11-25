<?php
$editorial = $args['editorial'] ?? false;
$english_sections = $editorial ? ($args['english_sections'] ?? array()) : array();
$content_areas = array('home-2', 'home-3', 'home-4', 'home-5');
$sidebar_areas = array('home-6');

if (!$editorial) {
    $content_areas[] = 'home-video-gallery';
}

$content_areas = array_filter($content_areas, 'reza_ervani_home_sidebar_active');
$sidebar_areas = array_filter($sidebar_areas, 'reza_ervani_home_sidebar_active');
$has_sidebar = $sidebar_areas || $english_sections;
$paired_hadits = $english_sections && in_array('home-5', $content_areas, true);
$lead_in_areas = $paired_hadits ? array_diff($content_areas, array('home-5')) : $content_areas;
$column_areas = array_intersect($content_areas, array('home-3', 'home-4'));
?>
<?php if ($content_areas || $has_sidebar) : ?>
    <div class="reza-home-widgets<?php echo $content_areas && $has_sidebar ? ' reza-home-widgets-with-sidebar' : ''; ?><?php echo $editorial ? ' reza-home-widgets-editorial' : ''; ?>">
        <?php if ($lead_in_areas) : ?>
            <div>
                <?php if (in_array('home-2', $lead_in_areas, true)) : ?>
                    <?php dynamic_sidebar('home-2'); ?>
                <?php endif; ?>
                <?php if (in_array('home-video-gallery', $lead_in_areas, true)) : ?>
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
                <?php if (in_array('home-5', $lead_in_areas, true)) : ?>
                    <?php dynamic_sidebar('home-5'); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($paired_hadits) : ?>
            <div class="reza-home-widgets-hadits">
                <?php dynamic_sidebar('home-5'); ?>
            </div>
        <?php endif; ?>
        <?php if ($has_sidebar) : ?>
            <div class="mh-widget-col-1 reza-home-sidebar<?php echo $english_sections ? ' reza-home-sidebar-english' : ''; ?>">
                <?php if ($english_sections) : ?>
                    <?php get_template_part('template-parts/editorial', 'sidebar-lists', array('sections' => $english_sections)); ?>
                <?php endif; ?>
                <?php foreach ($sidebar_areas as $area) : ?>
                    <?php dynamic_sidebar($area); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
