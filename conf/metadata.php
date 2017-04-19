<?php
/**
 * Options for the photogallery plugin
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Marco Nolletti
 */

$meta['use_phpThumb']     = array('onoff');
$meta['autoplay']         = array('onoff');
$meta['poster_width']     = array('numeric');
$meta['poster_height']    = array('numeric');
$meta['thumbnail_width']  = array('numeric');
$meta['thumbnail_height'] = array('numeric');
$meta['image_width']      = array('numeric');
$meta['image_height']     = array('numeric');
$meta['viewport_rotate']  = array('onoff');
$meta['panorama_width']   = array('numeric');
$meta['panorama_height']  = array('numeric');
$meta['posteralign']      = array('string');
$meta['panorama_ratio']   = array('string','_pattern' => '/([0-9]+):([0-9]+)/');

$meta['sort']    = array('multichoice', '_choices' => array('file','mod','date','title','random'));
$meta['options'] = array('multicheckbox', '_choices' => array('crop','reverse','recursive','showtitle','showinfo','showfname'));

