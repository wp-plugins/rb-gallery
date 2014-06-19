<?php 

$path = plugins_url();
// Directories
define("PIC_BIG_DIR", ABSPATH . 'wp-content/rb-gallery/gallery-uploads' );
define("PIC_THUMB_DIR", ABSPATH . 'wp-content/rb-gallery/album-thumbs' );
// Paths
define("PIC_BIG_PATH", content_url('rb-gallery/gallery-uploads/', __FILE__) );
define("PIC_THUMB_PATH", content_url('rb-gallery/album-thumbs/', __FILE__) );


//var_dump( ABSPATH . "wp-content" ); 
//generate_thumbnail($uploaded_image_path, $thumbnail_image_path, $width, $height)

function create_thumbnail($source_image_path, $thumbnail_image_path, $imageWidth, $imageHeight)
{
    list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
    $source_gd_image = false;
    switch ($source_image_type) {
        case IMAGETYPE_GIF:
            $source_gd_image = imagecreatefromgif($source_image_path);
            break;
    }
    if ($source_gd_image === false) {
        return false;
    }
    $source_aspect_ratio = $source_image_width / $source_image_height;
    if ($source_image_width > $source_image_height) {
        $real_height = $imageHeight;
        $real_width = $imageHeight * $source_aspect_ratio;
    } else if ($source_image_height > $source_image_width) {
        $real_height = $imageWidth / $source_aspect_ratio;
        $real_width = $imageWidth;

    } else {

        $real_height = $imageHeight > $imageWidth ? $imageHeight : $imageWidth;
        $real_width = $imageWidth > $imageHeight ? $imageWidth : $imageHeight;
    }


    $thumbnail_gd_image = imagecreatetruecolor($real_width, $real_height);
	
	if(($source_image_type == 1) || ($source_image_type==3)){
		imagealphablending($thumbnail_gd_image, false);
		imagesavealpha($thumbnail_gd_image, true);
		$transparent = imagecolorallocatealpha($thumbnail_gd_image, 255, 255, 255, 127);
		imagecolortransparent($thumbnail_gd_image, $transparent);
		imagefilledrectangle($thumbnail_gd_image, 0, 0, $real_width, $real_height, $transparent);
 	}
	else
	{
		$bg_color = imagecolorallocate($thumbnail_gd_image, 255, 255, 255);
		imagefilledrectangle($thumbnail_gd_image, 0, 0, $real_width, $real_height, $bg_color);
	}
    imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $real_width, $real_height, $source_image_width, $source_image_height);
	switch ($source_image_type)
	{
		case IMAGETYPE_GIF:
			imagepng($thumbnail_gd_image, $thumbnail_image_path, 9 );
		break;
	}
    imagedestroy($source_gd_image);
    imagedestroy($thumbnail_gd_image);
    return true;
}


/* File Upload Code */
function upload_file($tmp_name, $name, $type, $size) {

  $filename = basename($name);
  $ext = substr($filename, strrpos($filename, '.') + 1);
 
  if ($size > 900001) { echo 'exceeded file size limit'; }
  if (($ext == "gif")  && ($size < 900000)) {
		// Make more secure by checking
		$path = PIC_BIG_DIR;
		$name = $path . DIRECTORY_SEPARATOR . $name;

		if (move_uploaded_file($tmp_name, $name)) {
			return true;
		} else { return 'Could not upload - move the file!'; }
	}
	else { return false; }

}

function create_thumbname($image_name) {
		// Create Thumbnail name
	$thumb_img_nameA = substr($image_name, 0,-5);
	$thumb_img_nameB = $thumb_img_nameA .'_m.gif';
	return $thumb_img_nameB;
}


