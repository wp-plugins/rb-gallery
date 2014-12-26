<?php
/*
 *	Plugin Name: RB Gallery Plugin
 *	Plugin URI: http://www.ronniebailey.net
 *	Description: A simple one-page image gallery Wordpress plugin.
 *	Version: 1.3
 *	Author: Ron Bailey
 *	Author URI: http://www.ronniebailey.net
 *	License: GPLv3
 *
*/


function wprb_gallery_menu() {

	/*
	 * 	Use the add_options_page function
	 * 	add_options_page( $page_title, $menu_title, $capability, $menu-slug, $function ) 
	 *
	*/

	add_options_page(
		'Official_RB_Gallery_Plugin',
		'RB Gallery',
		'manage_options',
		'wprb-gallery',
		'wprb_gallery_options_page'
	);

}


add_action( 'admin_menu', 'wprb_gallery_menu' );


function wprb_gallery_scripts() {
	wp_enqueue_style( 'style-wprb-gallery', plugin_dir_url(__FILE__) . 'inc/rbgall-jscss/jquery.rbgallery.css', array(), null, false );
	wp_enqueue_style( 'style-wprb-gallery-thumbs', plugin_dir_url(__FILE__) . 'inc/rbgall-jscss/helpers/jquery.rbgallery-thumbs.css', array(), null, false );
	wp_enqueue_script( 'script-wprb-gallery', plugin_dir_url(__FILE__) . 'inc/rbgall-jscss/jquery.rbgallery.js', array('jquery'), null, false  );
	wp_enqueue_script( 'script-wprb-gallery-thumbs', plugin_dir_url(__FILE__) . 'inc/rbgall-jscss/helpers/jquery.rbgallery-thumbs.js', array('jquery'), null, false  );
}

add_action( 'init', 'wprb_gallery_scripts' );

function wprb_gallery_options_page() {

		if( !current_user_can( 'manage_options' ) ) {

			wp_die( 'You do not have sufficient permissions to access this page.' );

		}

		global $options;
		global $wpdb;

		require('inc/wprb-gallery-helpers.php');

		// Add Album
		if( isset( $_POST['wprbgall_form_submitted'] ) ) {
			$hidden_field = esc_html($_POST['wprbgall_form_submitted'] );

			if ($hidden_field == 'Y' && !empty($_POST['wprbgall_albumname']) ) {

				$wprbgall_albumname = esc_html( $_POST['wprbgall_albumname'] );
				$wprbgall_albumdesc = esc_html( $_POST['wprbgall_albumdesc'] );
				$wprbgall_albumord = esc_html( $_POST['wprbgall_albumorder'] );
				$rb_gal_table = rb_gallery_albums();

				//input name into database
				$wpdb->insert($rb_gal_table, array("album_name" => $wprbgall_albumname, "album_date" => date('Y-m-d'), "description" => $wprbgall_albumdesc, "album_order" => $wprbgall_albumord ));

				$redirect_url = admin_url('admin.php?page=wprb-gallery', __FILE__);
			    ?>
					<script>  window.location.replace("<?php echo $redirect_url; ?>"); </script>
			    <?php 


			} else { echo "The Album Name Field is empty, please fill it in."; }
		}

		// Update Album
		if( isset( $_POST['wprbgall_form_updated'] ) ) {
			$hidden_field = esc_html($_POST['wprbgall_form_updated'] );
			$album_id = esc_html($_POST['wprbgall_album_id']);

			if ($hidden_field == 'Y' && !empty($_POST['wprbgall_album_id']) ) {

				$wprbgall_albumname = esc_html( $_POST['wprbgall_albumname'] );
				$wprbgall_albumdesc = esc_html( $_POST['wprbgall_albumdesc'] );
				$rb_gal_table = rb_gallery_albums();

				//update database
				$wpdb->update( 
					$rb_gal_table, 
					array(
						"album_name" => $wprbgall_albumname, 
						"album_date" => date('Y-m-d'), 
						"description" => $wprbgall_albumdesc
						), 
					array( 'album_id' => $album_id ), 
					array( 
						'%s',
						'%s',
						'%s'	
					), 
					array( '%d' )  
					);


			}
		}


		// Sanitize this GET
		if (isset($_GET['album_id'])) {
			$album_id = $_GET['album_id'];
		} else { $album_id = ''; }

		/* Get Images from the DB that are associated with the album, return an array */
		$get_images_for_album = get_images_for_album($album_id);

		/* Add A Pic */

		/* Delete A Pic */

		require('inc/rboptions-page-wrapper.php');


	}


	function get_images_for_album($album_id) {
		
		global $wpdb;
		if (!empty($album_id)) {
		//sanitize this GET var 
		$results = $wpdb->get_results( 'SELECT * FROM wp_rbgallery_pics WHERE album_id= '.$album_id.'  ', OBJECT );
		return $results;
		}
	}


	function wprb_gallery_shortcode( $atts,$content = null ) {

		global $post;

		/*extract( shortcode_atts( array(
			'album_display' => 'list',
			'album_display' => 'column'
			), $atts ) );*/

		require( 'inc/front-end-helper.php' );
		$get_albums = get_albums();

		ob_start();
		require( 'inc/front-end.php' );
		$content = ob_get_clean();
		return $content;


	}

	add_shortcode( 'wprb_gallery', 'wprb_gallery_shortcode' );

