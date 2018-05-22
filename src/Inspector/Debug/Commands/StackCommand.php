<?php
namespace Inspector\Debug\Commands {

	class StackCommand extends \Inspector\Debug\Command {

		public function match(string $line, array &$argv = []) : bool {
			return preg_match("~^(s|stack)$~", $line);
		}

		public function requiresFrame() : bool {
			return true;
		}

		public function __invoke(\Inspector\Debug\Debugger $debugger, 
					 \Inspector\Debug\BreakPoint $bp = null, 
					 \Inspector\InspectorFrame &$frame = null, 
					 array $argv = []) : int {
			$stack = $frame->getStack();
			foreach ($stack as $name => $value) {	
				$type = gettype($value);

				printf("% -30s\t", $name);
				switch ($type) {
					case "object":
					case "array":
						printf("%s", $type);
						if ($type == "array") {
							printf("(%d)", count($value));
						} else printf("(%s)", get_class($value));
						printf("\n");
					break;

					default:
						if ($type == "string" && strlen($string) > 30) {
							printf("string(%d)\n", strlen($string));
							continue;
						}

						debug_zval_dump($value);
				}
			}
			return self::CommandInteract;
		}
	}
}
