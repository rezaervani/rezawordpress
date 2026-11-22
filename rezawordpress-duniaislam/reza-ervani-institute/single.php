<?php get_header(); ?>
<?php if (is_singular('post')) : ?>
    <?php get_template_part('template-parts/editorial', 'article'); ?>
<?php else : ?>
<div class="mh-wrapper mh-clearfix">
	<div id="main-content" class="mh-content" role="main" itemprop="mainContentOfPage"><?php
		while (have_posts()) : the_post();
			mh_before_post_content();
			get_template_part('content', 'single');
			mh_after_post_content();
			comments_template();
		endwhile; ?>
	</div>
	<?php get_sidebar(); ?>
</div>
<?php endif; ?>
<?php get_footer(); ?>
