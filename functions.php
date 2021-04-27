<?php

function fct_dev() {
    //return '';
    return time();
}

/* add common styles */
add_action( 'get_footer', function() { // wp_enqueue_scripts

    wp_enqueue_style( 'fckf-style',
    	get_template_directory_uri() . '/style.css',
    	false,
        wp_get_theme()->get('Version') . fct_dev(),
        'all'
    );

    wp_enqueue_script( 'fckf-common',
		get_template_directory_uri() . '/assets/common.js',
		[ "jquery" ],
		wp_get_theme()->get('Version') . fct_dev(),
		1
	);
    
    wp_enqueue_style( 'fckf-fonts',
        get_template_directory_uri() . '/assets/fonts.css',
        false,
        wp_get_theme()->get('Version') . fct_dev(),
        'all'
    );

});

/* add first-screen styles */
add_action( 'wp_head', function() {
    global $post;

?>
<style>
<?php

    // common first screen
    @include_once( get_template_directory() . '/assets/first-screen.css' );

    // personal first screen
    $qo = get_queried_object();
    if ( get_class( $qo ) === 'WP_Post' ) { // personal style for a post by slug
        $file = $qo->post_name;
    } elseif ( get_class( $qo ) === 'WP_Post_Type' ) { // personal for post type archive (basically, by slag too)
        $file = $qo->name;
    }
	@include_once( get_template_directory() . '/assets/fs-' . $file . '.css' );
?>
</style>
<?php
});


/* theme settings */
// fixed header on
add_filter( 'body_class', function ($classes) {
    $classes[] = 'header-fixed';
    return $classes;
});

// menu & thumbnails
add_action( 'init', function() {

	register_nav_menus( [
		'main' => 'Main Menu',
	]);
	
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );

});

// remove 'Archive:'
add_filter( 'get_the_archive_title', function($title) {
	if ( is_post_type_archive() ) {
		$title = post_type_archive_title( '', false );
	}else{
        global $post;
        $title = $post->post_title;
	}
	return $title;
});

// fix excerpt
add_filter( 'excerpt_more', function($more) {
    return '';
});


/* economy */
// disable creating default sizes on upload
add_action( 'intermediate_image_sizes_advanced', function($sizes) {

    unset(
        $sizes['medium'],
        $sizes['large'],
        $sizes['medium_large'],
        $sizes['1536x1536'],
        $sizes['2048x2048']
    );
	return $sizes;

});

// disable displaying default sizes in admin
add_action( 'admin_print_styles', function() {

    $screen = get_current_screen();

    if( $screen->id !== 'options-media' ) {
        return;
    }

  ?>
    <style>
        #wpbody-content form > table:first-of-type tr:nth-of-type( 2 ),
        #wpbody-content form > table:first-of-type tr:nth-of-type( 3 ) {
            display: none;
        }
    </style>
  <?php

} );

// disable emoji
add_action( 'init', function() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );	
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );	
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	
	// Remove from TinyMCE
	add_filter( 'tiny_mce_plugins', function( $plugins ) {
        if ( is_array( $plugins ) ) {
            return array_diff( $plugins, ['wpemoji'] );
        } else {
            return [];
        }
    });
});

// remove jquery migrate
add_action( 'wp_default_scripts', function ( $scripts ) {
    if ( !is_admin() && isset( $scripts->registered['jquery'] ) ) {
        $script = $scripts->registered['jquery'];
        if ( $script->deps ) {
            $script->deps = array_diff( $script->deps, ['jquery-migrate'] );
        }
    }
} );


// custom post type for global and reusable sections, like footer
add_action( 'init', function() {
	$labels = [
		'name'                => 'Sections',
		'singular_name'       => 'Section',
		'menu_name'           => 'Sections',
		'all_items'           => 'All sections',
		'view_item'           => 'View Section',
		'add_new_item'        => 'Add New Section',
		'add_new'             => 'Add New',
		'edit_item'           => 'Edit Section',
		'update_item'         => 'Update Section',
		'search_items'        => 'Search Section',
		'not_found'           => 'Section not found',
		'not_found_in_trash'  => 'Section not found in Trash',
	];
	$args = [
		'label'               => 'fct-section',
		'description'         => 'Global and reusable sections using Gutenberg editor for styling (footer)',
		'labels'              => $labels,
		'supports'            => [
									'title',
									'editor',
								],
		'hierarchical'        => false,
		'public'              => false,
		'show_in_rest'        => true, // turn on Gutenberg
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 20,
		'menu_icon'           => 'dashicons-schedule',
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'capability_type'     => 'page',
	];
	register_post_type( $args['label'], $args );
});

// gutenberg full-width & wide blocks support (wide is for normal, full is for full-screen)
add_action( 'after_setup_theme', function() {
    add_theme_support( 'align-wide' );
});
