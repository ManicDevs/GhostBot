<?php

namespace Command;

class Help extends \Classes\Command\Base
{
	protected $help = 'Show information about commands.';
	
	protected $usage = 'help [optional command]';
	
	protected $numberOfArguments = array(0, 1);

	public function command()
	{
		// We don't want any trailing \n, \r or anything in that area.
		$command = (!empty($this->arguments[0]) ? preg_replace('/\s\s+/', '', $this->arguments[0]) : '');
		
		// Get all available commands.
		$commands = $this->bot->getCommandsLoaded();
		
		// Does this user have privileges?
		$view_all = $this->verifyUser();
		
		// If no command specified we show a list of commands.
		if(empty($command))
		{
			$output = array();
			foreach($commands as $name => $details)
			{
				if($view_all || !$details->needsVerification())
				{
					$output[] = $name;
				}
			}
			$this->say('Help: Available commands: ' . implode(', ', $output));
		}
		else // Else a command was specified, so try to load the help for it.
		{
			// Get all commands.
			$commands = $this->bot->getCommandsLoaded();
			
			// Loop through each to get the one we need.
			foreach($commands as $name => $details)
			{
				if(trim(ucfirst(strtolower($command))) == $name)
				{
					// We found it!
					if(empty($details->getHelp()))
					{
						// But it doesn't have any help... :(
						$this->say('Help: No help available for command ' . $name);
						return;
					}
					$help = $details->getHelp();
					$this->say($name . ': ' . $help[0] . ($details->needsVerification() ? ' (verified users only)' : ''));
					$this->say('Help: Command usage: ' . $this->bot->commandPrefix . $help[1]);
					return;
				}
			}

			$this->say('Help: No such command: ' . $command);
		}
	}
}
