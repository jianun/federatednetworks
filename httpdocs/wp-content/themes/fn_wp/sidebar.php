<?php if(is_page(array('vision-and-values','overview','fn-at-a-glance', 'investors'))){ 
	$isVisionAndValues = $isOverview = $isFNGlance = $isInvestors = '';
	if(is_page('vision-and-values')){
		$isVisionAndValues = 'class="active"';
	} elseif(is_page('overview')){
		$isOverview = 'class="active"';
	} elseif(is_page('fn-at-a-glance')){
		$isFNGlance = 'class="active"';
	} elseif(is_page('investors')){
		$isInvestors = 'class="active"';
	}
?>
	<div class="sidebar">
					<ul>
						<li <?php echo $isOverview; ?>><a title="Overview" href="<?php bloginfo('home') ?>/more/company/about-us/overview/">Overview</a></li>
						<li <?php echo $isFNGlance; ?>><a title="FN at a Glance" href="<?php bloginfo('home') ?>/more/company/about-us/fn-at-a-glance/">FN at a Glance</a></li>
						<li <?php echo $isVisionAndValues ?>><a title="Values and Vision" href="<?php bloginfo('home') ?>/more/company/about-us/vision-and-values/">Vision and Values</a></li>
						<li <?php echo $isInvestors; ?>><a title="Investors" href="<?php bloginfo('home') ?>/more/company/about-us/investors/">Investors</a></li>
					</ul>
				</div>
<?php }elseif(is_page(array('management','advisory-board'))){ 
	$isManamement = $isAdvisoryBoard = '';
	if(is_page('management')){
		$isManamement = 'class="active"';
	} elseif(is_page('advisory-board')){
		$isAdvisoryBoard = 'class="active"';
	}

?>
	<div class="sidebar">
					<ul>
						<li <?php echo $isManamement?>><a title="Management" href="<?php bloginfo('home') ?>/more/company/team/management/">Management</a></li>
						<!--<li <?php // echo $isAdvisoryBoard?>><a title="Advisory Board" href="<?php // bloginfo('home') ?>/more/company/team/advisory-board/">Advisory Board</a></li>-->
					</ul>
				</div>
<?php }elseif(is_page(array('software-engeneers','sales-marketing','other'))){
	$isSoftwareEngineers = $isSalesMarketing = $isOther = '';
	if (is_page('software-engeneers')){
		$isSoftwareEngineers = 'class="active"';
	} elseif(is_page('sales-marketing')){
		$isSalesMarketing = 'class="active"';
	} elseif('other'){
		$isOther = 'class="active"';
	}
?>
<div class="sidebar">
					<ul>
						<li <?php echo $isSoftwareEngineers; ?>><a title="Software Engineers" href="<?php bloginfo('home') ?>/more/company/careers/software-engineers/">Software Engineers</a></li>
						<li <?php echo $isSalesMarketing; ?>><a title="Sales &amp; Marketing" href="<?php bloginfo('home') ?>/more/company/careers/sales-marketing/">Sales &amp; Marketing</a></li>
						<li <?php echo $isOther; ?>><a title="Other" href="<?php bloginfo('home') ?>/more/company/careers/other/">Other</a></li>
					</ul>
				</div>

<?php } elseif(in_category('news') || is_category('news')) {
	$isOld = $isNew = $isD = '';
	$isD = the_date('Y','' ,'' ,false);
	if ($isD == 2010 ){
		$isOld = 'class="active"';
	} 
	if($isD == 2011){
		$isNew = 'class="active"';
	}
				 ?>
<div class="sidebar">
	
	<ul>
		<li <?php echo $isNew; ?>><a href="/2011/?cat=3">2011</a></li>
		<li <?php echo $isOld; ?>><a href="/2010/?cat=3">2010</a></li>
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
<?php } elseif(in_category('press') || is_category('press') ) {
	
	$isOld = $isNew = $isD = '';
	$isD = the_date('Y','' ,'' ,false);
	if ($isD == 2010 ){
		$isOld = 'class="active"';
	} 
	if($isD == 2011){
		$isNew = 'class="active"';
	}
				 ?>
<div class="sidebar">
	
	<ul>
		<li <?php echo $isNew; ?>><a href="/2011/?cat=4">2011</a></li>
		<li <?php echo $isOld; ?>><a href="/2010/?cat=4">2010</a></li>
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
<?php } elseif(is_page_template('video_page.php')) {
	
	$isConferences = $isInterviews = '';
	if (is_page('conferences')){
		$isConferences = 'class="active"';
	} elseif(is_page('interviews')){
		$isInterviews = 'class="active"';
	} 
				 ?>
<div class="sidebar">
	<ul>
		<li <?php echo $isConferences; ?>><a title="Conference Videos" href="/more/buzz/video/conferences/">Conferences</a></li>
		<li <?php echo $isInterviews; ?>><a title="Interview Videos" href="/more/buzz/video/interviews/">Interviews</a></li>
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
<?php } elseif(is_page_template('events_page.php')) {
	
	$isEv2010 = $isEv2011 = '';
	if (is_page('events')){
		$isEv2010 = 'class="active"';
	} elseif(is_page('fn-exhibits-at-privacy-by-design-conference')){
		$isEv2011 = 'class="active"';
	} 
				 ?>
<div class="sidebar">
	<ul>
		<li <?php echo $isEv2011; ?>><a title="Interview Videos" href="/more/buzz/fn-exhibits-at-privacy-by-design-conference/">2011</a></li>
		<li <?php echo $isEv2010; ?>><a title="Conference Videos" href="/more/buzz/events/">2010</a></li>
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
							<div class="widget widget_border">
								<?php include (TEMPLATEPATH . "/searchform.php"); ?>
							</div>
								<?php  if ( !function_exists('dynamic_sidebar')
									|| !dynamic_sidebar('Sidebar') ) : 
								 endif; ?>
							<div class="widget tags widget_border">
								<h3>Tags</h3>
								<?php wp_tag_cloud('smallest=11&largest=11&unit=px&format=list'); ?>
							</div>
							<div class="twitter_block">
								<div class="twitter_block_header">
									<h3><a title="FN on Twitter" href="http://twitter.com/#!/FedNetworks">FN on Twitter</a></h3>
								</div>
								<div class="twitter_block_contnet">
									<?php if (function_exists('twitter_messages')) twitter_messages('FedNetworks', 5, true, true, false, true, false, false); ?> 
								</div>
								<div class="twitter_block_footer"><a title="FN on Twitter" href="http://twitter.com/#!/FedNetworks">FN on Twitter</a></div>
							</div>
							<div class="widget widget_border">
								<h3>What We're Reading</h3>
								<ul>
									<?php get_links('-1', '<li>', '</li>'); ?>
								</ul>
							</div>
							<div class="widget">
								<h3>Archive</h3>
								<ul>
									<?php wp_get_archives('cat=1'); ?>
								</ul>
							</div>
						</div>
<?php }