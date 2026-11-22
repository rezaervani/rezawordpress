<section class="reza-home-news reza-editorial-series" aria-labelledby="reza-home-news-heading">
    <header class="reza-editorial-series-header">
        <h2 id="reza-home-news-heading"><?php esc_html_e('Terkini', 'mh-healthmag'); ?></h2>
    </header>
    <?php if (have_posts()) : ?>
        <div class="reza-archive-grid">
            <?php while (have_posts()) :
                the_post();
                get_template_part('template-parts/category', 'story', array('heading_level' => 3));
            endwhile; ?>
        </div>
        <?php the_posts_pagination(array(
            'mid_size' => 1,
            'prev_text' => __('Sebelumnya', 'mh-healthmag'),
            'next_text' => __('Berikutnya', 'mh-healthmag'),
            'screen_reader_text' => __('Navigasi berita', 'mh-healthmag'),
        )); ?>
    <?php else : ?>
        <p class="reza-archive-empty"><?php esc_html_e('Belum ada artikel.', 'mh-healthmag'); ?></p>
    <?php endif; ?>
</section>
