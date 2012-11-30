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