<?php
namespace Inspector\Debug\Commands {

	class QuitCommand extends \Inspector\Debug\Command {

		public function match(string $line, array &$argv = []) : bool {
			return preg_match("~^(q|quit)$~", $line);
		}

		public function __invoke(\Inspector\Debug\Debugger $debugger, 
					 \Inspector\Debug\BreakPoint $bp = null, 
					 \Inspector\InspectorFrame &$frame = null, 
					 array $argv = []) : int {
			exit(0);
		}
	}
}
