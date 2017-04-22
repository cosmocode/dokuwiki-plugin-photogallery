<?php
/**
 * PhotoGallery media passthrough file
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Marco Nolletti
 */
define('DOKU_INC', realpath(__DIR__.'/../../../').'/');

if (!defined('DOKU_DISABLE_GZIP_OUTPUT')) define('DOKU_DISABLE_GZIP_OUTPUT', 1);
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/fetch.functions.php');
require_once('inc/pgdefines.php');
require_once('phpThumb/phpthumb.class.php');
session_write_close(); //close session

if (defined('SIMPLE_TEST')) {
    $INPUT = new Input();
}

// BEGIN main
$WIDTH  = $INPUT->int('w');
$HEIGHT = $INPUT->int('h');
$CACHE  = calc_cache($INPUT->str('cache'));
$opt = $INPUT->str('opt'); // phpThumb options

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
				if ($opt){
						$data['file'] = $FILE = media_photogallery_image($data['file'],$EXT,$WIDTH,$HEIGHT,$opt);
				} else {
						if($HEIGHT && $WIDTH) {
								$data['file'] = $FILE = media_crop_image($data['file'], $EXT, $WIDTH, $HEIGHT);
						} else {
								$data['file'] = $FILE = media_resize_image($data['file'], $EXT, $WIDTH, $HEIGHT);
						}
				}
    }
		
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
		if ($opt)
				$FILE = media_photogallery_image($FILE,$EXT,$WIDTH,$HEIGHT,$opt);
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

	// this is very important when using a single object to process multiple images
	$phpThumb->resetObject();

	// set data source -- do this first, any settings must be made AFTER this call
	$phpThumb->setSourceFilename($file);
	// $phpThumb->setParameter('config_document_root', '/home/groups/p/ph/phpthumb/htdocs/');
	// $phpThumb->setParameter('config_allow_src_above_docroot', true); // needed if you're working outside DOCUMENT_ROOT, in a temp dir for example
	$phpThumb->setParameter('config_output_format', 'jpg');
	$phpThumb->setParameter('config_imagemagick_path', '/usr/local/bin/convert');
	$phpThumb->setParameter('config_temp_directory', DOKU_INC.'data/cache/');
	$phpThumb->setParameter('config_prefer_imagemagick', true);
	$phpThumb->setParameter('config_disable_debug',true);
	$phpThumb->setParameter('config_cache_directory',null);
	$phpThumb->setParameter('w', $w);
	$phpThumb->setParameter('h', $h);
	foreach (explode('!',$opt) as $par) {
			preg_match('/^(.+)=(.+)$/', $par, $options);
			$phpThumb->setParameter($options[1], $options[2]);
	}

	// generate & output thumbnail
	$output_filename = getCacheName($file,'.media.'.$w.'x'.$h.'.photogallery.'.$phpThumb->config_output_format);
	
	if ($phpThumb->GenerateThumbnail()) { // this line is VERY important, do not remove it!
		if ($output_filename) {
			if ($phpThumb->RenderToFile($output_filename)) {
				// do something on success
				return $output_filename;
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
		// do something with error messages
		echo 'Failed (size='.$thumbnail_width.').<br>';
		echo '<div style="background-color:#FFEEDD; font-weight: bold; padding: 10px;">'.$phpThumb->fatalerror.'</div>';
		echo '<form><textarea rows="10" cols="60" wrap="off">'.htmlentities(implode("\n* ", $phpThumb->debugmessages)).'</textarea></form><hr>';
	}
}