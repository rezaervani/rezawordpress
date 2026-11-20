<div class="reza-editorial-sidebar-lists">
    <?php foreach ($args['sections'] as $section) :
        $title_id = 'reza-editorial-sidebar-title-' . $section['category_id'];
        $archive_url = get_category_link($section['category_id']);
        ?>
        <section class="reza-editorial-sidebar-section" aria-labelledby="<?php echo esc_attr($title_id); ?>">
            <h2 id="<?php echo esc_attr($title_id); ?>" class="reza-editorial-sidebar-heading">
                <?php if (is_wp_error($archive_url)) : ?>
                    <?php echo esc_html($section['title']); ?>
                <?php else : ?>
                    <a href="<?php echo esc_url($archive_url); ?>"><?php echo esc_html($section['title']); ?></a>
                <?php endif; ?>
            </h2>
            <ul class="reza-editorial-sidebar-stories">
                <?php foreach ($section['stories'] as $story) :
                    $thumbnail = get_the_post_thumbnail($story, 'mh-magazine-lite-small', array('alt' => '', 'loading' => 'lazy'));
                    ?>
                    <li>
                        <a class="reza-editorial-sidebar-story" href="<?php echo esc_url(get_permalink($story)); ?>">
                            <?php if ($thumbnail) : ?>
                                <?php echo $thumbnail; ?>
                            <?php else : ?>
                                <img src="<?php echo esc_url(get_theme_file_uri('images/placeholder-small.png')); ?>" width="80" height="60" alt="" loading="lazy">
                            <?php endif; ?>
                            <div>
                                <h3><?php echo esc_html(reza_ervani_editorial_title($story)); ?></h3>
                                <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $story)); ?>"><?php echo esc_html(get_the_date('', $story)); ?></time>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endforeach; ?>
</div>
