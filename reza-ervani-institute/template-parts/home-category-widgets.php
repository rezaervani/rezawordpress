<?php
$area = 'home-editorial-category-widgets';
if (!is_active_sidebar($area)) {
    return;
}
?>
<div class="reza-editorial-widget-columns">
    <?php dynamic_sidebar($area); ?>
</div>
