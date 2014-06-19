<?php

// Data

function get_albums() {
	global $wpdb;

	$results = $wpdb->get_results( 'SELECT * FROM wp_rbgallery_albums 
		INNER JOIN wp_rbgallery_pics
		ON wp_rbgallery_albums.album_id = wp_rbgallery_pics.album_id
		WHERE wp_rbgallery_pics.album_cover = 1
		ORDER BY album_order ASC', OBJECT );
	return $results;
}

function show_front_end_galleries($album_id) {

	if (!isset($album_id)) { $album_id = 0; }
	global $wpdb;

	// query DB and format data into fancy box html code
	$picquery = "SELECT * FROM wp_rbgallery_pics WHERE album_id = {$album_id} ORDER BY sorting_order DESC ";

	$show_pics = $wpdb->get_results($picquery);


	echo '<div class="show-rbgallery-div">';
		foreach ($show_pics as $show_pic) { 
			
	?>	

		<a class="fancybox-thumb" rel="fancybox-thumb" href="<?php echo PIC_BIG_DIR.'/'. $show_pic->pic_name; ?>" title="<?php echo $show_pic->title; ?>">
		<img src="<?php echo PIC_THUMB_DIR.'/'. $show_pic->thumbnail_url; ?>" alt="" /></a> 

	<?php

		}
	echo '</div>';

	if (!empty($show_pics)) {
		echo '<script>var executeCode = true;</script>';
	}

	echo '<script>

	   $( window ).load(function() {
	     	if (executeCode = true) {
	        	$(".fancybox-thumb").trigger("click");
	        }
	   });

	</script>';

}
