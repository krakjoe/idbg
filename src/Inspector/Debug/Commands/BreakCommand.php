<?php
namespace Inspector\Debug\Commands {

	use \Inspector\Debug\Command;
	use \Inspector\Debug\BreakPoint;
	use \Inspector\InspectorFrame as Frame;
	use \Inspector\Debug\Parameter;

	use \Inspector\InspectorFile;
	use \Inspector\InspectorClass;
	use \Inspector\InspectorFunction;

	trait BreakResolver {

		public function onResolve() {
			if ($this->parameter->getType() == Parameter::Method) {
				$inspector = 
					$this->getMethod($this->parameter->getValue(1));
			} else $inspector = $this;

			switch ($this->parameter->getOffsetType()) {
				case Parameter::OffsetLine:
					$opline = $inspector->getInstruction(0);

					while ($opline->getLine() < $this->parameter->getOffset()) {
						$opline = $opline->getNext();
					}
				break;

				case Parameter::OffsetOpline:
					$opline = $inspector->getInstruction(
						$this->parameter->getOffset());
				break;

				default:
					$opline = $inspector->getEntryInstruction();
			}

			if (!$opline) {
				return;
			}

			$this->debugger->createBreakPoint($opline, false, "");
		} 
	}

	class BreakCommand extends Command {

		public function requiresParameters() : ?array {
			return [
				Parameter::File | Parameter::Symbol | Parameter::Method | Parameter::Offset,
			];
		}

		public function __invoke(BreakPoint $bp = null, Frame &$frame = null, Parameter ... $parameters) : int {
			[$parameter] = $parameters;

			if ($parameter->getType() == Parameter::Method) {
				$this->inspector = new class(
					$parameter->getValue(0),
					$parameter,
					$this->debugger) extends InspectorClass {

					use BreakResolver;

					public function __construct($class, $parameter, $debugger) {
						parent::__construct($class);
						$this->parameter = $parameter;
						$this->debugger = $debugger;
					}
				};
			} else if ($parameter->getType() == Parameter::Symbol) {
				$this->inspector = new class(
					$parameter->getValue(),
					$parameter,
					$this->debugger) extends InspectorFunction {

					use BreakResolver;

					public function __construct($class, $parameter, $debugger) {
						parent::__construct($class);
						$this->parameter = $parameter;
						$this->debugger = $debugger;
					}
				};
			} else if ($parameter->getType() == Parameter::File) {
				$this->inspector = new class(
					realpath($parameter->getValue()),
					$parameter,
					$this->debugger) extends InspectorFile {

					use BreakResolver;

					public function __construct($class, $parameter, $debugger) {
						parent::__construct($class);
						$this->parameter = $parameter;
						$this->debugger = $debugger;
					}
				};
			} else {
				var_dump($parameter->getType());
			}

			return BreakCommand::CommandInteract;
		}
	}
}
