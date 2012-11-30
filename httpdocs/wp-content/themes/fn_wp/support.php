<?php
    /*
        Template Name:Support 
    */
?>
<?php get_header(); ?>
<!-- Content -->
		<div id="content">
			<div class="main">
				<div class="support_block">
					<div class="support_column">
						<h3>Technical Support</h3>
						<p>Find answers to your questions about our products, such as:</p>
						<ul>
							<li>Getting Started</li>
							<li>Installation</li>
							<li>Configuration</li>
							<li>Trouble Shooting</li>
						</ul>
						<script type="text/javascript">
							function support_t(){
							  var support_t_type = document.getElementById('support_t_type').value;
							  document.getElementById('enter').action = '/support/'+support_t_type+'/';
							  return true;
							}
						</script>
						<form method="post" action="" id="enter">
							<fieldset>
								<div class="support_select">
									<select name="support_t_type" id="support_t_type">
										<option value="technical-support">FN Secure Desktop</option>
										<option value="fn-secure-desktop-2">FN Secure Desktop 2</option>
										<option value="fn-secure-desktop-3">FN Secure Desktop 3</option>
									</select>
								</div>
								<em class="btn" ><span><input type="submit" value="Get Technical Support" onclick="support_t()" /></span></em>
							</fieldset>
						</form>
					</div>
					<div class="support_column">
						<h3>Account Support</h3>
						<p>Find answers to your customer service account questions, such as:</p>
						<ul>
							<li>Ecommerce, Payment and Tax</li>
							<li>Billing</li>
							<li>Subscription Renewals</li>
							<li>Refunds</li>
						</ul>
						<script type="text/javascript">
							function support_a(){
							  var support_a_type = document.getElementById('support_a_type').value;
							  document.getElementById('enter_a').action = '/support/'+support_a_type+'/';
							  return true;
							}
						</script>
						<form method="post" id="enter_a" action="">
							<fieldset>
								<div class="support_select">
									<select name="support_a_type" id="support_a_type">
										<option value="select-your-role">Select Your Role</option>
										<option value="select-your-role-2">Select Your Role 2</option>
										<option value="select-your-role-3">Select Your Role 3</option>
									</select>
								</div>
								<em class="btn" ><span><input type="submit" value="Get Account Support" onclick="support_a()" /></span></em>
							</fieldset>
						</form>
					</div>
					<div class="support_column last_s_col">
						<h3>Sales/Pre-Sales Support</h3>
						<p>Find answers to your sales or pre-salees questions such as:</p>
						<ul>
							<li>Scheduling Sales Call</li>
							<li>Trial Period FAQ</li>
							<li>Resources/white Papers</li>
							<li>Security Certifications /Compliance</li>
						</ul>
						<script type="text/javascript">
							function support_s(){
							  var support_s_type = document.getElementById('support_s_type').value;
							  document.getElementById('enter_s').action = '/support/'+support_s_type+'/';
							  return true;
							}
						</script>
						<form id="enter_s" method="post" action="">
							<fieldset>
								<div class="support_select">
									<select name="support_s_type" id="support_s_type">
										<option value="fn-server">FN Server</option>
										<option value="fn-server-2">FN Server 2</option>
										<option value="fn-server-3">FN Server 3</option>
									</select>
								</div>
								<em class="btn" ><span><input type="submit" value="Go Sales Support" onclick="support_s()" /></span></em>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
		</div>
<?php get_footer(); ?>