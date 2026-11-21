<div class="reza-latest-posts-columns" style="--reza-latest-posts-rows: <?php echo esc_attr(count($args['columns'][0])); ?>;">
    <?php foreach ($args['columns'] as $index => $stories) : ?>
        <ol class="reza-latest-posts-list" role="list" start="<?php echo esc_attr(1 + $index * $args['posts_per_column']); ?>">
            <?php foreach ($stories as $story) : ?>
                <li class="reza-latest-posts-item">
                    <a href="<?php echo esc_url(get_permalink($story)); ?>"><?php echo esc_html(reza_ervani_editorial_title($story)); ?></a>
                    <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $story)); ?>"><?php echo esc_html(get_the_date('', $story)); ?></time>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endforeach; ?>
</div>
