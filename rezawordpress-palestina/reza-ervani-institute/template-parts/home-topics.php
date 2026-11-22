<?php
$configured_topics = array(
    'ensiklopedi-al-quds' => array(__('Ensiklopedia al Quds', 'mh-healthmag'), 'feature'),
    'tokoh' => array(__('Tokoh', 'mh-healthmag'), 'grid'),
    'jurnal' => array(__('Jurnal', 'mh-healthmag'), 'list'),
);

$sections = array();
foreach ($configured_topics as $slug => list($title, $layout)) {
    $category = get_category_by_slug($slug);
    if (!$category) {
        continue;
    }
    $url = get_category_link($category);
    if (is_wp_error($url)) {
        continue;
    }
    $sections[] = array(
        'term' => $category,
        'title' => $title,
        'url' => $url,
        'stories' => reza_ervani_editorial_posts(array('cat' => $category->term_id, 'numberposts' => 4)),
        'children' => array(),
        'layout' => $layout,
        'show_images' => $slug !== 'jurnal',
        'empty_message' => __('Belum ada artikel di rubrik ini.', 'mh-healthmag'),
    );
}

if (!$sections) {
    return;
}
?>
<div class="reza-home-topics">
    <?php
    get_template_part('template-parts/category', 'topics', array('sections' => $sections));
    wp_reset_postdata();
    ?>
</div>
