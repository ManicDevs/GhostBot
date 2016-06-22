<?php

namespace Command;
 
class Quiz extends \Classes\Command\Base
{
	protected $help = 'Quizimodo. A short quiz question with a limit of 5 minutes.';
	
	protected $usage = 'quiz [start|stop|question|clue|give [answer]|stats [*|mostwinlose|[nick]]';
	
	protected $verify = false;
	
	protected $numberOfArguments = array(-1, 0, 1);
	
	protected $timer = 1;	
	
	protected $timerend = 300;

	protected $questions = array();
	
	protected $answers = array();
	
	protected $clues = array();

	public function command()
	{
		if(substr(strtolower($this->source), 0, 1) !== '#')
		{
			$this->say('Quiz: Unable to do anything in a Query, use the channel to do this!');
			return;
		}

		if(!file_exists('lib/Command/Quizdata/'))
		{
			mkdir('lib/Command/Quizdata/');
		}	
		
		if(!file_exists('lib/Command/Quizdata/quiz.db'))
		{
			touch('lib/Command/Quizdata/quiz.db');
			file_put_contents('lib/Command/Quizdata/quiz.db', 'Is this a dummy question? Yes or No|BREAK|yes|BREAK|I was put in to serve as a placement' . PHP_EOL);
		}
		
		if(!file_exists('lib/Command/Quizdata/stats.db'))
		{
			touch('lib/Command/Quizdata/stats.db');
			file_put_contents('lib/Command/Quizdata/stats.db', serialize(array()));
		}
		
		$quiz = file_get_contents('lib/Command/Quizdata/quiz.db');
		$stats = unserialize(file_get_contents('lib/Command/Quizdata/stats.db'));

		$action = strtoupper(@$this->arguments[0]);
		switch($action)
		{
			default:
				$this->say($this->usage);
			break;
			
			case 'START':
				if(!file_exists('lib/Command/Quizdata/' . strtolower(substr($this->source, 1)) . '.started'))
				{
					touch('lib/Command/Quizdata/' . strtolower(substr($this->source, 1)) . '.started');
					$this->say('Quiz: The quiz has started, the question is below...');
					$this->say('Quiz: Type ".quiz clue" for help or ".quiz give [answer]" to answer.');
					
					if(count(@$this->answers[strtolower(substr($this->source, 1))][0]) < 1)
					{
						$qacs = explode("\n", $quiz);
						if($qacs[count($qacs)-1] === '')
						{
							unset($qacs[count($qacs)-1]);
						}
						foreach($qacs as $key => $qac)
						{
							list($question, $answer, $clue) = explode('|BREAK|', $qac);
							$this->questions[strtolower(substr($this->source, 1))][$key] = $question;
							
							list($answerText, $answerLetter) = explode('::', $answer);
							$this->answers[strtolower(substr($this->source, 1))][0][$key] = $answerText;
							$this->answers[strtolower(substr($this->source, 1))][1][$key] = $answerLetter;
							
							$this->clues[strtolower(substr($this->source, 1))][$key] = $clue;
						}
					}					

					unset($this->location);					
					
					$this->location[strtolower(substr($this->source, 1))] = array_rand($this->questions[strtolower(substr($this->source, 1))]);
					$this->question[strtolower(substr($this->source, 1))] = $this->questions[strtolower(substr($this->source, 1))][$this->location[strtolower(substr($this->source, 1))]];
					
					echo PHP_EOL . 'Text: ' . $this->answers[strtolower(substr($this->source, 1))][0][$this->location[strtolower(substr($this->source, 1))]];
					echo PHP_EOL . 'Letter: ' . $this->answers[strtolower(substr($this->source, 1))][1][$this->location[strtolower(substr($this->source, 1))]] . PHP_EOL . PHP_EOL;
					#print_r($this->answers);
					//unset($this->answer);
					
					$this->answer[strtolower(substr($this->source, 1))][0] = $this->answers[strtolower(substr($this->source, 1))][0][$this->location[strtolower(substr($this->source, 1))]];
					$this->answer[strtolower(substr($this->source, 1))][1] = $this->answers[strtolower(substr($this->source, 1))][1][$this->location[strtolower(substr($this->source, 1))]];
					
					$this->clue[strtolower(substr($this->source, 1))] = $this->clues[strtolower(substr($this->source, 1))][$this->location[strtolower(substr($this->source, 1))]];
					$this->say(sprintf(' -> %s', $this->question[strtolower(substr($this->source, 1))]));
					$this->say(sprintf(' -> %s', $this->clue[strtolower(substr($this->source, 1))]));
					
					$pid = pcntl_fork();
					if(!$pid)
					{
						for($this->timer = 1; $this->timer <= $this->timerend; $this->timer++)
						{
							if(!file_exists('lib/Command/Quizdata/' . strtolower(substr($this->source, 1)) . '.started'))
							{
								exit;
								return;
							}
							
							if($this->timer === ($this->timerend / 2))
							{
								$this->say(sprintf('Quiz: The quiz has %s second(s) left.', ($this->timerend - $this->timer)));
							}
							
							if($this->timer >= $this->timerend)
							{
								unlink('lib/Command/Quizdata/' . strtolower(substr($this->source, 1)) . '.started');
								$this->say('Quiz: The quiz has ended, you have ran out of time!');
								$this->say(sprintf('Quiz: The answer was %s!', $this->answer[strtolower(substr($this->source, 1))][0]));
								exit;
								return;
							}
							sleep(1);
						}
					}
					return;
				}
					
				$this->say(sprintf('Quiz: %s, there is already a quiz started!', $this->usersource));
				return;
			break;
			
			case 'STOP':
				if(file_exists('lib/Command/Quizdata/' . strtolower(substr($this->source, 1)) . '.started'))
				{
					unlink('lib/Command/Quizdata/' . strtolower(substr($this->source, 1)) . '.started');
					unset($this->questions[strtolower(substr($this->source, 1))][$this->location[strtolower(substr($this->source, 1))]],
							$this->answers[strtolower(substr($this->source, 1))][0][$this->location[strtolower(substr($this->source, 1))]],
							$this->answers[strtolower(substr($this->source, 1))][1][$this->location[strtolower(substr($this->source, 1))]],
							$this->clues[strtolower(substr($this->source, 1))][$this->location[strtolower(substr($this->source, 1))]]);
					$this->say(sprintf('Quiz: %s has stopped the quiz!', $this->usersource));
					return;
				}
				
				$this->say(sprintf('Quiz: %s, there is no quiz started, type ".quiz start" to start!', $this->usersource));
				return;
			break;			

			case 'QUESTION':
				if(file_exists('lib/Command/Quizdata/' . strtolower(substr($this->source, 1)) . '.started'))
				{
					$this->say('Quiz: The question is below, give a new answer if you think you know it.');
					$this->say(sprintf(' -> %s', $this->question[strtolower(substr($this->source, 1))]));
					return;
				}
				
				$this->say(sprintf('Quiz: %s, there is no quiz started, type ".quiz start" to start!', $this->usersource));
				return;
			break;

			case 'CLUE':
				if(file_exists('lib/Command/Quizdata/' . strtolower(substr($this->source, 1)) . '.started'))
				{
					$this->say('Quiz: Your clue is below, give a new answer if you think you know it.');
					$this->say(sprintf(' -> %s', $this->clue[strtolower(substr($this->source, 1))]));
					return;
				}
				
				$this->say(sprintf('Quiz: %s, there is no quiz started, type ".quiz start" to start!', $this->usersource));
				return;
			break;
			
			case 'GIVE':
				if(file_exists('lib/Command/Quizdata/' . strtolower(substr($this->source, 1)) . '.started'))
				{
					$answer = $this->arguments;
					unset($answer[0]);
					echo PHP_EOL . 'Text: ' . $this->answers[strtolower(substr($this->source, 1))][0][$this->location[strtolower(substr($this->source, 1))]];
					echo PHP_EOL . 'Letter: ' . $this->answers[strtolower(substr($this->source, 1))][1][$this->location[strtolower(substr($this->source, 1))]] . PHP_EOL . PHP_EOL;	
					if(strtolower($this->answer[strtolower(substr($this->source, 1))][0]) === strtolower(implode(' ', $answer)) ||
						strtolower($this->answer[strtolower(substr($this->source, 1))][1]) === strtolower(implode(' ', $answer)))
					{
						unlink('lib/Command/Quizdata/' . strtolower(substr($this->source, 1)) . '.started');
						unset($this->questions[strtolower(substr($this->source, 1))][$this->location[strtolower(substr($this->source, 1))]],
								$this->answers[strtolower(substr($this->source, 1))][0][$this->location[strtolower(substr($this->source, 1))]],
								$this->answers[strtolower(substr($this->source, 1))][1][$this->location[strtolower(substr($this->source, 1))]],
								$this->clues[strtolower(substr($this->source, 1))][$this->location[strtolower(substr($this->source, 1))]]);
						$this->say(sprintf('Quiz: %s, You have won the quiz!!!', $this->usersource));
						if(!isset($stats[strtolower(substr($this->source, 1))]) || !isset($stats[strtolower(substr($this->source, 1))][strtolower($this->usersource)]))
						{
							$stats[strtolower(substr($this->source, 1))][strtolower($this->usersource)] = '1:0';
						}
						else
						{
							list($wins, $loses) = explode(':', $stats[strtolower(substr($this->source, 1))][strtolower($this->usersource)]);
							$wins++;
							$stats[strtolower(substr($this->source, 1))][strtolower($this->usersource)] = $wins . ':' . $loses;
						}
						file_put_contents('lib/Command/Quizdata/stats.db', serialize($stats));
						return;
					}
					if(!isset($stats[strtolower(substr($this->source, 1))]) || !isset($stats[strtolower(substr($this->source, 1))][strtolower($this->usersource)]))
					{
						$stats[strtolower(substr($this->source, 1))][strtolower($this->usersource)] = '0:1';
					}
					else
					{
						list($wins, $loses) = explode(':', $stats[strtolower(substr($this->source, 1))][strtolower($this->usersource)]);
						$loses++;
						$stats[strtolower(substr($this->source, 1))][strtolower($this->usersource)] = $wins . ':' . $loses;
					}
					$this->say(sprintf('Quiz: %s, You haven\'t won the quiz!!! Try again.', $this->usersource));
					file_put_contents('lib/Command/Quizdata/stats.db', serialize($stats));
					return;
				}
				
				$this->say(sprintf('Quiz: %s, there is no quiz started, type ".quiz start" to start!', $this->usersource));
				return;
			break;			

			case 'STATS':
				if(count(explode(':', @max(@$stats[strtolower(substr($this->source, 1))]))) == 2)
				{
					if(!isset($this->arguments[1]) || @$this->arguments[1] === '*')
					{
						$this->connection->sendData(sprintf('NOTICE %s :All Statistics', $this->usersource));					

						foreach($stats[strtolower(substr($this->source, 1))] as $key => $stat)
						{
							list($wins, $loses) = explode(':', $stats[strtolower(substr($this->source, 1))][$key]);
							$winners[strtolower(substr($this->source, 1))][$key] = $wins;
							$losers[strtolower(substr($this->source, 1))][$key] = $loses;
						}

						$this->connection->sendData(sprintf('NOTICE %s : -> Most wins by %s totalling at %s win(s).', 
								$this->usersource,
								array_search(max($winners[strtolower(substr($this->source, 1))]), $winners[strtolower(substr($this->source, 1))]),
								$winners[strtolower(substr($this->source, 1))][array_search(max($winners[strtolower(substr($this->source, 1))]), $winners[strtolower(substr($this->source, 1))])] ));
								
						$this->connection->sendData(sprintf('NOTICE %s : -> Most loses by %s totalling at %s lose(s).', 
								$this->usersource,
								array_search(max($losers[strtolower(substr($this->source, 1))]), $losers[strtolower(substr($this->source, 1))]),
								$losers[strtolower(substr($this->source, 1))][array_search(max($losers[strtolower(substr($this->source, 1))]), $losers[strtolower(substr($this->source, 1))])] ));
						
						foreach($stats[strtolower(substr($this->source, 1))] as $nick => $winlose)
						{
							list($wins, $loses) = explode(':', $winlose);
							$this->connection->sendData(sprintf('NOTICE %s : -> %s has won %s time(s) and lost %s time(s).', 
									$this->usersource,
									$nick,
									$wins,
									$loses));
						}
					}
					elseif(strtoupper(@$this->arguments[1]) === 'MOSTWINLOSE')
					{
						foreach($stats[strtolower(substr($this->source, 1))] as $key => $stat)
						{
							list($wins, $loses) = explode(':', $stats[strtolower(substr($this->source, 1))][$key]);
							$winners[strtolower(substr($this->source, 1))][$key] = $wins;
							$losers[strtolower(substr($this->source, 1))][$key] = $loses;
						}
						
						$this->say(sprintf('Stats: Most wins by %s totalling at %s win(s).', 
								array_search(max($winners[strtolower(substr($this->source, 1))]), $winners[strtolower(substr($this->source, 1))]),
								$winners[strtolower(substr($this->source, 1))][array_search(max($winners[strtolower(substr($this->source, 1))]), $winners[strtolower(substr($this->source, 1))])]));
						
						$this->say(sprintf('Stats: Most loses by %s totalling at %s lose(s).', 
								array_search(max($losers[strtolower(substr($this->source, 1))]), $losers[strtolower(substr($this->source, 1))]),
								$losers[strtolower(substr($this->source, 1))][array_search(max($losers[strtolower(substr($this->source, 1))]), $losers[strtolower(substr($this->source, 1))])]));
					}
					else
					{
						if(isset($stats[strtolower(substr($this->source, 1))][strtolower($this->arguments[1])]))
						{
							list($wins, $loses) = explode(':', $stats[strtolower(substr($this->source, 1))][strtolower($this->arguments[1])]);
							$this->say(sprintf('Stats: %s has won %s time(s) and lost %s time(s).', 
									$this->arguments[1],
									$wins,
									$loses));
						}
						else
						{
							$this->say('Stats: That nick hasn\'t been found!');
						}
					}
				}
				else
				{
					$this->say('Stats: There are currently no stats!');
				}
				return;
			break;
		}

		return;
	}
}
