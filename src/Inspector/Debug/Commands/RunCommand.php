<?php
namespace Inspector\Debug\Commands {

	use \Inspector\Debug\Command;
	use \Inspector\Debug\BreakPoint;
	use \Inspector\InspectorFrame as Frame;
	use \Inspector\Debug\Parameter;

	class RunCommand extends Command {

		public function requiresParameters() : ?array {
			return [
				Parameter::File,
			];
		}

		public function __invoke(BreakPoint $bp = null, Frame &$frame = null, Parameter ... $parameters) : int {
			\Inspector\InspectorFile::purge([
				realpath(sprintf("%s/../../", dirname(__FILE__))),
				realpath(sprintf("%s/../../../../bin", dirname(__FILE__)))]);
			\Inspector\InspectorFunction::purge(["Inspector", "Composer"]);
			\Inspector\InspectorClass::purge(["Inspector", "Composer"]);

			$this->debugger->purgeSources();

			[$file] = $parameters;

			try {
				$result = include(realpath($file->getValue()));
	 		} finally {
				if ($result) {
					debug_zval_dump($result);
				}
			}

			return RunCommand::CommandInteract;
		}
	}
}
