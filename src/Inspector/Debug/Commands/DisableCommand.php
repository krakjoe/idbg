<?php
namespace Inspector\Debug\Commands {

	class DisableCommand extends \Inspector\Debug\Command {

		public function match(string $line, array &$argv = []) : bool {
			if (preg_match("~^(disable)\s([0-9]+)$~", $line, $argv)) {
				$argv = [
					"id" => $argv[2]
				];
				return true;
			}
			return false;
		}

		public function __invoke(\Inspector\Debug\Debugger $debugger, 
					 \Inspector\Debug\BreakPoint $bp = null, 
					 \Inspector\InspectorFrame &$frame = null, 
					 array $config = []) : int {
			if ($debugger->disableBreakPoint($config["id"])) {
				printf("disabled breakpoint #%d\n", $config["id"]);
			}
			return DisableCommand::CommandInteract;
		}
	}
}
