<?php
namespace Inspector\Debug\Commands {

	use \Inspector\Debug\Command;
	use \Inspector\Debug\BreakPoint;
	use \Inspector\InspectorFrame as Frame;
	use \Inspector\Debug\Parameter;	

	class PrintCommand extends Command {

		public function requiresParameters() : ?array {
			return [
				Parameter::Symbol | Parameter::Numeric
			];
		}

		public function requiresFrame() : bool {
			return true;
		}

		public function __invoke(BreakPoint $bp = null, 
					 Frame &$frame = null, 
					 Parameter ... $parameters) : int {
			[$parameter] = $parameters;

			if ($parameter->getType() == Parameter::Numeric) {
				debug_zval_dump(
					$frame->getVariable($parameter->getSymbol()));

				return self::CommandInteract;
			}

			$stack = $frame->getStack();

			if (!$stack || !isset($stack[$parameter->getValue()])) {
				printf("%s does not exist in current scope\n", 
					$parameter->getValue());
				return self::CommandInteract;
			}

			debug_zval_dump($stack[$parameter->getValue()]);
			return self::CommandInteract;
		}
	}
}
