<?php $query = $args['query']; ?>
<ul class="reza-article-list-posts<?php echo $args['featured'] ? ' reza-article-list-featured' : ''; ?>">
    <?php while ($query->have_posts()) :
        $query->the_post();
        $lead = $args['featured'] && $query->current_post === 0;
        $size = $lead ? 'mh-magazine-lite-large' : ($args['featured'] ? 'mh-magazine-lite-medium' : 'mh-magazine-lite-small');
        ?>
        <li class="reza-article-list-item<?php echo $lead ? ' reza-article-list-lead' : ''; ?>">
            <a class="reza-article-list-thumb" href="<?php echo esc_url(get_permalink()); ?>" aria-label="<?php echo esc_attr(reza_ervani_editorial_title(get_post())); ?>">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail($size, array('alt' => '', 'loading' => 'lazy')); ?>
                <?php else : ?>
                    <img src="<?php echo esc_url(get_theme_file_uri($lead ? 'images/placeholder-large.png' : 'images/placeholder-small.png')); ?>" alt="" loading="lazy">
                <?php endif; ?>
            </a>
            <div>
                <h3 class="reza-article-list-title"><a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(reza_ervani_editorial_title(get_post())); ?></a></h3>
                <div class="mh-meta reza-article-list-meta"><?php mh_magazine_lite_loop_meta(); ?></div>
                <?php if ($args['featured']) : ?>
                    <div class="reza-article-list-excerpt"><?php the_excerpt(); ?></div>
                <?php endif; ?>
            </div>
        </li>
    <?php endwhile; ?>
</ul>
<?php wp_reset_postdata(); ?>
