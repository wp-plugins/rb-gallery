<?php
$path = plugins_url();
define("PIC_BIG_DIR", content_url() . '/rb-gallery/gallery-uploads' );
define("PIC_THUMB_DIR", content_url() . '/rb-gallery/album-thumbs' );
?>
<!-- make sure jquery is installed -->
 <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
 <script src="<?php echo $path; ?>/wprb-gallery/inc/fancybox/jquery.fancybox.js"></script>
 <script src="<?php echo $path; ?>/wprb-gallery/inc/fancybox/jquery.fancybox-thumbs.js"></script>
 <link rel="stylesheet" href="<?php echo $path; ?>/wprb-gallery/inc/fancybox/jquery.fancybox.css"> 
 <link rel="stylesheet" href="<?php echo $path; ?>/wprb-gallery/inc/fancybox/jquery.fancybox-thumbs.css"> 
 
<?php /*if ($atts = 'list') {echo 'it is a list';}
if ($atts = 'column') {echo 'it is a column';}*/
 ?>
<style>
	.wprb_album_group {
		width:100%;
	}

	.wprb_album_single h3 {
		font-size:90%;
	}

	.wprb_album_single {
		width:24%;
		float:left;
		padding-left:5%;
	}

</style>

	<div class="wprb_album_group">
<?php

foreach($get_albums as $get_album) { ?>
	<div class="wprb_album_single">
<form method="POST" class="view_album_click" action="<?php echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>">
<input type="hidden" name="album_id" value="<?php echo $get_album->album_id; ?>" >
	<?php 
		echo '<h3>'. $get_album->album_name.'</h3>'; 
		echo '<img src=" '.PIC_THUMB_DIR.'/'. $get_album->thumbnail_url.'" />'; 
	?>
	<br /><button type="send">View Album</button>
</form>

	</div>
<?php } ?>
	</div>


<?php


//$album_id = $_POST['album_id'];

$album_id = (isset($_POST['album_id']) ? $_POST['album_id'] : null);

/* Show front end galleries - in front_end_helper.php */
show_front_end_galleries($album_id);


?>
<style>
.show-rbgallery-div {display:none;}
</style>
<script>
/* This code selects the images that go with the album and displays them in fancybox */

$(document).ready(function() {

$(".fancybox-thumb").fancybox({
		prevEffect	: 'none',
		nextEffect	: 'none',
		helpers	: {
			title	: {
				type: 'outside'
			},
			thumbs	: {
				width	: 50,
				height	: 50
			}
		}
	});

});

</script>