<?php

chdir(__DIR__);
set_time_limit(0);
error_reporting(E_ALL);

exec('rm -rf lib/Command/Quizdata/*.started');

/*
$_ = $_SERVER['_'];  
$restartMyself = function ()
{
	global $shutdown, $_, $argv;

	if($shutdown)
	{
		exit;
	}
	pcntl_exec($_, $argv);
};
register_shutdown_function($restartMyself);
#pcntl_signal(SIGTERM, $restartMyself);
#pcntl_signal(SIGHUP,  $restartMyself);
#pcntl_signal(SIGINT,  $restartMyself);
#set_error_handler(function() { global $shutdown; $shutdown = true; echo "An Error...." . PHP_EOL; exit;} , E_ALL); // Catch all errors
*/

if(!file_exists('configs/config.php'))
{
	throw new \Exception("Error: Unable to locate configuration [configs/config.php]");
}
$config = include_once 'configs/config.php';

if(!file_exists('configs/commands.php'))
{
	throw new \Exception("Error: Unable to locate configuration [configs/commands.php]");
}
$commands = include_once 'configs/commands.php';

if(!file_exists('configs/listeners.php'))
{
	throw new \Exception("Error: Unable to locate configuration [configs/listeners.php]");
}
$listeners = include_once 'configs/listeners.php';

$timezone = ini_get('date.timezone');
if(empty($timezone))
{
	if(empty($config['timezone']))
	{
		$config['timezone'] = 'UTC';
	}
	date_default_timezone_set($config['timezone']);
}

require 'lib/Autoloader.php';
spl_autoload_register('Autoloader::load');

$bot = new \Classes\IRC\BotCore($config);

foreach($commands as $commandName => $args)
{
	$reflector = new ReflectionClass($commandName);
	$command = $reflector->newInstanceArgs($args);
	$bot->addCommand($command);
}

foreach($listeners as $listenerName => $args)
{
	$reflector = new ReflectionClass($listenerName);
	$listener = $reflector->newInstanceArgs($args);
	$bot->addListener($listener);
}

$bot->doConnect();
