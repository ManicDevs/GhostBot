<?php

namespace Command;

/**
 * @package IRCBot
 * @subpackage Command
 */
class Sha1 extends \Classes\Command\Base
{
	protected $help = 'Get a SHA1 from a string.';
	
	protected $usage = 'sha1 [SHA1] or [String]';

	private $apiUri = '';

	protected $numberOfArguments = array(1, -1);

	public function command()
	{
		if(preg_match('/^[a-f0-9]{40}$/i', trim($this->arguments[0])))
		{	
			$post = http_build_query(
	    		array(
	        		'hash'		=> trim($this->arguments[0]),
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
			$resultData = file_get_contents('http://md5decrypt.net/en/Sha1/', false, $context);

			preg_match('/<fieldset class=trouve>(.*) : <b>(.*)<\/b><br\/><br\/>Found in (.*) on <a/is', $resultData, $result);
			
			if(count($result) == 4)
			{
				$this->say(sprintf('SHA1: %s | String(%s): %s | Time: %.3fs', $result[1], strlen($result[2]), $result[2], $result[3]));
				return;
			}
			
			$sha1dir = str_split(substr(trim($this->arguments[0]), 0, 20), 1);
			$sha1dir = 'lib/Command/Sha1data/' . implode('/', $sha1dir);
			if(file_exists($sha1dir . '/' . substr(trim($this->arguments[0]), 20)))
			{
				$timeStart = microtime(true);
				$result = file_get_contents($sha1dir . '/' . substr(trim($this->arguments[0]), 20));
				$timeEnd = microtime(true);
				
				$this->say(sprintf('SHA1: %s | String(%s): %s | Time: %.3fs', trim($this->arguments[0]), strlen($result), $result, (($timeEnd - $timeStart) * 1000)));
				return;
			}
			
			$this->say(sprintf('SHA1: %s not found.', trim($this->arguments[0])));
			return;
		}
		
		$sha1str = implode(' ', $this->arguments);
		$sha1str = preg_replace('/\s\s+/', ' ', $sha1str);
		$sha1str = trim($sha1str);

		$timeStart = microtime(true);
		$result= sha1($sha1str);
		$timeEnd = microtime(true);
		
		$sha1dir = str_split(substr($result, 0, 20), 1);
		$sha1dir = 'lib/Command/Sha1data/' . implode('/', $sha1dir);
		if(!file_exists($sha1dir))
		{
			mkdir($sha1dir, 0755, true);
			file_put_contents($sha1dir . '/' . substr($result, 20), $sha1str);
		}
		
		$this->say(sprintf('SHA1: %s | String(%s): %s | Time: %.3fs', $result, strlen($sha1str), $sha1str, (($timeEnd - $timeStart) * 1000)));
	}
}
