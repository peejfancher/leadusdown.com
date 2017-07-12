<?php
global $options;
foreach ($options as $value) {
    if (get_settings( $value['id'] ) === FALSE) { $$value['id'] = $value['std']; } else { $$value['id'] = get_settings( $value['id'] ); }
}
?>
<?php get_header(); ?>
<?php get_sidebar(); ?>
 <div id="content">
 
	 <div id="post">
	 
	 
			<h3 class="title"><?php _e('Error 404 - Nothing Found', 'wpzoom');?></h3>
			
			<?php if (have_posts()) : $count = 0; ?>
            <?php while (have_posts()) : the_post(); $count++; ?>
            
            
			<?php endwhile; else: ?>
				<br />
				<br />
				<h3><?php _e('The page you are looking for could not be found.', 'wpzoom');?> </h3>
 
            <?php endif; ?>  
 	 
	</div> 
</div> <!-- /#content -->
 <?php get_footer(); ?>