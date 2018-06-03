<?php
namespace Inspector\Debug\Commands {

	use \Inspector\Debug\Command;
	use \Inspector\Debug\BreakPoint;
	use \Inspector\InspectorFrame as Frame;
	use \Inspector\Debug\Parameter;

	class FinishCommand extends Command {

		public function requiresFrame() : bool {
			return true;
		}

		public function __invoke(BreakPoint $bp = null, Frame &$frame = null, Parameter ... $parameters) : int {
			$opline = $frame->getInstruction();
			/* disable breaks for the rest of  this function */
			do {
				if ($bp = $opline->getBreakPoint()) {
					$bp->disable();
				}
			} while ($opline = $opline->getNext());

			/* set break on the next instruction after this frame returns */
			$prev = $frame->getPrevious();

			if ($prev) {
				$opline =
					$prev->getInstruction();
				
				$this->debugger
					->createBreakPoint($opline->getNext(), true, "");
			}

			return ContinueCommand::CommandReturn;
		}
	}
}
