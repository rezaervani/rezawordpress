<?php
$areas = array_filter(array_keys(reza_ervani_editorial_category_widget_rows()), 'reza_ervani_home_sidebar_active');
if (!$areas) {
    return;
}
?>
<div class="reza-editorial-category-widget-rows">
    <?php foreach ($areas as $area) : ?>
        <div class="reza-editorial-widget-columns">
            <?php dynamic_sidebar($area); ?>
        </div>
    <?php endforeach; ?>
</div>
