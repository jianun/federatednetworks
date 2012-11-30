<?php
    /*
        Template Name: MediaKitDownloadsPage
    */
?>
<?php get_header(); ?>
<!-- Content -->
		<div id="content">
			<div class="main">
	<!-- Main content -->
				<div class="main_content">
					<div class="page_title">
						<h1>Media Kit Downloads</h1>
						<ul class="share_this">
							<li><script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=08252a75-a87d-4025-b708-fb7d227d8945&amp;type=website&amp;post_services=email%2Cfacebook%2Ctwitter%2Cgbuzz%2Cmyspace%2Cdigg%2Csms%2Cwindows_live%2Cdelicious%2Cstumbleupon%2Creddit%2Cgoogle_bmarks%2Clinkedin%2Cbebo%2Cybuzz%2Cblogger%2Cyahoo_bmarks%2Cmixx%2Ctechnorati%2Cfriendfeed%2Cpropeller%2Cwordpress%2Cnewsvine"></script></li>
							<li><a class="link_print" href="javascript: window.print();">Print</a></li>
						</ul>
					</div>
					<div class="page_content">
						<div class="to_col_content">
							<h2>FN Logo</h2>
							<ul class="logos">
								<li><img alt="FN Logo" src="<?php bloginfo('template_directory'); ?>/images/logos.png" /></li>
							</ul>
							<ul class="btn_downloads">
								<li><a href="/downloads/FederatedNetworks_Logo_PNG.zip"><span>PNG</span></a></li>
								<li><a href="/downloads/FederatedNetworks_Logo_EPS.zip"><span>EPS</span></a></li>
							</ul>
							<h2>Screenshots</h2>
							<img alt="Screenshots" src="<?php bloginfo('template_directory'); ?>/images/screen.png" />
							<ul class="btn_downloads">
								<li><a href="/downloads/Federated_Networks_Screenshots_PNG.zip"><span>PNG</span></a></li>
								<li><a href="/downloads/Federated_Networks_Screenshots_300DPI.zip"><span>300 DPI TIFF</span></a></li>
							</ul>
						</div>
						<div class="right_column">
							<ul>
								<li><a href="/pdf/FN PressKit 2010.pdf" class="media-kit">Download Media Kit PDF</a></li>
							</ul>
						</div>
					</div>
				</div>
	<!-- sidebar -->
				<?php get_sidebar(); ?>
			</div>
		</div>
<?php get_footer(); ?>