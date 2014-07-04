<?php
$path = plugins_url();
define("PIC_BIG_DIR", content_url() . '/rb-gallery/gallery-uploads' );
define("PIC_THUMB_DIR", content_url() . '/rb-gallery/album-thumbs' );
 ?>
<style>
	.wprb_album_group {
		width:100%;
	}

	.wprb_album_single h3 {
		font-size:90%;
	}

	.wprb_album_single {

	}

</style>

<div class="wprb_album_group">
<?php

foreach($get_albums as $get_album) { 

	?>
<div class="wprb_album_single">
<form method="POST" class="view_album_click" action="<?php echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>">
<input type="hidden" name="album_id" value="<?php echo $get_album->album_id; ?>" >
	<?php 
		echo '<h3>'. $get_album->album_name.'</h3>'; 
		echo "<input type='image' src=" .PIC_THUMB_DIR.'/'. $get_album->thumbnail_url. " />"; 
	?>
</form>

	</div>

<?php } ?>
	</div>


<?php


//$album_id = $_POST['album_id'];

$album_id = (isset($_POST['album_id']) ? $_POST['album_id'] : null);

$get_pics = get_pics( $album_id );


/* Show front end galleries - in front_end_helper.php */
show_front_end_galleries($album_id, $get_pics);
// trigger lightbox


function show_front_end_galleries($album_id, $get_pics) {

	global $wpdb;


	echo '<style>
	ul#imageSet {
		list-style-type: none;
	} 
	#lightbox-wrapper {
    left: 25%;
    overflow: hidden;
    top:40px;
    position: absolute;
	}
	.show-rbgallery-div {display:none;}
	</style>';

	echo '<ul id="imageSet">';

if (is_array($get_pics)) {
	foreach ($get_pics as $get_pic) { 
			
	?>	
		<li>
		 <a href="<?php echo PIC_BIG_DIR.'/'.$get_pic->pic_name; ?>" class="lightboxTrigger" title="<?php echo $get_pic->title; ?>">
            </a>
          <img src=" <?php echo PIC_THUMB_DIR.'/'. $get_pic->thumbnail_url; ?> " class="lbthumb"/>
         </li>
	<?php

		}
	}

	echo '</ul>';

	if (!empty($get_pic)) {
		echo '<script>var executeCode = true;</script>';
	}

	echo '<script>

	   jQuery( window ).load(function() {
	     	if (executeCode = true) {
	        	jQuery(".lightboxTrigger").trigger("click");
	        }
	   });

	</script>';
	

}
