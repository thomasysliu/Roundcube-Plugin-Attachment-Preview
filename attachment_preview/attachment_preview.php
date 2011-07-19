<?php
/**
 * Attachment Preview
 *
 * A plugin to preview upload attachments
 *
 * @version 1.0
 * @author Thomas Yu - Sian , Liu
 * @url https://github.com/thomasysliu/Roundcube-Plugin-Attachment-Preview
 * 
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
class attachment_preview extends rcube_plugin
{
	public $task = 'mail';

	function init()
	{
		$rcmail = rcmail::get_instance();
		if($rcmail->action == 'compose') {
			$this->include_script('attachment_preview.js');
			$this->add_texts('localization/', true);
		}
		$this->register_action('plugin.preview', array($this, 'preview'));
		$this->register_action('plugin.download', array($this, 'download'));

	}
	//From http://php.net/manual/en/function.imagecreatefromwbmp.php
	//By alexander at alexauto dot nl 08-Oct-2008 11:59
	function imagecreatefrombmp($p_sFile) 
	{ 
		//    Load the image into a string 
		$file    =    fopen($p_sFile,"rb"); 
		$read    =    fread($file,10); 
		while(!feof($file)&&($read<>"")) 
			$read    .=    fread($file,1024); 

		$temp    =    unpack("H*",$read); 
		$hex    =    $temp[1]; 
		$header    =    substr($hex,0,108); 

		//    Process the header 
		//    Structure: http://www.fastgraph.com/help/bmp_header_format.html 
		if (substr($header,0,4)=="424d") 
		{ 
			//    Cut it in parts of 2 bytes 
			$header_parts    =    str_split($header,2); 

			//    Get the width        4 bytes 
			$width            =    hexdec($header_parts[19].$header_parts[18]); 

			//    Get the height        4 bytes 
			$height            =    hexdec($header_parts[23].$header_parts[22]); 

			//    Unset the header params 
			unset($header_parts); 
		} 

		//    Define starting X and Y 
		$x                =    0; 
		$y                =    1; 

		//    Create newimage 
		$image            =    imagecreatetruecolor($width,$height); 

		//    Grab the body from the image 
		$body            =    substr($hex,108); 

		//    Calculate if padding at the end-line is needed 
		//    Divided by two to keep overview. 
		//    1 byte = 2 HEX-chars 
		$body_size        =    (strlen($body)/2); 
		$header_size    =    ($width*$height); 

		//    Use end-line padding? Only when needed 
		$usePadding        =    ($body_size>($header_size*3)+4); 

		//    Using a for-loop with index-calculation instaid of str_split to avoid large memory consumption 
		//    Calculate the next DWORD-position in the body 
		for ($i=0;$i<$body_size;$i+=3) 
		{ 
			//    Calculate line-ending and padding 
			if ($x>=$width) 
			{ 
				//    If padding needed, ignore image-padding 
				//    Shift i to the ending of the current 32-bit-block 
				if ($usePadding) 
					$i    +=    $width%4; 

				//    Reset horizontal position 
				$x    =    0; 

				//    Raise the height-position (bottom-up) 
				$y++; 

				//    Reached the image-height? Break the for-loop 
				if ($y>$height) 
					break; 
			} 

			//    Calculation of the RGB-pixel (defined as BGR in image-data) 
			//    Define $i_pos as absolute position in the body 
			$i_pos    =    $i*2; 
			$r        =    hexdec($body[$i_pos+4].$body[$i_pos+5]); 
			$g        =    hexdec($body[$i_pos+2].$body[$i_pos+3]); 
			$b        =    hexdec($body[$i_pos].$body[$i_pos+1]); 

			//    Calculate and draw the pixel 
			$color    =    imagecolorallocate($image,$r,$g,$b); 
			imagesetpixel($image,$x,$height-$y,$color); 

			//    Raise the horizontal position 
			$x++; 
		} 

		//    Unset the body / free the memory 
		unset($body); 

		//    Return image-object 
		return $image; 
	} 

	function get_thumbnail($attachment,$ext)
	{
		switch(strtolower($ext)){
			case "png":
				$src = imagecreatefrompng($attachment);
			break;
			case "jpg":
				case "jpeg":
				$src = imagecreatefromjpeg($attachment);
			break;
			case "bmp":
				$src = $this->imagecreatefrombmp($attachment);
			break;
			case "gif":
				$src = imagecreatefromgif($attachment);
			break;
			case "xpm":
				if(imagetypes() & IMG_XPM){
					$src = imagecreatefromxpm($attachment);
					break;
				}else{
					header('Content-type: image/gif');
					echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
					exit;
				}

			case "xbm":
				if(imagetypes() & IMG_XBM){
					$src = imagecreatefromxbm($attachment);
					break;
				}else{
					header('Content-type: image/gif');
					echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
					exit;
				}
			case "gd2":
				$src = imagecreatefromgd2($attachment);
			break;
			case "gd":
				$src = imagecreatefromgd($attachment);
			break;
			default:
			header('Content-type: image/gif');
			echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
			exit;
			break;
		}

		list($width,$height)=getimagesize($attachment);
		$newwidth=120;
		$newheight=($height/$width)*$newwidth;
		$tmp=imagecreatetruecolor($newwidth,$newheight);

		$newwidth1=120;
		$newheight1=($height/$width)*$newwidth1;
		$tmp1=imagecreatetruecolor($newwidth1,$newheight1);

		imagecopyresampled($tmp,$src,0,0,0,0,$newwidth,$newheight,

				$width,$height);

		imagecopyresampled($tmp1,$src,0,0,0,0,$newwidth1,$newheight1, 

				$width,$height);

		header('Content-Type: image/jpeg');
		// Skip the filename parameter using NULL, then set the quality to 75%
		imagejpeg($tmp1, NULL, 75);

	}
	function preview()
	{

		$RCMAIL = rcmail::get_instance();
		$COMPOSE_ID = get_input_value('_id', RCUBE_INPUT_GPC);
		if( isset( $_SESSION['compose_data'] ) ){
			$_SESSION['compose'] = $_SESSION['compose_data'][$COMPOSE_ID]; // After roundcube version 4542 
		}
		else{
			$_SESSION['compose']['id'] = $COMPOSE_ID;  // Before roundcube version 4542
		}
		if (preg_match('/^rcmfile(\w+)$/', $_GET['_file'], $regs))
			$id = $regs[1];

		if ($attachment = $_SESSION['compose']['attachments'][$id])
			$attachment = $RCMAIL->plugins->exec_hook('attachment_display', $attachment);

		if ($attachment['status']) {
			if (empty($attachment['size']))
				$attachment['size'] = $attachment['data'] ? strlen($attachment['data']) : @filesize($attachment['path']);

			$path_info = pathinfo($attachment['name']);
			$this->get_thumbnail($attachment['path'],$path_info['extension']);

		}


		exit;
	}
	function download()
	{

		$RCMAIL = rcmail::get_instance();
		$COMPOSE_ID = get_input_value('_id', RCUBE_INPUT_GPC);
		if( isset( $_SESSION['compose_data'] ) ){
			$_SESSION['compose'] = $_SESSION['compose_data'][$COMPOSE_ID]; // After roundcube version 4542 
		}
		else{
			$_SESSION['compose']['id'] = $COMPOSE_ID;  // Before roundcube version 4542
		}
		if (preg_match('/^rcmfile(\w+)$/', $_GET['_file'], $regs))
			$id = $regs[1];

		if ($attachment = $_SESSION['compose']['attachments'][$id])
			$attachment = $RCMAIL->plugins->exec_hook('attachment_display', $attachment);

		if ($attachment['status']) {
			if (empty($attachment['size']))
				$attachment['size'] = $attachment['data'] ? strlen($attachment['data']) : @filesize($attachment['path']);


			if (file_exists($attachment['path'])) {
				$ua = $_SERVER["HTTP_USER_AGENT"]; 
				$filename = $attachment['name'];
				$encoded_filename = urlencode($filename); 
				$encoded_filename = str_replace("+", "%20", $encoded_filename); 
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				if (preg_match("/MSIE/", $ua)) { 
					header('Content-Disposition: attachment; filename="' . $encoded_filename . '"'); 
				} else if (preg_match("/Firefox/", $ua)) { 
					header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"'); 
				} else { 
					header('Content-Disposition: attachment; filename="' . $filename . '"'); 
				}
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . filesize( $attachment['path'] ));
				ob_clean();
				flush();
				readfile($attachment['path']);

			}

		}
		exit;
	}
}

?>
