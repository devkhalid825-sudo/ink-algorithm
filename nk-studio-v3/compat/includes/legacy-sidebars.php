<?php
/**
 * The 15 widget areas the original Neuros theme registers (themes/neuros/functions.php neuros_widgets_init()).
 * Under the NK theme they would vanish, WordPress would treat their widgets as orphaned, and the original sidebar
 * (e.g. "Recent posts" next to the blog) disappears. Registering them again changes nothing in the database.
 * Generated from the real source; each area is only registered when nothing else registered that id first.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nkc_legacy_sidebar_defs() {
	return array (
	  0 => 
	  array (
	    'name' => 'Page Sidebar',
	    'id' => 'sidebar',
	    'description' => 'Widgets in this area will be shown on all pages.',
	    'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-wrapper">',
	    'after_widget' => '</div></div>',
	    'before_title' => '<h5 class="widget-title"><span>',
	    'after_title' => '</span></h5>',
	  ),
	  1 => 
	  array (
	    'name' => 'Post Sidebar',
	    'id' => 'sidebar-post',
	    'description' => 'Widgets in this area will be shown on all posts.',
	    'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-wrapper">',
	    'after_widget' => '</div></div>',
	    'before_title' => '<h5 class="widget-title"><span>',
	    'after_title' => '</span></h5>',
	  ),
	  2 => 
	  array (
	    'name' => 'Career Sidebar',
	    'id' => 'sidebar-vacancy',
	    'description' => 'Widgets in this area will be shown on all career pages.',
	    'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-wrapper">',
	    'after_widget' => '</div></div>',
	    'before_title' => '<h5 class="widget-title"><span>',
	    'after_title' => '</span></h5>',
	  ),
	  3 => 
	  array (
	    'name' => 'Service Sidebar',
	    'id' => 'sidebar-service',
	    'description' => 'Widgets in this area will be shown on all service pages.',
	    'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-wrapper">',
	    'after_widget' => '</div></div>',
	    'before_title' => '<h5 class="widget-title"><span>',
	    'after_title' => '</span></h5>',
	  ),
	  4 => 
	  array (
	    'name' => 'Case Study Sidebar',
	    'id' => 'sidebar-case-study',
	    'description' => 'Widgets in this area will be shown on all case study pages.',
	    'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-wrapper">',
	    'after_widget' => '</div></div>',
	    'before_title' => '<h5 class="widget-title"><span>',
	    'after_title' => '</span></h5>',
	  ),
	  5 => 
	  array (
	    'name' => 'Archive Sidebar',
	    'id' => 'sidebar-archive',
	    'description' => 'Widgets in this area will be shown on all posts and archive pages.',
	    'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-wrapper">',
	    'after_widget' => '</div></div>',
	    'before_title' => '<h5 class="widget-title"><span>',
	    'after_title' => '</span></h5>',
	  ),
	  6 => 
	  array (
	    'name' => 'FAQ Sidebar',
	    'id' => 'sidebar-faq',
	    'description' => 'Widgets in this area will be shown on FAQ page.',
	    'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-wrapper">',
	    'after_widget' => '</div></div>',
	    'before_title' => '<h5 class="widget-title"><span>',
	    'after_title' => '</span></h5>',
	  ),
	  7 => 
	  array (
	    'name' => 'Side Panel Sidebar',
	    'id' => 'sidebar-side',
	    'description' => 'Widgets in this area will be shown on side panel.',
	    'before_widget' => '<div id="%1$s" class="widget side-widget %2$s"><div class="widget-wrapper side-widget-wrapper">',
	    'after_widget' => '</div></div>',
	    'before_title' => '<h5 class="widget-title side-widget-title">',
	    'after_title' => '</h5>',
	  ),
	  8 => 
	  array (
	    'name' => 'Menu Sidebar',
	    'id' => 'sidebar-menu',
	    'description' => 'Widgets in this area will be shown on compact menu panel.',
	    'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-wrapper">',
	    'after_widget' => '</div></div>',
	    'before_title' => '<h5 class="widget-title"><span>',
	    'after_title' => '</span></h5>',
	  ),
	  9 => 
	  array (
	    'name' => 'Footer Sidebar (Style 1)',
	    'id' => 'sidebar-footer-style1',
	    'description' => 'Widgets in this area will be shown on footer area.',
	    'before_widget' => '<div id="%1$s" class="widget footer-widget %2$s"><div class="widget-wrapper footer-widget-wrapper">',
	    'after_widget' => '</div></div>',
	    'before_title' => '<h6 class="widget-title footer-widget-title">',
	    'after_title' => '</h6>',
	  ),
	  10 => 
	  array (
	    'name' => 'Footer Sidebar (Style 2)',
	    'id' => 'sidebar-footer-style2',
	    'description' => 'Widgets in this area will be shown on footer area.',
	    'before_widget' => '<div id="%1$s" class="widget footer-widget %2$s"><div class="widget-wrapper footer-widget-wrapper">',
	    'after_widget' => '</div></div>',
	    'before_title' => '<h6 class="widget-title footer-widget-title">',
	    'after_title' => '</h6>',
	  ),
	  11 => 
	  array (
	    'name' => 'Footer Sidebar (Style 3)',
	    'id' => 'sidebar-footer-style3',
	    'description' => 'Widgets in this area will be shown on footer area.',
	    'before_widget' => '<div id="%1$s" class="widget footer-widget %2$s"><div class="widget-wrapper footer-widget-wrapper">',
	    'after_widget' => '</div></div>',
	    'before_title' => '<h6 class="widget-title footer-widget-title">',
	    'after_title' => '</h6>',
	  ),
	  12 => 
	  array (
	    'name' => 'Footer Sidebar (Style 4)',
	    'id' => 'sidebar-footer-style4',
	    'description' => 'Widgets in this area will be shown on footer area.',
	    'before_widget' => '<div id="%1$s" class="widget footer-widget %2$s"><div class="widget-wrapper footer-widget-wrapper">',
	    'after_widget' => '</div></div>',
	    'before_title' => '<h6 class="widget-title footer-widget-title">',
	    'after_title' => '</h6>',
	  ),
	  13 => 
	  array (
	    'name' => 'Footer Sidebar (Style 5)',
	    'id' => 'sidebar-footer-style5',
	    'description' => 'Widgets in this area will be shown on footer area.',
	    'before_widget' => '<div id="%1$s" class="widget footer-widget %2$s"><div class="widget-wrapper footer-widget-wrapper">',
	    'after_widget' => '</div></div>',
	    'before_title' => '<h6 class="widget-title footer-widget-title">',
	    'after_title' => '</h6>',
	  ),
	  14 => 
	  array (
	    'name' => 'Sidebar WooCommerce',
	    'id' => 'sidebar-woocommerce',
	    'description' => 'Widgets in this area will be shown on Woocommerce Pages.',
	    'before_widget' => '<div id="%1$s" class="widget wooсommerce-widget %2$s"><div class="widget-wrapper">',
	    'after_widget' => '</div></div>',
	    'before_title' => '<h5 class="widget-title"><span>',
	    'after_title' => '</span></h5>',
	  ),
	);
}

add_action( 'widgets_init', function () {
	if ( nkc_original_theme_active() || ! nkc_legacy_theme() ) {
		return;
	}
	foreach ( nkc_legacy_sidebar_defs() as $def ) {
		if ( 'sidebar-woocommerce' === $def['id'] && ! class_exists( 'WooCommerce' ) ) {
			continue;
		}
		if ( ! is_registered_sidebar( $def['id'] ) ) {
			register_sidebar( $def );
		}
	}
}, 20 );
