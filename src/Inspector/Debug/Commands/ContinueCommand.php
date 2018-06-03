<?php
namespace Inspector\Debug\Commands {

	use \Inspector\Debug\Command;
	use \Inspector\Debug\BreakPoint;
	use \Inspector\InspectorFrame as Frame;
	use \Inspector\Debug\Parameter;

	class ContinueCommand extends Command {

		public function __invoke(BreakPoint $bp = null, Frame &$frame = null, Parameter ... $parameters) : int {			
			return ContinueCommand::CommandReturn;
		}
	}
}
