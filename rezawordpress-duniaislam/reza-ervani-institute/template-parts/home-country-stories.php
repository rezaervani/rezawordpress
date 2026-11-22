<?php
$stories = $args['stories'];
$lead = array_shift($stories);
$parts = $args['parts'];
$side = $parts[$args['three_parts'] ? 3 : 2] ?? null;

$story_args = array(
    'heading_level' => $args['heading_level'],
    'show_excerpt' => false,
    'image_size' => 'thumbnail',
);
?>
<div class="reza-country-stories<?php echo $side ? ' reza-country-stories-with-side' : ''; ?>">
    <div class="reza-country-main">
        <div class="reza-country-lead" role="group" aria-label="<?php esc_attr_e('Semua artikel negara', 'mh-healthmag'); ?>">
            <?php if ($lead) : ?>
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
                <?php if ($stories) : ?>
                    <div class="reza-country-list">
                        <?php foreach ($stories as $story) :
                            $GLOBALS['post'] = $story;
                            setup_postdata($story);
                            get_template_part('template-parts/category', 'story', $story_args);
                        endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php elseif ($args['empty_message']) : ?>
                <p class="reza-archive-empty"><?php echo esc_html($args['empty_message']); ?></p>
            <?php endif; ?>
        </div>
        <?php if ($args['three_parts'] && isset($parts[2])) : ?>
            <?php get_template_part('template-parts/home', 'country-part', $parts[2] + array('side' => false, 'heading_level' => $args['heading_level'])); ?>
        <?php endif; ?>
    </div>
    <?php if ($side) : ?>
        <?php get_template_part('template-parts/home', 'country-part', $side + array('side' => true, 'heading_level' => $args['heading_level'])); ?>
    <?php endif; ?>
</div>