/* INSTALL SCRIPT */
function plugin_install_script_for_wprb_gallery()
{
	global $wpdb;

        $sql = "CREATE TABLE wp_rbgallery_albums (
		  album_id int(10) unsigned NOT NULL AUTO_INCREMENT,
		  album_name varchar(100) DEFAULT NULL,
		  album_date date DEFAULT NULL,
		  description text,
		  album_order int(10) DEFAULT NULL,
		  PRIMARY KEY (album_id)
		  );";
		require_once( plugin_dir_path( dirname(dirname(dirname(__FILE__)))) . '/wp-admin/includes/upgrade.php' );
		dbDelta( $sql );


        $sql = "CREATE TABLE wp_rbgallery_pics (
		  pic_id int(10) unsigned NOT NULL AUTO_INCREMENT, 
		  album_id int(10) unsigned NOT NULL, 
		  title text, 
		  description text, 
		  thumbnail_url text NOT NULL, 
		  sorting_order int(20) DEFAULT NULL, 
		  date date DEFAULT NULL, 
		  url varchar(250) DEFAULT NULL, 
		  video int(10) NOT NULL, 
		  tags text, 
		  pic_name text NOT NULL, 
		  PRIMARY KEY (pic_id) 
		  );";
		require_once( plugin_dir_path( dirname(dirname(dirname(__FILE__)))) . '/wp-admin/includes/upgrade.php' );
		dbDelta( $sql );


    // make gallery directories
    $rb_gallery = plugin_dir_path( dirname(dirname(__FILE__))) . 'rb-gallery/';
    $album_thumbs = plugin_dir_path( dirname(dirname(__FILE__))) . 'rb-gallery/album-thumbs';
    $dontremove = plugin_dir_path( dirname(dirname(__FILE__))) . 'rb-gallery/dontremove';
    $gallery_uploads = plugin_dir_path( dirname(dirname(__FILE__))) . 'rb-gallery/gallery-uploads';

    if (!is_dir($rb_gallery)) {
    	mkdir($rb_gallery);
    }

    if (!is_dir($album_thumbs)) {
    	mkdir($album_thumbs);
    }

    if (!is_dir($dontremove)) {
    	mkdir($dontremove);
    }

    if (!is_dir($gallery_uploads)) {
    	mkdir($gallery_uploads);
    }

    // move file to new location
    $old_loc = plugin_dir_path(__FILE__) .'images/dontremove.jpg';
    $new_loc = plugin_dir_path( dirname(dirname(__FILE__))). 'rb-gallery/dontremove/dontremove.jpg';
    rename($old_loc , $new_loc);
}

/* UNINSTALL SCRIPT */
function plugin_uninstall_script_for_wprb_gallery()
{
	global $wpdb;

	$wpdb->query("DROP TABLE wp_rbgallery_albums ");
	$wpdb->query("DROP TABLE wp_rbgallery_pics ");

	// delete gallery directories
    $rb_gallery = plugin_dir_path( dirname(dirname(__FILE__))) . 'rb-gallery/';

    rrmdir($rb_gallery);
   

    //Delete any options thats stored also?
	//delete_option('wp_yourplugin_version');

}

/* UNINSTALL -- Empties Directory Tree and Deletes the Directory where images are stored */
function rrmdir($dir) {
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
       }
     }
     reset($objects);
     rmdir($dir);
   }
}

register_activation_hook(__FILE__, 'plugin_install_script_for_wprb_gallery');
register_uninstall_hook(__FILE__, 'plugin_uninstall_script_for_wprb_gallery')

?>