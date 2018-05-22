<?php
namespace Inspector\Debug\Commands {
	
	class ContinueCommand extends \Inspector\Debug\Command {

		public function match(string $line, array &$argv = []) : bool {
			return preg_match("~^(c|continue)$~", $line);
		}

		public function __invoke(\Inspector\Debug\Debugger $debugger, 
					 \Inspector\Debug\BreakPoint $bp = null, 
					 \Inspector\InspectorFrame &$frame = null, 
					 array $argv = []) : int {
			return ContinueCommand::CommandReturn;
		}
	}
}
