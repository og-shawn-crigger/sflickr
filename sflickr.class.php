<?php
/* phpFlickr Extension Class, used for VisionManagementSystems CMS.
 *
 *  Extends PHPFlickR Class with some functions used to easily make gallery's.
 *
 *  @author     Shawn Crigger <support@s-vizion.com
 *  @version    1.0.5 (last revision: May 21, 2011)
 *  @copyright  (c) 201 - 2012 S-Vizion Software
 *  @package    sFlickR
 *  @license   MIT License,  You can modify but can not redistribute modifyed file without giving credit to Authors
 * 
 */ 
require('phpFlickr/phpFlickr.php');
error_reporting ( 0 );

class sFlickr extends phpFlickr
{

/**
 * @var string FlickR Username
 */
  var $fusrnam;
/**
 * @var string FlickR User ID Number
 */
  var $fuserid;
/**
 * @var string Your FlickR API Key 
 */  
  var $fapi;
/**
 * @var string Your FlickR Secret Key
 */
  var $fsecret;
/**
 * @var string URL to Website
 */
  var $siteurl;
  
/**
 * Creates the PHPFlickR Object with your Data
 *
 * @param  array  $f_config   FlickR Configuration Information
 */
  public function sFlickr( $f_config = array() )
  {   
    $this->fusrnam = $f_config['fusrnam'];
    $this->fuserid = $f_config['fuserid'];
    $this->fapi    = $f_config['fapi'];
    $this->fsecret = $f_config['fsecret'];
    $this->siteurl = $f_config['siteurl'];
    
    // use the api key generated from your flickr account
    parent::__construct( $this->fapi, $this->fsecret);
    $this->enableCache("fs", "cache");  
  }
  
  /**
   * Builds the FlickR Photo ID using Data pulled from PHPFlickR
   * 
   * @param  array  $pdata   Photo Data provided by the function that calls this one.
   * @param  string $size    Defaults to Thumbnail,  Can be used for medium, square, etc, check Flickr's API for all options available.
   * 
   * @return string          URL to the FlickR photo build using the photo data
   */
  public function build_photosetPrimary ( $pdata = array() , $size = '_t' )
  {
    $server = $pdata['server'];
    $secret = $pdata['secret'];
    $photid = $pdata['primary'];
    $farmid = $pdata['farm'];
  
    $photo  = 'http://farm' . $farmid . '.static.flickr.com/' . $server . '/' . $photid . '_' . $secret . $size . '.jpg';
  
    return $photo;
  }

  /**
   * Builds Collection of Photosets by ID and outputs them as a Gallery
   * 
   * @param string $id ID of FlickR Collection to Pull
   * 
   * @return string          Html Output for Gallery of Photosets
   */
  public function build_Collections ( $id = 0 )
  {
      
    $gallerys = $this->collections_getTree ( $id, $this->fuserid );
    
    foreach ($gallerys['collections']['collection'] as $gallery )
    {
      $title    = $gallery['title'];
      
      $html     = build_breadcrumbs( $title );
      
      $html    .= '<ul id="gallery"> ' . "\n";
      
      foreach ( $gallery['set'] as $photos )
      {
        
        $pid   = $photos['id'];
        
        $photo = $this->photosets_getInfo( $pid ); 
        
        $thumb  = $this->build_photosetPrimary ( $photo, '_m' );
        $photid = $photo['id'];
        $idphot = $photo['photos'];
        $title  = wordwrap( $photo['title'] , 25, "<br />\n");
        $cut    = strpos ( $title, '<br' );
        $title  = ( $cut > 0 ) ? substr ( $title, 0, $cut ) : $title; 
    

        $link   = $this->siteurl . $_SERVER['REQUEST_URI'] . '?pid=' . $photid . '&amp;c=' . $idphot;
        
        $html  .= '<li>' . "\n" .
                  '<a href="' . $link . '" ' . $class . ' >' . "\n" .
            
                  '<h2>' . $title . '</h2>' . "\n" .              
                  '<img src="' . $thumb . '" width="150" height="150" alt="' . $photo['title'] . '" title="' . $photo['title'] . '" />' ."\n" .                
                  '</a></li>' . "\n";

      }
    }
  
    $html .= '</ul>' . "\n";

    return $html;       
  }
  

