<?
/**
 * imginator - Image Placeholder Generator
 * 
 * @author     Steve Ottoz
 * @copyright  2014 dev.so
 * @license    MIT License http://opensource.org/licenses/MIT
 * @version    0.1.0
 * @link       https://github.com/soDEVch/imginator
 */
class imginator {
  private $default_bg = 'cccccc';
  private $default_txt = '999999';
  private $default_size = 320;
  private $default_imagetype = 'png';
  private $font = 'Oswald-Regular.ttf';
  
  /**
   * The imginator constructer takes the data and text, checks them for valid input
   * and replaces invalid options with default values.
   * After the data has been prepared, it gets passed to the create_image function.
   * 
   * @access public
   * @param mixed $data
   * @param mixed $text
   * @return void
   */
  public function __construct($data,$text) {
    $imagetype = strpos($data, '.jpg') !== false || strpos($data, '.jpeg') !== false ? 'jpeg' : (strpos($data, '.gif') !== false ? 'gif' : 'png');
  	$imagedata = explode('/',str_replace(array('.jpg', '.jpeg', '.gif', '.png'), '', $data));
  	$size = explode('x', $imagedata[0]);
  	$width = is_numeric($size[0]) ? $size[0] : $this->default_size;
  	$height = isset($size[1]) ? (is_numeric($size[1]) ? $size[1] : $width) : $width;
  	$bg_color = isset($imagedata[1]) ? (strlen($imagedata[1]) == 3 ? ($this->is_valid_color($imagedata[1][0].$imagedata[1][0].$imagedata[1][1].$imagedata[1][1].$imagedata[1][2].$imagedata[1][2]) ? $imagedata[1][0].$imagedata[1][0].$imagedata[1][1].$imagedata[1][1].$imagedata[1][2].$imagedata[1][2] : $this->default_bg) :  ($this->is_valid_color($imagedata[1]) ? $imagedata[1] : $this->default_bg)) : $this->default_bg;
  	$txt_color = isset($imagedata[2]) ? (strlen($imagedata[2]) == 3 ? ($this->is_valid_color($imagedata[2][0].$imagedata[2][0].$imagedata[2][1].$imagedata[2][1].$imagedata[2][2].$imagedata[2][2]) ? $imagedata[2][0].$imagedata[2][0].$imagedata[2][1].$imagedata[2][1].$imagedata[2][2].$imagedata[2][2] : $this->default_txt) :  ($this->is_valid_color($imagedata[2]) ? $imagedata[2] : $this->default_txt)) : $this->default_txt;  	
  	$this->create_image($width, $height, $bg_color, $txt_color, $imagetype, $text);
  }
  
  /**
   * The create_image function takes the prepared data and creates the image. 
   * If text is false the default text [width]x[height] is used. The width of
   * the text is checked to make sure it fits the image size.
   * After the image is created, it gets passed to the output_image function.
   * 
   * @access private
   * @param mixed $width
   * @param mixed $height
   * @param mixed $bg_color
   * @param mixed $txt_color
   * @param mixed $imagetype
   * @param mixed $text
   * @return void
   */
  private function create_image($width, $height, $bg_color, $txt_color, $imagetype, $text ) {
  		
    $text = $text ? $text : $width.'x'.$height;
    $image = ImageCreate($width, $height);  
  
  	$bg_color = ImageColorAllocate($image, base_convert(substr($bg_color, 0, 2), 16, 10), 
  										   base_convert(substr($bg_color, 2, 2), 16, 10), 
  										   base_convert(substr($bg_color, 4, 2), 16, 10));
  
  	$txt_color = ImageColorAllocate($image,base_convert(substr($txt_color, 0, 2), 16, 10), 
  										   base_convert(substr($txt_color, 2, 2), 16, 10), 
  										   base_convert(substr($txt_color, 4, 2), 16, 10));
  										   
    ImageFill($image, 0, 0, $bg_color);
  
  	$sizeratio = $width < 200 ? ($width < 100 ? 3 : 4 ) : 5;	
  	$fontsize = ($width>$height) ? floor($height / $sizeratio) : floor($width / $sizeratio);
  	
  	$fontfits = false;	
  	while(!$fontfits) {
    	$textsize = imagettfbbox($fontsize, 0, $this->font, $text);
  	
    	$textwidth = $textsize[2] - $textsize[0];
    	$textheight = $textsize[3] - $textsize[5];
    	
    	if ($textwidth > $width-($width/5) || $textheight > $height-($height/5)) {
      	$fontsize--;
    	}
    	else {
      	$fontfits = true;
    	}
  	}
  
  	$left = ($width - $textwidth)/2;
  	$top = ($height - -$textheight)/2;
      
  	imagettftext($image,$fontsize, 0, $left, $top, $txt_color, $this->font, $text);  
  	
  	$this->output_image($image, $imagetype);
  }
  
  /**
   * The output_image function takes the image and creates the output along 
   * with the cache and content-type headers.
   * 
   * @access private
   * @param mixed $image
   * @param mixed $imagetype
   * @return void
   */
  private function output_image($image, $imagetype) {
    $seconds_to_cache = 60 * 60 * 24 * 90;
    $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
  
    // put this above any php image generation code:
    session_start(); 
    header("Cache-Control: private, max-age=$seconds_to_cache, pre-check=$seconds_to_cache");
    header("Pragma: private");
    header("Expires: " . date(DATE_RFC822,$seconds_to_cache));
    
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
      // send the last mod time of the file back
      header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'], 
      true, 304);
      exit;
    }
    else {
      header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
    }
  
    header("Content-Type: image/$imagetype");
    
    $imagefunc = 'image'.$imagetype;
    $imagefunc($image);
    
    ImageDestroy($image); 
  }
  
  /**
   * The is_valid_color function checks if the provided colors are valid hex color values.
   * 
   * @access private
   * @param mixed $color
   * @return boolean
   */
  private function is_valid_color($color) {
  	return preg_match('/^[a-f0-9]{6}$/i', $color);
  }
}
?>