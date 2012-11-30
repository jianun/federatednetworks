function zkafp_admin_ajax(postID,addType) {
	
	var containerBox = null;
	
	if (addType == 1) {
		containerBox = document.getElementById('zkafp_all_' + postID);
    } else {
		containerBox = document.getElementById('zkafp_cat_' + postID);
	}
	
	containerBox.className = 'zkafp_loading';
		
	//Using Sack for AJAX
	var mysack = new sack("admin-ajax.php");
	
	mysack.execute = 1;
	mysack.method = 'POST';
	mysack.setVar("action", "zkafp_admin");
	mysack.setVar("id", postID);
	mysack.setVar("type", addType);
	mysack.setVar("is_on", (containerBox.className == 'yafpp_on' ? 1 : 0));
	mysack.encVar("cookie", document.cookie, false);
	mysack.onError = function() { alert('Error featuring, please try again.' )};
	mysack.runAJAX();
	
    return true;
}