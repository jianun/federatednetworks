=== ZK Advanced Feature Post ===
Author: vn.Zinki (vn.zinki)
Author URI: http://zinki.info/
Plugin URI: http://zinki.info/158-wordpress-plugin-zk-advanced-feature-post
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=8YCWP2CL9XU4N&lc=VN&item_name=Wordpress%20Plugins&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: featured posts, featured, highlight, starred, star, highlight posts, feature, featured post list
Requires at least: 2.8.4
Tested up to: 3.2.1
Stable tag: 1.8.21

AJAX feature post function for your wordpress. Especially you can get featured post for custom category only.

== Description ==

1. AJAX function to manage your feature post.
2. Widget to display your feature list (for custom category only).
3. Function for developer that can insert feature list into anywhere you want.

If you have any questions or suggestions, please comment : [Plugin page](http://zinki.info/158-wordpress-plugin-zk-advanced-feature-post "Plugin page").

== Installation ==

1. Upload `zk-advanced-feature-post` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Read the readme.txt

== Frequently Asked Questions ==

= How to use zk_featured() function ? =

Using this code
`$options = array( 'method' => 'loop',    // 'loop','array' - default = 'loop'
            'cat' => '3,4,5', // default = 'all'
            'count' => 16, // default = 5
            'orderby' => 'rand', // 'author','date','title','modified','parent','id','rand','comment_count'
            'order' => 'DESC' // 'ASC','DESC'
            );
 
zk_featured($options);`

= The difference between 'loop' and 'array' method ? =

This is 'loop' method
`$options = array( 'method' => 'loop',
            'count' => 16
            );
zk_featured($options);
 
while (have_posts()) : the_post();
    //Do something
endwhile;`

And this is 'array' method
`$options = array( 'method' => 'array',
            'count' => 16
            );
$result = zk_featured($options);
    echo '<pre>';
    print_r($result);
    echo '</pre>';`
	
You can try to know how it works.

== Screenshots ==
1. Select feature post in the Admin panel

2. Widget display

== Changelog ==

= 1.8.21 =
* Add option to get thumbnail from featured image
* Add option to set excerpt lenght

= 1.4.11 =
* Revert to default wordpress query after sidebar call

= 0.12.30 =
* Fix class name error

= 0.12.25 =
* Fix CSS
* Fix thumb display if there's no image in post

= 0.12.16 =
* Change folder structure

= 0.12.15 =
* First version
