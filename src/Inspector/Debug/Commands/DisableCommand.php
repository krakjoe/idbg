<?php
namespace Inspector\Debug\Commands {

	use \Inspector\Debug\Command;
	use \Inspector\Debug\BreakPoint;
	use \Inspector\InspectorFrame as Frame;
	use \Inspector\Debug\Parameter;

	class DisableCommand extends \Inspector\Debug\Command {

		public function requiresParameters() : ?array {
			return [
				Parameter::Numeric
			];
		}

		public function __invoke(BreakPoint $bp = null, 
					 Frame &$frame = null, 
					 Parameter ... $parameters) : int {
			[$parameter] = $parameters;

			if ($this->debugger->disableBreakPoint($parameter->getValue())) {
				printf("disabled breakpoint #%d\n", $parameter->getValue());
			}

			return DisableCommand::CommandInteract;
		}
	}
}
