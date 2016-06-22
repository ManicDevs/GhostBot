<?php

namespace Command;

class Md5 extends \Classes\Command\Base
{
	protected $help = 'Get a plain text string from an MD5 or an MD5 from a string.';

	protected $usage = 'md5 [MD5] or [String]';

	private $apiUri = '';

	protected $numberOfArguments = array(1, -1);
	
	public function command()
	{
		if(preg_match('/^[a-f0-9]{32}$/i', trim($this->arguments[0])))
		{	
			$post = http_build_query(
	    		array(
	        		'hash'		=> trim($this->arguments[0]),
	        		'md5md5'		=> 'md5md5',
	        		'decrypt'	=> 'Decrypt'
	    		)
			);
			
			$opts = array('http' =>
	    		array(
	        		'method'  		=> 'POST',
	        		'header'  		=> "Content-type: application/x-www-form-urlencoded",
	        		'content' 		=> $post
	    		)
			);
	
			$context  = stream_context_create($opts);
			$resultData = file_get_contents('http://md5decrypt.net/en/', false, $context);

			preg_match('/<fieldset class=trouve>(.*) : <b>(.*)<\/b><br\/><br\/>Found in (.*) on <a/is', $resultData, $result);
			
			if(count($result) == 4)
			{
				$this->say(sprintf('MD5: %s | String(%s): %s | Time: %.3fs', $result[1], strlen($result[2]), $result[2], $result[3]));
				return;
			}
			
			$md5dir = str_split(substr(trim($this->arguments[0]), 0, 16), 1);
			$md5dir = 'lib/Command/Md5data/' . implode('/', $md5dir);
			if(file_exists($md5dir . '/' . substr(trim($this->arguments[0]), 16)))
			{
				$timeStart = microtime(true);
				$result = file_get_contents($md5dir . '/' . substr(trim($this->arguments[0]), 16));
				$timeEnd = microtime(true);
				
				$this->say(sprintf('MD5: %s | String(%s): %s | Time: %.3fs', trim($this->arguments[0]), strlen($result), $result, (($timeEnd - $timeStart) * 1000)));
				return;
			}			

			$this->say(sprintf('MD5: %s not found.', trim($this->arguments[0])));
			return;
		}
		
		$md5str = implode(' ', $this->arguments);
		$md5str = preg_replace('/\s\s+/', ' ', $md5str);
		$md5str = trim($md5str);

		$timeStart = microtime(true);
		$result= md5($md5str);
		$timeEnd = microtime(true);
		
		$md5dir = str_split(substr($result, 0, 16), 1);
		$md5dir = 'lib/Command/Md5data/' . implode('/', $md5dir);
		if(!file_exists($md5dir))
		{
			mkdir($md5dir, 0755, true);
			file_put_contents($md5dir . '/' . substr($result, 16), $md5str);
		}
		
		$this->say(sprintf('MD5: %s | String(%s): %s | Time: %.3fs', $result, strlen($md5str), $md5str, (($timeEnd - $timeStart) * 1000)));
	}
}
