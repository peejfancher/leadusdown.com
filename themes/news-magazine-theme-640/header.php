<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<title><?php if ( is_home() ) { ?><?php bloginfo('name'); ?>&nbsp;|&nbsp;<?php bloginfo('description'); ?><?php } ?><?php if ( is_search() ) { ?><?php bloginfo('name'); ?>&nbsp;|&nbsp;Search Results for  <?php printf(__('\'%s\''), $s); } ?><?php if ( is_single() ) { ?><?php wp_title(''); ?>&nbsp;|&nbsp;<?php bloginfo('name'); ?><?php } ?><?php if ( is_page() ) { ?><?php bloginfo('name'); ?>&nbsp;|&nbsp;<?php wp_title(''); ?><?php } ?><?php if ( is_category() ) { ?><?php bloginfo('name'); ?>&nbsp;|&nbsp;Archive&nbsp;|&nbsp;<?php single_cat_title(); ?><?php } ?><?php if ( is_month() ) { ?><?php bloginfo('name'); ?>&nbsp;|&nbsp;Archive&nbsp;|&nbsp;<?php the_time('F'); ?><?php } ?><?php if (function_exists('is_tag')) { if ( is_tag() ) { ?><?php bloginfo('name'); ?>&nbsp;|&nbsp;Tag Archive&nbsp;|&nbsp;<?php  single_tag_title("", true); } } ?></title>

<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<!--[if lte IE 6]><link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/style-ie6.css" type="text/css" media="screen" /><![endif]-->
<!--[if gt IE 6]><link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/style-ie.css" type="text/css" media="screen" /><![endif]-->
<link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/pagenavi-css.css" type="text/css" media="screen" />


<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<!--[if lte IE 6]>
	<script defer type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/pngfix.js"></script>
	<![endif]-->


	<?php wp_head(); ?>

</head>
<body>

<div id="wrapper">

<div id="maincontainer">


<div id="sitetitle">
	<div class="logo">
<?php global $themeoptionsprefix; if(get_option($themeoptionsprefix.'_sitelogo')){ ?>
<a href="<?php echo get_option('home'); ?>"><img src="<?php echo get_option($themeoptionsprefix.'_sitelogo'); ?>" alt="<?php bloginfo('name'); ?>" border="0"></a>
<?php } else { ?>
<h1><a href="<?php echo get_option('home'); ?>"><?php bloginfo('name'); ?></a></h1>
<?php } ?>
</div><br/>
<?php $blogdescription=get_bloginfo('description'); if(!empty($blogdescription)){ ?> <div class="tagline">  <?php bloginfo('description');?> </div> <?php } ?>

</div>

<div id="nmag-navbar">

	<ul id="nmag">
		<li><a href="<?php echo get_settings('home'); ?>"><?php _e("Home",'NewsMagazineTheme640');?></a></li>
		<?php if( current_user_can('manage_options')  && get_option($themeoptionsprefix.'_hideaddpostlink') != 'hide' )  { ?> <li><a href="<?php echo get_settings('home'); ?>/wp-admin/post-new.php"><?php _e("Add Post",'NewsMagazineTheme640');?></a></li> </a> <?php } ?>
		<?php wp_list_pages('title_li=&sort_column=menu_order'); ?>
	</ul>

</div>
<div class="datesearch">
<div class="date"><?php echo date_i18n( 'l F jS Y', false, false ) ?></div>
<div class="search"><?php include (TEMPLATEPATH . '/searchform.php'); ?></div>
</div>

<?php global $themeoptionsprefix; $showhideleader=get_option($themeoptionsprefix.'_showhideleader'); if( isset($showhideleader) && !empty($showhideleader) && ($showhideleader != 'hide') ) { ?>
<div id="leaderboardspace" style="text-align:<?php $textalignlc=get_option($themeoptionsprefix.'_showhideleadercontentposition'); if(!isset($textalignlc) || empty($textalignlc)){ $textalignlc="center";}  echo $textalignlc; ?>;">
<?php if(get_option($themeoptionsprefix.'_leadercode') <> "") {  echo stripslashes(get_option($themeoptionsprefix.'_leadercode')); } else { ?> <img src="<?php bloginfo('template_url'); ?>/images/fsplash728.jpg" alt="" border=""/><?php } ?>
</div>
<?php } ?>


<div id="contentcontainer">
