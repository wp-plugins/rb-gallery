<?php 

function rb_gallery_albums()
{
    global $wpdb;
    return $wpdb->prefix . "rbgallery_albums";
}

function rb_gallery_pics()
{
    global $wpdb;
    return $wpdb->prefix . "rbgallery_pics";
}
