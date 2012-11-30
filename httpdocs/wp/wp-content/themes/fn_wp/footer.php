<!-- Footer -->
		<div id="footer">
			<div class="main">
	<!-- Top footer -->
				<div id="footer_tabs">
					<ul class="footer_menu">
							<li><a href="#f_press">Press</a></li>
							<li><a href="#f_news">News</a></li>
							<li><a href="#f_blog">Blog</a></li>
							<li><a href="#f_site_map">Site Map</a></li>
					</ul>
					<div class="top_footer">
						<div class="follow">
							<h4>FOLLOW US</h4>
							<ul>
								<li><a class="twitter" href="http://twitter.com/fednetworks">Twitter</a></li>
								<li><a class="youtube" href="http://www.youtube.com/user/FederatedNetworks">Youtube</a></li>
								<li><a class="facebook" href="http://www.facebook.com/pages/Federated-Networks/114541465271437">Facebook</a></li>
								<li><a class="linked_in" href="http://ca.linkedin.com/company/federated-networks?trk=ppro_cprof">linked in</a></li>
							</ul>
						</div>
					</div>
					<div id="f_press" class="f_tab footer_news f_press_label">
						<h3>Press</h3>
						<ul>
							<?php 
								$c_query = new WP_Query('showposts=3&orderby=date&order=desc&category_name=press');
									if ($c_query->have_posts()) :
							?>
								<?php  while ($c_query->have_posts()) : $c_query->the_post(); ?>
									<li>
										<strong><?php the_time('M d, Y ') ?></strong>
										<?php 
											global $more;    // Declare global $more (before the loop).
											$more = 0;       // Set (inside the loop) to display content above the more tag.
											echo substr(get_the_excerpt(), 0, 150);
										?>
										<a href="<?php the_permalink() ?>">More</a>
									</li>
								<?php endwhile //have posts ?>
							<?php endif //have posts ?>
						</ul>
					</div>
					<div id="f_news" class="f_tab footer_news ui-tabs-hide">
						<h3>News</h3>
						<ul>
							<?php 
								$c_query = new WP_Query('showposts=3&orderby=date&order=desc&category_name=news');
									if ($c_query->have_posts()) :
							?>
								<?php  while ($c_query->have_posts()) : $c_query->the_post(); ?>
									<li>
										<strong><?php the_time('M d, Y ') ?></strong>
										<?php 
											global $more;    // Declare global $more (before the loop).
											$more = 0;       // Set (inside the loop) to display content above the more tag.
											echo substr(get_the_excerpt(), 0, 150);
										?>
										<a href="<?php the_permalink() ?>">More</a>
									</li>
								<?php endwhile //have posts ?>
							<?php endif //have posts ?>
						</ul>
					</div>
					<div id="f_blog" class="f_tab footer_news blog_ico ui-tabs-hide">
						<h3>Blog</h3>
						<ul>
							<?php 
								$c_query = new WP_Query('showposts=3&orderby=date&order=desc&category_name=blog');
									if ($c_query->have_posts()) :
							?>
								<?php  while ($c_query->have_posts()) : $c_query->the_post(); ?>
									<li>
										<strong><?php the_time('M d, Y ') ?></strong>
										<?php 
											global $more;    // Declare global $more (before the loop).
											$more = 0;       // Set (inside the loop) to display content above the more tag.
											echo substr(get_the_excerpt(), 0, 150);
										?>
										<a href="<?php the_permalink() ?>">More</a>
									</li>
								<?php endwhile //have posts ?>
							<?php endif //have posts ?>
						</ul>
					</div>
					<div id="f_site_map" class="f_tab footer_news ui-tabs-hide f_sm_label">
						<h3>Site Map</h3>
						<div class="footer_col_wrap">
							<div class="footer_col">
								<h4><a href="<?php bloginfo('home') ?>/learn/why-fn/overview/">LEARN</a></h4>
								<ul>
									<li>
										<a href="<?php bloginfo('home') ?>/learn/why-fn/overview/">Why FN?</a>
										<ul>
											<li><a href="<?php bloginfo('home') ?>/learn/why-fn/overview/">Overview</a></li>
											<li><a href="<?php bloginfo('home') ?>/learn/why-fn/heavy-duty-security/">Heavy Duty Security</a></li>
											<li><a href="<?php bloginfo('home') ?>/learn/why-fn/easy-to-use/">Easy to Use</a></li>
											<li><a href="<?php bloginfo('home') ?>/learn/why-fn/cloud-native/">Cloud Native</a></li>
											<li><a href="<?php bloginfo('home') ?>/learn/why-fn/end-2-end/">End-2-End</a></li>
											<li><a href="<?php bloginfo('home') ?>/learn/why-fn/security-as-a-service/">Security as a Service</a></li>
											<li><a href="<?php bloginfo('home') ?>/learn/why-fn/radically-affordable/">Radically Affordable</a></li>
										</ul>
									</li>
									<!-- <li><a href="<?php // bloginfo('home') ?>/learn/attack-demos/">Attack Demos</a></li>
									<li><a class="fancy-link" href="#sorry">Quick Tour</a></li> -->
									<li>
										<a href="<?php bloginfo('home') ?>/learn/technology/architecture/">Technology</a>
										<ul>
											<li><a href="<?php bloginfo('home') ?>/learn/technology/architecture/">Architecture</a></li>
											<li><a href="<?php bloginfo('home') ?>/learn/technology/patents/">Patents</a></li>
										</ul>
									</li>
								</ul>
							</div>
							<div class="footer_col">
								<h4><a href="<?php bloginfo('home') ?>/all-products/">PRODUCTS</a></h4>
								<ul>
									<li><a class="fancy-link" href="#sorry">Client Agent</a></li>
									<li><a class="fancy-link" href="#sorry">Server Agent</a></li>
									<li><a class="fancy-link" href="#sorry">eID</a></li>
									<li><a class="fancy-link" href="#sorry">eVote</a></li>
									<li><a class="fancy-link" href="#sorry">eStatement</a></li>
								</ul>
							</div>
							<div class="footer_col">
								<h4><a  class="fancy-link" href="#sorry">SUPPORT</a></h4>
								<ul>
									<li><a class="fancy-link" href="#sorry">Technical Support</a></li>
									<li><a class="fancy-link" href="#sorry">Account Support</a></li>
									<li><a class="fancy-link" href="#sorry">Sales Sopport</a></li>
								</ul>
							</div>
							<div class="footer_col">
								<?php 
									$category_id_news = get_cat_ID( 'news' );
									$category_link_news = get_category_link( $category_id_news );
									$category_id_press = get_cat_ID( 'press' );
									$category_link_press = get_category_link( $category_id_press );
								 ?>
								<h4><a href="<?php echo $category_link_news ?>">COMPANY</a></h4>
								<ul>
									<li>
										<a href="<?php echo $category_link_news ?>">News/Press/Events</a>
										<ul>
											<li><a href="<?php echo $category_link_news ?>">News</a></li>
											<li><a href="<?php echo $category_link_press ?>">Press Releases</a></li>
											<li><a href="<?php bloginfo('home') ?>/company/federated-networks-presentation-at-demo-fall-2010-in-silicon-valley/">Video</a></li>
											<li><a href="<?php bloginfo('home') ?>/company/events/">Events</a></li>
											<li><a href="<?php bloginfo('home') ?>/company/media-kit/">Media Kit</a></li>
										</ul>
									</li>
									<li>
										<a href="<?php bloginfo('home') ?>/company/about-us/about/">About Us</a>
										<ul>
											<li><a href="<?php bloginfo('home') ?>/company/about-us/about/">Overview</a></li>
											<li><a href="<?php bloginfo('home') ?>/company/about-us/team/">Team</a></li>
											<li><a href="<?php bloginfo('home') ?>/company/about-us/investors/">Investors</a></li>
											<li><a href="<?php bloginfo('home') ?>/company/about-us/contact/">Contact Us</a></li>
										</ul>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<!-- Sub footer -->
		<div class="sub_footer">
			<div class="main">
				<p>Copyright 2009. Federated Networks. All rights reserved</p>
			</div>
		</div>
