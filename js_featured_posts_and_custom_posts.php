<?php

/*
 * Plugin Name: 	Featured Posts and Custom Posts
 * Plugin URI: 		http://www.reactivedevelopment.net/snippets/featured-posts-custom-posts
 * Description: 	Allows the user to feature posts and custom posts. When a post is featured it gets the post metta _jsFeaturedPost  
 * Version: 		1.0
 * Author:        	Reactive Development LLC
 * Author URI:   	http://www.reactivedevelopment.net/
 *
 * License:       	GNU General Public License, v2 (or newer)
 * License URI:  	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * Image Credit:
 * Star image is from: http://www.iconfinder.com/icondetails/9662/24/bookmark_star_icon and http://www.iconfinder.com/icondetails/9604/24/star_icon
 *   
 */

/*  -------------------------------------------------------------------------------------------------------------------------

        when we activate the plugin do this 

    ---------------------------------------------------------------------------------------------------------------------- */

    function install_js_featured_posts() {			
        
        $currentJSoption = unserialize( get_option( 'jsFeaturedPosts' ) );
        if ( empty( $currentJSoption ) ){
            $js_featured_posts_option = array( 'installed' => 'yes', 'jsFeaturedPosts' => array( ) );            
            add_option( 'jsFeaturedPosts', serialize( $js_featured_posts_option ), '', 'yes' ); 
        } 
            
    } register_activation_hook( __FILE__,'install_js_featured_posts' );

/*  -------------------------------------------------------------------------------------------------------------------------

        plugin code below 

    ---------------------------------------------------------------------------------------------------------------------- */

    // add fetured colum to posts
    function add_js_featured_colum( $columns ){
        $columns['featured_js_posts'] = 'Featured'; return $columns;
    } add_filter('manage_posts_columns', 'add_js_featured_colum', 2);
    
    // add the content to our new colum
    function add_js_featured_post_column_content( $col, $id ){
        
        if ( $col == 'featured_js_posts' ){
            $class = '';
            $jsFeaturedPost = get_post_meta( $id, '_jsFeaturedPost', true );
            if ( !empty( $jsFeaturedPost ) ){ $class = ' selected'; }
            echo  '<a id="postFeatured_'.$id.'" class="featured_posts_star'.$class.'"></a>';            
        }
        
    } add_action('manage_posts_custom_column', 'add_js_featured_post_column_content', 10, 2);
    
    // get of this this themes cpts and loop through them to create the correct action and filters
    function js_featured_posts_get_and_loop_through_post_types(){
        
        $post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'names' );
        foreach ($post_types as $post_type ) {            
            add_filter('manage_'.$post_type.'_posts_columns', 'add_js_featured_colum', 2);
            add_action('manage_'.$post_type.'_posts_custom_column', 'add_js_featured_post_column_content', 10, 2);            
        }
        
    } add_action('admin_init','js_featured_posts_get_and_loop_through_post_types');
    
    // chnage the width of our colum
    function js_featured_posts_colum_width(){
         
        $imgSrc = plugins_url( 'img/star.png' , __FILE__ ); ?>
        <style>
            #featured_js_posts, .column-featured_js_posts{ width:100px; text-align: center !important; }
            .featured_posts_star{ display:block; height:24px; width:24px; margin:8px auto 0 auto; border:none; 
                background: transparent url(<? echo $imgSrc; ?>) 0 -24px no-repeat; cursor:pointer; }
            .featured_posts_star.selected, .featured_posts_star:active{ background-position:0 0; }
        </style>
        <?

    } add_action('admin_head','js_featured_posts_colum_width');
    
    // add jquery function to admin head to save
    function js_featured_posts_add_jquery_to_head(){
        
        if ( current_user_can("administrator") ){ ?>					
            
            <script type="text/javascript" language="javascript">                
                jQuery(document).ready(function(){                
                    // when the checkbox is clicked save the meta option for this post
                    jQuery('.featured_posts_star').click(function() {
                        var selected = 'yes';
                        if ( jQuery(this).hasClass( 'selected' ) ){ 
                            jQuery(this).removeClass( 'selected' );
                            selected = 'no'; 
                        } else { jQuery(this).addClass( 'selected' ); }                        
                        // get id
                        var tempID = jQuery(this).attr( 'id' );
                            tempID = tempID.split( '_' );            
                        jQuery.post( ajaxurl, 'action=jsfeatured_posts&post='+tempID[1]+'&jsFeaturedPost='+selected ); 
                            
                    }); 
                        
                }); 
            
            </script> <?

        }

    } add_action( 'admin_head', 'js_featured_posts_add_jquery_to_head' );
    
    // add ajax call to wp in order to save the remove delete post link
    function js_featured_posts_link_add_ajax_call_to_wp(){		
        
        /*  found this example in the dont-break-the-code-example */			
        $jsFeaturedPost = $_POST['jsFeaturedPost'];
        $currentJSPostID = (int)$_POST["post"];
        if( !empty( $currentJSPostID ) && $jsFeaturedPost !== NULL ) {
            if ( $jsFeaturedPost == 'no' ){ delete_post_meta( $currentJSPostID, "_jsFeaturedPost" ); }
            else { add_post_meta( $currentJSPostID, "_jsFeaturedPost", 'yes' ); }
        } exit;

    } add_action('wp_ajax_jsfeatured_posts', 'js_featured_posts_link_add_ajax_call_to_wp');
    
/*  -------------------------------------------------------------------------------------------------------------------------

        when we deactivate the plugin do this 

    ---------------------------------------------------------------------------------------------------------------------- */

    function remove_js_featured_posts() {         
        delete_option( 'jsFeaturedPosts' );
    } register_deactivation_hook( __FILE__, 'remove_js_featured_posts' );
    
/*  -------------------------------------------------------------------------------------------------------------------------

        end

    ---------------------------------------------------------------------------------------------------------------------- */
    
?>
