<?php global $redux_builder_amp;  ?>
<!doctype html>
<html amp <?php echo AMP_HTML_Utils::build_attributes_string( $this->get( 'html_tag_attributes' ) ); ?>>
<head>
	<meta charset="utf-8">
  <link rel="dns-prefetch" href="https://cdn.ampproject.org">
	<?php
	global $redux_builder_amp;
	if ( is_home() || is_front_page()  ){
		global $wp;
		$current_archive_url = home_url( $wp->request );
		$amp_url = trailingslashit($current_archive_url);
	} ?>
	<link rel="canonical" href="<?php echo $amp_url ?>">
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
	<?php do_action( 'amp_post_template_head', $this ); ?>

	<style amp-custom>
	<?php $this->load_parts( array( 'style' ) ); ?>
	<?php do_action( 'amp_post_template_css', $this ); ?>
	</style>
</head>
<body class="amp_home_body">
<?php $this->load_parts( array( 'header-bar' ) ); ?>

<?php do_action( 'ampforwp_after_header', $this ); ?>



<main>
	<?php
		if ( get_query_var( 'paged' ) ) {
	        $paged = get_query_var('paged');
	    } elseif ( get_query_var( 'page' ) ) {
	        $paged = get_query_var('page');
	    } else {
	        $paged = 1;
	    }

	    $exclude_ids = get_option('ampforwp_exclude_post');

		$q = new WP_Query( array(
			'post_type'           => 'post',
			'orderby'             => 'date',
			's' 				  => get_search_query() ,
			'ignore_sticky_posts' => 1,
			'paged'               => esc_attr($paged),
			'post__not_in' 		  => $exclude_ids,
			'has_password' => false ,
			'post_status'=> 'publish'
		) ); ?>



	<div class="amp-wp-content">
 		<h3 class="page-title"> You searched for: <?php echo  get_search_query();?>  </h3>
 	</div>

	<?php if ( $q->have_posts() ) : while ( $q->have_posts() ) : $q->the_post();
		$ampforwp_amp_post_url = trailingslashit( get_permalink() ) . AMP_QUERY_VAR ; ?>

		<div class="amp-wp-content amp-loop-list">
			<?php if ( has_post_thumbnail() ) { ?>
				<?php
				$thumb_id = get_post_thumbnail_id();
				$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'amp_design3_thumb', true);
				$thumb_url = $thumb_url_array[0];
				?>
				<div class="home-post_image"><a href="<?php echo esc_url( $ampforwp_amp_post_url ); ?>"><amp-img layout="responsive" src=<?php echo $thumb_url ?> width=450 height=270 ></amp-img></a></div>
			<?php } ?>

			<div class="amp-wp-post-content">
                <ul class="amp-wp-tags">
					<?php foreach((get_the_category()) as $category) { ?>
             			<li><?php echo $category->cat_name ?></li>
					<?php } ?>
                </ul>
				<h2 class="amp-wp-title"> <a href="<?php echo esc_url( $ampforwp_amp_post_url ); ?>"> <?php the_title(); ?></a></h2>


				<?php
					if(has_excerpt()){
						$content = get_the_excerpt();
					}else{
						$content = get_the_content();
					}
				?>
		        <p><?php echo wp_trim_words( $content , '15' ); ?></p>
                <div class="featured_time"><?php echo human_time_diff( get_the_time('U'), current_time('timestamp') ) . ' ago'; ?></div>

		    </div>
            <div class="cb"></div>
	</div>

	<?php endwhile;  ?>

	<div class="amp-wp-content pagination-holder">


		<div id="pagination">
			<div class="next"><?php next_posts_link( $redux_builder_amp['amp-translator-next-text'] , 0 ) ?></div>
			<?php if ( $paged > 1 ) { ?>
				<div class="prev"><?php previous_posts_link( $redux_builder_amp['amp-translator-previous-text'] ); ?></div>
			<?php } ?>
			<div class="clearfix"></div>
		</div>
	</div>
	<?php else : ?>
		<div class="amp-wp-content">
 			Sorry No posts Found for <?php echo get_search_query(); ?>
 		</div>

	<?php endif; ?>
	<?php wp_reset_postdata(); ?>
</main>
<?php $this->load_parts( array( 'footer' ) ); ?>
<?php do_action( 'amp_post_template_footer', $this ); ?>
</body>

</html>