  /**
   * Returns Photos from a FlickR Photoset
   * 
   * @access public
   * 
   * @param string  $id     ID of the Photoset to Pull Photo information from.
   * @param integer $limit  The Number of Photos to Pull
   * @param boolean $wrap   Defaults to False, Set to True to Wrap Output in a Unordered List
   * 
   * @return string         HTML Output of the Photos
   */
  public function getFlickrPhotosets($id, $limit = 0 , $wrap = false )
  {
    
      
    $class  = ' class="lightbox pic" target="_blank" style="text-decoration:none;" ';
    $info   = $this->photosets_getInfo( $id );    
    $limit  = ( $limit > 0 ) ? $limit : $info['photos'];
     
    $return = build_breadcrumbs( $info['title']. ' Gallery' );
    
    
    $info   = '<h1 class="gallery">' . $return . '</h1>' . "\n";
    
    $info   = $return;
    
    $photos = $this->photosets_getPhotos($id, NULL, NULL, $limit);

    if ( true == false )
    {
      shuffle ( $photos['photoset'] );
      $photos['photoset'] = array_slice( $photos['photoset'], 0, 7 );
    }    
    
    if ( count($photos['photoset']['photo']) == 0 )
      return '';
      
    $return = ( $wrap == true ) ? $info . '<ul id="gallery" class="clearfix" > ' : '';
          
    foreach ($photos['photoset']['photo'] as $photo)
    {
      if ( $wrap == true )
        $return .= '<li class="thumbs">' . "\n";
      
      $size = 'medium';
      $size = 'large';
      
      $return .=  '<a href="' . $this->buildPhotoURL($photo, $size ) . '" ' . $class . ' title="' . $photo['title'] . '">' . "\n" .
                  '<img src="' . $this->buildPhotoURL($photo, 'thumbnail') . '" alt="' . $photo['title'] . '" title="' . $photo['title'] . '" width="100" />' ."\n" .
                  '</a>' . "\n";

      if ( $wrap == true )
        $return .= '</li>' . "\n";
    
    }
  
    if ( $wrap == true )
      $return .= '</ul>' . "\n";
      
    return $return;
  } 
  
  /**
   * Fetchs List of all Photosets owned by UserID and Returns HTML Output in a List Format to be displayed as a Gallery
   *
   * @access public
   * @return string     HTML Output of Gallery
   */
  public function return_photosets_getList( )
  {
  
    $class   = ' class="sbox pic" target="_blank" style="text-decoration:none;" ';
    $html    = '';
    
    $photosets = $this->photosets_getList( $this->fuserid );
    
    $html = '<ul id="gallery" class="clearfix" > ' . "\n";
    foreach ( $photosets['photoset'] as $photo )
    {
      
      $thumb  = $this->build_photosetPrimary ( $photo, '_m' );
      $photid = $photo['id'];
      $idphot = $photo['photos'];
      $title  = wordwrap( $photo['title'] , 25, "<br />\n");
      $cut    = strpos ( $title, '<br' );
      $title  = ( $cut > 0 ) ? substr ( $title, 0, $cut ) : $title; 
  
      $link   = $this->siteurl . $_SERVER['REQUEST_URI'] . '?pid=' . $photid . '&amp;c=' . $idphot;
      
      $html  .= '<li>' . "\n" .
                '<a href="' . $link . '" ' . $class . ' >' . "\n" .
          
                '<h2>' . $title . '</h2>' . "\n" .              
                '<img src="' . $thumb . '" width="150" height="150" alt="' . $photo['title'] . '" title="' . $photo['title'] . '" />' ."\n" .                
                '</a></li>' . "\n";
    }
  
    $html .= '</ul><br style="height:20px;" />' . "\n";
    
    return $html;
  
  }

