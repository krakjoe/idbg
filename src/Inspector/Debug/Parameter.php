<?php
namespace Inspector\Debug {

	class Parameter {
		const Raw                = 0x00000000;
		const File               = 0x00000001;
		const Numeric            = 0x00000010;
		const Method             = 0x00000100;
		const Symbol             = 0x00001000;
		const OffsetLine         = 0x00010000;
		const OffsetOpline       = 0x00100000;
		const Offset             = 0x00110000;
		const OffsetLineRange    = 0x01000000;
		const OffsetOplineRange  = 0x10000000;
		const OffsetRange        = 0x11000000;
		const OffsetAny          = 0x11110000;

		const TypeMask           = 0x0000FFFF;
		const OffsetMask         = 0xFFFF0000;

		private static $rules = [
			"type" => [
				Parameter::Numeric  => "([0-9\.\-\+]+)",
				Parameter::Method   => "([a-zA-Z0-9\_\\\\]+)::([a-zA-Z0-9\_]+)",
				Parameter::Symbol   => "([a-zA-Z0-9\_\\\\]+)",
				Parameter::File     => "file://([^:]+)",
				Parameter::Raw      => "(.*)",
			],
			"groups" => [
				Parameter::Method   => 2,
				Parameter::Numeric  => 1,
				Parameter::Symbol   => 1,
				Parameter::File     => 1,
				Parameter::Raw      => 1,
			],
			"offset" => [
				Parameter::OffsetLine        => "\:([0-9]+)",
				Parameter::OffsetOpline      => "#([0-9]+)",
				Parameter::OffsetLineRange   => ":([0-9]+)\-([0-9]+)",
				Parameter::OffsetOplineRange => "#([0-9]+)\-([0-9]+)"
			]
		];

		public static function getTypeName(int $constant) : string {
			switch ($constant & Parameter::TypeMask) {
				case Parameter::File: return "File";
				case Parameter::Numeric: return "Numeric";
				case Parameter::Method: return "Method";
				case Parameter::Symbol: return "Symbol";
			}
			return "Raw";
		}

		public static function getOffsetTypeName(int $constant) : string {
			switch ($constant & Parameter::OffsetMask) {
				case Parameter::OffsetLine: return "Line";
				case Parameter::OffsetOpline: return "Opline";
				case Parameter::OffsetLineRange: return "LineRange";
				case Parameter::OffsetOplineRange: return "OplineRange";
			}
			return "Unknown";
		}

		public static function explain(int $constant) : string {
			$explain = [
				"types" => [],
				"offset" => [],
			];

			if ($constant == Parameter::Raw) {
				$explain["types"][] = "Raw";
			} else {
				foreach ([
					Parameter::File => "File",
					Parameter::Numeric => "Numeric",
					Parameter::Method => "Method",
					Parameter::Symbol => "Symbol",
					Parameter::Raw => "Raw",
				] as $check => $name) {
					if ($constant & $check) {
						$explain["types"][] = $name;
					}
				}
			}
			
			if ($constant &~ Parameter::TypeMask) {
				foreach([
					Parameter::OffsetAny   => "Offset (Any)",
					Parameter::OffsetRange => "Range (Line or Opline)",
					Parameter::Offset      => "Offset (Line or Opline)",
				] as $check => $name) {
					if ($constant & $check) {
						$explain["offset"][] = $name;
						break;
					}
				}

				if (!count($explain["offset"])) {
					foreach([
						Parameter::OffsetLine  => "Line",
						Parameter::OffsetOpline => "Opline",
						Parameter::OffsetLineRange => "Line Range",
						Parameter::OffsetOplineRange => "Opline Range",
					] as $check => $name) {
						if ($constant & $check) {
							$explain["offset"][] = $name;
						}
					}
				}
			}

			if (count($explain["offset"])) {
				return sprintf(
					"%s with %s",
					implode(" or ", $explain["types"]),
					implode(" or ", $explain["offset"]));
			} else return implode(" or ", $explain["types"]);			
		}

		private function __construct(int $type, string $input, array $values, ?array $offsets = []) {
			$this->type = $type;
			$this->input  = $input;
			$this->values = $values;
			$this->offsets = $offsets;
		}

