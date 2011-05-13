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

	}
	function get_thumbnail($attachment,$ext)
	{
		switch(strtolower($ext)){
			case "png":
				$src = imagecreatefrompng($attachment);
			break;
			case "jpg":
				case "jpeg":
				//print "jpg";
				$src = imagecreatefromjpeg($attachment);
			break;
			case "bmp":
				$src = imagecreatefrombmp($attachment);
			break;
			case "gif":
				$src = imagecreatefromgif($attachment);
			break;
			case "xpm":
				$src = imagecreatefromxpm($attachment);
			break;
			case "xbm":
				$src = imagecreatefromxbm($attachment);
			break;
			case "bmp":
				$src = imagecreatefromwbmp($attachment);
			break;
			case "gd2":
				$src = imagecreatefromgd2($attachment);
			break;
			case "gd":
				$src = imagecreatefromgd($attachment);
			break;
			default:
			header('Content-type: image/gif');
			echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
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
		$_SESSION['compose'] = $_SESSION['compose_data'][$COMPOSE_ID];
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
}

?>
