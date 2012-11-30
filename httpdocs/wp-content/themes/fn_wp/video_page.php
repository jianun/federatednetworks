<?php
/*
	 Template Name: VideoPage
 */
?>
<?php get_header(); ?>
<!-- Content -->
		<div id="content">
			<div class="main">
	<!-- Main content -->
				<div class="main_content">
					<div class="page_title">
						<h1><?php the_title(); ?></h1>
					</div>
					<div class="page_content">
						<?php if (have_posts()) : ?>
							<?php while (have_posts()) : the_post(); // the loop ?>
								<?php the_content('Read more'); ?>
							<?php endwhile; //have posts ?>
						<?php endif; //have posts ?>
					</div>
				</div>
	<!-- sidebar -->
				<?php get_sidebar(); ?>
			</div>
		</div>
<?php get_footer(); ?>