<?php
namespace Inspector\Debug\Commands {

	use \Inspector\Debug\BreakPoint;
	use \Inspector\Debug\Parameter;
	use \Inspector\InspectorFrame as Frame;

	class ListCommand extends \Inspector\Debug\Command {

		public function requiresParameters() : ?array {
			return [
				Parameter::Method | 
				Parameter::Symbol | 
				Parameter::File
			];
		}

		public function __invoke(BreakPoint $bp = null, Frame &$frame = null, Parameter ... $parameters) : int {
			$inspector = null;

			[$parameter] = $parameters;

			try {
				$file = null;

				switch ($parameter->getType()) {
					case Parameter::Symbol:
						if (class_exists($parameter->getValue())) {
							$reflector = 
								new \ReflectionClass($parameter->getValue());
						} else if (function_exists($parameter->getValue())) {
							$reflector =
								new \ReflectionFunction($parameter->getValue());
						} else {
							throw new \ReflectionException(sprintf(
								"could not find class or function %s", $parameter->getValue()));
						}

						if ($reflector) {
							$file = $reflector->getFileName();
							$start = $reflector->getStartLine();
							$end = $reflector->getEndLine();
						}
					break;

					case Parameter::Method:
						$reflector = 
							new \ReflectionMethod(...$parameter->getValue());
						$file = $reflector->getFileName();
						$start = $reflector->getStartLine();
						$end = $reflector->getEndLine();
					break;

					case Parameter::File:
						$file = $parameter->getValue();
						$start = 0;
						$end = -1;
					break;
				}

				if ($file) {
					$this->debugger->listSource($file, $start, $end);
				}
			} catch (\ReflectionException $ex) {
				switch ($parameter->getType()) {
					case Parameter::Symbol:
						printf("the symbol %s could not be found\n", $parameter->getValue());
					break;

					case Parameter::Method:
						vsprintf("the method %s::%s could not be found\n", ...$parameter->getValue());
					break;

					case Parameter::File:
						printf("the file %s could not be found\n", $parameter->getValue());
					break;
				}
			}

			return ListCommand::CommandInteract;
		}
	}
}
