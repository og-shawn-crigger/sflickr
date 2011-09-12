<?php
/**
 * Simple FlickR Photo Gallery Module
 *
 * This script pulls data from FlickR and display's the results as a photogallery
 *
 *
 *  @author     Shawn Crigger <support@s-vizion.com
 *  @version    1.0.5 (last revision: May 21, 2011)
 *  @copyright  (c) 201 - 2012 S-Vizion Software
 *  @package    sFlickR
 *  @license   MIT License,  You can modify but can not redistribute modifyed file without giving credit to Authors
 *  
 **/  
require('sflickr.class.php');

$f_config['fusrnam'] = 'User Name';
$f_config['fuserid'] = 'YOURID';
$f_config['fapi']    = 'API';
$f_config['fsecret'] = 'SECRET';
$f_config['siteurl'] = 'http://localhost';

$f = new sFlickr ( $f_config );

/**
 * Check Super Globals GET and POST and Sanitize and set the $id variable to be used 
 */
$pid = (isset($_GET['pid']) AND intval($_GET['pid']) > 0 ) ? $_GET['pid'] : ((isset($_POST['pid']) AND intval($_POST['pid']) >0 ) ? $_POST['pid'] : $_POST['pid']);

/**
 * Check Super Globals GET and POST and Sanitize and set the action variable to be used 
 */
$c   = (isset($_GET['c']) AND strlen($_GET['c']) > 0 ) ? $_GET['c'] : ((isset($_POST['c']) AND strlen($_POST['c']) >0 ) ? $_POST['c'] : $_POST['c']);


if ( $pid > 0 && $c > 0 )
{
  echo $f->getFlickrPhotosets ( $pid, $c, true);
} else {
  echo $f->return_photosets_getList( ) ;
}

?>