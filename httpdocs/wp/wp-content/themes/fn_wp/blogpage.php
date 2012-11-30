<?php
    /*
        Template Name: BlogPage
    */
?>
<?php get_header(); ?>
<!-- Content -->
			<div id="content">
				<div class="main">
					<div class="blog_wrapp">
	<!-- Blog Content -->
						<div class="blog_content">
							<?php if (have_posts()) : ?>
								<?php while (have_posts()) : the_post(); // the loop ?>
									<div class="post" id="post-<?php the_ID(); ?>">
										<h1><?php the_title(); ?></h1>
										<p>Posted by <?php the_author_link(); ?> on <?php the_time('F jS, Y') ?></p>
										<div class="post_content_wrap">
											<div class="share_sidebar">
												<ul>
													<li><span class="st_twitter_vcount"></span></li>
													<li><span class="st_facebook_vcount"></span></li>
													<li><span class="st_email_vcount"></span></li>
													<li><span class="st_sharethis_vcount"></span></li>
												</ul>
											</div>
											<div class="post_content">
												<?php the_content(); ?>
											</div>
										</div>
									</div>
								<?php endwhile; ?>
							<?php else : ?>
								<div class="post">
									<h2 class="center">Not Found</h2>
									<p class="center">Sorry, but you are looking for something that isn't here.</p>
									<?php include (TEMPLATEPATH . "/searchform.php"); ?>
								</div>
							<?php endif; ?>
						</div>
						<?php get_sidebar(); ?>
					</div>
				</div>
			</div>
		</div>
<?php get_footer(); ?>