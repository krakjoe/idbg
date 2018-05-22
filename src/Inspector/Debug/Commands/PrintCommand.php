<?php
namespace Inspector\Debug\Commands {
	
	class PrintCommand extends \Inspector\Debug\Command {

		public function match(string $line, array &$argv = []) : bool {
			if (preg_match("~^(p|print)\s+(.*)$~si", $line, $argv)) {
				$argv = [
					"symbol" => $argv[2]
				];
				return true;
			}
			return false;
		}

		public function requiresFrame() : bool {
			return true;
		}

		public function __invoke(\Inspector\Debug\Debugger $debugger, 
					 \Inspector\Debug\BreakPoint $bp = null, 
					 \Inspector\InspectorFrame &$frame = null, 
					 array $argv = []) : int {
			if (is_numeric($configure["symbol"])) {
				debug_zval_dump(
					$frame->getVariable($configure["symbol"]));

				return self::CommandInteract;
			}

			$stack = $frame->getStack();

			if (!$stack || !isset($stack[$configure["symbol"]])) {
				printf("%s does not exist in current scope\n", 
					$configure["symbol"]);
				return self::CommandInteract;
			}

			debug_zval_dump($stack[$configure["symbol"]]);
			return self::CommandInteract;
		}
	}
}
