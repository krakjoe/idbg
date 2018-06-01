<?php
namespace Inspector\Debug\Commands {

	use \Inspector\Debug\Command;
	use \Inspector\Debug\BreakPoint;
	use \Inspector\InspectorFrame as Frame;
	use \Inspector\Debug\Parameter;	

	class FrameCommand extends Command {

		public function requiresParameters() : ?array {
			return [
				Parameter::Numeric
			];
		}

		public function requiresFrame() : bool {
			return true;
		}

		public function __invoke(BreakPoint $bp = null, 
					 Frame &$frame = null, 
					 Parameter ... $parameters) : int {
			[$parameter] = $parameters;

			if (!count($this->frames)) {
				$next = $frame;
				do {
					$this->frames[] = $next;
				} while ($next && $next = $next->getPrevious());
			}

			if ($parameter->getValue() < 0 || $parameter->getValue() > count($this->frames)) {
				printf("frame out of bounds\n");
				return FrameCommand::CommandInteract;
			}

			$frame = $this->frames[$parameter->getValue()];

			if ($parameter->getValue() == 0) {
				$this->frames = [];
			}

			return FrameCommand::CommandInteract;
		}

		private $frames = [];
	}
}
