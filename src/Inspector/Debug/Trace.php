<?php
namespace Inspector\Debug {
	use Inspector\InspectorFrame;
	use Inspector\InspectorMethod;

	class Trace {

		public function __construct(InspectorFrame $frame) {
			do {
				$scope = $frame->getFunction();
				if (!$scope) {
					continue;
				}
				if ($scope instanceof InspectorMethod) {
					$class = $scope->getDeclaringClass();
				}
				$opline = $frame->getInstruction();

				$this->trace[] = [
					"scope" => $scope->getName(),
					"class" => $class ? $class->getName() : null,
					"file" => $scope->getFileName(),
					"line" => $opline->getLine(),
					"params" => $this->parameterize(
							$frame->getParameters())
				];

				if (!$scope->getName()) {
					break;
				}
			} while ($frame = $frame->getPrevious());
		}

		public function parameterize(array $parameters) {
			$parameterized = [];

			foreach ($parameters as $parameter) {
				switch (gettype($parameter)) {
					case "object":
						$parameterized[] = sprintf("Object(%s)", get_class($parameter));
					break;

					case "array":
						$parameterized[] = sprintf("array(%d)", count($parameter));
					break;

					case "integer":
						$parameterized[] = sprintf("%d", $parameter);
					break;

					case "float":
						$parameterized[] = sprintf("%f", $parameter);
					break;

					default:
						$parameterized[] = "string";
				}
			}

			return sprintf("(%s)", implode(", ", $parameterized));
		}

		public function getTrace() {
			return $this->trace;
		}

		public function __toString() {
			$trace = [];

			foreach ($this->trace as $idx => $frame) {
				$line = [];

				$line[] = sprintf("#%d ", $idx);
				if ($frame["scope"]) {
					if ($frame["class"]) {
						$line[] = sprintf("%s::%s", $frame["class"], $frame["scope"]);
					} else $line[] = sprintf("%s", $frame["scope"]);
				} else $line[] = sprintf("main");
				$line[] = sprintf("%s", $frame["params"]);
				$line[] = sprintf(" in %s on line %d", $frame["file"], $frame["line"]);

				$trace[] = implode(null, $line);
			}

			return implode(PHP_EOL, $trace);
		}

		private $trace;
	}
}
