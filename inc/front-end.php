<?php
// Frontend Gallery Actions
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
		echo "<input id='getrbgall' type='image' src=" .PIC_THUMB_DIR.'/'. $get_album->thumbnail_url. " />"; 
	?>
</form>
	</div>
<?php } ?>
	</div>
<?php

$album_id = (isset($_POST['album_id']) ? $_POST['album_id'] : null);
$get_pics = get_pics( $album_id );

/* Show front end galleries - in front_end_helper.php */

if (!empty($get_pics)) {
	show_front_end_galleries($album_id, $get_pics);
}
// trigger lightbox

function show_front_end_galleries($album_id, $get_pics) {
	global $wpdb;

	echo '<style>
	.rbgallery-custom .rbgallery-skin {
		box-shadow: 0 0 50px #222;
		}
	.show-rbgallery-div {display:none;}
	</style>
	<div class="show-rbgallery-div">';

if (is_array($get_pics)) {
	foreach ($get_pics as $get_pic) { 	
?>	
	<a href="<?php echo PIC_BIG_DIR.'/'.$get_pic->pic_name; ?>" data-rbgallery-group="thumb" class="rbgallery-thumbs" title="<?php echo $get_pic->title; ?>"><img src="<?php echo PIC_THUMB_DIR.'/'. $get_pic->thumbnail_url; ?>" /></a>
	<?php

		}
	}
	echo '</div><script>
	jQuery(document).ready(function($) { 
	        $(".rbgallery").rbgallery();
	        $(".rbgallery-thumbs").rbgallery({
					prevEffect : "none",
					nextEffect : "none",

					closeBtn  : false,
					arrows    : true,
					nextClick : true,

					helpers : {
						thumbs : {
							width  : 50,
							height : 50
						}
				    }
			}).trigger("click");
	   });
	</script>';
} 