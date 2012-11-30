<?php
    /*
        Template Name: AttacksDemoLibrary
    */
?>
<?php get_header(); ?>
<!-- Content -->
		<div id="content">
			<div class="main">
				<div class="page_title">
					<h1>Attacks Demo Library</h1>
					<ul class="share_this">
						<li><script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=08252a75-a87d-4025-b708-fb7d227d8945&amp;type=website&amp;post_services=email%2Cfacebook%2Ctwitter%2Cgbuzz%2Cmyspace%2Cdigg%2Csms%2Cwindows_live%2Cdelicious%2Cstumbleupon%2Creddit%2Cgoogle_bmarks%2Clinkedin%2Cbebo%2Cybuzz%2Cblogger%2Cyahoo_bmarks%2Cmixx%2Ctechnorati%2Cfriendfeed%2Cpropeller%2Cwordpress%2Cnewsvine"></script></li>
					</ul>
					<form action="#">
						<fieldset>
							<div class="attacks_select_wrap">
								<div class="attack_select">
									<select>
										<option>Product Group 1</option>
										<option>Product Group 2</option>
										<option>Product Group 3</option>
									</select>
								</div>
								<div class="attack_select">
									<select>
										<option>Select Company</option>
										<option>Select Company</option>
										<option>Select Company</option>
										<option>Select Company</option>
									</select>
								</div>
							</div>
						</fieldset>
					</form>
					
				</div>
				<div class="attack_tabs" id="att_tab">
					<ul id="pane" class="attack_tabs_menu">
						<li>
							<a href="#video_1">
								<img alt="" src="<?php bloginfo('template_directory'); ?>/images/dev/video.gif" />
								<strong>Company 1</strong>
								<span>Lorem ipsum dolor amet, consectetuer adipiscing</span>
								<span class="time">3:28</span>
							</a>
						</li>
						<li>
							<a href="#video_2">
								<img alt="" src="<?php bloginfo('template_directory'); ?>/images/dev/video.gif" />
								<strong>Company 2</strong>
								<span>Lorem ipsum dolor amet, consectetuer adipiscing</span>
								<span class="time">3:28</span>
							</a>
						</li>
						<li>
							<a href="#video_3">
								<img alt="" src="<?php bloginfo('template_directory'); ?>/images/dev/video.gif" />
								<strong>Company 3</strong>
								<span>Lorem ipsum dolor amet, consectetuer adipiscing</span>
								<span class="time">3:28</span>
							</a>
						</li>
						<li>
							<a href="#video_4">
								<img alt="" src="<?php bloginfo('template_directory'); ?>/images/dev/video.gif" />
								<strong>Company 4</strong>
								<span>Lorem ipsum dolor amet, consectetuer adipiscing</span>
								<span class="time">3:28</span>
							</a>
						</li>
						<li>
							<a href="#video_5">
								<img alt="" src="<?php bloginfo('template_directory'); ?>/images/dev/video.gif" />
								<strong>Company 5</strong>
								<span>Lorem ipsum dolor amet, consectetuer adipiscing</span>
								<span class="time">3:28</span>
							</a>
						</li>
						<li>
							<a href="#video_6">
								<img alt="" src="<?php bloginfo('template_directory'); ?>/images/dev/video.gif" />
								<strong>Company 6</strong>
								<span>Lorem ipsum dolor amet, consectetuer adipiscing</span>
								<span class="time">3:28</span>
							</a>
						</li>
						<li>
							<a href="#video_7">
								<img alt="" src="<?php bloginfo('template_directory'); ?>/images/dev/video.gif" />
								<strong>Company 7</strong>
								<span>Lorem ipsum dolor amet, consectetuer adipiscing</span>
								<span class="time">3:28</span>
							</a>
						</li>
						<li>
							<a href="#video_8">
								<img alt="" src="<?php bloginfo('template_directory'); ?>/images/dev/video.gif" />
								<strong>Company 8</strong>
								<span>Lorem ipsum dolor amet, consectetuer adipiscing</span>
								<span class="time">3:28</span>
							</a>
						</li>
					</ul>
					<?php if (have_posts()) : ?>
						<?php while (have_posts()) : the_post(); // the loop ?>
							<?php the_content(); ?>
						<?php endwhile; ?>
					<?php else : ?>
						<div class="error-404">
							<h2>Not Found</h2>
							<p>Sorry, but you are looking for something that isn't here.</p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
<?php get_footer(); ?>