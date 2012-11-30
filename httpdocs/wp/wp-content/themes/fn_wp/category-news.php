<?php get_header(); ?>
<!-- Content -->
		<div id="content">
			<div class="main">
	<!-- Main content -->
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
										<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
										<?php the_content('Read more'); ?>
									</div>
								</div>
							<?php endwhile //have posts ?>
							<ul class="post_links">
									<li class="next_blog"><?php previous_posts_link('Previous Entries') ?></li>
									<li class="prev_blog"><?php next_posts_link('Next Entries') ?></li>
								</ul>
						<?php endif //have posts ?>
					</div>
				</div>
	<!-- sidebar -->
				<?php get_sidebar(); ?>
			</div>
		</div>
<?php get_footer(); ?>