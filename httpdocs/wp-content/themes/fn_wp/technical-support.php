<?php    /*        Template Name:Technical Support     */?>
<?php get_header(); ?>
<div id="content">
	<div class="main">
		<div class="answers_block_wrap"></div>
		<div class="search_question_block">
			<form action="#">
				<h3>Type question or problem below and get instant answers!</h3>
			<!-- Nanorep begin -->
				<div id='nanoRepEmbedContainer'></div>
				<div id='nanoRepProxyContainer'><a href='http://www.nanorep.com' style='font-size:12px;color:#000;' title='nanoRep customer support software'>Loading nanoRep customer support software</a></div>
				<script type='text/javascript'>
					var _nRepData = _nRepData || [];
					/* API here */;
					_nRepData['embed'] = [880, 350, 'nanoRepEmbedContainer'];
					(function(){
						var windowLoadFunc = function(){
							var _nRepData = window._nRepData || [];
							_nRepData['windowLoaded'] = true;
							if (typeof(_nRepData['windowOnload']) === 'function')
								_nRepData['windowOnload']();
						};
						if (window.attachEvent)
							window.attachEvent('onload', windowLoadFunc);
						else if (window.addEventListener)
							window.addEventListener('load', windowLoadFunc, false);
						var sc = document.createElement('script');
						sc.type = 'text/javascript';
						sc.async = true;
						sc.defer = true;
						sc.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'my.nanorep.com/widget/scripts/embed.js?account=federatednetworks';
						var _head = document.getElementsByTagName('head')[0];
						_head.appendChild(sc);
					})
					();
				</script>
			<!-- Nanorep end -->
			<p>For best results, enter one question at a time. If you don't get a satisfactory answer, try rewording, or feel free to ask one our support staff. </p>
			</form>
		</div>
	</div>
</div><?php get_footer(); ?>