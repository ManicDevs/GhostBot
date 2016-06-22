<?php

namespace Classes\IRC;

class BotCore extends BotSocket
{
	private $socket = null;
	
	private $nickCounter = 0;	

	private $numberOfReconnects = 0;	
	
	public function __construct(array $configuration)
	{
		$this->socket = new \Classes\IRC\BotSocket;

		if(count($configuration) === 0)
		{
			return;
		}

		$this->setConfiguration($configuration);
	}

   public function doConnect()
	{
		if(empty($this->nickToUse))
		{
			$this->nickToUse = $this->nickName;
		}

		if($this->socket->isConnected())
		{
			$this->socket->doDisconnect();
		}

		$this->log('The following commands are known by the bot: "' . implode( ',', array_keys( $this->commandsLoaded ) ) . '".', 'INFO');
		$this->log('The following listeners are known by the bot: "' . implode( ',', array_keys( $this->listenersLoaded ) ) . '".', 'INFO');

		$this->socket->doConnect();

		$this->doMainCycle();
	}

	private function doMainCycle()
	{
		global $config;
		
		$this->commandPrefix = $config['prefix'];		
		
		sleep(3);

		if(strlen($this->password) > 0)
		{
			$this->sendData('USER ' . $this->nickToUse . ' irc-net. ' . $this->nickToUse . ' :' . $this->userName);
		}	

		while(true)
		{	
			$buffer = $this->socket->recvData(10240);
			$args = explode(' ', $buffer);
			
			$this->log($buffer, 'RECV');	
			$newargs = array_slice($args, 0, 9);
			$newbuffer = implode(' ', $newargs);
			
			//print_r($args);			

			if($args[0] == 'PING')
			{
				$this->sendData('PONG ' . $args[1]);
			}

			if($args[0] == 'ERROR' && strpos($buffer, 'Closing link') || strpos($buffer, 'Erroneous Nickname'))
			{
				if($this->numberOfReconnects >= $this->maxReconnects)
				{
					$this->log('Closing Link after "' . $this->numberOfReconnects . '" reconnects.', 'EXIT');
					exit;
				}

				$this->log($buffer, 'CONNECTION LOST');
				sleep(5);
				++$this->numberOfReconnects;
				$this->socket->doConnect();
				return;
			}

			if($args[0] == 'NOTICE' || @$args[1] == 'NOTICE' && strpos($buffer, 'Found your hostname') || 
					strpos($buffer, 'Could not resolve your hostname') || strpos($buffer, 'Your hostname does not match') || strpos($buffer, 'You need to send your password'))
			{
				sleep(1);
				if(strlen($this->password) > 0)
				{
					$this->sendData('PASS ' . $this->password);
				}
				$this->sendData('USER ' . $this->nickToUse . ' irc-net. ' . $this->nickToUse . ' :' . $this->userName);
				$this->sendData('NICK ' . $this->nickToUse);
				$this->sendData('MODE ' . $this->nickToUse . ' +R');
				//$this->sendData('MODE ' . $this->nickToUse . ' ' . $this->modesToUse);
				if(isset($config['operline']) && is_array($config['operline']))
				{
					$this->sendData('OPER ' . $config['operline'][0] . ' ' . $config['operline'][1]);
				}
				if(isset($config['hostline']))
				{
					//$this->sendData('SETHOST ' . $config['hostline']);				
				}
			}

			if(@$args[1] == '433' && strpos($buffer, 'Nickname is already in use'))
			{
				$this->nickToUse = $this->nickName . (++$this->nickCounter);
				$this->sendData('NICK ' . $this->nickToUse);
			}

			if(@$args[1] == '001' && strpos($newbuffer, 'Welcome'))
			{
				$this->doJoinChannels($this->channels);
			}
			
			//$this->log( $buffer );

			//$this->logger->intervalFlush();

			foreach($this->listenersLoaded as $listener)
			{
				if(is_array($listener->getKeywords()))
				{
					foreach($listener->getKeywords() as $keyword)
					{
						//compare listeners keyword and 1st arguments of server response
						if($keyword === @$args[1])
						{
							$listener->execute($buffer);
						}
					}
				}
			}

			if(isset($args[3]) && strlen($args[3]) > 1)
			{
				// $source finds the channel or user that the command originated.
				$source = substr(trim(\Classes\Functions::removeLineBreaks($args[2])), 0);
				$command = substr(trim(\Classes\Functions::removeLineBreaks($args[3])), 1);
				
				if($source == $this->nickToUse && $args[1] == 'PRIVMSG')
				{
					$source = $this->getUserNickName($args[0]);
				}
				
				$arguments = array_slice($args, 4);
				//print_r($arguments);
				//unset($args);

				// Check if the response was a command.
				if(stripos($command, $this->commandPrefix) === 0 && $args[1] != '372')
				{
					$command = ucfirst(substr($command, strlen($this->commandPrefix)));
					
					// Command does not exist:
					if(!array_key_exists($command, $this->commandsLoaded))
					{
						$this->log('The following, not existing, command was called: "' . $command . '".', 'MISSING');
						$this->log('The following commands are known by the bot: "' . implode(',', array_keys($this->commandsLoaded)) . '".', 'MISSING');
						continue;
					}
					else
					{
						$this->executeCommand($source, $command, $arguments, $buffer);
					}
				}
				unset($args);
			}
		}
	}

