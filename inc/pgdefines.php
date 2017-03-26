<?php
/**
 * DokuWiki Plugin photogallery (Common defines)
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Marco Nolletti
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(__DIR__.'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('PHOTOGALLERY_REL')) define('PHOTOGALLERY_REL',DOKU_REL.'lib/plugins/photogallery/');
if(!defined('PHOTOGALLERY_PGIMG')) define('PHOTOGALLERY_PGIMG','phpThumb/pgImg.php');
if(!defined('PHOTOGALLERY_PGIMG_REL')) define('PHOTOGALLERY_PGIMG_REL',PHOTOGALLERY_REL.PHOTOGALLERY_PGIMG);
if(!defined('PHOTOGALLERY_PGIMG_FILE')) define('PHOTOGALLERY_PGIMG_FILE',realpath(__DIR__.'/../'.PHOTOGALLERY_PGIMG));
if(!defined('PHOTOGALLERY_MEDIA_REL')) define('PHOTOGALLERY_MEDIA_REL',DOKU_REL.$GLOBALS['conf']['savedir'].'/media/');
if(!defined('PHOTOGALLERY_MEDIA_FILE')) define('PHOTOGALLERY_MEDIA_FILE',realpath(__DIR__.'/../'.PHOTOGALLERY_MEDIA_REL));
if(!defined('PHOTOGALLERY_IMAGES')) define('PHOTOGALLERY_IMAGES',PHOTOGALLERY_REL.'images/');
if(!defined('PHOTOGALLERY_PGIMG_EXE_PERM')) define('PHOTOGALLERY_PGIMG_EXE_PERM',0110); // Owner and group execute permission in octal notation