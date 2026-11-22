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
    $featured = $layout === 'priority';
    $count = $settings['counts'][$slug] ?? ($featured
        ? Reza_Ervani_Home_Topics::PRIORITY_COUNT
        : Reza_Ervani_Home_Topics::COMPACT_COUNT);
    $section = array(
        'term' => $category,
        'url' => $url,
        'heading_id' => $args['widget_id'] . '-country-' . $category->term_id,
        'stories' => reza_ervani_editorial_posts(array('cat' => $category->term_id, 'numberposts' => $count)),
        'children' => array(),
        'layout' => $featured ? 'spread' : 'list',
        'empty_message' => __('Belum ada artikel di rubrik ini.', 'mh-healthmag'),
    );
    if ($featured) {
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