function save_images_to_database($image_name, $album_id, $thumb_img_name) {
	global $wpdb;
	// Save image info to databse - put in upload function and resize function
	//$thumbname = create_thumbname($image_name);

	$rows_ret = $wpdb->query($wpdb->prepare("INSERT INTO wp_rbgallery_pics (`album_id`, `thumbnail_url`, `pic_name`) VALUES (%d,%s,%s); ",$album_id, $thumb_img_name, $image_name));
	
	$redirect_url = admin_url('admin.php?page=wprb-gallery&album_id=', __FILE__).$album_id;
    ?>
		 <script>  window.location.replace("<?php echo $redirect_url; ?>"); </script> 
    <?php 

}

//Add images and save them -- later check for duplicate names and append a number

if (isset($_FILES['photo']['tmp_name'])) {
$uploaded_file_ok = upload_file($_FILES['photo']['tmp_name'], $_FILES['photo']['name'],$_FILES["photo"]["type"], $_FILES["photo"]["size"]);
	
	$big_img_name = $_FILES['photo']['name'];

	if ($uploaded_file_ok == true) {
	$gall_oldpath = PIC_BIG_DIR.DIRECTORY_SEPARATOR.$big_img_name;
	$thumb_img_name = create_thumbname($big_img_name);
	$gall_thumbpath  = PIC_THUMB_DIR.DIRECTORY_SEPARATOR.$thumb_img_name;

	// Set Thumbnail Size
	create_thumbnail($gall_oldpath, $gall_thumbpath, 240, 160);

	//create_thumbnail($_FILES['photo']['name']);
	save_images_to_database($big_img_name, $_GET['album_id'], $thumb_img_name);
	} 
	if ($uploaded_file_ok == false) {
		echo "Cannot Upload this type of file";
	} 

}

// Delete Album

function delete_album($album_id) {
	global $wpdb; 

	$wpdb->delete( 'wp_rbgallery_albums', array( 'album_id' => $album_id ) );
	delete_multiple_pics($album_id);

}

function delete_multiple_pics($album_id) {

	global $wpdb; 

	$getpics = $wpdb->get_results("SELECT pic_id, album_id, thumbnail_url, pic_name FROM wp_rbgallery_pics WHERE album_id = ".$album_id);
	foreach ($getpics as $getpic) {

		delete_pic($getpic->pic_id,$getpic->album_id );
	}
	$wpdb->delete( 'wp_rbgallery_pics', array( 'album_id' => $album_id ) );
}

if (isset( $_GET['delete'] ) && isset($_GET['album_id'])) {

	if ($_GET['delete'] == 1) {
		delete_album( $_GET['album_id'] );
	}
}

// Delete Picture

if (isset( $_GET['pic_id'] )) {

	if (@$_GET['delete'] == 2) {
		delete_pic( $_GET['pic_id'],$_GET['album_id'] );
	}
}


function delete_pic( $pic_id,$album_id ) {
	global $wpdb; 
	// select pic by id
	$getpics = $wpdb->get_row("SELECT pic_id, thumbnail_url, pic_name FROM wp_rbgallery_pics WHERE pic_id = ".$pic_id);

	// delete from directories

	//var_dump($getpic);
	$bigpic_path = PIC_BIG_DIR.DIRECTORY_SEPARATOR.$getpics->pic_name;
	$thumb_path  = PIC_THUMB_DIR.DIRECTORY_SEPARATOR.$getpics->thumbnail_url;

		unlink($bigpic_path);
		unlink($thumb_path);


	// delete from DB
	$wpdb->delete( 'wp_rbgallery_pics', array( 'pic_id' => $pic_id ) );

	$redirect_url = admin_url('admin.php?page=wprb-gallery&album_id=', __FILE__).$album_id;
    ?>
		<script>  window.location.replace("<?php echo $redirect_url; ?>"); </script> 
    <?php 
}


