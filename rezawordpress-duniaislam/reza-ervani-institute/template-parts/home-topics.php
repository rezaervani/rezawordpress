<?php
$settings = $args['settings'];
$priority = array();
$compact = array();
foreach (Reza_Ervani_Home_Topics::categories() as $category) {
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
        ? Reza_Ervani_Home_Topics::PRIORITY_COUNT
        : Reza_Ervani_Home_Topics::COMPACT_COUNT);
    $term_ids = $expanded ? Reza_Ervani_Home_Topics::selected_subcategories(
        $category,
        $settings['subcategories'][$slug] ?? array()
    ) : array();
    $query = $term_ids ? array('tax_query' => array(array(
        'taxonomy' => 'category',
        'field' => 'term_id',
        'terms' => $term_ids,
        'include_children' => true,
    ))) : array('cat' => $category->term_id);
    $subcategories = array();
    foreach ($term_ids as $term_id) {
        $term = get_category($term_id);
        if (!$term || is_wp_error($term)) {
            continue;
        }
        $link = get_category_link($term);
        if (!is_wp_error($link)) {
            $subcategories[] = array('name' => $term->name, 'url' => $link);
        }
    }
    $section = array(
        'term' => $category,
        'url' => $url,
        'heading_id' => $args['widget_id'] . '-country-' . $category->term_id,
        'stories' => reza_ervani_editorial_posts($query + array('numberposts' => $count)),
        'subcategories' => $subcategories,
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
