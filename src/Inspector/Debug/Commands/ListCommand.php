<?php
namespace Inspector\Debug\Commands {
	class ListCommand extends \Inspector\Debug\Command {

		const ListNone     = 0x00000000;
		const ListFunction = 0x00000001;
		const ListMethod   = 0x00000010;

		public function match(string $line, array &$argv = []) : bool {
			if (preg_match("~^(l|list)\s(class|method|function|file)\s((([^:]+)::)?(.*))?\s?$~", $line, $argv)) {
				switch ($argv[2]) {
					case "class":
						$argv = [
							"type" => "class",
							"class" => $argv[3],
							"range" => []
						];
					break;

					case "method":
						$argv = [
							"type" => "method",
							"class" => $argv[5],
							"method" => $argv[6],
							"range" => [],
						];
					break;

					case "function":
						$argv = [
							"type" => "function",
							"function" => $argv[3]
						];
					break;	
				}

				return true;
			}
			return false;
		}

		public function __invoke(\Inspector\Debug\Debugger $debugger, 
					 \Inspector\Debug\BreakPoint $bp = null, 
					 \Inspector\InspectorFrame &$frame = null, 
					 array $config = []) : int {
			$inspector = null;

			try {
				switch ($config["type"]) {
					case "class":
						$inspector = new \Inspector\InspectorClass($config["class"]);
					break;

					case "method":
						$inspector = new \Inspector\InspectorMethod($config["class"], $config["method"]);
					break;

					case "function":
						$inspector = new \Inspector\InspectorFunction($config["function"]);
					break;	
				}

				if ($inspector) {
					$debugger->listSource(
						$inspector->getFileName(), 
						$inspector->getStartLine(), 
						$inspector->getEndLine());
				}
			} catch (ReflectionException $ex) {
				switch ($config["type"]) {
					case "function":
						printf("the function %s could not be found\n", $config["function"]);
					break;

					case "class":
						printf("the class %s could not be found\n", $config["class"]);
					break;

					case "method":
						printf("the method %s::%s could not be found\n", $config["class"], $config["method"]);
					break;
				}
			}

			return ListCommand::CommandInteract;
		}
	}
}