//Edit Pic
// Update Picture
if (isset($_POST['update_imgs'])) {
		if( $_POST['update_imgs'] == 'edit_image') {
			$wprb_imgedit_alidimg = esc_html($_POST['wprb_imgedit_alidimg'] );
			$wprb_imgedit_albumid = esc_html($_POST['wprb_imgedit_albumid']);
			$update_imgs = esc_html($_POST['update_imgs']);


				$wprb_imgedit_title = esc_html( $_POST['wprb_imgedit_title'] );
				$wprb_imgedit_description = esc_html( $_POST['wprb_imgedit_description'] );
				$wprb_imgedit_sortingorder = esc_html( $_POST['wprb_imgedit_sortingorder'] );
				$wprb_imgedit_albumcover = esc_html( $_POST['wprb_imgedit_albumcover'] );
				$rb_pics_table = 'wp_rbgallery_pics';

		global $wpdb; 

		//update database
		$rows_ret = $wpdb->query($wpdb->prepare("UPDATE wp_rbgallery_pics SET pic_id = %d,album_id = %d, title = %s, description = %s, sorting_order = %d,album_cover = %d WHERE pic_id = %d AND album_id = %d; ",$wprb_imgedit_alidimg,$wprb_imgedit_albumid ,$wprb_imgedit_title,$wprb_imgedit_description ,$wprb_imgedit_sortingorder,$wprb_imgedit_albumcover,$wprb_imgedit_alidimg,$wprb_imgedit_albumid));


				$redirect_url = admin_url('admin.php?page=wprb-gallery&album_id=', __FILE__).$wprb_imgedit_albumid;
		    ?>
				<script>  window.location.replace("<?php echo $redirect_url; ?>"); </script> 
			<?php 
		}
}

?>
<link type="text/css" rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/themes/smoothness/jquery-ui.min.css" media="screen" />
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="<?php echo plugins_url('css/bootstrap.css', __FILE__); ?>"> 
<style>.panel-group .panel { overflow: visible !important; }</style>

<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="<?php echo plugins_url('js/bootstrap.min.js', __FILE__); ?>"></script>


<div class="wrap">
	
	<div id="icon-options-general" class="icon32"></div>
	<h2>RB Gallery Plugin</h2>
	
	<div id="poststuff">
	
		<div id="post-body" class="metabox-holder columns-2">
		
			<!-- main content -->
			<div id="post-body-content">
				
				<div class="meta-box-sortables ui-sortable">
					
					<div class="postbox">
					
						<h3><span>Add Album</span></h3>
						<div class="inside">
							
						<form name="wprbgall_albumname_form" method="post" action="">

						<input type="hidden" name="wprbgall_form_submitted" value="Y" >

							<table class="form-table">
								<tr>
									<td><label for="wprbgall_albumname">Album Name</label></td>
									<td><input name="wprbgall_albumname" id="rbgall_albumname" type="text" value="" class="regular-text" /></td>
								</tr>
								<tr>
									<td><label for="wprbgall_albumdesc">Description</label></td>
									<td><input name="wprbgall_albumdesc" id="rbgall_albumdesc" type="text" value="" class="regular-text" /></td>
								</tr>
								<tr>
									<td><label for="wprbgall_albumorder">Album Order</label></td>
									<td><input name="wprbgall_albumorder" id="rbgall_albumorder" type="text" value="" class="regular-text" /></td>
								</tr>
							</table>
							<p>
							<input class="button-primary" type="submit" name="rbgall_albumname_submit" value="add" />
							</p>
							</form>

						</div> <!-- .inside -->
					
					</div> <!-- .postbox -->









<div class="panel-group" id="accordion">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
          Albums
        </a>
      </h4>
    </div>

<?php 

if ($album_id !== '') {
	$in = '';
} else {
	$in = 'in';
}


?>
    <div id="collapseOne" class="panel-collapse collapse <?php echo $in; ?>">
      <div class="panel-body">
        

