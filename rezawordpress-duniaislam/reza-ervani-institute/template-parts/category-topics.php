<?php
$level = $args['heading_level'] ?? 2;
$heading = 'h' . min(6, $level);
$layouts = array('feature', 'grid', 'list');
?>
<div class="reza-archive-topics">
    <?php foreach ($args['sections'] as $index => $section) :
        $term = $section['term'];
        $title = $section['title'] ?? $term->name;
        $url = $section['url'];
        $stories = $section['stories'];
        $heading_id = $section['heading_id'] ?? 'reza-archive-topic-' . $term->term_id;
        $default_layout = $layouts[($index + $level - 2) % count($layouts)];
        $layout = $section['layout'] ?? $default_layout;
        if (count($stories) < 2 && empty($args['preserve_layout'])) {
            $layout = 'list';
        }
        $classes = array('reza-archive-topic');
        if ($section['children']) {
            $classes[] = 'reza-archive-topic-branch';
        }
        if ($section['layout']) {
            $classes[] = 'reza-archive-topic-balanced';
        }
        $country_layout = in_array($layout, array('spread', 'double'), true);
        $story_args = array(
            'heading_level' => $level + 1,
            'show_image' => $layout !== 'feature',
            'show_excerpt' => false,
        );
        ?>
        <section class="<?php echo esc_attr(implode(' ', $classes)); ?>" aria-labelledby="<?php echo esc_attr($heading_id); ?>">
            <header class="reza-archive-topic-header">
                <<?php echo $heading; ?> id="<?php echo esc_attr($heading_id); ?>" class="reza-archive-topic-title">
                    <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a>
                </<?php echo $heading; ?>>
                <a class="reza-editorial-archive-link" href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr(sprintf(__('Lihat semua artikel di %s', 'mh-healthmag'), $title)); ?>">
                    <?php esc_html_e('Lihat semua', 'mh-healthmag'); ?> <span aria-hidden="true">→</span>
                </a>
            </header>
            <?php if (!empty($section['subcategories'])) : ?>
                <nav class="reza-country-subcategories" aria-label="<?php echo esc_attr(sprintf(__('Subkategori %s', 'mh-healthmag'), $title)); ?>">
                    <ul>
                        <?php foreach ($section['subcategories'] as $subcategory) : ?>
                            <li><a href="<?php echo esc_url($subcategory['url']); ?>"><?php echo esc_html($subcategory['name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            <?php if ($stories && $country_layout) : ?>
                <?php get_template_part('template-parts/home', 'country-stories', array(
                    'stories' => $stories,
                    'three_columns' => $layout === 'spread',
                    'heading_level' => $level + 1,
                )); ?>
            <?php elseif ($stories) : ?>
                <div class="reza-archive-topic-<?php echo esc_attr($layout); ?>">
                    <?php if ($layout === 'feature') :
                        $lead = array_shift($stories);
                        $GLOBALS['post'] = $lead;
                        setup_postdata($lead);
                        get_template_part('template-parts/category', 'story', array('featured' => true, 'heading_level' => $level + 1));
                    endif; ?>
                    <div class="reza-archive-topic-stories">
                        <?php foreach ($stories as $story) :
                            $GLOBALS['post'] = $story;
                            setup_postdata($story);
                            get_template_part('template-parts/category', 'story', $story_args);
                        endforeach; ?>
                    </div>
                </div>
            <?php elseif (!empty($section['empty_message'])) : ?>
                <p class="reza-archive-empty"><?php echo esc_html($section['empty_message']); ?></p>
            <?php endif; ?>
            <?php if ($section['children']) :
                get_template_part('template-parts/category', 'topics', array(
                    'sections' => $section['children'],
                    'heading_level' => $level + 1,
                ));
            endif; ?>
        </section>
    <?php endforeach; ?>
</div>
