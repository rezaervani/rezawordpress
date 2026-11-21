<?php
$level = $args['heading_level'] ?? 2;
$heading = 'h' . min(6, $level);
$layouts = array('feature', 'grid', 'list');
?>
<div class="reza-archive-topics">
    <?php foreach ($args['sections'] as $index => $section) :
        $term = $section['term'];
        $url = $section['url'];
        $stories = $section['stories'];
        $heading_id = 'reza-archive-topic-' . $term->term_id;
        $layout = count($stories) > 1 ? $layouts[($index + $level - 2) % count($layouts)] : 'list';
        $story_args = array('heading_level' => $level + 1, 'show_excerpt' => $layout === 'grid');
        ?>
        <section class="reza-archive-topic" aria-labelledby="<?php echo esc_attr($heading_id); ?>">
            <header class="reza-archive-topic-header">
                <<?php echo $heading; ?> id="<?php echo esc_attr($heading_id); ?>" class="reza-archive-topic-title">
                    <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($term->name); ?></a>
                </<?php echo $heading; ?>>
                <a class="reza-editorial-archive-link" href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr(sprintf(__('Lihat semua artikel di %s', 'mh-healthmag'), $term->name)); ?>">
                    <?php esc_html_e('Lihat semua', 'mh-healthmag'); ?> <span aria-hidden="true">→</span>
                </a>
            </header>
            <?php if ($stories) : ?>
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
