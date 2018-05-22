<?php
namespace Inspector\Debug\Commands {

	class FrameCommand extends \Inspector\Debug\Command {

		public function match(string $line, array &$argv = []) : bool {
			if (preg_match("~^(f|frame)\s([0-9]+)$~", $line, $argv)) {
				$argv = [
					"depth" => $argv[2]
				];
				return true;
			}
			return false;
		}

		public function requiresFrame() : bool {
			return true;
		}

		public function __invoke(\Inspector\Debug\Debugger $debugger, 
					 \Inspector\Debug\BreakPoint $bp = null, 
					 \Inspector\InspectorFrame &$frame = null, 
					 array $config = []) : int {
			if (!count($this->frames)) {
				$next = $frame;
				do {
					$this->frames[] = $next;
				} while ($next && $next = $next->getPrevious());
			}

			if ($config["depth"] < 0 || $config["depth"] > count($this->frames)) {
				printf("frame out of bounds\n");
				return FrameCommand::CommandInteract;
			}

			$frame = $this->frames[$config["depth"]];

			if ($config["depth"] == 0) {
				$this->frames = [];
			}

			return FrameCommand::CommandInteract;
		}

		private $frames = [];
	}
}
