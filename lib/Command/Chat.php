<?php

namespace Command;

/**
 * @package IRCBot
 * @subpackage Command
 */
class Chat extends \Classes\Command\Base
{
	protected $help = 'Talking ChatBot AI (Limited Daily API Calls)';
	
	protected $usage = 'chat [message]';

	protected $numberOfArguments = -1;

	public function command()
	{		
		$chattxt = implode(' ', $this->arguments);
		
		$chattxt = urlencode(trim($chattxt));
		
		$result = $this->fetch("http://www.botlibre.com/rest/botlibre/form-chat?instance=165&application=618028528562244936&message=$chattxt");
		
		$chattxt = simplexml_load_string($result);
		if(is_object($chattxt))
		{
			$this->say(sprintf("%s, %s", $this->usersource, str_replace(' <br/> ', ' ', $chattxt->message)));
			return;
		}
		$comebacks = array('Do you speak to your mother with that mouth!?',
								'Do you want me to fucking spank you!?',
								'I\'m going to fucking shove those words down your throat!',
								'What the fuck!? Speak to me like that and I\'ll kill you!',
								'Fucky Fuck Fuckitty Fuck!!!');
		
		$this->say(sprintf('%s, %s', $this->usersource, $comebacks[array_rand($comebacks)]));
	}
}
