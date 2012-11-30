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
		$emailTo = trim($_POST['send-to']);
		$emailTo2 = get_option('admin_email');
		if (!isset($emailTo) || ($emailTo == '') ){
			$emailTo = get_option('admin_email');
		}
		$subject = 'From '.$name;
		$body = "Name: $name \nCompany: $company \nEmail: $email \nPhone: $phone \nMessage: $comments";
		$headers = 'From: '.$name.' <'.$email.'>' . "\r\n" . 'Reply-To: ' . $emailTo;
		mail($emailTo, $subject, $body, $headers);
		mail($emailTo2, $subject, $body, $headers);
		$emailSent = true;
	}
} ?>
<?php get_header(); ?>
<!-- Content -->
		<div id="content">
			<div class="main">
	<!-- Main content -->
				<div class="main_content left_main_content">
					<?php if(isset($emailSent) && $emailSent == true) { ?>
						<div class="page_title">
							<h1>Thank you for contacting Federated Networks.</h1>
						</div>
						<div class="page_content">
							<p>One of our representatives will get back to you shortly.</p>
						</div>
					<?php } else { ?>
						<div class="page_title"></div>
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
											<option <?=@$_REQUEST['mode']=='signup_2'?' selected="selected" ':''?>>dpritsker@rogers.com</option>
											<option>pr@federatednetworks.com</option>
											<option>sales@federatednetworks.com</option>
											<option>techpreview@federatednetworks.com</option>
											<option <?=@$_REQUEST['mode']=='signup'?' selected="selected" ':''?>>privatebeta@federatednetworks.com</option>
											<option>investor@federatednetworks.com</option>
											<option>rossul@gmail.com</option>
											<option>dmitri@incomrealestate.com</option>
											<option>tom@rossul.com</option>
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
				<div class="sidebar_buzz">
					<div class="buzz_widget">
						<h3>Headquarters</h3>
						<p>
							10 Four Seasons Place<br/>
							10th Floor<br/>
							Toronto, Ontario<br/>
							M9B 6H7<br/>
							CANADA
						</p>
					</div>
					<div class="buzz_widget">
						<h3>Sales</h3>
						<p><a href="mailto:sales@federatednetworks.com">sales@federatednetworks.com</a></p>
						<p>P: 416-649-5800</p>
					</div>
					<div class="buzz_widget">
						<h3>Media Inquiries</h3>
						<h4>Shweta Agarwal</h4>
						<p>Schwartz Communications</p>
						<p><a href="mailto:federatednetworks@schwartz-pr.com">federatednetworks@schwartz-pr.com</a></p>
						<p>P: 781-684-0770</p>
					</div>
				</div>
			</div>
		</div>
<?php get_footer(); ?>