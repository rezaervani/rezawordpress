<?php
$settings = $args['settings'];
$priority = array();
$compact = array();
foreach (Reza_Ervani_Duniaislam_Topics::categories() as $category) {
    $slug = $category->slug;
    $layout = $settings['layouts'][$slug] ?? 'compact';
    if ($layout === 'hidden') {
        continue;
    }
    $url = get_category_link($category);
    if (is_wp_error($url)) {
        continue;
    }
    $expanded = in_array($layout, array('priority', 'double'), true);
    $count = $settings['counts'][$slug] ?? ($expanded
        ? Reza_Ervani_Duniaislam_Topics::PRIORITY_COUNT
        : Reza_Ervani_Duniaislam_Topics::COMPACT_COUNT);
    $heading_id = $args['widget_id'] . '-country-' . $category->term_id;
    $parts = array();
    $part_numbers = $layout === 'priority' ? array(2, 3) : ($expanded ? array(2) : array());
    foreach ($part_numbers as $number) {
        $part = $settings['sections'][$slug][$number] ?? array();
        $term = Reza_Ervani_Duniaislam_Topics::subcategory($category, $part['category'] ?? 0);
        if (!$term) {
            continue;
        }
        $link = get_category_link($term);
        if (!is_wp_error($link)) {
            $parts[$number] = array(
                'title' => $term->name,
                'url' => $link,
                'heading_id' => $heading_id . '-part-' . $number,
                'stories' => reza_ervani_editorial_posts(array(
                    'cat' => $term->term_id,
                    'numberposts' => $part['count'] ?? Reza_Ervani_Duniaislam_Topics::PRIORITY_COUNT,
                )),
            );
        }
    }
    $section = array(
        'term' => $category,
        'url' => $url,
        'heading_id' => $heading_id,
        'stories' => reza_ervani_editorial_posts(array('cat' => $category->term_id, 'numberposts' => $count)),
        'parts' => $parts,
        'children' => array(),
        'layout' => $expanded ? ($layout === 'priority' ? 'spread' : 'double') : 'list',
        'empty_message' => __('Belum ada artikel di rubrik ini.', 'mh-healthmag'),
    );
    if ($expanded) {
        $priority[$slug] = $section;
    } else {
        $compact[] = $section;
    }
}

if (!$priority && !$compact) {
    return;
}

$ordered_priority = array_values(array_replace(array_intersect_key($settings['layouts'], $priority), $priority));
$directory_title = $args['widget_id'] . '-more-countries';
?>
<div class="reza-home-topics">
    <?php if ($ordered_priority) : ?>
        <div class="reza-home-country-priority">
            <?php get_template_part('template-parts/category', 'topics', array(
                'sections' => $ordered_priority,
                'preserve_layout' => true,
            )); ?>
        </div>
    <?php endif; ?>
    <?php if ($compact) : ?>
        <section class="reza-home-country-directory" aria-labelledby="<?php echo esc_attr($directory_title); ?>">
            <h2 id="<?php echo esc_attr($directory_title); ?>" class="reza-archive-more-title"><?php esc_html_e('Negara Lainnya', 'mh-healthmag'); ?></h2>
            <?php get_template_part('template-parts/category', 'topics', array(
                'sections' => $compact,
                'heading_level' => 3,
                'preserve_layout' => true,
            )); ?>
        </section>
    <?php endif; ?>
</div>
<?php wp_reset_postdata(); ?>
