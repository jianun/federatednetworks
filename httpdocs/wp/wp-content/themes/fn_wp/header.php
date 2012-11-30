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
					<strong class="logo"><a title="<?php bloginfo('name'); ?>" href="<?php bloginfo('home') ?>"><?php bloginfo('name'); ?></a></strong>
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
								$isBlog = $isProducts = $isLearn = $isCompany = $isSupport = '';
								if(in_category('blog')&&!(in_category('news') || in_category('press')) || is_page('about-this-blog')){
									$isBlog = 'class="active"';
								} elseif(is_page('all-products')){
									$isProducts = 'class="active"';
								} elseif(is_page(array('overview','unbeatable','easy-to-use','cloud-native','end-2-end','security-as-a-service','radically-affordable','architecture','patents','attack-demos','attacks-demo-library'))) {
									$isLearn = 'class="active"';
								} if(is_page(array('about','team','investors','contact','media-kit','events','federated-networks-presentation-at-demo-fall-2010-in-silicon-valley','inlab-ventures-media-interview-with-federated-networks')) || (in_category('news') || in_category('press'))) {
									$isCompany = 'class="active"';
								}
								if(is_page(array('support','ideas-forum', 'technical-support', 'tech-specs', 'tutorials'))){
									$isSupport = 'class="active"';
								}
							?>
							<ul>
								<li <?php echo $isLearn ?>><a class="learn_item" href="<?php bloginfo('home') ?>/learn/why-fn/overview/">LEARN</a></li>
								<li <?php echo $isProducts ?>><a class="products_item"  href="<?php bloginfo('home') ?>/all-products/">PRODUCTS</a></li>
								<li <?php echo $isSupport ?>><a class="support_item fancy-link" href="#sorry">SUPPORT</a></li>
								<li <?php echo $isBlog ?>><a class="blog_item" href="<?php echo $category_link; ?>">Blog</a></li>
								<li <?php echo $isCompany ?>><a class="company_item" href="<?php echo $category_link_news ?>">COMPANY</a></li>
							</ul>
						</div>
						<div class="lang">
							<ul>
								<li class="active"><a title="English" class="en fancy-link" href="#sorry">English</a></li>
								<li><a title="Français" class="fr" href="#">Français</a></li>
								<li><a title="Español" class="es" href="#">Español</a></li>
								<li><a title="Deutsch" class="dk" href="#">Deutsch</a></li>
								<li><a title="Italiano" class="it" href="#">Italiano</a></li>
								<li><a title="Português" class="pt" href="#">Português</a></li>
								<li><a title="Nederlands"  class="nl" href="#">Nederlands</a></li>
								<li><a title="中文"  class="cn" href="#">中文</a></li>
								<li><a title="日本語" class="jp" href="#">日本語</a></li>
								<li><a title="한국어" class="kr" href="#">한국어</a></li>
								<li><a title="Русский" class="ru" href="#">Русский</a></li>
							</ul>
						</div>
						<a class="login orange_login fancy-link" href="#sorry">Login</a>
					</div>
				</div>
				<?php if(!is_front_page()) { ?>
					<div class="sub_title">
						<?php if(is_page(array('overview','unbeatable','easy-to-use','cloud-native','end-2-end','security-as-a-service','radically-affordable','architecture','patents','attack-demos','attacks-demo-library'))) { 
						$isWhyFn = $isTechnology = $isAttackDemos = '';
						if(is_page(array('overview','unbeatable','easy-to-use','cloud-native','end-2-end','security-as-a-service','radically-affordable'))){
							$isWhyFn = 'class="active"';
						} elseif(is_page(array('architecture','patents'))){
							$isTechnology = 'class="active"';
						} elseif(is_page(array('attack-demos','attacks-demo-library'))){
							$isAttackDemos = 'class="active"';
						}
						?>
							<h2>LEARN</h2>
							<ul class="sub_menu">
								<li <?php echo $isWhyFn ?>><a href="<?php bloginfo('home') ?>/learn/why-fn/overview/">Why FN?</a></li>
								<li <?php echo $isTechnology ?>><a href="<?php bloginfo('home') ?>/learn/technology/architecture/">Technology</a></li>
							</ul>
						<?php } elseif((is_single() || is_home() || is_archive() || is_category() || is_page('about-this-blog'))&&!(in_category('news') || in_category('press')) ){ 
							$isNewPosts = $isAboutThisBlog = '';
							if (is_category('blog') || in_category('blog')){
								$isNewPosts = 'class="active"';
							}elseif(is_page('about-this-blog')){
								$isAboutThisBlog = 'class="active"';
							}
						?>
							<h2>FN BLOG</h2>
							<ul class="sub_menu">
								<?php
									// Get the ID of a given category
									$category_id = get_cat_ID( 'blog' );
									// Get the URL of this category
									$category_link = get_category_link( $category_id );
								?>
								<li <?php echo $isNewPosts ?>><a href="<?php echo $category_link; ?>">New Posts</a></li>
								<!--<li><a href="<?php// echo get_option('home'); ?>/archive/">Archives</a></li>-->
								<li <?php echo $isAboutThisBlog ?>><a href="<?php bloginfo('home') ?>/about-this-blog/">About This Blog</a></li>
							</ul>
						<?php } elseif(is_page('all-products')){?>
							<h2>PRODUCTS</h2>
							<!-- <ul class="user_menu">
								<li><a  class="fancy-link" href="#sorry" >My Account</a></li>
								<li><a class="basket fancy-link" href="#sorry">Basket</a></li>
							</ul> -->
						<?php }elseif(is_page(array('about','team','investors','contact','media-kit','events','federated-networks-presentation-at-demo-fall-2010-in-silicon-valley','inlab-ventures-media-interview-with-federated-networks')) || is_category('news') || in_category('news') || is_category('press') || in_category('press')) {
								$isAboutUs = $isNewsPressEvents = '';
								if(is_page(array('about','team','investors','contact'))){
									$isAboutUs = 'class="active"';
								}elseif((is_page(array('media-kit','events','federated-networks-presentation-at-demo-fall-2010-in-silicon-valley','inlab-ventures-media-interview-with-federated-networks')) || in_category('news') || in_category('press'))){
									$isNewsPressEvents = 'class="active"';
								}
							 ?>
							<h2>COMPANY</h2>
							<ul class="sub_menu">
								<li <?php echo $isNewsPressEvents ?>><a href="<?php echo $category_link_news; ?>">News/Press/Events</a></li>
								<li <?php echo $isAboutUs ?>><a href="<?php bloginfo('home') ?>/company/about-us/about/">About Us</a></li>
							</ul>
					 <?php } elseif(is_page(array('support','ideas-forum', 'technical-support', 'tech-specs', 'tutorials'))) { ?>
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
										<li<?php echo $istechnicalsupport ?>><a class="help" href="<?php bloginfo('home') ?>/support/technical-support/">Help</a></li>
										<li<?php echo $istutorials ?>><a class="tutorials" href="<?php bloginfo('home') ?>/support/tutorials/">Tutorials</a></li>
										<li<?php echo $isideasforum ?>><a class="ideas_forum" href="<?php bloginfo('home') ?>/support/ideas-forum/">Ideas Forum</a></li>
										<li<?php echo $techspecs ?>><a class="tech_specs" href="<?php bloginfo('home') ?>/support/tech-specs/">Tech Specs</a></li>
									</ul>
								</div>
						<?php  }  ?>
					<?php  }  ?>
					</div>
				<?php } ?>
			</div>
		</div>