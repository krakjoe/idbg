<?php
namespace Inspector\Debug\Commands {

	use \Inspector\Debug\Command;
	use \Inspector\Debug\BreakPoint;
	use \Inspector\InspectorFrame as Frame;
	use \Inspector\Debug\Parameter;
	use function \Inspector\addressof;

	class StackCommand extends Command {

		public function requiresFrame() : bool {
			return true;
		}

		public function __invoke(BreakPoint $bp = null, Frame &$frame = null, Parameter ... $parameters) : int {
			$stack = $frame->getStack();
			foreach ($stack as $name => $value) {
				$type = gettype($value);

				printf("% -30s\t0x%x\t", $name, addressof($value));

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
						if ($type == "string" && strlen($value) > 30) {
							printf("string(%d)\n", strlen($value));
							continue;
						}

						debug_zval_dump($value);
				}
			}
			return self::CommandInteract;
		}
	}
}
