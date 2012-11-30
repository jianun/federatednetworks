<?php
    /*
        Template Name: Archive
    */
?>
<?php get_header(); ?>
<!-- Content -->
			<div class="main">
				<div id="content">
					<div class="blog_wrapp">
	<!-- Blog Content -->
						<div class="blog_content">
							<div class="post">
								<h1>Blog Archives</h1>
								<ul id="arch">
								<?php
     if( function_exists('collapsArch') ) {
      collapsArch();
     } else {
      echo "<ul>\n";
      wp_get_archives();
      echo "</ul>\n";
     }
    ?>
</ul>
							</div>
						</div>
						<?php get_sidebar(); ?>
					</div>
				</div>
			</div>
		</div>
<?php get_footer(); ?>
