<form method="get" id="searchform" action="<?php echo $_SERVER['PHP_SELF']; ?>" >
		<div class="blog_search">
			<div class="bg_search_field">
				<input type="text" value="Blog Search" onfocus="if (this.value == 'Blog Search') {this.value = '';}" onblur="if (this.value == '') {this.value ='Blog Search';}" name="s" id="s" />
			</div>
			<div class="btn_search">
				<div class="btn_search_wrap">
					<input type="submit" value="Search" id="searchsubmit" />
				</div>
			</div>
		</div>
	</fieldset>
</form>