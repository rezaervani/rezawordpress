<?php
$sections = array();
foreach (Reza_Ervani_Palestina_Topics::topics() as $slug => list($title, $layout)) {
    $category_id = $args['categories'][$slug] ?? 0;
    $category = $category_id ? get_category($category_id) : get_category_by_slug($slug);
    if (!$category || is_wp_error($category)) {
        continue;
    }
    $url = get_category_link($category);
    if (is_wp_error($url)) {
        continue;
    }
    $sections[] = array(
        'term' => $category,
        'title' => $category_id ? $category->name : $title,
        'url' => $url,
        'heading_id' => $args['widget_id'] . '-' . $slug,
        'stories' => reza_ervani_editorial_posts(array('cat' => $category->term_id, 'numberposts' => $args['counts'][$slug])),
        'children' => array(),
        'layout' => $layout,
        'empty_message' => __('Belum ada artikel di rubrik ini.', 'mh-healthmag'),
    );
}

if (!$sections) {
    return;
}
?>
<div class="reza-home-topics">
    <?php
    get_template_part('template-parts/category', 'topics', array(
        'sections' => $sections,
        'preserve_layout' => true,
    ));
    wp_reset_postdata();
    ?>
</div>