<div class="postbox">
<h3><span>Most Recent Albums</span></h3>
	<div class="inside">
						
	<ul class="wprbgall-badges">
	<?php

	$check_db_forempty = $wpdb->query( "SHOW TABLES LIKE 'wp_rbgallery_albums' " );
	if(empty($check_db_forempty)) { echo 'Database is not found for this plugin'; exit(); }

	$results = $wpdb->get_results( "SELECT wp_rbgallery_albums.album_id, wp_rbgallery_albums.album_name, wp_rbgallery_albums.album_date, wp_rbgallery_albums.description, wp_rbgallery_pics.thumbnail_url, wp_rbgallery_pics.album_cover FROM wp_rbgallery_albums LEFT OUTER JOIN wp_rbgallery_pics ON wp_rbgallery_albums.album_id = wp_rbgallery_pics.album_id GROUP BY wp_rbgallery_albums.album_id;", OBJECT );

		//for ($i = 0; $i < 5; $i++ ):

	foreach ($results as $res) :
		?>
		<li>
			<ul>
				<li>
					<?php 
					echo "<strong>Album ID - " . $res->album_id . "</strong><br />";
					echo "Title: <strong>" . $res->album_name . "</strong><br />";
					if (isset($res->description)) {
					echo "Description: <em>" . $res->description . "</em><br />";
											}
					echo "Date Created: " . $res->album_date . "<br />"; 
					?>
				</li>
				<li>
				<?php if (!empty($res->thumbnail_url)) { ?>
					<img class="wprbgall-gravatar" width="120px" src="<?php echo PIC_THUMB_PATH .'/'. $res->thumbnail_url; ?>">
				<?php } else {
					echo '<img src="'.content_url() . '/rb-gallery/dontremove/dontremove.jpg" />';
					} ?>
				</li>
				<li class="album-name">
				<!-- &album_id=<?php //echo $id; ?> rbedit_album -->
					<a href="<?php echo site_url(); ?>/wp-admin/admin.php?page=wprb-gallery&album_id=<?php echo $res->album_id; ?>">Edit Album</a>
				</li>
				<li class="delete-album">
					<a href="<?php echo site_url(); ?>/wp-admin/admin.php?page=wprb-gallery&delete=1&album_id=<?php echo $res->album_id; ?>" onClick="return confirm_del_album();">Delete Album</a>
				</li>
				<li><hr /></li>
			</ul>
		</li>
	<?php endforeach; ?>
	</ul>

		</div> <!-- .inside -->
					
			</div> <!-- .postbox -->







      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
          Album Detail
        </a>
      </h4>
    </div>
<?php 

if (!empty($get_images_for_album) || empty($get_images_for_album)) {
	$in = 'in';
} else {
	$in = '';
}


?>
    <div id="collapseTwo" class="panel-collapse collapse <?php echo $in; ?>">
      <div class="panel-body">




<?php 
if (isset($upload_message))
{
	echo $upload_message;
}

if (isset($_GET['album_id'])) {
$get_album_id = $_GET['album_id'];
// Get data if exists
$edit_abum_data = $wpdb->get_results( 'SELECT * FROM wp_rbgallery_albums WHERE album_id ='. $get_album_id, OBJECT );
}


// Check and Display album editing capability
if (isset($edit_abum_data)) { 

	foreach(@$edit_abum_data as $ead) { 
?>
<!-- Button trigger modal -->
<button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal">
  Add Pictures
</button> 


<h4>Edit Album</h4>
<?php echo 'Album ID - '. $ead->album_id; ?>
<form name="wprbgall_albumname_form" method="post" action="">

	<input type="hidden" name="wprbgall_form_updated" value="Y" >
	<input type="hidden" name="wprbgall_album_id" value="<?php echo $get_album_id; ?>" >

	<table class="form-table">
			<tr>
				<td><label for="wprbgall_albumname">Album Name</label></td>
				<td><input name="wprbgall_albumname" id="rbgall_albumname" type="text" value="<?php if(isset($ead->album_name)){echo $ead->album_name;} ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<td><label for="wprbgall_albumdesc">Description</label></td>
				<td><input name="wprbgall_albumdesc" id="rbgall_albumdesc" type="text" value="<?php if(isset($ead->description)){echo $ead->description;} ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<td><label for="wprbgall_albumorder">Album Order</label></td>
				<td><input name="wprbgall_albumorder" id="rbgall_albumorder" type="text" value="<?php if(isset($ead->album_order)){echo $ead->album_order;} ?>" class="regular-text" /></td>
			</tr>
		</table>
		<p>
		<input class="button-primary" type="submit" name="rbgall_albumname_submit" value="update" />
		</p>
</form>

	<?php } 
			} //isset check 
			else {echo 'Select an album to edit.';}
	?>

