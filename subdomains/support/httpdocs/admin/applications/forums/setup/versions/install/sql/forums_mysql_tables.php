<?php
/**
* Installation Schematic File
* Generated on Thu, 19 Feb 2009 08:15:47 +0000 GMT
*/
$TABLE[] = "CREATE TABLE forum_perms (
  perm_id int(10) NOT NULL auto_increment,
  perm_name varchar(250) NOT NULL default '',
  PRIMARY KEY  (perm_id)
);";
$TABLE[] = "CREATE TABLE forum_tracker (
  frid mediumint(8) NOT NULL auto_increment,
  member_id mediumint(8) NOT NULL default 0,
  forum_id smallint(5) NOT NULL default 0,
  start_date int(10) default NULL,
  last_sent int(10) NOT NULL default 0,
  forum_track_type varchar(100) NOT NULL default 'delayed',
  PRIMARY KEY  (frid),
  KEY forum_track_type (forum_track_type),
  KEY member_id ( member_id , last_sent ),
  KEY fm_id (forum_id)
);";
$TABLE[] = "CREATE TABLE forums (
  id smallint(5) NOT NULL auto_increment,
  topics mediumint(6) default NULL,
  posts mediumint(6) default NULL,
  last_post int(10) default NULL,
  last_poster_id mediumint(8) NOT NULL default 0,
  last_poster_name VARCHAR( 255 ) NOT NULL DEFAULT '',
  name varchar(128) NOT NULL default '',
  description text,
  position int(5) unsigned default 0,
  use_ibc tinyint(1) default NULL,
  use_html tinyint(1) default NULL,
  status tinyint(1) default 1,
  password varchar(32) default NULL,
  password_override varchar(255) default NULL,
  last_title varchar(250) default NULL,
  last_id int(10) default NULL,
  sort_key varchar(32) default NULL,
  sort_order varchar(32) default NULL,
  prune tinyint(3) default NULL,
  topicfilter varchar(32) NOT NULL default 'all',
  show_rules tinyint(1) default NULL,
  preview_posts tinyint(1) default NULL,
  allow_poll tinyint(1) NOT NULL default 1,
  allow_pollbump tinyint(1) NOT NULL default 0,
  inc_postcount tinyint(1) NOT NULL default 1,
  skin_id int(10) default NULL,
  parent_id mediumint(5) default '-1',
  quick_reply tinyint(1) default 0,
  redirect_url varchar(250) default '',
  redirect_on tinyint(1) NOT NULL default 0,
  redirect_hits int(10) NOT NULL default 0,
  redirect_loc varchar(250) default '',
  rules_title varchar(255) NOT NULL default '',
  rules_text text,
  topic_mm_id varchar(250) NOT NULL default '',
  notify_modq_emails text,
  sub_can_post tinyint(1) default 1,
  permission_custom_error text,
  permission_array mediumtext NULL,
  permission_showtopic tinyint(1) NOT NULL default 0,
  queued_topics mediumint(6) NOT NULL default 0,
  queued_posts mediumint(6) NOT NULL default 0,
  forum_allow_rating tinyint(1) NOT NULL default 0,
  forum_last_deletion int(10) NOT NULL default 0,
  newest_title varchar(250) default NULL,
  newest_id int(10) NOT NULL default 0,
  min_posts_post int(10) unsigned NOT NULL,
  min_posts_view int(10) unsigned NOT NULL,
  can_view_others tinyint(1) NOT NULL default 1,
  hide_last_info tinyint(1) NOT NULL default 0,
  name_seo varchar(255) default NULL,
  seo_last_title varchar(255) NOT NULL default '',
  seo_last_name varchar(255) NOT NULL default '',
  last_x_topic_ids text,
  forums_bitoptions int(10) unsigned NOT NULL default 0,
  disable_sharelinks INT(1) NOT NULL default 0,
  deleted_posts INT(10) NOT NULL default 0,
  deleted_topics INT(10) NOT NULL default 0,
  rules_raw_html tinyint(1) NOT NULL default 0,
  PRIMARY KEY  (id),
  KEY position (position,parent_id)
);";
$TABLE[] = "CREATE TABLE mod_queued_items (
  id int(11) NOT NULL auto_increment,
  type varchar(32) NOT NULL default 'post',
  type_id int(11) NOT NULL default 0,
  PRIMARY KEY  (id),
  KEY type_id (type_id)
);";
$TABLE[] = "CREATE TABLE moderator_logs (
  id int(10) NOT NULL auto_increment,
  forum_id int(5) default 0,
  topic_id int(10) NOT NULL default 0,
  post_id int(10) default NULL,
  member_id mediumint(8) NOT NULL default 0,
  member_name varchar(32) NOT NULL default '',
  ip_address varchar(16) NOT NULL default '0',
  http_referer varchar(255) default NULL,
  ctime int(10) default NULL,
  topic_title varchar(128) default NULL,
  action varchar(128) default NULL,
  query_string varchar(128) default NULL,
  PRIMARY KEY  (id),
  KEY ctime (ctime),
  KEY ip_address (ip_address)
);";
$TABLE[] = "CREATE TABLE moderators (
  mid mediumint(8) NOT NULL auto_increment,
  forum_id text,
  member_name varchar(32) NOT NULL default '',
  member_id mediumint(8) NOT NULL default 0,
  edit_post tinyint(1) default NULL,
  edit_topic tinyint(1) default NULL,
  delete_post tinyint(1) default NULL,
  delete_topic tinyint(1) default NULL,
  view_ip tinyint(1) default NULL,
  open_topic tinyint(1) default NULL,
  close_topic tinyint(1) default NULL,
  mass_move tinyint(1) default NULL,
  mass_prune tinyint(1) default NULL,
  move_topic tinyint(1) default NULL,
  pin_topic tinyint(1) default NULL,
  unpin_topic tinyint(1) default NULL,
  post_q tinyint(1) default NULL,
  topic_q tinyint(1) default NULL,
  allow_warn tinyint(1) default NULL,
  edit_user tinyint(1) NOT NULL default 0,
  is_group tinyint(1) default 0,
  group_id smallint(3) default NULL,
  group_name varchar(200) default NULL,
  split_merge tinyint(1) default 0,
  can_mm tinyint(1) NOT NULL default 0,
  mod_can_set_open_time tinyint(1) NOT NULL default 0,
  mod_can_set_close_time tinyint(1) NOT NULL default 0,
  mod_bitoptions int(10) unsigned NOT NULL default 0,
  PRIMARY KEY  (mid),
  KEY group_id (group_id),
  KEY member_id (member_id)
);";
$TABLE[] = "CREATE TABLE polls (
  pid mediumint(8) NOT NULL auto_increment,
  tid int(10) NOT NULL default 0,
  start_date int(10) default NULL,
  choices text,
  starter_id mediumint(8) NOT NULL default 0,
  votes smallint(5) NOT NULL default 0,
  forum_id smallint(5) NOT NULL default 0,
  poll_question varchar(255) default NULL,
  poll_only tinyint(1) NOT NULL default 0,
  poll_view_voters int(1) NOT NULL default 0,
  PRIMARY KEY  (pid),
  KEY tid (tid)
);";
$TABLE[] = "CREATE TABLE posts (
  pid int(10) NOT NULL auto_increment,
  append_edit tinyint(1) default 0,
  edit_time int(10) default NULL,
  author_id mediumint(8) NOT NULL default 0,
  author_name varchar(255) default NULL,
  use_sig tinyint(1) NOT NULL default 0,
  use_emo tinyint(1) NOT NULL default 0,
  ip_address varchar(16) NOT NULL default '',
  post_date int(10) default NULL,
  icon_id smallint(3) default NULL,
  post mediumtext,
  queued tinyint(1) NOT NULL default 0,
  topic_id int(10) NOT NULL default 0,
  post_title varchar(255) default NULL,
  new_topic tinyint(1) default 0,
  edit_name varchar(255) default NULL,
  post_key varchar(32) NOT NULL default '0',
  post_parent int(10) NOT NULL default 0,
  post_htmlstate smallint(1) NOT NULL default 0,
  post_edit_reason varchar(255) NOT NULL default '',
  PRIMARY KEY  (pid),
  KEY topic_id (topic_id,queued,pid,post_date),
  KEY author_id (author_id,topic_id),
  KEY post_date (post_date),
  KEY ip_address (ip_address),
  KEY post_key (post_key)
);";
$TABLE[] = "CREATE TABLE topic_mmod (
  mm_id smallint(5) NOT NULL auto_increment,
  mm_title varchar(250) NOT NULL default '',
  mm_enabled tinyint(1) NOT NULL default 0,
  topic_state varchar(10) NOT NULL default 'leave',
  topic_pin varchar(10) NOT NULL default 'leave',
  topic_move smallint(5) NOT NULL default 0,
  topic_move_link tinyint(1) NOT NULL default 0,
  topic_title_st varchar(250) NOT NULL default '',
  topic_title_end varchar(250) NOT NULL default '',
  topic_reply tinyint(1) NOT NULL default 0,
  topic_reply_content text,
  topic_reply_postcount tinyint(1) NOT NULL default 0,
  mm_forums text,
  topic_approve tinyint(1) NOT NULL default 0,
  PRIMARY KEY  (mm_id)
);";
$TABLE[] = "CREATE TABLE topic_ratings (
  rating_id int(10) NOT NULL auto_increment,
  rating_tid int(10) NOT NULL default 0,
  rating_member_id mediumint(8) NOT NULL default 0,
  rating_value smallint(6) NOT NULL default 0,
  rating_ip_address varchar(16) NOT NULL default '',
  PRIMARY KEY  (rating_id),
  KEY rating_tid (rating_tid,rating_member_id),
  KEY rating_ip_address (rating_ip_address)
);";
$TABLE[] = "CREATE TABLE topic_views (
  views_tid int(10) NOT NULL default 0
);";