		public static function parse(array $expect, string $input) : array {
			$parameters = [];
			$start      = 0;
			$end        = strlen($input);
			$idx        = 0;

			while ($start < $end) {
				$parameter = null;
				$expected  = $expect[$idx];

				if ($expected == Parameter::Raw) {
					$parameter = 
						new Parameter(Parameter::Raw, trim(substr($input, $start)), []);
				} else {
					$end = strpos($input, " ", $start);

					$chunk = substr($input, $start, $end ? $end : strlen($input));

					foreach (Parameter::$rules["type"] as $type => $pattern) {

						foreach (Parameter::$rules["offset"] as $offsetType => $offsetPattern) {
							if (preg_match("~^{$pattern}{$offsetPattern}$~si", $chunk, $groups)) {
								$parameter = new Parameter($type | $offsetType, $chunk, 
									array_slice($groups, 1, Parameter::$rules["groups"][$type]),
									array_slice($groups, Parameter::$rules["groups"][$type]+1)
								);
								$type |= $offsetType;
								break 2;
							}
						}

						if (preg_match("~^{$pattern}$~si", $chunk, $groups)) {
							$parameter = new Parameter($type, $chunk, array_slice($groups, 1));
							break;
						}
					}

					if (!$parameter) {
						throw new \RuntimeException(sprintf(
							"failed to parse parameter at %d, expected %s", $idx + 1,
							Parameter::explain($expected)));
					}

					if (!($type & ($expected &~ Parameter::OffsetMask))) {
						throw new \RuntimeException(sprintf(
							"unexpected parameter at %d, expected %s, got %s (%s)", $idx + 1, 
							Parameter::explain($expected),
							Parameter::explain($type),
							$chunk));
					}

					if ($expected &~ Parameter::TypeMask) {
						if (!($type &~ Parameter::TypeMask)) {
							throw new \RuntimeException(sprintf(
								"missing offset for parameter at %d, expected %s, got %s (%s)", $idx + 1, 
								Parameter::explain($expected),
								Parameter::explain($type),
								$chunk));
						} else {
							if (!(($type &~ Parameter::TypeMask) & ($expected &~ Parameter::TypeMask))) {
								throw new \RuntimeException(sprintf(
									"incorrect offset for parameter at %d, expected %s, got %s (%s)", $idx + 1, 
									Parameter::explain($expected),
									Parameter::explain($type),
									$chunk));
							}
						}
					} else {
						if (($type &~ Parameter::TypeMask)) {
							throw new \RuntimeException(sprintf(
								"unexpected offset for parameter at %d, expected %s, got %s (%s)", $idx + 1, 
								Parameter::explain($expected),
								Parameter::explain($type),
								$chunk));
						}
					}
				}

				$parameters[] = $parameter;

				if ($expected == Parameter::Raw) {
					break;
				}

				$start += strlen($chunk);
				$idx++;
			}

			if (count($parameters) < count($expect)) {
				throw new \RuntimeException(sprintf(
					"%d is not enough parameters, expected %d", 
					count($parameters),
					count($expect)));
			}

			return $parameters;
		}

		public function getType() : int {
			return $this->type & Parameter::TypeMask;
		}

		public function getOffsetType() : int {
			return $this->type & Parameter::OffsetMask;
		}

		public function getOffset() {
			switch ($this->type & Parameter::OffsetMask) {
				case Parameter::OffsetOpline:
				case Parameter::OffsetLine: 
					return $this->offsets[0];

				default:
					return $this->offsets;
			}
		}

		public function getOffsetRange() {
			switch ($this->type & Parameter::OffsetMask) {
				case Parameter::OffsetLine:
				case Parameter::OffsetOpline:
					return [$this->offsets[0], $this->offsets[0]];

				default:
					return $this->offset;
			}
		}

		public function getValue(int $idx = -1) {
			switch ($this->type & Parameter::TypeMask) {
				case Parameter::Method: 
					if ($idx == -1)
						return $this->values;
					else return $this->values[$idx];

				default:
					return $this->values[0];
			}
		}

		public function getInput() { return $this->input; }

		public function __debugInfo() {
			if (!$this->getOffsetType()) {
				return [
					"type"       => Parameter::getTypeName($this->type),
					"value"      => $this->getValue(),
					"input"      => $this->getInput(),
				];
			} else {
				return [
					"type"       => Parameter::getTypeName($this->type),
					"value"      => $this->getValue(),
					"offsetType" => Parameter::getOffsetTypeName($this->type),
					"offset"     => $this->getOffset(),
					"input"      => $this->getInput(),
				];
			}
		}

		private $type;
		private $input;
		private $values;
		private $offsets;
	}
}