<hr />


<!-- Modal -->
<div class="rbmodal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="rbmodal-dialog">
    <div class="rbmodal-content" style="width:600px">
      <div class="rbmodal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="rbmodal-title" id="myModalLabel">Add A Picture to Album</h4>
      </div>
      <div class="rbmodal-body" style="height:100px;"> 
        
<!-- <?php //echo $path; ?>/wprb-gallery/inc/upload.php -->
<form action="" method="post" enctype="multipart/form-data">
	Your Photo: <input type="file" name="photo" size="25" />
	<input type="submit" name="submit" value="Submit" />
</form>

   </div>
      <div class="rbmodal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div> 











       
<?php if (!empty($get_images_for_album) ) { ?>

<div class="postbox">
<h3><span>Pictures</span></h3>
	<div class="inside">
						
	<ul class="wprbgall-badges">
	<?php
	// check album cover for making album live on site
	$wprb_ablum_cover_totals = 0;
	foreach ($get_images_for_album as $cacover) {
		$wprb_ablum_cover_totals = $wprb_ablum_cover_totals + $cacover->album_cover;	
	}
	if ($wprb_ablum_cover_totals == 0) {
		// launch alert
		?>
		<p class="alternate"> You must make one of your images an album cover to see it on your web-page. Select 'Edit Picture'</p>
		<?php
	}

	foreach ($get_images_for_album as $get_image_album) :?>
		<li>
			<ul>
				<li>
					<?php 
					echo "<strong>Album ID - " . $get_image_album->album_id . "</strong><br />";
					echo "<strong>" . $get_image_album->title . "</strong><br />";
					echo $get_image_album->description . "<br />"; 
					//echo $get_image_album->pic_name . "<br />"; 
					?>
				</li>
				<li>
					<img class="wprbgall-gravatar" width="120px" src="<?php echo PIC_THUMB_PATH . $get_image_album->thumbnail_url; ?>">
					<?php echo "<br />Sorting Order: " . $get_image_album->sorting_order . "<br />";

					if ($get_image_album->album_cover == 0) {
						echo "Album Cover: No ";
					}

					if ($get_image_album->album_cover == 1) {
						echo "Album Cover: Yes ";
					}

					?>
				</li>

			<li class="album-name">
					<!-- Button trigger modal -->

					<a href="<?php echo site_url(); ?>/wp-admin/admin.php?page=wprb-gallery&album_id=<?php echo $get_image_album->album_id; ?>&edit=1&pic_id=<?php echo $get_image_album->pic_id; ?>">Edit Picture</a>

			</li>
			<li class="delete-album">
				<a href="<?php echo site_url(); ?>/wp-admin/admin.php?page=wprb-gallery&album_id=<?php echo $get_image_album->album_id; ?>&delete=2&pic_id=<?php echo $get_image_album->pic_id; ?>" onClick="return confirm_del_img();">Delete Picture</a>
				<br /><br />
			</li>
			<li><hr /></li>
			<?php endforeach; ?>
		</ul>
	</li>
							
</ul>




<?php

/* get parameter and invoke the modal */
if (isset($_GET['edit'])) {
	if ($_GET['edit'] == 1) {

		$imageID = $_GET['pic_id'];

		$edit_pics = $wpdb->get_results( 'SELECT * FROM wp_rbgallery_pics WHERE pic_id ='. $imageID, OBJECT );
		?>
		<script>

		jQuery(function ($) {
			$('#pictureEditmodal').modal('show');
		});

		</script>


<!-- Modal -->
<div class="rbmodal fade" id="pictureEditmodal" tabindex="-1" role="dialog" aria-labelledby="pictureEditmodalLabel" aria-hidden="true">
  <div class="rbmodal-dialog">
    <div class="rbmodal-content">
      <div class="rbmodal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="rbmodal-title" id="pictureEditmodalLabel">Edit Picture</h4>
      </div>
      <div class="rbmodal-body">

      <?php 

      foreach($edit_pics as $edit_pic) { 

      ?>

    <form name="wprbgall_pic_edit" method="post" action="">
 
			<input type="hidden" name="wprb_imgedit_alidimg" value="<?php echo $_GET['pic_id']; ?>"/>
		    <input type="hidden" name="update_imgs" value="edit_image"/>
		    <input type="hidden" name="wprb_imgedit_albumid" value="<?php echo $_GET['album_id']; ?>"/>
	<table class="form-table">
      <tr>
			<td><label for="wprb_imgedit_title">Picture Title</label></td>
		    <td><input type="text" name="wprb_imgedit_title" value="<?php echo $edit_pic->title; ?>"/></td>
		</tr>
      <tr>
			<td><label for="wprb_imgedit_description">Description</label></td>
		    <td><input type="text" name="wprb_imgedit_description" value="<?php echo $edit_pic->description; ?>"/></td>
		</tr>
      <tr>
			<td><label for="wprb_imgedit_sortingorder">Sorting Order</label></td>
		    <td><input type="text" name="wprb_imgedit_sortingorder" value="<?php echo $edit_pic->sorting_order; ?>"/></td>
		</tr>
      <tr>
			<td><label for="wprb_imgedit_albumcover">Make this an album cover?</label></td>
		    <td><select name="wprb_imgedit_albumcover">
		      <option value="0">No</option>
			  <option value="1">Yes</option>
			</select> 
		</td>
	  </tr>
		  <tr>
			  <td>
		      	<input type="submit" name="submit" value="Submit"/>
		      </td>
	      </tr>
      </table>
      </form>
      <?php } ?>

	</div> 	
      <div class="rbmodal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
		<?php
	}

}
?>








						</div> <!-- .inside -->
					
					</div> <!-- .postbox -->







		<?php } else {
			echo 'There are no images';
		}

     ?>


      </div>
    </div>
  </div>