$TABLE[] = "CREATE TABLE topics (
  tid int(10) NOT NULL auto_increment,
  title varchar(250) NOT NULL default '',
  description varchar(250) default NULL,
  state varchar(8) default NULL,
  posts int(10) default NULL,
  starter_id mediumint(8) NOT NULL default 0,
  start_date int(10) default NULL,
  last_poster_id mediumint(8) NOT NULL default 0,
  last_post int(10) default NULL,
  icon_id tinyint(2) default NULL,
  starter_name varchar(255) default NULL,
  last_poster_name varchar(255) default NULL,
  poll_state varchar(8) default NULL,
  last_vote int(10) default NULL,
  views int(10) default NULL,
  forum_id smallint(5) NOT NULL default 0,
  approved tinyint(1) NOT NULL default 0,
  author_mode tinyint(1) default NULL,
  pinned tinyint(1) default NULL,
  moved_to varchar(64) default NULL,
  total_votes int(5) NOT NULL default 0,
  topic_hasattach smallint(5) NOT NULL default 0,
  topic_firstpost int(10) NOT NULL default 0,
  topic_queuedposts int(10) NOT NULL default 0,
  topic_open_time int(10) NOT NULL default 0,
  topic_close_time int(10) NOT NULL default 0,
  topic_rating_total smallint(5) unsigned NOT NULL default 0,
  topic_rating_hits smallint(5) unsigned NOT NULL default 0,
  title_seo varchar(250) NOT NULL default '',
  seo_last_name varchar(255) NOT NULL default '',
  seo_first_name varchar(255) NOT NULL default '',
  topic_deleted_posts INT(10) NOT NULL default 0,
  PRIMARY KEY  (tid),
  KEY topic_firstpost (topic_firstpost),
  KEY last_post (forum_id,pinned,last_post,state),
  KEY forum_id (forum_id,pinned,approved),
  KEY starter_id (starter_id,forum_id,approved),
  KEY last_post_sorting (last_post,forum_id),
  KEY start_date (start_date),
  KEY last_x_topics (forum_id,approved,start_date)
);";

$TABLE[] = "CREATE TABLE tracker (
  trid mediumint(8) NOT NULL auto_increment,
  member_id mediumint(8) NOT NULL default 0,
  topic_id int(10) NOT NULL default 0,
  start_date int(10) default NULL,
  last_sent int(10) NOT NULL default 0,
  topic_track_type varchar(100) NOT NULL default 'delayed',
  PRIMARY KEY  (trid),
  KEY topic_id (topic_id),
  KEY tm_id ( member_id , topic_id , last_sent ),
  KEY topic_track_type ( topic_track_type )
);";
?>