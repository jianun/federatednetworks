<?php
    /*
        Template Name: ContactPage
    */
?>
<?php
$emailError = '';
if(isset($_POST['submitted'])) {
	if(trim($_POST['contactName']) === '') {
		$nameError = 'Please enter your name.';
		$hasError = true;
	} else {
		$name = trim($_POST['contactName']);
	}

	if(trim($_POST['email']) === '')  {
		$emailError = 'Please enter your email address.';
		$hasError = true;
	} else if (!eregi("^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,4}$", trim($_POST['email']))) {
		$emailError = 'You have entered an incorrect email address.';
		$hasError = true;
	} else {
		$email = trim($_POST['email']);
	}

	if(trim($_POST['comments']) === '') {
		$commentError = 'Please enter your message.';
		$hasError = true;
	} else {
		if(function_exists('stripslashes')) {
			$comments = stripslashes(trim($_POST['comments']));
		} else {
			$comments = trim($_POST['comments']);
		}
	}
	$phone = trim($_POST['phone']);
	$company = trim($_POST['company']);
	if(!isset($hasError)) {
		$emailTo = get_option('tz_email');
		if (!isset($emailTo) || ($emailTo == '') ){
			$emailTo = get_option('admin_email');
		}
		$subject = 'From '.$name;
		$body = "Name: $name \nCompany: $company \nEmail: $email \nPhone: $phone \nMessage: $comments";
		$headers = 'From: '.$name.' <'.$emailTo.'>' . "\r\n" . 'Reply-To: ' . $email;
		mail($emailTo, $subject, $body, $headers);
		$emailSent = true;
	}
} ?>
<?php get_header(); ?>
<!-- Content -->
		<div id="content">
			<div class="main">
	<!-- Main content -->
				<div class="main_content">
					<?php if(isset($emailSent) && $emailSent == true) { ?>
						<div class="page_title">
							<h1>Thank you for contacting Federated Networks.</h1>
						</div>
						<div class="page_content">
							<p>One of our representatives will get back to you shortly.</p>
						</div>
					<?php } else { ?>
						<div class="page_title">
							<h1>Contact Us</h1>
							<ul class="share_this">
								<li><script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=08252a75-a87d-4025-b708-fb7d227d8945&amp;type=website&amp;post_services=email%2Cfacebook%2Ctwitter%2Cgbuzz%2Cmyspace%2Cdigg%2Csms%2Cwindows_live%2Cdelicious%2Cstumbleupon%2Creddit%2Cgoogle_bmarks%2Clinkedin%2Cbebo%2Cybuzz%2Cblogger%2Cyahoo_bmarks%2Cmixx%2Ctechnorati%2Cfriendfeed%2Cpropeller%2Cwordpress%2Cnewsvine"></script></li>
								<li><a class="link_print" href="javascript: window.print();">Print</a></li>
							</ul>
						</div>
						<div class="page_content">
							<h3 id="validation-error" class="error_contact_field" style="display:<?=@$_GET['msg']=='error'?'block':'none'?>">Please complete all fields</h3>
							<?php if($emailError != '') { ?>
<span class="error"><?php echo $emailError; ?></span>
<?php } ?>
							<form action="<?php the_permalink(); ?>"  method="post" onsubmit="return formValidate(this);">
								<fieldset>
									<div class="contact-element">
										<label>Send to</label>
										<select id="default-usage-select" name="send-to">
											<option>info@federatednetworks.com</option>
											<option>pr@federatednetworks.com</option>
											<option>sales@federatednetworks.com</option>
											<option>techpreview@federatednetworks.com</option>
											<option <?=@$_REQUEST['mode']=='signup'?' selected="selected"':''?> >privatebeta@federatednetworks.com</option>
											<option>investor@federatednetworks.com</option>
										</select>
									</div>
									<div class="contact-element">
										<label>Name</label>
										<div class="contact-field"><input type="text" name="contactName" id="contactName" value="<?php if(isset($_POST['contactName'])) echo $_POST['contactName'];?>" class="required requiredField" /></div>
										<label class="last">Company</label>
										<div class="contact-field"><input type="text" name="company" value="" /></div>
									</div>
									<div class="contact-element">
										<label>Email</label>
										<div class="contact-field"><input type="text" name="email" id="email" value="<?php if(isset($_POST['email']))  echo $_POST['email'];?>" class="required requiredField email" /></div>
										<label class="last">Phone</label>
										<div class="contact-field"><input type="text" name="phone" value="" /></div>
									</div>
									<div class="contact-element">
										<label>Message</label>
										<textarea name="comments" id="commentsText" rows="20" cols="30" class="required requiredField"><?php if(isset($_POST['comments'])) { if(function_exists('stripslashes')) { echo stripslashes($_POST['comments']); } else { echo $_POST['comments']; } } ?></textarea>
									</div>
									<div class="contact-element">
										<div class="btn-send"><input type="submit" name="submitted" value="send" /></div>
										<input type="hidden" name="submitted" id="submitted" value="true" />
									</div>
								</fieldset>
							</form>
						</div>
					<?php } ?>
				</div>
	<!-- sidebar -->
				<?php get_sidebar(); ?>
			</div>
		</div>
<?php get_footer(); ?>