</div>





					
				</div> <!-- .meta-box-sortables .ui-sortable -->
				
			</div> <!-- post-body-content -->
			
			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				
				<div class="meta-box-sortables">
					
					<div class="postbox">
					
						<h3><span>RB Gallery</span></h3>
						<div class="inside">
							By: <a href="http://www.websiteop.com" title="Web design and Internet Marketing Solutions">WebsiteOp</a><br />
							To use this gallery, paste the following shortcode into your wordpress page<br /><br />

							[wprb_gallery]

							<br /><br />
							And the albums will be available. <br /><br />
							To see a album on your page:<br />
							- Create an album<br />
							- Upload a picture<br />
							- Edit the picture and make it 'Album Cover'<br />
							- Now you will be able to see it on the page.
						</div> <!-- .inside -->
						
					</div> <!-- .postbox -->
					
				</div> <!-- .meta-box-sortables -->
				
			</div> <!-- #postbox-container-1 .postbox-container -->
			
		</div> <!-- #post-body .metabox-holder .columns-2 -->
		
		<br class="clear">
	</div> <!-- #poststuff -->
	
</div> <!-- .wrap -->
<script>
/* Album Delete Confirm */
function confirm_del_album() {
	if (confirm("All images will be deleted under this album, are you sure?")) {
        return true;
    } else {
        return false;
    }
}

function confirm_del_img() {
	if (confirm("Are you sure you want to delete this image?")) {
        return true;
    } else {
        return false;
    }
}

</script>