<?php
namespace Inspector\Debug\Commands {

	use \Inspector\Debug\Command;
	use \Inspector\Debug\BreakPoint;
	use \Inspector\InspectorFrame as Frame;
	use \Inspector\Debug\Parameter;

	class TraceCommand extends Command {

		public function requiresFrame() : bool {
			return true;
		}

		public function __invoke(BreakPoint $bp = null, Frame &$frame = null, Parameter ... $parameters) : int {
			printf("%s\n", (string) new \Inspector\Debug\Trace($frame));

			return TraceCommand::CommandInteract;
		}
	}
}