<!-- Fancy -->
		<div class="fancy-none">
			<div id="sorry">
				<div class="fn-form-fancy">
					<h3>This feature will be available summer 2011.</h3>
					<p>For more information please <a href="<?php bloginfo('home') ?>/company/contact/">Contact Us.</a></p>
				</div>
			</div>
			<?php if(is_page('attack-demos')){ ?>
				<div id="zemana">
				<div class="fn-form-company">
					<h3>Zemana</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="webroot">
				<div class="fn-form-company">
					<h3>Webroot</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="avira">
				<div class="fn-form-company">
					<h3>Avira</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="avg">
				<div class="fn-form-company">
					<h3>AVG</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="panda">
				<div class="fn-form-company">
					<h3>Panda</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="kaspersky">
				<div class="fn-form-company">
					<h3>Kaspersky</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="mcafee">
				<div class="fn-form-company">
					<h3>McAfee</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="trend-micro">
				<div class="fn-form-company">
					<h3>Trend Micro</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="symantec">
				<div class="fn-form-company">
					<h3>Symantec</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="voltage">
				<div class="fn-form-company">
					<h3>Voltage</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="pgp">
				<div class="fn-form-company">
					<h3>PGP</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="cplab">
				<div class="fn-form-company">
					<h3>CP Lab.com</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="hitek-software">
				<div class="fn-form-company">
					<h3>HiTek Software</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="new-softwares">
				<div class="fn-form-company">
					<h3>New Softwares</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="open-ssl">
				<div class="fn-form-company">
					<h3>Open SSL</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="ping-identity">
				<div class="fn-form-company">
					<h3>Ping Identity</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="open-id">
				<div class="fn-form-company">
					<h3>Open ID</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="rsa">
				<div class="fn-form-company">
					<h3>RSA</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="versign">
				<div class="fn-form-company">
					<h3>Versign</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="trusteer">
				<div class="fn-form-company">
					<h3>Trusteer</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="fsprolabs">
				<div class="fn-form-company">
					<h3>fsprolabs</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="qfxsoftware">
				<div class="fn-form-company">
					<h3>qfxsoftware</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="eset">
				<div class="fn-form-company">
					<h3>Eset</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="bit-defender">
				<div class="fn-form-company">
					<h3>Bit Defender</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="cp-lab-com">
				<div class="fn-form-company">
					<h3>cp-lab.com</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="passcom-team">
				<div class="fn-form-company">
					<h3>Passcom Team</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="carl-roth">
				<div class="fn-form-company">
					<h3>Carl L. Roth</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="sow">
				<div class="fn-form-company">
					<h3>Sow</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="moonsoftware">
				<div class="fn-form-company">
					<h3>Moonsoftware</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="mirrasoft">
				<div class="fn-form-company">
					<h3>Mirrasoft</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="billeo">
				<div class="fn-form-company">
					<h3>Billeo</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<div id="callpod">
				<div class="fn-form-company">
					<h3>Callpod</h3>
					<dl>
						<dt>Product Name</dt>
							<dd>Lorem ipsumt.</dd>
						<dt>Product Price</dt>
							<dd>$59.00</dd>
						<dt>Category</dt>
							<dd>Antivirus</dd>
						<dt>Description</dt>
							<dd>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat.</dd>
					</dl>
				</div>
			</div>
			<?php } ?>
			<?php if(is_page('architecture')){ ?>
				<div id="fn-agent">
					<div class="fn-agent-wrap">
						<div class="label-2">&nbsp;</div>
						<h3>FN Secure Agent&trade;</h3>
						<p>FN's unhackable client enables users to connect securely to all things digital. This gives you full control over the privacy and confidentiality of your data. Files, passwords and online communications including email and social networking are 100% secure.</p>
					</div>
				</div>
				<div id="mcas">
					<div class="mcas-wrap">
						<div class="label-1">&nbsp;</div>
						<h3>Meta Certificate Authority Services&trade;</h3>
						<p>FN's Application Security Level Protocol ("ASL") supports a heterogeneous group of agents, each of which can securely perform their roles in a networked PKI system. FN's Meta Certificate Authority services enables the confidential (with zero knowledge) exchange, validation, verification and secure protection of evolving and unbounded data definitions, the data itself and multi-party agreements, in full compliance with social, economic and legal norms and regulations. </p>
						<p>Important differences between FN's revolutionary Meta Certificate Authority Services&trade; vs. existing Certificate Authority solutions include:</p>
						<table>
							<tr>
								<th>Meta Certificate Authority</th>
								<th>Existing Certificate Authority</th>
								<th>The FN Advantage</th>
							</tr>
							<tr>
								<td>Zero Knowledge</td>
								<td>Full Knowledge</td>
								<td>Supports private/confidential data</td>
							</tr>
							<tr>
								<td>Dynamically Extensible</td>
								<td>Static</td>
								<td>Real time modification to reflect changing user data</td>
							</tr>
							<tr>
								<td>Real-Time Revoke</td>
								<td>Revoke List "Challenges"</td>
								<td>Globally scalable (Important for identity and other business critical services.)</td>
							</tr>
							<tr>
								<td>Natively Supports Sec DNS</td>
								<td>Sec DNS is New Undertaking</td>
								<td>No instantiation problem</td>
							</tr>
							<tr>
								<td>Multi Party Agreement</td>
								<td>Single Party Policy</td>
								<td>Agreement-based enforcement (Singleton decision-making and decision enforcement.)</td>
							</tr>
							<tr>
								<td>No Single Root</td>
								<td>Single Root</td>
								<td>Compromised private key does not compromise whole system</td>
							</tr>
							<tr>
								<td>Fails Gracefully</td>
								<td>Fails Hard</td>
								<td>Distributing new keys is seamless</td>
							</tr>
						</table>
					</div>
				</div>
				<div id="ccss">
					<div class="ccss-wpap">
						<div class="label-3">&nbsp;</div>
						<h3>Cloud Connect Security Services&trade;</h3>
						<p>FN has a unique zero-knowledge system that secures the authenticity and integrity of business critical data against both external (hacking) as well as insider threats (rogue employees). Unparalleled end-to-end security coupled with an extremely low footprint implementation (including site hosted as well as cloud services) creates a highly compelling enterprise security solution.</p>
					</div>
				</div>
				<div id="aaas">
					<div class="aaas-wrap">
						<div class="label-4">&nbsp;</div>
						<h3>Authentic Attribute Authority Services&trade;</h3>
						<p>FN's security solution establishes an authority to validate the authenticity of data in social, economic and legal contexts. This advanced solution ushers in a new era of cyber confidence and trust for today's evolving world of web-enabled interactions. FN's Authentic Attribute Authority Service can confirm, with certainty and without any knowledge of the actual data, the representations of any party simply by checking with the data attributes owner. </p>
						<p>Real-life applications include:</p>
						<ul>
							<li>An employer being able to verify the degree and graduation status of a person representing their Ivy League education.</li>
							<li>Conducting a zero-knowledge verification of a business tax ID number with the Revenue Agency to authenticate that a person validly represents a business.</li>
						</ul>
					</div>
				</div>
			<?php } ?>
		</div>
		<script type="text/javascript">
			//<!-- [CDATA[
				  var _gaq = _gaq || [];
				  _gaq.push(['_setAccount', 'UA-1115384-10']);
				  _gaq.push(['_trackPageview']);
				
				  (function() {
					var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
					ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
					var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
				  })();
				  
			//]] -->
		</script>
		<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
		<script type="text/javascript">
				stLight.options({
						publisher:'08252a75-a87d-4025-b708-fb7d227d8945'
				});
		</script>
		<?php wp_footer(); ?>
		<?php if(is_page('contact')) {?>
			<script type="text/javascript">
				//<![CDATA[
					jQuery("#default-usage-select").selectbox();
				//]]>
			</script>
		<?php } ?>
	</body>
</html>