	public function doJoinChannels(array $channels)
	{
		if(is_array($channels))
		{
			foreach($channels as $channelArgs)
			{
				@list($channel, $password) = explode(':', $channelArgs);
				$this->sendData("JOIN $channel $password");
			}
		}
	}

	public function getUserNickName($buffer)
	{
		if(preg_match('/:([a-zA-Z0-9_]+)!/', $buffer, $matches) !== false)
		{
			return isset($matches[1])?$matches[1]:null;
		}

		return false;
	}

	public function getClassName($object)
	{
		$objectName = explode( '\\', get_class( $object ) );
		$objectName = $objectName[count( $objectName ) - 1];

		return $objectName;
	}

	public function addCommand(\Classes\Command\Base $command)
	{
		$commandName = $this->getClassName($command);
		$command->setIRCConnection($this->socket);
		$command->setIRCBot($this);
		$this->commandsLoaded[$commandName] = $command;
		$this->log('The following Command was added to the Bot: "' . $commandName . '".', 'INFO');
	}

	public function executeCommand($source, $commandName, array $arguments, $data)
	{
		$command = $this->commandsLoaded[$commandName];
		$command->executeCommand($arguments, $source, $data);
	}

	public function addListener(\Classes\Listener\Base $listener)
	{
		$listenerName = $this->getClassName($listener);
		$listener->setIRCConnection($this->socket);
		$listener->setIRCBot($this);
		$this->listenersLoaded[$listenerName] = $listener;
		$this->log('The following Listener was added to the Bot: "' . $listenerName . '".', 'INFO');
	}

	public function getCommandPrefix()
	{
		return $this->commandPrefix;
	}

	public function getCommandsLoaded()
	{
		return $this->commandsLoaded;
	}

	public function setMaxReconnects($maxReconnects)
	{
		$this->maxReconnects = (int)$maxReconnects;
	}	

	public function setServerAddr($serverAddr)
	{
		$this->socket->setServerAddr((string)$serverAddr);
	}
	
	public function setServerPort($serverPort)
	{
		$this->socket->setServerPort((int)$serverPort);
	}
	
	public function setPassword($password)
	{
		$this->password = (string)$password;
	}

	public function setUserName($userName)
	{
		$this->userName = (string)$userName;
	}
	 
	public function setNickName($nickName)
	{
		$this->nickName = (string)$nickName;
	}	

	public function setModes($modes)
	{
		$this->modesToUse = (string)$modes;
	}

	public function setChannels($channels)
	{
		$this->channels = (array)$channels;
	}
	
	private function setConfiguration(array $configuration)
	{
		$this->setMaxReconnects($configuration['max_reconnects']);
		$this->setServerAddr($configuration['serveraddr']);
		$this->setServerPort($configuration['serverport']);
		
		$this->setPassword($configuration['password']);
		$this->setUserName($configuration['username']);
		$this->setNickName($configuration['nickname']);
		$this->setModes($configuration['modes']);
		$this->setChannels($configuration['channels']);
	}
	
	public function sendData($command)
	{
		if(mb_substr($command, 0, 4) == 'PASS')
		{
			$this->log('PASS *****', 'COMMAND');
		}
		elseif(mb_substr($command, 0, 4) == 'OPER')
		{
			$this->log('OPER ***** *****', 'COMMAND');
		}
		else
		{
			$this->log($command, 'SEND');
		}
		$this->socket->sendData($command);
	}

	public function log($buffer = '', $status = 'INFO')
	{
		if($buffer != '')
		{
			echo "[$status]: $buffer" . PHP_EOL;
		}
	}
}
