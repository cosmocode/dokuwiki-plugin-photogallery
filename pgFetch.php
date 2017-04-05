<?php
/**
 * PhotoGallery media passthrough file
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Marco Nolletti
 */
if(!defined('DOKU_INC')) define('DOKU_INC', realpath(__DIR__.'/../../../').'/');
require_once('inc/pgdefines.php');
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if (!defined('DOKU_DISABLE_GZIP_OUTPUT')) define('DOKU_DISABLE_GZIP_OUTPUT', 1);
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/fetch.functions.php');
require_once('phpThumb/phpthumb.class.php');
session_write_close(); //close session

if (defined('SIMPLE_TEST')) {
    $INPUT = new Input();
}

// BEGIN main
$WIDTH  = $INPUT->int('w');
$HEIGHT = $INPUT->int('h');
$CACHE  = calc_cache($INPUT->str('cache'));
$OPT = $INPUT->str('opt'); // phpThumb options

$mimetypes = getMimeTypes();

if(!$INPUT->str('src')){

    //get input
    $MEDIA  = stripctl(getID('media', false)); // no cleaning except control chars - maybe external
    $REV    = & $INPUT->ref('rev');
    //sanitize revision
    $REV = preg_replace('/[^0-9]/', '', $REV);

    list($EXT, $MIME, $DL) = mimetype($MEDIA, false);
    if($EXT === false) {
        $EXT  = 'unknown';
        $MIME = 'application/octet-stream';
        $DL   = true;
    }

    // check for permissions, preconditions and cache external files
    list($STATUS, $STATUSMESSAGE) = checkFileStatus($MEDIA, $FILE, $REV, $WIDTH, $HEIGHT);

    // prepare data for plugin events
    $data = array(
        'media'         => $MEDIA,
        'file'          => $FILE,
        'orig'          => $FILE,
        'mime'          => $MIME,
        'download'      => $DL,
        'cache'         => $CACHE,
        'ext'           => $EXT,
        'width'         => $WIDTH,
        'height'        => $HEIGHT,
        'status'        => $STATUS,
        'statusmessage' => $STATUSMESSAGE,
        'ispublic'      => media_ispublic($MEDIA),
    );

    // handle the file status
    $evt = new Doku_Event('FETCH_MEDIA_STATUS', $data);
    if($evt->advise_before()) {
        // redirects
        if($data['status'] > 300 && $data['status'] <= 304) {
            if (defined('SIMPLE_TEST')) return; //TestResponse doesn't recognize redirects
            send_redirect($data['statusmessage']);
        }
        // send any non 200 status
        if($data['status'] != 200) {
            http_status($data['status'], $data['statusmessage']);
        }
        // die on errors
        if($data['status'] > 203) {
            print $data['statusmessage'];
            if (defined('SIMPLE_TEST')) return;
            exit;
        }
    }
    $evt->advise_after();
    unset($evt);

    //handle image resizing/cropping/phpThumbing
    if((substr($MIME, 0, 5) == 'image') && ($WIDTH || $HEIGHT)) {
				if ($OPT){
						$data['file'] = $FILE = media_photogallery_image($data['file'],$EXT,$WIDTH,$HEIGHT,$OPT);
				} else {
						if($HEIGHT && $WIDTH) {
								$data['file'] = $FILE = media_crop_image($data['file'], $EXT, $WIDTH, $HEIGHT);
						} else {
								$data['file'] = $FILE = media_resize_image($data['file'], $EXT, $WIDTH, $HEIGHT);
						}
				}
    }
		
		// End NOM ======================

    // finally send the file to the client
    $evt = new Doku_Event('MEDIA_SENDFILE', $data);
    if($evt->advise_before()) {
        sendFile($data['file'], $data['mime'], $data['download'], $data['cache'], $data['ispublic'], $data['orig']);
    }
    // Do something after the download finished.
    $evt->advise_after();  // will not be emitted on 304 or x-sendfile
} else{
		$FILE = PHOTOGALLERY_IMAGES_FILE.$INPUT->str('src');
		list($EXT, $MIME, $DL) = mimetype($FILE, false);
		list($STATUS, $STATUSMESSAGE) = checkLocalFileStatus($FILE, $WIDTH, $HEIGHT);
		// // send any non 200 status
		if($STATUS != 200) {
				http_status($STATUS, $STATUSMESSAGE);
		}
		if ($OPT)
				$FILE = media_photogallery_image($FILE,$EXT,$WIDTH,$HEIGHT,$OPT);
		else
				$FILE = media_crop_image($FILE, $EXT, $WIDTH, $HEIGHT);
		sendFile($FILE, $MIME, $DL, $CACHE, false, $FILE);
}

// END DO main

/**
 * Check local image file for preconditions and return correct status code
 *
 * READ: MIME, EXT, CACHE
 * WRITE: FILE, array( STATUS, STATUSMESSAGE )
 *
 * @author Marco Nolletti
 *
 * @param string $file   reference to the file variable
 * @param int    $width
 * @param int    $height
 * @return array as array(STATUS, STATUSMESSAGE)
 */
