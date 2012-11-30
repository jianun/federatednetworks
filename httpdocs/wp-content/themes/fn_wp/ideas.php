<?php
    /*
        Template Name: UserVoicePage
    */
?>
<?php get_header(); ?>
<!-- Content -->
		<div id="content">
			<div class="main">
				<div class="ideas_block">
					<iframe onload="this.style.height = '0px'; this.style.height = this.document.body.scrollHeight+(this.document.body.offsetHeight – this.document.body.clientHeight) + 'px';" src="http://supportclient.uservoice.com/forums/115099-general" name="myiframe" scrolling="auto" target="myiframe" style="width:960px;height:auto;"></iframe>
				</div>
			</div>
		</div>
<?php get_footer(); ?>