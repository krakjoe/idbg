<?php
namespace Inspector\Debug\Commands {

	class TraceCommand extends \Inspector\Debug\Command {

		public function match(string $line, array &$argv = []) : bool {
			return preg_match("~^(bt|back|backtrace)$~", $line);
		}

		public function requiresFrame() : bool {
			return true;
		}

		public function __invoke(\Inspector\Debug\Debugger $debugger, 
					 \Inspector\Debug\BreakPoint $bp = null, 
					 \Inspector\InspectorFrame &$frame = null, 
					 array $config = []) : int {
			printf("%s\n", (string) new \Inspector\Debug\Trace($frame));

			return TraceCommand::CommandInteract;
		}
	}
}
