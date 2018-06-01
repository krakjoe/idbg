<?php
namespace Inspector\Debug\Commands {

	use \Inspector\Debug\Command;
	use \Inspector\Debug\BreakPoint;
	use \Inspector\InspectorFrame as Frame;
	use \Inspector\Debug\Parameter;

	class DeleteCommand extends Command {

		public function requiresParameters() : ?array {
			return [
				Parameter::Numeric
			];
		}

		public function __invoke(BreakPoint $bp = null, Frame &$frame = null, Parameter ... $parameters) : int {
			if ($this->debugger->removeBreakPoint($parameter->getValue)) {
				printf("removed breakpoint #%d\n", $parameter->getValue());
			}
			return DeleteCommand::CommandInteract;
		}
	}

}
