<?php

// Data

function get_pics($album_id) {
	global $wpdb;

	if (empty($album_id)) { return null; }
	$results = $wpdb->get_results( 'SELECT wp_rbgallery_albums.album_id, wp_rbgallery_albums.album_order,wp_rbgallery_pics.title, wp_rbgallery_pics.thumbnail_url, wp_rbgallery_pics.sorting_order, wp_rbgallery_pics.pic_name  
		FROM wp_rbgallery_albums 
		INNER JOIN wp_rbgallery_pics
		ON wp_rbgallery_albums.album_id = wp_rbgallery_pics.album_id
		WHERE wp_rbgallery_albums.album_id = '.$album_id.'
		ORDER BY wp_rbgallery_pics.sorting_order DESC', OBJECT );
	return $results;
}

function get_albums() {
	global $wpdb;

	$results = $wpdb->get_results( 'SELECT wp_rbgallery_albums.album_id, wp_rbgallery_albums.album_name, wp_rbgallery_albums.album_order,wp_rbgallery_pics.thumbnail_url, wp_rbgallery_pics.sorting_order  FROM wp_rbgallery_albums 
		INNER JOIN wp_rbgallery_pics
		ON wp_rbgallery_albums.album_id = wp_rbgallery_pics.album_id
		WHERE wp_rbgallery_pics.sorting_order = 1
		ORDER BY wp_rbgallery_albums.album_order ASC', OBJECT );
	return $results;
}