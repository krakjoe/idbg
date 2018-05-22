<?php
namespace Inspector\Debug\Commands {
	
	class DeleteCommand extends \Inspector\Debug\Command {

		public function match(string $line, array &$argv = []) : bool {
			if (preg_match("~^(del|delete)\s([0-9]+)$~", $line, $argv)) {
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
			if ($debugger->removeBreakPoint($config["id"])) {
				printf("removed breakpoint #%d\n", $config["id"]);
			}
			return DeleteCommand::CommandInteract;
		}
	}

}
