<?php

namespace Command;

/* In dev.. Prolly not gonna go forward! */

class Bate extends \Classes\Command\Base
{
	protected $help = 'Get an random Chaturbate feed.';
	
	protected $usage = 'bate';

	protected $numberOfArguments = 0;

	public function command()
	{
		$html = $this->fetch('https://chaturbate.com/female-cams/');
		$html = $this->strfetch($html->find('ul.list', 0));

		$feeds = $html->find('li');
		
		foreach($feeds as $feed)
		{
			if(is_object($feed->find('div.title', 0)))
			{			
				$bates[] = array(
						'url' => 'https://chaturbate.com/'.str_replace('\'s chat room', '/', $feed->find('img.png', 0)->alt),
						'age'	=> $feed->find('span.age', 0)->plaintext,
						'subject' => $feed->find('ul.subject', 0)->plaintext,
						'location' => $feed->find('li.location', 0)->plaintext,
						'lengthviewers' => $feed->find('li.cams', 0)->plaintext
				);
			}
		}
		$feed = $bates[array_rand($bates)];		

		$this->say(sprintf('Bate: -> %s', $feed['url']));
		$this->say(sprintf(' -> Age, Location: %s,%s', $feed['age'], implode(' ',array_unique(explode(' ', $feed['location'])))));
		$this->say(sprintf(' -> Length, Viewers:%s', implode(' ',array_unique(explode(' ', $feed['lengthviewers'])))));
	}
}
