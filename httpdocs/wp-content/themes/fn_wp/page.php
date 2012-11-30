<?php get_header(); ?>
<!-- Content -->
		<div id="content">
			<div class="main">
	<!-- Main content -->
				<div class="main_content">
					<?php if (have_posts()) : ?>
						<?php while (have_posts()) : the_post(); // the loop ?>
							<div class="page_title">
								<h1><?php the_title(); ?></h1>
								<ul class="share_this">
									<li><script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=08252a75-a87d-4025-b708-fb7d227d8945&amp;type=website&amp;post_services=email%2Cfacebook%2Ctwitter%2Cgbuzz%2Cmyspace%2Cdigg%2Csms%2Cwindows_live%2Cdelicious%2Cstumbleupon%2Creddit%2Cgoogle_bmarks%2Clinkedin%2Cbebo%2Cybuzz%2Cblogger%2Cyahoo_bmarks%2Cmixx%2Ctechnorati%2Cfriendfeed%2Cpropeller%2Cwordpress%2Cnewsvine"></script></li>
									<li><a class="link_print" href="javascript: window.print();">Print</a></li>
								</ul>
							</div>
							<div class="page_content">
								<?php the_content(); ?>
							</div>
						<?php endwhile; ?>
					<?php else : ?>
						<div class="error-404">
							<h2>Not Found</h2>
							<p>Sorry, but you are looking for something that isn't here.</p>
						</div>
					<?php endif; ?>
				</div>
	<!-- sidebar -->
				<?php get_sidebar(); ?>
			</div>
		</div>
<?php get_footer(); ?>