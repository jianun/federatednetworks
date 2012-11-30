<?php get_header(); ?>
<!-- Content -->
			<div id="content">
				<div class="main">
					<div class="blog_wrapp">
						<?php if(in_category('press')) { ?>
							<div class="main_content">
								<div class="page_title">
									<h1>Press Releases</h1>
									<ul class="share_this">
										<li><script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=08252a75-a87d-4025-b708-fb7d227d8945&amp;type=website&amp;post_services=email%2Cfacebook%2Ctwitter%2Cgbuzz%2Cmyspace%2Cdigg%2Csms%2Cwindows_live%2Cdelicious%2Cstumbleupon%2Creddit%2Cgoogle_bmarks%2Clinkedin%2Cbebo%2Cybuzz%2Cblogger%2Cyahoo_bmarks%2Cmixx%2Ctechnorati%2Cfriendfeed%2Cpropeller%2Cwordpress%2Cnewsvine"></script></li>
										<li><a class="link_print" href="javascript: window.print();">Print</a></li>
									</ul>
								</div>
								<div class="page_content">
									<?php if (have_posts()) : ?>
										<?php while (have_posts()) : the_post(); // the loop ?>
												<div class="to_col_content press-block-content ">
													<div class="press-block">
														<div class="press-block-content to_col_content">
															<h2><?php the_title(); ?></h2>
															<p><em><?php the_time('M d, Y ') ?></em></p>
															<?php the_content(); ?>
														</div>
													</div>
												</div>
										<?php endwhile //have posts ?>
									<?php endif //have posts ?>
									<div class="right_column p_0">
										<ul>
											<?php $_download  = get_post_meta($post->ID, 'download', true); if($_download !== '') ?>
											<?php echo $_download ?>
										</ul>
									</div>
								</div>
							</div>
						<?php } elseif(in_category('news')) { ?>
							<div class="main_content">
								<div class="page_title">
									<h1>News</h1>
									<ul class="share_this">
										<li><script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=08252a75-a87d-4025-b708-fb7d227d8945&amp;type=website&amp;post_services=email%2Cfacebook%2Ctwitter%2Cgbuzz%2Cmyspace%2Cdigg%2Csms%2Cwindows_live%2Cdelicious%2Cstumbleupon%2Creddit%2Cgoogle_bmarks%2Clinkedin%2Cbebo%2Cybuzz%2Cblogger%2Cyahoo_bmarks%2Cmixx%2Ctechnorati%2Cfriendfeed%2Cpropeller%2Cwordpress%2Cnewsvine"></script></li>
										<li><a class="link_print" href="javascript: window.print();">Print</a></li>
									</ul>
								</div>
								<div class="page_content">
									<?php if (have_posts()) : ?>
										<?php while (have_posts()) : the_post(); // the loop ?>
													<div class="press-block">
														<div class="press-block-date">
															<span><?php the_time('M d, Y ') ?></span>
														</div>
														<div class="press-block-content">
															<h2><?php the_title(); ?></h2>
															<?php the_content(); ?>
														</div>
													</div>
										<?php endwhile //have posts ?>
									<?php endif //have posts ?>
								</div>
							</div>
						<?php } else {?>
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
										<?php comments_template(); ?>
									<?php endwhile; ?>
								<?php else : ?>
									<div class="post">
										<h2 class="center">Not Found</h2>
										<p class="center">Sorry, but you are looking for something that isn't here.</p>
										<?php include (TEMPLATEPATH . "/searchform.php"); ?>
									</div>
								<?php endif; ?>
							</div>
						<?php } ?>
						<?php get_sidebar(); ?>
					</div>
				</div>
			</div>
		</div>
<?php get_footer(); ?>