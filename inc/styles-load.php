<?php

// ++add namespace

// add styles
add_action( 'wp_enqueue_scripts', function() { // ++try footer?

    $enqueue_dir = get_template_directory() . '/assets/styles/';
    $enqueue_url = get_template_directory_uri() . '/assets/styles/';
    $enqueue_files = array_merge( ['fonts'], fct1_get_style_files_() );

    foreach ( $enqueue_files as $v ) {
        if ( !is_file( $enqueue_dir . $v . '.css' ) ) { continue; }

        wp_enqueue_style(
            FCT1S['prefix'] . $v,
            $enqueue_url . $v . '.css',
            [ FCT1S['prefix'] . 'style' ], // after the main one
            FCT1S_VER,
            'all'
        );

    }
    
    // main css & js
    wp_enqueue_style( FCT1S['prefix'] . 'style',
    	get_template_directory_uri() . '/style.css',
    	[],
        FCT1S_VER,
        'all'
    );
    wp_enqueue_script( FCT1S['prefix'] . 'common',
		get_template_directory_uri() . '/assets/common.js',
		[ 'jquery' ],
		FCT1S_VER,
		1
	);

});


add_action( 'wp_head', function() { // include the first-screen styles, instead of enqueuing
?>
<style><?php
    $include_dir = get_template_directory() . '/assets/styles/first-screen/';
    $include_files = array_merge( ['style'], fct1_get_style_files_() );

    ob_start();

    foreach ( $include_files as $v ) {
        if ( !is_file( $include_dir . $v . '.css' ) ) { continue; }

        if ( FCT1S['dev'] ) { echo "\n\n".'/*---------- '.$v.'.css ----------*'.'/'."\n"; }
        
        include_once( $include_dir . $v . '.css' );

    }

    $content = ob_get_contents();
    ob_end_clean();
    
    if ( !FCT1S['dev'] ) { $content = fct1_css_minify( $content ); }
    
    echo $content;

?></style>
<?php

    echo FCT1S['fonts_external'] . "\n";

}, 7 );


// moving the Gutenberg away from the first screen ++do the same with jquery and other, as all loads async-ly
add_action( 'wp_enqueue_scripts', function() {
    wp_dequeue_style( 'wp-block-library' );
});
add_action( 'wp_footer', function() {
    wp_enqueue_style( 'wp-block-library' );
});


// make a list of .css files names, according to the url: post-type, archive, front
function fct1_get_style_files_() {
    static $files = null;
    if ( $files !== null ) { return $files; }

    $files = [];
    if ( is_front_page() ) {
        $files[] = 'front-page';
    }
    if ( is_search() ) {
        $files[] = 'search';
    }

    // get post type
    $qo = get_queried_object();
    if ( !is_object( $qo ) ) { return $files; }
    if ( get_class( $qo ) === 'WP_Post_Type' ) {
        $post_type = $qo->name;
    }
    if ( get_class( $qo ) === 'WP_Post' ) {
        $post_type = $qo->post_type;
    }

    if ( is_singular( $post_type ) ) {
        $files[] = $post_type;
    }
    if ( is_home() || is_archive() && ( !$post_type || $post_type === 'page' ) ) {
        $files[] = 'archive-post';
    }
    if( is_post_type_archive( $post_type ) ) {
        $files[] = 'archive-' . $post_type;
    }

    return $files;
}

function fct1_css_minify($text) {
    $text = preg_replace( '/\/\*(?:.*?)*\*\//', '', $text ); // remove comments
    $text = preg_replace( '/\s+/', ' ', $text ); // one-line & only single speces
    $text = preg_replace( '/ ?([\{\};:\>\~\+]) ?/', '$1', $text ); // remove spaces
    $text = preg_replace( '/\+(\d)/', ' + $1', $text ); // restore spaces in functions
    $text = preg_replace( '/(?:[^\}]*)\{\}/', '', $text ); // remove empty properties
    $text = str_replace( [';}', '( ', ' )'], ['}', '(', ')'], $text ); // remove last ; and spaces
    // ++can also remove 0 from 0.5
    return trim( $text );
}