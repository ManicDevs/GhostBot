<?php

namespace Command;

class Joke extends \Classes\Command\Base
{
	protected $help = 'Return a random joke.';

	protected $usage = 'joke';

	protected $numberOfArguments = 0;

	public function command()
	{
		$data1 = $this->fetch("http://tambal.azurewebsites.net/joke/random");
		$joke1 = json_decode($data1);
		
		if($joke1)
		{
			if(isset($joke1->joke))
			{
				$jokes[] = $joke1->joke;
			}
		}
		
		$data2 = $this->fetch("http://api.yomomma.info/");
		$joke2 = json_decode($data2);
		
		if($joke2)
		{
			if(isset($joke2->joke))
			{
				$jokes[] = $joke2->joke;
			}
		}
		
		if(count($jokes) >= 1)
		{
			$rand = array_rand($jokes);
			$this->say(sprintf('Joke: %s', $jokes[$rand]));
			return;
		}
		
		$this->say("I don't feel like laughing today. :(");
	}
}
