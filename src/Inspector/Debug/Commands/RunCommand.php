<?php
namespace Inspector\Debug\Commands {

	class RunCommand extends \Inspector\Debug\Command {

		public function match(string $line, array &$argv = []) : bool {
			if (preg_match("~^(r|run)\s(.*)$~", $line, $argv)) {
				$argv = [
					"file" => $argv[2]
				];
				return true;
			}
			return false;
		}

		public function __invoke(\Inspector\Debug\Debugger $debugger, 
					 \Inspector\Debug\BreakPoint $bp = null, 
					 \Inspector\InspectorFrame &$frame = null, 
					 array $config = []) : int {
			try {
				$result = include($config["file"]);
	 		} catch (\Throwable $ex) {
				$debugger->exception($ex);
			} finally {
				if ($result) {
					debug_zval_dump($result);
				}
			}

			return RunCommand::CommandInteract;
		}
	}
}
