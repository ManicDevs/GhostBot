<?php

namespace Listener;

class Image extends \Classes\Listener\Base
{
	private $apiUri = "";

	public function execute($data)
	{
		$imgDetails = $this->getImgDetails($data);
		if($imgDetails)
		{
			$args = $this->getArguments($data);
			list($link, $width, $height, $bits, $mime) = explode('|', implode('|', $imgDetails));
			$link = explode('/', $link);			
			$this->say(sprintf("Img: /%s | Width: %s | Height: %s | Bits: %s | MimeType: %s", $link[count($link)-1], $width, $height, $bits, $mime), $args[2]);
		}
	}

	private function getImgDetails($data)
	{
		//preg_match('~(http.*\.)(....|.....)~i', $data, $matches);
		preg_match('~(https?://)[^/\s]+/\S+\.(jpe?g|png|gif)~i', $data, $matches);
		if(isset($matches[0]))
		{	
			$imgDataArr = getimagesize($matches[0]);
			if(is_array($imgDataArr))
			{
				return array('link' => $matches[0],'width' => $imgDataArr[0], 'height' => $imgDataArr[1], 'bits' => $imgDataArr['bits'], 'mime' => $imgDataArr['mime']);
			}
			return false;
		}
		return false;
	}
	
	private function decodeChars($input)
	{
		return htmlspecialchars_decode(preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $input));
	}
	
	public function getKeywords()
	{
		return array("PRIVMSG");
	}
}
