<?php

// Data

function get_pics($album_id) {
	global $wpdb;

	if (empty($album_id)) { return null; }
	$results = $wpdb->get_results( "SELECT {$wpdb->prefix}wp_rbgallery_albums.album_id, {$wpdb->prefix}wp_rbgallery_albums.album_order,{$wpdb->prefix}wp_rbgallery_pics.title, {$wpdb->prefix}wp_rbgallery_pics.thumbnail_url, {$wpdb->prefix}wp_rbgallery_pics.sorting_order, {$wpdb->prefix}wp_rbgallery_pics.pic_name  
		FROM {$wpdb->prefix}wp_rbgallery_albums 
		INNER JOIN {$wpdb->prefix}wp_rbgallery_pics
		ON {$wpdb->prefix}wp_rbgallery_albums.album_id = {$wpdb->prefix}wp_rbgallery_pics.album_id
		WHERE {$wpdb->prefix}wp_rbgallery_albums.album_id = {$album_id}
		ORDER BY {$wpdb->prefix}wp_rbgallery_pics.sorting_order DESC", OBJECT );
	return $results;
}

function get_albums() {
	global $wpdb;

	$results = $wpdb->get_results( "SELECT {$wpdb->prefix}wp_rbgallery_albums.album_id, {$wpdb->prefix}wp_rbgallery_albums.album_name, {$wpdb->prefix}wp_rbgallery_albums.album_order,{$wpdb->prefix}wp_rbgallery_pics.thumbnail_url, {$wpdb->prefix}wp_rbgallery_pics.sorting_order  FROM {$wpdb->prefix}wp_rbgallery_albums 
		INNER JOIN {$wpdb->prefix}wp_rbgallery_pics
		ON {$wpdb->prefix}wp_rbgallery_albums.album_id = {$wpdb->prefix}wp_rbgallery_pics.album_id
		WHERE {$wpdb->prefix}wp_rbgallery_pics.sorting_order = 1
		ORDER BY {$wpdb->prefix}wp_rbgallery_albums.album_order ASC", OBJECT );
	return $results;
}
