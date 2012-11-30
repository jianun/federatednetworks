<div class="sidebar_buzz">
	<?php if (is_page_template('contact.php')){ ?>
		<div class="buzz_widget">
			<h3>FEDERATED NETWORKS</h3>
			<p>
				10 Four Seasons Place<br/>
				10th Floor<br/>
				Toronto, Ontario<br/>
				M9B 6H7<br/>
				CANADA<br/>
			</p>
			<h3>SALES</h3>
			<p><a href="mailto:PR@federatednetworks.com">PR@federatednetworks.com</a></p>
			<p>P: 416-6495800</p>
		</div>
	<?php } else { ?>
	<div class="buzz_widget">
		<h3>Media Inquiries</h3>
		<h4>Janis O’Reilly</h4>
		<p>Director, Marketing Communications</p>
		<p><a href="mailto:PR@federatednetworks.com">PR@federatednetworks.com</a></p>
	</div>
	<?php if (is_page_template('video_page.php')){ ?>
		<ul class="video_sidebar">
			<?php $_video  = get_post_meta($post->ID, 'video', true); if($_video !== '') ?>
			<?php echo $_video ?>
		</ul>
	<?php } ?>
	<?php if (is_page_template('media_kit_page.php')){ ?>
		<div class="right_column">
			<ul>
				<li><a href="/pdf/FN PressKit 2010.pdf" class="media-kit">Download Media Kit PDF</a></li>
			</ul>
		</div>
	<?php } }?>
</div>