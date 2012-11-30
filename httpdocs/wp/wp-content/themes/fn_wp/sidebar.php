<?php if(is_page(array('overview','heavy-duty-security','easy-to-use','cloud-native','end-2-end','security-as-a-service','radically-affordable'))) { 
	$isOverview = $isHeavy_duty_security = $isEasy_to_use = $isCloud_native = $isEnd_2_end = $isSecurity_as_a_service = $isRadically_affordable = '';
	if(is_page('overview')){
		$isOverview = 'class="active"';
	}elseif(is_page('heavy-duty-security')){
		$isHeavy_duty_security = 'class="active"';
	}elseif(is_page('easy-to-use')){
		$isEasy_to_use = 'class="active"';
	}elseif(is_page('cloud-native')){
		$isCloud_native = 'class="active"';
	}elseif(is_page('end-2-end')){
		$isEnd_2_end = 'class="active"';
	}elseif(is_page('security-as-a-service')){
		$isSecurity_as_a_service = 'class="active"';
	}elseif(is_page('radically-affordable')){
		$isRadically_affordable = 'class="active"';
	}
?>
<div class="sidebar">
					<ul>
						<li <?php echo $isOverview ?>><a href="<?php bloginfo('home') ?>/learn/why-fn/overview/">Overview</a></li>
						<li <?php echo $isHeavy_duty_security  ?>><a href="<?php bloginfo('home') ?>/learn/why-fn/heavy-duty-security/">Heavy Duty Security</a></li>
						<li <?php echo $isEasy_to_use ?>><a href="<?php bloginfo('home') ?>/learn/why-fn/easy-to-use/">Easy to Use</a></li>
						<li <?php echo $isCloud_native ?>><a href="<?php bloginfo('home') ?>/learn/why-fn/cloud-native/">Cloud Native</a></li>
						<li <?php echo $isEnd_2_end ?>><a href="<?php bloginfo('home') ?>/learn/why-fn/end-2-end/">End-2-End</a></li>
						<li <?php echo $isSecurity_as_a_service ?>><a href="<?php bloginfo('home') ?>/learn/why-fn/security-as-a-service/">Security as a Service</a></li>
						<li <?php echo $isRadically_affordable ?>><a href="<?php bloginfo('home') ?>/learn/why-fn/radically-affordable/">Radically Affordable</a></li>
					</ul>
				</div>
<?php }elseif(is_page(array('architecture','patents'))) {
	$isArchitecture = $isPatents = '';
	if(is_page('architecture')){
		$isArchitecture = 'class="active"';
	}elseif(is_page('patents')){
		$isPatents = 'class="active"';
	}
 ?>
<div class="sidebar">
					<ul>
						<li <?php echo $isArchitecture ?>><a href="<?php bloginfo('home') ?>/learn/technology/architecture/">Architecture</a></li>
						<li <?php echo $isPatents ?>><a href="<?php bloginfo('home') ?>/learn/technology/patents/">Patents</a></li>
					</ul>
				</div>
<?php }elseif(is_page(array('about','team','investors','contact'))) {
	$isAbout = $isTeam = $isInvestors = $isContact = '';
	if(is_page('about')){
		$isAbout = 'class="active"';
	}elseif(is_page('team')){
		$isTeam = 'class="active"';
	}elseif(is_page('investors')){
		$isInvestors = 'class="active"';
	}elseif(is_page('contact')){
		$isContact = 'class="active"';
	}
 ?>
<div class="sidebar">
					<ul>
						<li <?php echo $isAbout?>><a href="<?php bloginfo('home') ?>/company/about-us/about/">Overview</a></li>
						<li <?php echo $isTeam?>><a href="<?php bloginfo('home') ?>/company/about-us/team/">Team</a></li>
						<li <?php echo $isInvestors?>><a href="<?php bloginfo('home') ?>/company/about-us/investors/">Investors</a></li>
						<li <?php echo $isContact?>><a href="<?php bloginfo('home') ?>/company/about-us/contact/">Contact Us</a></li>
					</ul>
				</div>
<?php }elseif (is_page('archive')) { ?>
<!-- Blog sidebar -->
						<div class="blog_sidebar">
							<div class="widget">
								<ul class="share_this">
									<li><a class="link_print" href="javascript: window.print();">Print</a></li>
								</ul>
							</div>
							<?php include (TEMPLATEPATH . "/searchform.php"); ?>
							<div class="widget widget_border">
								<h3>Topics</h3>
								<ul class="topics">
									 <?php wp_list_categories( 'title_li=' ); ?>  
								</ul>
							</div>
							<div class="widget">
								<h3>Tags</h3>
								<ul class="tags">
									<?php wp_tag_cloud('smallest=11&largest=11&unit=px&format=list'); ?>
								</ul>
							</div>
						</div>
<?php } elseif(is_page(array('media-kit','events','federated-networks-presentation-at-demo-fall-2010-in-silicon-valley','inlab-ventures-media-interview-with-federated-networks')) || in_category('news') || in_category('press') || is_category('news') || is_category('press')) {
					$isEvents = $isMediaKit = $isNews = $isPress = $isVideo = '';
					if(is_page('events')){
						$isEvents = 'class="active"';
					}elseif(is_page('media-kit')){
						$isMediaKit = 'class="active"';
					}elseif(is_category('news') || in_category('news')){
						$isNews = 'class="active"';
					}elseif(is_category('press') || in_category('press')){
						$isPress = 'class="active"';
					}elseif(is_page('federated-networks-presentation-at-demo-fall-2010-in-silicon-valley') || is_page('inlab-ventures-media-interview-with-federated-networks')){
						$isVideo = 'class="active"';
					}
					
					 // Get the ID of a given category
						$category_id_news = get_cat_ID( 'news' );
					// Get the URL of this category
						$category_link_news = get_category_link( $category_id_news );
					 // Get the ID of a given category
						$category_id_press = get_cat_ID( 'press' );
					// Get the URL of this category
						$category_link_press = get_category_link( $category_id_press );
				 ?>
<div class="sidebar">
					<ul>
						<li <?php echo $isNews ?>><a href="<?php echo $category_link_news ?>">News</a></li>
						<li <?php echo $isPress ?>><a href="<?php echo $category_link_press ?>">Press Releases</a></li>
						<li <?php echo $isVideo ?>><a href="<?php bloginfo('home') ?>/company/federated-networks-presentation-at-demo-fall-2010-in-silicon-valley/">Video</a></li>
						<li <?php echo $isEvents ?>><a href="<?php bloginfo('home') ?>/company/events/">Events</a></li>
						<li <?php echo $isMediaKit ?>><a href="<?php bloginfo('home') ?>/company/media-kit/">Media Kit</a></li>
					</ul>
					<div class="sidebar-block b_n">
						<div class="sidebar_contact">
							<strong>Media Inquiries</strong>
							<h4>Shweta Agarwal</h4>
							<p>Schwartz Communications<br /> 781-684-0770</p>
							<p><a href="federatednetworks@schwartz-pr.com">federatednetworks@schwartz-pr.com</a></p>
						</div>
					</div>
				</div>
<?php } else {  ?>
<!-- Blog sidebar -->
						<div class="blog_sidebar">
							<div class="widget">
								<ul class="share_this">
									<li><a class="link_print" href="javascript: window.print();">Print</a></li>
								</ul>
							</div>
							<?php include (TEMPLATEPATH . "/searchform.php"); ?>
							<?php  if ( !function_exists('dynamic_sidebar')
								|| !dynamic_sidebar('Sidebar') ) : 
							 endif; ?>
							 <div class="widget widget_border">
								<h3>Archive</h3>
								<ul>
									<?php wp_get_archives('cat=1'); ?>
								</ul>
							</div>
							<div class="widget widget_border">
								<h3>Topics</h3>
								<ul class="topics">
									 <?php wp_list_categories( 'title_li=&exclude=3,4' ); ?>  
								</ul>
							</div>
							<div class="widget">
								<h3>Tags</h3>
								<ul class="tags">
									<?php wp_tag_cloud('smallest=11&largest=11&unit=px&format=list'); ?>
								</ul>
							</div>
							<div class="twitter_block">
								<div class="twitter_block_header">
									<h3>Federated Newtorks on Twitter</h3>
								</div>
								<div class="twitter_block_contnet">
									<?php if (function_exists('twitter_messages')) twitter_messages('FedNetworks', 5, true, true, false, true, false, false); ?> 
								</div>
								<div class="twitter_block_footer"></div>
							</div>
							<div class="widget">
								<h3>What We're Reading</h3>
								<ul>
									<?php get_links('-1', '<li>', '</li>'); ?>
								</ul>
							</div>
						</div>
<?php }