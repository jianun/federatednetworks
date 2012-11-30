<!-- Comments -->
	<div class="comments">
			<?php if ( post_password_required() ) : ?>
				<p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'twentyten' ); ?></p>
			<?php
					return;
				endif;
			?>
<?php if ( have_comments() ) : ?>
			<h2>Comments</h2>
			<ul>
				<?php foreach ($comments as $comment) : // The Comments Loop ?>
					<li id="comment-<?php comment_ID() ?>">
						<div class="comment_header">
							<div class="comment_footer">
								<div class="comment_title">
									<?php comment_author_link() ?>
									<span>said</span>
									<span><?php comment_time() ?> on <?php comment_date('M jS, Y') ?></span>
								</div>
								<?php comment_text() ?>
							</div>
						</div>
					</li>
				<?php endforeach; /* end for each comment */ ?>
			</ul>
<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<div class="navigation">
				<div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Older Comments', 'twentyten' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer Comments <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?></div>
			</div>
<?php endif; // check for comment navigation ?>

<?php else : // or, if we don't have comments:

	if ( ! comments_open() ) :
?>
	<p class="nocomments"><?php _e( 'Comments are closed.', 'twentyten' ); ?></p>
	<?php endif; // end ! comments_open() ?>

<?php endif; // end have_comments() ?>
					<!-- Comment form -->
								<SCRIPT type="text/javascript">

								<!--
								   function checK(comment_form) {
								      <?php if($user_ID) : ?>
								              if (document.comment_form.comment.value=='') {alert("Please enter your message.");document.comment_form.comment.focus();return false}
								           <?php else : ?>

								if (document.comment_form.author.value=='') {alert("Please enter your name.");document.comment_form.author.focus();return false}
								if (document.comment_form.email.value=='') {alert("Please enter your email address.");document.comment_form.email.focus();return false}

								if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(document.comment_form.email.value)){
								if (document.comment_form.comment.value=='') {alert("Please enter your message.");document.comment_form.comment.focus();return false};
								return true
								}
								alert('Please enter your email address.');document.comment_form.email.select()
								return false

								<?php endif; // endif user_ID ?>

								}

								//-->

</SCRIPT>
								<form name="comment_form" action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform" onsubmit="return checK ( );">
									<fieldset>
										<?php $req = 0; if ( is_user_logged_in() ) : ?>
											<p class="p_10">Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo wp_logout_url(get_permalink()); ?>" title="Log out of this account">Log out &raquo;</a></p>
											<div class="comment_form">
												<div class="contact-element">
													<label>Comment</label>
													<textarea name="comment" id="comment" cols="10" rows="20" ></textarea>
												</div>
												<div class="comment_submit">
													<div class="comm_subm_btn">
														<span>
															<input name="submit" type="submit" id="submit" tabindex="5" value="Add Comment" />
														</span>
													</div>
												</div>
											</div>
										<?php else : ?>
											<div class="comment_form">
												<div class="contact-element">
													<label>Name</label>
													<div class="contact-field">
														<input type="text" name="author" id="author" value="" />
													</div>
													<label>Email</label>
													<div class="contact-field">
														<input type="text"  name="email" id="email" value="" />
													</div>
												</div>
												<div class="contact-element">
													<label>Comment</label>
													<textarea name="comment" id="comment" cols="10" rows="20" ></textarea>
												</div>
												<div class="check_wrap">
													<div class="check_element">
														<input type="checkbox" id="id_1" />
														<label for="id_1">Remember me</label>
													</div>
													<div class="check_element">
														<input type="checkbox" id="id_2" />
														<label for="id_2">E-Mail me when someone replies to this comment</label>
													</div>
												</div>
												<div class="comment_submit">
													<div class="comm_subm_btn">
														<span>
															<input name="submit" type="submit" id="submit" tabindex="5" value="Add Comment" />
														</span>
													</div>
												</div>
											</div>
											<?php comment_id_fields(); ?>
											<input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" />
										<?php endif; ?>
									</fieldset>
								</form>
							</div>
