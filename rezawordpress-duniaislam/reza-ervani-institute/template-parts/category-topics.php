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
        $spread = $layout === 'spread';
        $featured = $spread || $layout === 'feature';
        $story_args = array(
            'heading_level' => $level + 1,
            'show_image' => !$featured,
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
            <?php if ($stories) : ?>
                <div class="<?php echo esc_attr($spread ? 'reza-archive-spread reza-archive-spread-with-companions' : 'reza-archive-topic-' . $layout); ?>">
                    <?php if ($featured) :
                        $lead = array_shift($stories);
                        $GLOBALS['post'] = $lead;
                        setup_postdata($lead);
                        get_template_part('template-parts/category', 'story', array('featured' => true, 'heading_level' => $level + 1));
                    endif; ?>
                    <?php if ($stories || !$spread) : ?>
                        <div class="<?php echo esc_attr($spread ? 'reza-archive-companions' : 'reza-archive-topic-stories'); ?>">
                            <?php foreach ($stories as $story) :
                                $GLOBALS['post'] = $story;
                                setup_postdata($story);
                                get_template_part('template-parts/category', 'story', $story_args);
                            endforeach; ?>
                        </div>
                    <?php endif; ?>
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
