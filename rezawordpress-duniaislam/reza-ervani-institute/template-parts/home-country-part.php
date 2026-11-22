<?php $heading = 'h' . min(6, max(2, (int) $args['heading_level'])); ?>
<section class="reza-country-part<?php echo $args['side'] ? ' reza-country-part-side' : ''; ?>" aria-labelledby="<?php echo esc_attr($args['heading_id']); ?>">
    <<?php echo $heading; ?> class="reza-country-part-title" id="<?php echo esc_attr($args['heading_id']); ?>">
        <a href="<?php echo esc_url($args['url']); ?>"><?php echo esc_html($args['title']); ?></a>
    </<?php echo $heading; ?>>
    <?php if ($args['stories']) : ?>
        <div class="reza-country-list">
            <?php foreach ($args['stories'] as $story) :
                $GLOBALS['post'] = $story;
                setup_postdata($story);
                get_template_part('template-parts/category', 'story', array(
                    'heading_level' => $args['heading_level'] + 1,
                    'show_excerpt' => false,
                    'image_size' => 'thumbnail',
                ));
            endforeach; ?>
        </div>
    <?php else : ?>
        <p class="reza-archive-empty"><?php esc_html_e('Belum ada artikel di subkategori ini.', 'mh-healthmag'); ?></p>
    <?php endif; ?>
</section>
