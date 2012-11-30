<?php
	if ( function_exists('register_sidebar') )
		register_sidebar(array(
		'name' => 'Sidebar',
		'before_widget' => '<div class="widget widget_border">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	));
//custom_field
	function admin_custom_field_height () {
	    echo "<style type='text/css'> #postcustom textarea, #postcustomstuff textarea {height: 120px;} </style>\n";
	}
	add_action('admin_head', 'admin_custom_field_height');