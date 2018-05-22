<?php
namespace Inspector\Debug {

	abstract class Command {
		abstract public function match(string $line, array &$argv) : bool;

		abstract public function __invoke(\Inspector\Debug\Debugger $debugger, 
						  \Inspector\Debug\BreakPoint $bp = null, 
						  \Inspector\InspectorFrame &$frame = null, 
						  array $argv = []) : int;

		public function getName() : string { return get_class($this); }

		public function requiresFrame() : bool { return false; }

		const CommandInteract = 0;
		const CommandReturn   = 1;
	}
}
