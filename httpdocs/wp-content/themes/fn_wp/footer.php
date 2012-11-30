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
								<h4><a title="WHY FN?" href="<?php bloginfo('home') ?>/why-fn/key-reasons/proven-results/">WHY FN?</a></h4>
								<ul>
									<li>
										<h5><a title="Key Reasons" href="<?php bloginfo('home') ?>/why-fn/key-reasons/proven-results/">Key Reasons</a></h5>
										<ul>
											<li><a title="EFFECTIVE SOLUTIONSS" href="<?php bloginfo('home') ?>/why-fn/key-reasons/proven-results/">Effective Solutions</a></li>
											<li><a title="THE BUSINESS CASE" href="<?php bloginfo('home') ?>/why-fn/key-reasons/radicaly-affordable/">The Business Case</a></li>
											<li><a title="TECHNOLOGY" href="<?php bloginfo('home') ?>/why-fn/key-reasons/agent-architecture/">Technology</a></li>
										</ul>
									</li>
									<li><h5><a class="fancy-link" href="#sorry">ATTACK DEMOS</a></h5></li>
									<li><h5><a class="fancy-link" href="#sorry">SECURITY CENTER</a></h5></li>
									<li><h5><a class="fancy-link" href="#sorry">SOLUTIONS</a></h5></li>
								</ul>
							</div>
							<div class="footer_col">
								<h4><a title="PRODUCTS" href="<?php bloginfo('home') ?>/all-products/">PRODUCTS</a></h4>
								<ul>
									<li>
										<h5><a title="Client Agent" class="fancy-link" href="#sorry">CLIENT</a></h5>
										<ul>
											<li><a class="fancy-link" href="#sorry">Desktop Security Agent</a></li>
											<li><a class="fancy-link" href="#sorry">Stealth Browser Add-on</a></li>
											<li><a class="fancy-link" href="#sorry">eID</a></li>
											<li><a class="fancy-link" href="#sorry">Enterprise Password Manager</a></li>
											<li><a class="fancy-link" href="#sorry">Phone</a></li>
										</ul>
										<h5><a title="Client Agent" class="fancy-link" href="#sorry">SERVER</a></h5>
										<ul>
											<li><a class="fancy-link" href="#sorry">Server Agent</a></li>
											<li><a class="fancy-link" href="#sorry">Multi-User Login</a></li>
											<li><a class="fancy-link" href="#sorry">Financial Services Edition</a></li>
											<li><a class="fancy-link" href="#sorry">eStatements &amp; eBills</a></li>
										</ul>
										<h5><a title="Client Agent" class="fancy-link" href="#sorry">SERVICES</a></h5>
										<ul>
											<li><a class="fancy-link" href="#sorry">Authentic Attribute  Authority</a></li>
											<li><a class="fancy-link" href="#sorry">eVote</a></li>
											<li><a class="fancy-link" href="#sorry">eGovernment ID</a></li>
										</ul>
									</li>
								</ul>
							</div>
							<div class="footer_col">
								<h4><a title="SUPPORT" class="fancy-link" href="#sorry">SUPPORT</a></h4>
								<ul>
									<li>
										<h5><a title="Technical Support" href="#">TECHNICAL SUPPORT</a></h5>
										<ul>
											<li><a title="Help" class="fancy-link" href="#sorry">Help</a></li>
											<li><a title="Tutorials" class="fancy-link" href="#sorry">Tutorials</a></li>
											<li><a title="Ideas Forum" class="fancy-link" href="#sorry">Ideas Forum</a></li>
											<li><a title="Tech Specs" class="fancy-link" href="#sorry">Tech Specs</a></li>
										</ul>
									</li>
									<li><h5><a title="Account Support" class="fancy-link" href="#sorry">ACCOUNT SUPPORT</a></h5></li>
									<li><h5><a title="SALES SUPPORT" class="fancy-link" href="#sorry">SALES SUPPORT</a></h5></li>
								</ul>
							</div>
							<div class="footer_col">
								<?php
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
								<h4><a title="MORE FN" href="<?php echo $category_link; ?>">MORE</a></h4>
								<ul>
									<li>
										<h5><a title="BUZZ" href="/2011/?cat=3">BUZZ</a></h5>
										<ul>
											<li><a title="FN News" href="/2011/?cat=3">News</a></li>
											<li><a title="Press Releases" href="/2011/?cat=4">Press Releases</a></li>
											<li><a title="Video" href="<?php bloginfo('home') ?>/more/buzz/video/conferences/">Video</a></li>
											<li><a title="Events" href="<?php bloginfo('home') ?>/more/buzz/fn-exhibits-at-privacy-by-design-conference/">Events</a></li>
											<li><a title="Media Kit" href="<?php bloginfo('home') ?>/more/buzz/media-kit/">Media Kit</a></li>
										</ul>
									</li>
									<li>
										<h5><a title="" href="<?php bloginfo('home') ?>/more/company/about-us/overview/">Company</a></h5>
										<ul>
											<li><a title="About us" href="<?php bloginfo('home') ?>/more/company/about-us/overview/">About Us</a></li>
											<li><a title="Team" href="<?php bloginfo('home') ?>/more/company/team/management/">Team</a></li>
										</ul>
									</li>
									<li>
										<h5><a title="FN BLOG" href="<?php echo $category_link; ?>">BLOG</a></h5>
										<ul>
											<li><a title="Consumer Secutiry" href="<?php echo $category_link_consumersecurity ?>">Consumer Security</a></li>
											<li><a title="Pro Secutiry" href="<?php echo $category_link_prosecurity ?>">Pro Security</a></li>
											<li><a title="Fn Products" href="<?php echo $link_fnporoducts ?>">FN Products</a></li>
										</ul>
									</li>
									<li><h5><a title="CONTACT US" href="<?php bloginfo('home') ?>/more/contact-us/">CONTACT US</a></h5></li>
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
				<p class="alignleft">Connect secure to all things digital, with uncompromising security, privacy and trust.</p>
				<p class="alignright">Copyright 2012. Federated Networks. All rights reserved. </p>
			</div>
		</div>
<!-- Fancy -->
		<div class="fancy-none">
			<div id="sorry">
				<div class="fn-form-fancy">
					<h3>This feature will be available Spring/Summer 2012.</h3>
					<p>For more information please <a href="<?php bloginfo('home') ?>/more/contact-us/">Contact Us.</a></p>
				</div>
			</div>
			<div id="video_1">
				<iframe width="425" height="349" src="http://www.youtube.com/embed/DY7IWA1h7lg" frameborder="0" allowfullscreen></iframe>
			</div>
			<?php if(is_page('system-architecture')){ ?>
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
		<!-- Begin Optify Tracking Code. Please place immediately before closing </body> tag -->
		<script type="text/javascript">
			var _opt = _opt || [];
			_opt.push([ 'view', 'L95WJF0L' ]);
			(function() {
			var scr = document.createElement('script'); scr.type='text/javascript'; scr.async=true;
			scr.src = '//service.optify.net/opt-v2.js';
			var other = document.getElementsByTagName('script')[0]; other.parentNode.insertBefore(scr, other);
			})();
		</script>
	</body>
</html>