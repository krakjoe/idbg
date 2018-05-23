<?php
namespace Inspector\Debug {
	use Inspector\InspectorFrame;
	use Inspector\InspectorBreakPoint;
	use Inspector\InspectorInstruction;

	class BreakPoint extends InspectorBreakPoint {

		public function __construct(Debugger $debugger, InspectorInstruction $opline, int $idx, bool $temporary = false, string $export = null) {
			$this->debugger = $debugger;
			$this->idx = $idx;
			$this->temporary = $temporary;
			$this->export = $export;

			parent::__construct($opline);
		}

		public function getId() {
			return $this->idx;
		}

		public function isTemporary() : bool {
			return $this->temporary;
		}

		public function hit(InspectorFrame $frame) {
			$this->debugger
				->hit($this, $frame);

			if ($this->temporary) {
				$this->debugger
					->disableBreakPoint($this->idx);
			}
		}

		public function export() {
			return $this->export;
		}

		private $debugger;
		private $idx;
		private $temporary;
		private $export;
	}
}