function checkLocalFileStatus($file, $width=0, $height=0) {
    global $MIME, $EXT, $CACHE, $INPUT;

    //media to local file
		if(empty($file)) {
				return array(400, 'Bad request');
		}
		// check token for resized images
		if (($width || $height) && media_get_token($file, $width, $height) !== $INPUT->str('tok')) {
				return array(412, 'Precondition Failed');
		}

    //check file existance
    if(!file_exists($file)) {
        return array(404, 'Not Found');
    }

    return array(200, null);
}

function media_photogallery_image($file, $ext, $w, $h, $opt){	
	
	//die();
	// create phpThumb object
	$phpThumb = new phpThumb();

	$capture_raw_data = false; // set to true to insert to database rather than render to screen or file (see below)
	// this is very important when using a single object to process multiple images
	$phpThumb->resetObject();

	// set data source -- do this first, any settings must be made AFTER this call
	$phpThumb->setSourceFilename($file);  // for static demo only
	//$phpThumb->setSourceFilename($_FILES['userfile']['tmp_name']);
	// or $phpThumb->setSourceData($binary_image_data);
	// or $phpThumb->setSourceImageResource($gd_image_resource);

	// PLEASE NOTE:
	// You must set any relevant config settings here. The phpThumb
	// object mode does NOT pull any settings from phpThumb.config.php
	//$phpThumb->setParameter('config_document_root', '/home/groups/p/ph/phpthumb/htdocs/');
	//$phpThumb->setParameter('config_cache_directory', '/tmp/persistent/phpthumb/cache/');

	// set parameters (see "URL Parameters" in phpthumb.readme.txt)
	$phpThumb->setParameter('w', $w);
	$phpThumb->setParameter('h', $h);
	//$phpThumb->setParameter('fltr', 'gam|1.2');
	//$phpThumb->setParameter('fltr', 'wmi|../watermark.jpg|C|75|20|20');
		//$fltr = array();
//	echo $opt; die();
		foreach (explode('!',$opt) as $par) {
				preg_match('/^(.+)=(.+)$/', $par, $options);
				//echo $options[1]. "->" . $options[2];
				// if (preg_match('/^([a-z]+)\[(.+)\]$/', $options[1], $filters)){
					// //$fltr[$filters[1]] = $filters[2];
					// $phpThumb->setParameter($filters[1],$options[2]);
					// //echo $filters[1].'='.$options[2];
					// }
				// else{
						$phpThumb->setParameter($options[1], $options[2]);
						//echo $options[1].'='. $options[2];
				// }
		}
	//	if (count($fltr) > 0)
		//		$phpThumb->setParameter('fltr[]', $fltr);
	//die();

	// set options (see phpThumb.config.php)
	// here you must preface each option with "config_"
	$phpThumb->setParameter('config_output_format', 'jpeg');
	$phpThumb->setParameter('config_imagemagick_path', '/usr/local/bin/convert');
	//$phpThumb->setParameter('config_allow_src_above_docroot', true); // needed if you're working outside DOCUMENT_ROOT, in a temp dir for example
	$phpThumb->setParameter('config_temp_directory', DOKU_INC.'data/cache/');
	$phpThumb->setParameter('config_prefer_imagemagick', true);
	$phpThumb->setParameter('config_disable_debug',true);
	$phpThumb->setParameter('config_cache_directory',null);

	// generate & output thumbnail
	//$local = getCacheName($file,'.media.'.$cw.'x'.$ch.'.crop.'.$ext);
	$output_filename = getCacheName($file,'.media.'.$w.'x'.$h.'.photogallery.'.$phpThumb->config_output_format);
	//$output_filename = './thumbnails/'.getCacheName($from,'.photogallery.'.$from_w.'x'.$from_h.$phpThumb->config_output_format);
	//echo $output_filename; return;
	
	if ($phpThumb->GenerateThumbnail()) { // this line is VERY important, do not remove it!
		$output_size_x = imagesx($phpThumb->gdimg_output);
		$output_size_y = imagesy($phpThumb->gdimg_output);
		if ($output_filename || $capture_raw_data) {
			if ($capture_raw_data && $phpThumb->RenderOutput()) {
				// RenderOutput renders the thumbnail data to $phpThumb->outputImageData, not to a file or the browser
				//$mysqli->query("INSERT INTO `table` (`thumbnail`) VALUES ('".mysqli_real_escape_string($phpThumb->outputImageData)."') WHERE (`id` = '".mysqli_real_escape_string($id)."')");
			} elseif ($phpThumb->RenderToFile($output_filename)) {
				// do something on success
				return $output_filename;//NOM
				//echo 'Successfully rendered:<br><img src="'.$output_filename.'">';
			} else {
				// do something with debug/error messages
				echo 'Failed (size='.$thumbnail_width.'):<pre>'.implode("\n\n", $phpThumb->debugmessages).'</pre>';
			}
			$phpThumb->purgeTempFiles();
		} else {
			$phpThumb->OutputThumbnail();
		}
	} else {
		// do something with debug/error messages
		echo 'Failed (size='.$thumbnail_width.').<br>';
		echo '<div style="background-color:#FFEEDD; font-weight: bold; padding: 10px;">'.$phpThumb->fatalerror.'</div>';
		echo '<form><textarea rows="10" cols="60" wrap="off">'.htmlentities(implode("\n* ", $phpThumb->debugmessages)).'</textarea></form><hr>';

	}

}