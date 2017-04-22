<?php
/**
 * DokuWiki Plugin photogallery (Common defines)
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Marco Nolletti
 */

if(!defined('PHOTOGALLERY_REL')) define('PHOTOGALLERY_REL',DOKU_REL.'lib/plugins/photogallery/');
if(!defined('PHOTOGALLERY_PGFETCH')) define('PHOTOGALLERY_PGFETCH','pgFetch.php');
if(!defined('PHOTOGALLERY_PGFETCH_REL')) define('PHOTOGALLERY_PGFETCH_REL',PHOTOGALLERY_REL.PHOTOGALLERY_PGFETCH);
if(!defined('PHOTOGALLERY_PGFETCH_FILE')) define('PHOTOGALLERY_PGFETCH_FILE',realpath(__DIR__.'/../'.PHOTOGALLERY_PGFETCH));
if(!defined('PHOTOGALLERY_IMAGES_REL')) define('PHOTOGALLERY_IMAGES_REL',PHOTOGALLERY_REL.'images/');
if(!defined('PHOTOGALLERY_IMAGES_FILE')) define('PHOTOGALLERY_IMAGES_FILE',realpath(__DIR__.'/../images').'/');
if(!defined('PHOTOGALLERY_PGFETCH_EXE_PERM')) define('PHOTOGALLERY_PGFETCH_EXE_PERM',0110); // Owner and group execute permission in octal notation