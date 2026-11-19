<?php
$category = $args['category'];
$cards = $args['cards'];
$archive_url = get_category_link($category->term_id);
?>
<section class="reza-editorial-questions" aria-labelledby="reza-editorial-questions-title">
    <header class="reza-editorial-questions-header">
        <h2 id="reza-editorial-questions-title"><?php echo esc_html($category->name); ?></h2>
        <?php if (!is_wp_error($archive_url)) : ?>
            <a class="reza-editorial-archive-link" href="<?php echo esc_url($archive_url); ?>">
                <?php esc_html_e('Semua Tanya Jawab', 'mh-healthmag'); ?> <span aria-hidden="true">→</span>
            </a>
        <?php endif; ?>
    </header>
    <div class="reza-editorial-question-grid">
        <?php foreach ($cards as $card) : ?>
            <section class="reza-editorial-question-card">
                <h3><a href="<?php echo esc_url($card['url']); ?>"><?php echo esc_html($card['title']); ?></a></h3>
                <?php if ($card['thumbnail_post']) : ?>
                    <?php echo get_the_post_thumbnail($card['thumbnail_post'], 'mh-magazine-lite-medium', array(
                        'class' => 'reza-editorial-question-image',
                        'loading' => 'lazy',
                    )); ?>
                <?php endif; ?>
                <ul>
                    <?php foreach ($card['stories'] as $story) : ?>
                        <li>
                            <a href="<?php echo esc_url(get_permalink($story)); ?>"><?php echo esc_html(reza_ervani_editorial_title($story)); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endforeach; ?>
    </div>
</section>
