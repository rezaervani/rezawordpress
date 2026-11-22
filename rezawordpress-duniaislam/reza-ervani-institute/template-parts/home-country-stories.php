<?php
$stories = $args['stories'];
$lead = array_shift($stories);
$columns = array('under' => $stories);
if ($args['three_columns'] && count($stories) > 1) {
    $columns['side'] = array_splice($columns['under'], intdiv(count($stories), 2));
}

$story_args = array(
    'heading_level' => $args['heading_level'],
    'show_excerpt' => false,
    'image_size' => 'thumbnail',
);
?>
<div class="reza-country-stories<?php echo isset($columns['side']) ? ' reza-country-stories-with-side' : ''; ?>">
    <div class="reza-country-lead">
        <div class="reza-archive-spread">
            <?php
            $GLOBALS['post'] = $lead;
            setup_postdata($lead);
            get_template_part('template-parts/category', 'story', array(
                'featured' => true,
                'heading_level' => $args['heading_level'],
            ));
            ?>
        </div>
        <?php if ($columns['under']) : ?>
            <div class="reza-country-list">
                <?php foreach ($columns['under'] as $story) :
                    $GLOBALS['post'] = $story;
                    setup_postdata($story);
                    get_template_part('template-parts/category', 'story', $story_args);
                endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if (isset($columns['side'])) : ?>
        <div class="reza-country-list reza-country-list-side">
            <?php foreach ($columns['side'] as $story) :
                $GLOBALS['post'] = $story;
                setup_postdata($story);
                get_template_part('template-parts/category', 'story', $story_args);
            endforeach; ?>
        </div>
    <?php endif; ?>
</div>