  /**
   * Builds Array of Photosets inside of Collection ID
   *
   * @access private
   * 
   * @param string $id ID of the Collection to create Photoset List
   * 
   * @return array     Array of Photosets inside of Collection
   */
  private function buildPhotosetArray ( $id )
  {

    $photos = array();
    $count  = 0;
    
    $plist  = $this->collections_getTree ( $id, $this->fuserid );
    
    foreach ( $plist['collections']['collection'] as $sets )
      foreach ( $sets['set'] as $set )
        $photos[] = $set['id'];      
    
    $plist = array();
    
    foreach ( $photos as $pid )
    {
      
      $info   = $this->photosets_getInfo( $pid );    
      $count  = $info['photos'];

      $photo  = $this->photosets_getPhotos( $pid, NULL, NULL, $count);
      $photo  = $photo['photoset']['photo'];
      
      $plist  = array_merge_recursive ( $plist, $photo );

    }    
    
    return $plist;
  }

  /**
   * Developer echo function
   */
  function PF ( $var , $pretext = NULL , $color = '#A70000' )
  {
    echo '<pre style="color:'.$color.';">';
    echo '<strong>'.$pretext.':</strong></br>';

    if ( gettype($var) == 'array' OR gettype($var) == 'object')
      print_r($var);
    else
      echo ($var);
      
      echo '</pre>';
    
      return;
  }
    
  /**
   * Returns Random Photos from a FlickR Photoset
   * 
   * @access public
   * 
   * @param string  $id     ID of the Photoset to Pull Photo information from.
   * @param integer $limit  The Number of Photos to Pull
   * 
   * @return string         HTML Output of the Photos
   */
  public function getRandomFlickrPhotos( $id, $limit = 7 )
  {
          
    $class  = ' class="lightbox pic" target="_blank" style="text-decoration:none;" ';

    $photos = $this->buildPhotosetArray ( $id );    
    $count  = count ( $photos ) - 1;

    if ( $count < 0 )
      return '';
    
    $nonrepeatarray = array();
    
    for ($i = 0; $i < $limit; $i++)
    {

      $rand = rand(0, $count);
      while ( in_array ( $rand, $nonrepeatarray ) )
        $rand = rand(0, $count);
      array_push($nonrepeatarray, $rand);

      $photo = $photos[$rand];
      
      $size = 'medium';
      $size = 'large';
      
      $return .=  '<a href="' . $this->buildPhotoURL($photo, $size ) . '" ' . $class . ' title="' . $photo['title'] . '">' . "\n" .
                  '<img src="' . $this->buildPhotoURL($photo, 'thumbnail') . '" alt="' . $photo['title'] . '" title="' . $photo['title'] . '" width="100" />' ."\n" .
                  '</a>' . "\n";
    
    }
      
    return $return;
  } 

  /**
   * Returns Random Photos from a FlickR Photoset
   * 
   * @access public
   * 
   * @param string  $id     ID of the Photoset to Pull Photo information from.
   * @param integer $limit  The Number of Photos to Pull
   * 
   * @return string         HTML Output of the Photos
   */
  public function getRandomFlickrPhotostream( $id, $limit = 7 )
  {
          
    $class  = ' class="lightbox pic" target="_blank" style="text-decoration:none;" ';

    $info   = $this->photosets_getInfo( $id );    
    $count  = $info['photos'];
         
    $photos = $this->photosets_getPhotos($id, NULL, NULL, $count);
    
    $count  = $info['photos'] - 1;
    if ( count($photos['photoset']['photo']) == 0 )
      return '';
    
    $nonrepeatarray = array();
    
    for ($i = 0; $i < $limit; $i++)
    {

      $rand = rand(0, $count);
      while ( in_array ( $rand, $nonrepeatarray ) )
        $rand = rand(0, $count);
      array_push($nonrepeatarray, $rand);

      $photo = $photos['photoset']['photo'][$rand];
      
      $size = 'medium';
      $size = 'large';
      
      $return .=  '<a href="' . $this->buildPhotoURL($photo, $size ) . '" ' . $class . ' title="' . $photo['title'] . '">' . "\n" .
                  '<img src="' . $this->buildPhotoURL($photo, 'thumbnail') . '" alt="' . $photo['title'] . '" title="' . $photo['title'] . '" width="100" />' ."\n" .
                  '</a>' . "\n";
    
    }
      
    return $return;
  } 

}
?>