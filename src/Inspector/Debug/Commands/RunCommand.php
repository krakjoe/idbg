<?php
namespace Inspector\Debug\Commands {

	class RunCommand extends \Inspector\Debug\Command {

		public function match(string $line, array &$argv = []) : bool {
			if (preg_match("~^(r|run)\s(.*)$~", $line, $argv)) {
				$argv = [
					"code" => $argv[2]
				];
				return true;
			}
			return false;
		}

		public function __invoke(\Inspector\Debug\Debugger $debugger, 
					 \Inspector\Debug\BreakPoint $bp = null, 
					 \Inspector\InspectorFrame &$frame = null, 
					 array $config = []) : int {
			$stack = [];

			if ($frame) {
				$stack = 
					$frame->getStack();
				extract($stack);
			}

			try {
				$result = eval("return {$config["code"]};");
	 		} catch (\Throwable $ex) {
				$debugger->exception($ex);
			} finally {
				if ($stack) {
					foreach ($stack as $k => $v) {
						unset($v);
					}

					unset($stack);
				}

				if ($result) {
					debug_zval_dump($result);
				}
			}

			return RunCommand::CommandInteract;
		}
	}
}
