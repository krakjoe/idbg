<?php
namespace Inspector\Debug {
	use Inspector\InspectorFrame;
	use Inspector\InspectorBreakPoint;
	use Inspector\InspectorInstruction;

	class ExceptionBreakPoint extends BreakPoint {

		public function __construct(Debugger $debugger, InspectorInstruction $opline, int $idx, bool $temporary = false, \Throwable $exception) {
			try {
				parent::__construct($debugger, $opline, $idx, $temporary);
			} finally {
				$this->exception = $exception;
				$this->disable();
			}
		}

		public function getException() {
			return $this->exception;
		}

		private $exception;
	}
}
