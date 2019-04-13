<?php
add_action( 'template_redirect', 'ampforwp_redirection', 10 );
function ampforwp_redirection() {
  global $redux_builder_amp, $wp, $post;
  $hide_cats_amp = $url = '';
  $hide_cats_amp = is_category_amp_disabled();
  $go_to_url  = "";
  $url        = "";
  // Redirection for Homepage and Archive Pages when Turned Off from options panel
  if ( ampforwp_is_amp_endpoint() ) {
    if ( (is_archive() && 0 == $redux_builder_amp['ampforwp-archive-support']) || true == $hide_cats_amp || ((ampforwp_is_home() || ampforwp_is_front_page()) && 0 == $redux_builder_amp['ampforwp-homepage-on-off-support']) ) {
      $url = $wp->request;
      if( ampforwp_is_home() && get_query_var('amp') ) {
        $url = 'amp';
      } 
      $redirection_location = add_query_arg( '', '', home_url( $url ) );
      
      $redirection_location = trailingslashit($redirection_location );
      
      $redirection_location = dirname($redirection_location);
      wp_safe_redirect( $redirection_location );
      exit;
    }
  }

  //Auto redirect /amp to ?amp when 'Change End Point to ?amp' option is enabled #2480
  if ( ampforwp_is_amp_endpoint() && true == ampforwp_get_setting('amp-core-end-point') ){
    $current_url = $endpoint = $new_url = '';
    $current_url = home_url($wp->request);
    $endpoint = '?' . AMPFORWP_AMP_QUERY_VAR;
    $checker =  explode('/', $current_url); 
     $amp_check = in_array('amp', $checker);
     if ( true == $amp_check && 'amp' == end($checker) ) {
      $new_url = str_replace('/'.AMPFORWP_AMP_QUERY_VAR,'/'.$endpoint, $current_url );
       wp_safe_redirect( $new_url );
      exit;
    }
  }

  // AMP Takeover
  if ( ampforwp_get_setting('ampforwp-amp-takeover') && !ampforwp_is_non_amp() ) {
    $redirection_location = '';
    $current_location     = '';
    $home_url             = '';
    $blog_page_id         = '';
    $amp_on_off         = '';
    $current_location     = home_url( $wp->request);
    $home_url             = get_bloginfo('url');

    /*
     * If certain conditions does not match then return early and exit from redirection
     */

    // return if the current page is Feed page, as we don't need anything on feedpaged
    if ( is_feed() ) {
      return;
    }

    // Homepage
    if ( ( ampforwp_is_home() || $current_location == $home_url ) && ! $redux_builder_amp['ampforwp-homepage-on-off-support'] ) {
      return;
    }

    // Frontpage
    if ( is_front_page() && $current_location == $home_url ) {
      return;
    }

    // Archive
    if ( is_archive() && ! $redux_builder_amp['ampforwp-archive-support'] ) {
      return;
    }
   
    // AMP and non-amp Homepage
    if ( is_home() && ampforwp_is_front_page() && ! ampforwp_is_home() ) {
      return;
    }

    //blogpage
    if ( is_home() && $redux_builder_amp['amp-on-off-for-all-pages']==false ) {
      return;
    }

    // Enabling AMP Takeover only when selected in Custom Post Type 
    $supported_types_for_takeover = array();
    $supported_types_for_takeover = ampforwp_get_all_post_types();
    if( $supported_types_for_takeover ){
            $current_type = get_post_type(get_the_ID());
            if(!in_array($current_type, $supported_types_for_takeover)){ 
              return ;
            }
    }
    // Single and Pages
    if ( is_singular() ) {
      $amp_metas = json_decode(get_post_meta( get_the_ID(),'ampforwp-post-metas',true), true );
      if ( 'hide-amp' == $amp_metas['ampforwp-amp-on-off'] ) {
        $amp_on_off = true;
      }
    }
    if ( ( is_single() && !$redux_builder_amp['amp-on-off-for-all-posts'] ) || ( is_page() && !$redux_builder_amp['amp-on-off-for-all-pages'] ) || ($amp_on_off) ) {
      return;
    }

    // Blog page
    if ( ampforwp_is_blog() ) {
      $blog_page_id         = get_option('page_for_posts');
      $redirection_location = get_the_permalink($blog_page_id);
    }

    // if the current page is ampfrontpage or normal frontpage take it to homepage of site
    if ( ampforwp_is_front_page() || is_front_page() ) {
      $redirection_location = $home_url;
    }

    // Single.php and page.php
    if ( ( is_single() && $redux_builder_amp['amp-on-off-for-all-posts'] ) || ( is_page() && $redux_builder_amp['amp-on-off-for-all-pages'] ) ) {
      $redirection_location = get_the_permalink();
    }

    /* Fallback, if for any reason, $redirection_location is still NULL
     * then redirect it to homepage. 
     */
    if ( empty( $redirection_location ) ) {
      $redirection_location = $home_url;
    }

    // Removing the AMP on login register etc of Theme My Login plugin
    if (false === ampforwp_remove_login_tml() ){
      return;
    }
    wp_safe_redirect( $redirection_location );
    exit;  
  }

  // Mobile redirection
  if ( ampforwp_get_setting('amp-mobile-redirection') ) {
    require_once AMPFORWP_PLUGIN_DIR.'/includes/vendor/Mobile_Detect.php';
    $post_type                  = '';
    $supported_types            = '';
    $supported_amp_post_types   = array();
    $url_to_redirect            = '';
    $supported_types            = ampforwp_get_all_post_types();
    $supported_types            = apply_filters('get_amp_supported_post_types',$supported_types);
    $post_type                  = get_post_type();
  
    $supported_amp_post_types   = in_array( $post_type , $supported_types );
    $url_to_redirect            = ampforwp_amphtml_generator();
    $mobile_detect = $isTablet = '';
    // instantiate the Mobile detect class
    $mobile_detect = new AMPforWP_Mobile_Detect;
    $isMobile = $mobile_detect->isMobile();
    $isTablet = $mobile_detect->isTablet();
    $isTabletUserAction = ampforwp_get_setting('amp-tablet-redirection');
    
    $redirectToAMP = false;
    if( $isMobile && $isTabletUserAction && $isTablet ){ //Only For tablet
      $redirectToAMP = true;
    }else if($isMobile && !$isTablet){ // Only for mobile
      $redirectToAMP = true;
    }

    // No mobile redirection on oembeds #2003
    if ( function_exists('is_embed') && is_embed() ){
      return;
    }
    // Return if Dev mode is enabled
    if ( isset($redux_builder_amp['ampforwp-development-mode']) && $redux_builder_amp['ampforwp-development-mode'] ) {
      return;
    }
    // Return if the set value is not met and do not start redirection
    if ( 'disable' == ampforwp_meta_redirection_status() ) {
        return;
    }
    if ( false == $supported_amp_post_types ) {
      return;
    }
    if ( is_archive() && 0 == $redux_builder_amp['ampforwp-archive-support'] ) {
      return;
    }
    if ( is_feed() ) { 
      return; 
    }
    if(get_query_var( 'json' )){
      return; 
    }
    // #1192 Password Protected posts exclusion
    if ( post_password_required( $post ) ) { 
      return; 
    }

    // Return if some categories are selected as Hide #999
    if ( is_category_amp_disabled() ) {
      return;
    }

    // If we are in AMP mode then retrun and dont start redirection
    if ( ampforwp_is_amp_endpoint() ) {
        return;
    } 

    if ( is_page() && 0 == $redux_builder_amp['amp-on-off-for-all-pages'] ) {
        return;
    }

    if ( is_home() && is_front_page() && 0 == $redux_builder_amp['ampforwp-homepage-on-off-support'] ) {
        return;
    }
    if ( is_front_page() && 0 == $redux_builder_amp['ampforwp-homepage-on-off-support'] ) {
        return;
    }

    if ( ! session_id() ) {
        session_start();
    }

    if ( isset( $_SESSION['ampforwp_mobile'] ) && 'mobile-on' == $_SESSION['ampforwp_amp_mode'] && 'exit' == $_SESSION['ampforwp_mobile'] ) {
        return;
    }

    if ( $mobile_detect->isMobile() && 'mobile-on' == $_SESSION['ampforwp_amp_mode'] && 1 == $_GET['nonamp'] ) {
        // non mobile session variable creation
        session_start();
        $_SESSION['ampforwp_mobile'] = 'exit';
        if ( ampforwp_is_amp_endpoint() ) {
            session_destroy();
        }
    }

    if ( function_exists('weglot_plugin_loaded') ) {
      $url_to_redirect = ampforwp_get_weglot_url();
      $url_to_redirect = ampforwp_url_controller($url_to_redirect); 
    }
    // Check if we are on Mobile phones then start redirection process
    if ( $redirectToAMP ) {

        if ( ! isset($_SESSION['ampforwp_amp_mode']) || ! isset($_GET['nonamp']) ) {

          $_SESSION['ampforwp_amp_mode'] = 'mobile-on';

          if ( $url_to_redirect ) {
            wp_redirect( $url_to_redirect , 301 );
            exit();
          }        
          // if nothing matches then return back
          return;
        }
    }
    // #1947 when nonamp=1 it should redirect to original link
    $go_to_url  = "";
    $url        = "";
    $url = ampforwp_amphtml_generator();
    if ( empty($url) ) {
      $url = home_url( $wp->request );
    }
   // var_dump($url);
    $nonamp_checker = get_query_var( 'nonamp');
     if($url){
     if( $nonamp_checker == 1 ){ 
        $go_to_url = remove_query_arg('nonamp', $url);
        $go_to_url = explode('/', $go_to_url);
        $go_to_url = array_flip($go_to_url);
        unset($go_to_url['amp']);
        $go_to_url = array_flip($go_to_url);     
        $go_to_url  = implode('/', $go_to_url);
 
      wp_safe_redirect( $go_to_url, 301 );
      exit;
    }
    else{
      return;
    }
  }
  session_destroy();
  return;
  }
}

// #1947 when nonamp=1 it should redirect to original link so that google
add_filter( 'query_vars', 'ampforwp_custom_query_var' );
function ampforwp_custom_query_var($vars) {
  $vars[] = 'nonamp';
  return $vars;
}
// #1947 ends here