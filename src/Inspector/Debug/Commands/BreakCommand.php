<?php
namespace Inspector\Debug\Commands {

	class BreakCommand extends \Inspector\Debug\Command {

		const BreakNone     = 0x00000000;
		const BreakFunction = 0x00000001;
		const BreakMethod   = 0x00000010;
		const BreakLine     = 0x00000100;
		const BreakOpline   = 0x00001000;

		public function match(string $line, array &$argv = []) : bool {
			if (preg_match("~^(b|break)\s((([a-zA-Z0-9\_]+)::)?([a-zA-Z0-9\_]+))?\s?(([:|#])([0-9]+))?$~", $line, $argv)) {

				$configure = [
					"breakPointType" => 0,
					"input" => $argv[2],
					"class" => $argv[4],
					"function" => $argv[5],
					"offsetType" => $argv[7],
					"offset" => $argv[8],
				];

				if (!empty($configure["class"])) {
					$configure["breakPointType"] = BreakCommand::BreakMethod;
				} else $configure["breakPointType"] = BreakCommand::BreakFunction;

				if (empty($configure["function"])) {
					return false;
				}

				switch ($configure["offsetType"]) {
					case ":":
						$configure["breakPointType"] |= BreakCommand::BreakLine;
					break;

					case "#":
						$configure["breakPointType"] |= BreakCommand::BreakOpline;
					break;
				}

				$argv = $configure;

				return true;
			}
			return false;
		}

		public function __invoke(\Inspector\Debug\Debugger $debugger, 
					 \Inspector\Debug\BreakPoint $bp = null, 
					 \Inspector\InspectorFrame &$frame = null, 
					 array $config = []) : int {

			if ($config["breakPointType"] & BreakCommand::BreakMethod) {
				$inspector = new \Inspector\InspectorMethod(
					$config["class"], $config["function"]);
			} else {
				$inspector = new \Inspector\InspectorFunction($config["function"]);
			}
			
			if ($config["breakPointType"] & BreakCommand::BreakLine) {
				$opline = $inspector->getInstruction();
				$offset = $inspector->getStartLine();

				while ($opline && $opline->getLine() - $offset < $config["offset"]) {
					$opline = $opline->getNext();
				}
			} else if ($config["breakPointType"] & BreakCommand::BreakOpline) {
				$opline = $inspector->getInstruction($config["offset"]);
			} else {
				$opline = $inspector->getEntryInstruction();
			}

			if (!$opline) {
				printf("failed to find breakpoint %s\n", $config["input"]);
				return BreakCommand::CommandInteract;
			}

			$idx = $debugger->createBreakPoint($opline);

			if ($idx > 0) {
				printf("created breakpoint #%d\n", $idx);
			} else printf("cannot create breakpoint (exists)\n");

			return BreakCommand::CommandInteract;
		}
	}
}
