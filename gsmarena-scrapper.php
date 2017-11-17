<?php
/*
Plugin Name:  GsmArena To WooCommerce Crawler
Plugin URI:   
Description:  This plugin add facility to import products from GsmArena to woocommerce 
Version:      1.0
Author:       Junaid
Author URI:   https://github.com/junaidit
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/


add_action('admin_menu', 'gsmarena_control_menu');

function gsmarena_control_menu() {
  add_submenu_page('options-general.php', 'gsm-to-wp', 'GsmArena To Wp', 'manage_options', 'gsmarena-control-menu', 'gsmarena_control_options');
}


function gsmarena_control_options(){

	echo '<p><a href="'.admin_url().'/options-general.php?page=gsmarena-control-menu&fetchfromgsm">Fetch Products</a></p>';

	if(isset($_GET['fetchfromgsm'])){
	set_time_limit(0);

	  $current_user = wp_get_current_user();
	  
	include (dirname ( __FILE__ ) . "/includes/functions.php");
	
	$dataDirectory = dirname ( __FILE__ ) . "/data";


	foreach ( load_resources($dataDirectory) as $file ) {

		$fh = fopen($dataDirectory .'/'. $file, 'r') or die("can't open file");
		while ( ( $row = fgets($fh)) != false ) {

			$dataArr = json_decode($row,true);
			$specs = refine_str($dataArr['specs'],'<div class=specs-tabs>','</p>');
			$specs = str_replace('</div>', '', $specs);

			$post = array(
		    'post_author' => $current_user->ID,
		    'post_content' => $specs,
		    'post_status' => "publish",
		    'post_title' => $dataArr['title'],
		    'post_parent' => '',
		    'post_type' => "product",
			);


			//Create post
			$post_id = wp_insert_post( $post, $wp_error );

			Generate_Featured_Image( get_site_url() . '/product_images/'.$dataArr['img'],   $post_id );


		}


		fclose($fh);

	}

	echo PHP_EOL . "<b>...ALL DONE...</b>";
 }
}





function Generate_Featured_Image( $image_url, $post_id  ){
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    $filename = basename($image_url);
    if(wp_mkdir_p($upload_dir['path']))     $file = $upload_dir['path'] . '/' . $filename;
    else                                    $file = $upload_dir['basedir'] . '/' . $filename;
    file_put_contents($file, $image_data);

    $wp_filetype = wp_check_filetype($filename, null );
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
    $res2= set_post_thumbnail( $post_id, $attach_id );
}