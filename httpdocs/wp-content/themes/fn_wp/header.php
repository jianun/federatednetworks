<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<title><?php bloginfo('name'); ?> <?php wp_title(); ?></title>
		<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
		<link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/print.css" type="text/css" media="print" />
		<link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/iphone.css" media="only screen and (max-device-width: 480px)" />
		<link type="text/css" rel="stylesheet" media="screen and (resolution: 132dpi)" href="<?php bloginfo('template_directory'); ?>/ipad.css" />
		<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
		<link href='http://fonts.googleapis.com/css?family=Droid+Sans:regular,bold&v1' rel='stylesheet' type='text/css'>
		<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jquery-1.5.1.min.js"></script>
		<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/plugins.js"></script>
		<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/script.js"></script>
		<?php wp_head(); ?>
		<script type="text/javascript">
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-26135924-1']);
		  _gaq.push(['_trackPageview']);
		  (function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		</script>
	</head>
	<body <?php if(is_front_page()) {?>class="home"<?php }  elseif(is_page(array('ideas-forum','technical-support', 'tech-specs', 'tutorials'))) { ?>  class="support_page" <?php } ?> >
			<div id="skipNav">
				<ul title="Accessibility options">
					<li><a accesskey="S" href="#content">Skip to Content</a></li>
					<li><a accesskey="N" href="#nav">Skip to Navigation</a></li>
				</ul>
			</div>
		<div id="header">
			<div class="main">
				<div class="top_header">
					<strong class="logo"><a title="Federated Networks" href="<?php bloginfo('home') ?>/">Federated Networks</a></strong>
					<div class="top_box">
						<div id="nav" class="main_menu">
							<?php
							    // Get the ID of a given category
							    $category_id = get_cat_ID( 'Blog' );
							    // Get the URL of this category
							    $category_link = get_category_link( $category_id );
								// Get the ID of a given category
								$category_id_news = get_cat_ID( 'news' );
								// Get the URL of this category
								$category_link_news = get_category_link( $category_id_news );
								$isBlog = $isProducts = $isWhy_fn = $isCompany = $isSupport = '';
								if(in_category('blog')&&!(in_category('news') || in_category('press')) || is_page('about-this-blog')){
									$isBlog = 'class="active"';
								} elseif(is_page('all-products')){
									$isProducts = 'class="active"';
								} elseif(is_page_template('why-fn.php')) {
									$isWhy_fn = 'class="active"';
								} if(is_page(array('about','team','investors','contact','media-kit','events','federated-networks-presentation-at-demo-fall-2010-in-silicon-valley','inlab-ventures-media-interview-with-federated-networks')) || (in_category('news') || in_category('press'))) {
									$isCompany = 'class="active"';
								}
								if(is_page(array('support','ideas-forum', 'technical-support', 'tech-specs', 'tutorials'))){
									$isSupport = 'class="active"';
								}
							?>
							<ul>
								<li <?php echo $isWhy_fn ?>><a title="Why Federated Networks?" href="<?php bloginfo('home') ?>/why-fn/key-reasons/proven-results/">Why FN?</a></li>
								<li <?php echo $isProducts ?>><a title="Internet Security &amp; Privacy Protection Products"  href="<?php bloginfo('home') ?>/all-products/">PRODUCTS</a></li>
								<li <?php echo $isSupport ?>><a class="fancy-link" title="Support for Federated Networks Products" href="#sorry">SUPPORT</a></li>
								<li class="dd">
									<a title="Federated Networks More" class="main_more">MORE</a>
									<ul>
										<li><a title="FN SECURITY BLOGS" href="<?php echo $category_link; ?>">BLOG</a></li>
										<li><a title="News" href="/2011/?cat=3">BUZZ</a></li>
										<li><a title="About Federated Networks" href="<?php bloginfo('home') ?>/more/company/about-us/overview/">Company</a></li>
										<li><a title="Contact Us" href="<?php bloginfo('home') ?>/more/contact-us/">Contact US</a></li>
									</ul>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<?php if(!is_front_page()) { ?>
					<div class="sub_title">
						<?php if(is_page_template('why-fn.php') || is_page_template('attak-demos.php')) { 
						$isKeyReasons = $isAttackDemos = $isSecurityCenter =  $isSolutions = $isResources = '';
						if(is_page(array('proven-results', 'agent-architecture', 'system-architecture', 'hacker-challanges', 'attack-dynamics', 'empirical-methodology', 'assurance-model', 'attack-summary', 'radically-affordable', 'anytime-anywhere', 'one-stop-shopping', 'supported-apps', 'security-as-a-service', 'encryption', 'key-technologies', 'conceptual-differentiators', 'transparent-disclosure', 'patents', 'unwhite-paper'))){
							$isKeyReasons = ' class="active"';
						}
						?>
							<h2>Why FN?</h2>
							<ul class="sub_menu">
								<li<?php echo $isKeyReasons ?>><a title="Key Reasons" href="<?php bloginfo('home') ?>/why-fn/key-reasons/proven-results/">Key Reasons</a></li>
								<!--<li<?php // echo $isAttackDemos ?>><a title="Attack Demos" class="fancy-link" href="#sorry">Attack Demos</a></li>
								<li<?php // echo $isSecurityCenter ?>><a title="Security Center" class="fancy-link" href="#sorry">Security Center</a></li>
								<li<?php // echo $isSolutions ?>><a title="Solutions" class="fancy-link" href="#sorry">Solutions</a></li>
								<li<?php // echo $isResources ?>><a title="Resources" class="fancy-link" href="#sorry">Resources</a></li>-->
							</ul>
						<?php } elseif((is_single() || is_home() || is_page_template('blogpage.php') || is_archive() || is_category())&&!(in_category('news') || in_category('press')) ){ 
							$isProSecurity = $isConsumerSecurity = $isFnProducts = '';
							if(is_category('blog') || in_category('blog') || is_author()){
								$isProSecurity = $isConsumerSecurity = $isFnProducts = '';
							} elseif (is_category('pro-security') || in_category('pro-security')){
								$isProSecurity = 'class="active"';
							}elseif(is_category('consumer-security') || in_category('consumer-security')){
								$isConsumerSecurity = 'class="active"';
							}elseif(is_category('fn-products') || in_category('fn-products')){
								$isFnProducts = 'class="active"';
							}
							
							// Get the ID of a given category
							$category_id_prosecurity = get_cat_ID( 'pro security' );
							$category_link_prosecurity = get_category_link( $category_id_prosecurity );
							// Get the ID of a given category
							$category_id_consumersecurity = get_cat_ID( 'consumer security' );
							$category_link_consumersecurity = get_category_link( $category_id_consumersecurity );
							// Get the ID of a given category
							$category_id_fnporoducts = get_cat_ID( 'Fn Products' );
							$link_fnporoducts = get_category_link( $category_id_fnporoducts );
						?>
							<h2><a href="<?php echo $category_link; ?>">FN SECURITY BLOGS</a></h2>
							<ul class="sub_menu">
								<li <?php echo $isConsumerSecurity ?>><a href="<?php echo $category_link_consumersecurity ?>">Consumer Security</a></li>
								<li <?php echo $isProSecurity ?>><a href="<?php echo $category_link_prosecurity ?>">Pro Security</a></li>
								<li <?php echo $isFnProducts ?>><a href="<?php echo $link_fnporoducts ?>">FN Products</a></li>
							</ul>
						<?php } elseif(is_page('all-products')){?>
							<h2>PRODUCTS</h2>
						<?php }elseif(is_page('contact-us')){?>
							<h2>Contact Us</h2>
						<?php }elseif(is_page(array('overview','management','advisory-board','software-engeneers','sales-marketing','other','vision-and-values','fn-at-a-glance', 'investors'))) { ?>
							<?php 
								$isAboutUs = $isTeam = $isCareers = '';
								if(is_page(array('vision-and-values','overview','fn-at-a-glance', 'investors'))){
									$isAboutUs = 'class="active"';
								}elseif(is_page(array('management','advisory-board'))){
									$isTeam = 'class="active"';
								}elseif(is_page(array('software-engeneers','sales-marketing','other'))){
									$isCareers = 'class="active"';
								}
							?>
							<h2>Company</h2>
							<ul class="sub_menu">
								<li <?php echo $isAboutUs ?>><a title="About Us" href="<?php bloginfo('home') ?>/more/company/about-us/overview/">About Us</a></li>
								<li <?php echo $isTeam ?>><a title="Team" href="<?php bloginfo('home') ?>/more/company/team/management/">Team</a></li>
								<!--<li <?php // echo $isCareers ?>><a title="Careers" href="<?php // bloginfo('home') ?>/more/company/careers/software-engeneers/">Careers</a></li>-->
							</ul>
					 <?php }elseif(is_page(array('media-kit','conferences','interviews')) || is_page_template('events_page.php') || is_category('news') || in_category('news') || is_category('press') || in_category('press')) {
								
								$isEvents = $isMediaKit = $isNews = $isPress = $isVideo = '';
								if(is_page_template('events_page.php')){
									$isEvents = 'class="active"';
								}elseif(is_page('media-kit')){
									$isMediaKit = 'class="active"';
								}elseif(is_category('news') || in_category('news')){
									$isNews = 'class="active"';
								}elseif(is_category('press') || in_category('press')){
									$isPress = 'class="active"';
								}elseif(is_page('conferences') || is_page('interviews')){
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
							<h2>Buzz</h2>
							<ul class="sub_menu">
								<li <?php echo $isNews ?>><a href="/2011/?cat=3">News</a></li>
								<li <?php echo $isPress ?>><a href="/2011/?cat=4">Press Releases</a></li>
								<li <?php echo $isVideo ?>><a href="<?php bloginfo('home') ?>/more/buzz/video/conferences/">Video</a></li>
								<li <?php echo $isEvents ?>><a href="<?php bloginfo('home') ?>/more/buzz/fn-exhibits-at-privacy-by-design-conference/">Events</a></li>
								<li <?php echo $isMediaKit ?>><a href="<?php bloginfo('home') ?>/more/buzz/media-kit/">Media Kit</a></li>
							</ul>
					 <?php } elseif (is_page(array('support', 'ideas-forum', 'technical-support', 'tech-specs', 'tutorials'))) { ?>
							<?php 
								$isideasforum = $istechnicalsupport = $techspecs = $istutorials = '';
								if(is_page('ideas-forum')){
									$isideasforum = ' class="active"';
								} elseif(is_page('technical-support')){
									$istechnicalsupport = ' class="active"';
								} elseif(is_page('tech-specs')){
									$techspecs = ' class="active"';
								} elseif(is_page('tutorials')){
									$istutorials = ' class="active"';
								}
							?>
							<?php if(is_page('support')) { ?>
								<h2>SUPPORT</h2>
							<?php } else { ?>
								<div class="sub_title_left">
									<h2>TECHNICAL SUPPORT</h2>
									<div class="selected_menu">
										<p>Selected Product</p>
										<div class="select_page_wrap">
											<h3>FN Secure Desktop</h3>
											<ul>
												<li><a href="#">FN Secure Desktop</a></li>
												<li><a href="#">FN Secure Desktop</a></li>
												<li><a href="#">FN Secure Desktop</a></li>
											</ul>
										</div>
									</div>
								</div>
								<div class="support_menu">
									<ul class="sub_menu">
										<li<?php echo $istechnicalsupport ?>><a title="Help" class="help" href="<?php bloginfo('home') ?>/support/technical-support/">Help</a></li>
										<li<?php echo $istutorials ?>><a title="Tutorials" class="tutorials" href="<?php bloginfo('home') ?>/support/tutorials/">Tutorials</a></li>
										<li<?php echo $isideasforum ?>><a title="Ideas Forum" class="ideas_forum" href="<?php bloginfo('home') ?>/support/ideas-forum/">Ideas Forum</a></li>
										<li<?php echo $techspecs ?>><a title="Tech Specs" class="tech_specs" href="<?php bloginfo('home') ?>/support/tech-specs/">Tech Specs</a></li>
									</ul>
								</div>
						<?php  }  ?>
					<?php  }  ?>
					</div>
				<?php } ?>
			</div>
		</div>