<?php
/**
 * Options for the gallery plugin
 *
 * @author Marco Nolletti <mnolletti@gmail.com>
 */

$meta['poster_width']     = array('numeric');
$meta['poster_height']    = array('numeric');
$meta['thumbnail_width']  = array('numeric');
$meta['thumbnail_height'] = array('numeric');
$meta['image_width']      = array('numeric');
$meta['image_height']     = array('numeric');
$meta['posteralign']      = array('string');

$meta['sort']    = array('multichoice', '_choices' => array('file','mod','date','title','random'));
$meta['options'] = array('multicheckbox', '_choices' => array('cache','crop','reverse','recursive','showtitle','showinfo','showfname'));

