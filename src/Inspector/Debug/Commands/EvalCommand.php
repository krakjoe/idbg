<?php
namespace Inspector\Debug\Commands {

	use \Inspector\Debug\Command;
	use \Inspector\Debug\BreakPoint;
	use \Inspector\InspectorFrame as Frame;
	use \Inspector\Debug\Parameter;

	class EvalCommand extends Command {

		public function requiresParameters() : ?array {
			return [
				Parameter::Raw
			];
		}

		public function __invoke(BreakPoint $bp = null, Frame &$frame = null, Parameter ... $parameters) : int {
			$stack = [];

			[$parameter] = $parameters;

			if ($frame) {
				$stack = 
					$frame->getStack();
				extract($stack);
			}

			try {
				$result = eval(sprintf(
					"return %s;",
					$parameter->getInput()
				));
	 		} catch (\Throwable $ex) {
				$this->debugger->exception($ex);
			} finally {
				if ($stack) {
					foreach ($stack as $k => $v) {
						unset($v);
					}

					unset($stack);
				}

				debug_zval_dump($result);
			}

			return RunCommand::CommandInteract;
		}
	}
}
