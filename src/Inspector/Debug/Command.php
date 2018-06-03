<?php
namespace Inspector\Debug {

	use \Inspector\Debug\Parameter;
	use \Inspector\InspectorFrame as Frame;

	abstract class Command {
		public function __construct(Debugger $debugger) {
			$this->debugger = $debugger;
		}

		abstract public function __invoke(BreakPoint $bp = null, 
						  Frame &$frame = null, 
						  Parameter ... $parameter) : int;
		
		public function getName() : string {
			$components = preg_split(
				"~\\\\~", get_class($this));

			return strtolower(
				str_ireplace(
					"command", "", end($components))); 
		}

		public function getAbbreviations() : array {
			return [
				strtolower(substr($this->getName(), 0, 2))
			];
		}

		public function getHelp() {
			if (($parameters = $this->requiresParameters())) {
				$help = [];
				foreach ($parameters as $parameter) {
					$help[] = Parameter::explain($parameter);
				}
				return implode(", ", $help);
			}
		}

		public function requiresParameters() : ?array { return null; }
		public function requiresFrame() : bool { return false; }

		const CommandInteract = 0;
		const CommandReturn   = 1;

		protected $debugger;
	}
}
