<?php
namespace Inspector\Debug\Commands {

	use \Inspector\Debug\Command;
	use \Inspector\Debug\BreakPoint;
	use \Inspector\InspectorFrame as Frame;
	use \Inspector\Debug\Parameter;

	class ContinueCommand extends Command {

		public function requiresFrame() : bool {
			return true;
		}

		public function __invoke(BreakPoint $bp = null, Frame &$frame = null, Parameter ... $parameters) : int {
			$function = $frame->getFunction();
			$opline   = $function->getInstruction(0);

			do {
				$brk = $opline->getBreakPoint();

				if ($brk && $brk->isTemporary()) {
					$brk->disable();
				}
			} while ($opline = $opline->getNext());

			return ContinueCommand::CommandReturn;
		}
	}
}
