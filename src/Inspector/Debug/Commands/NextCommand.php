<?php
namespace Inspector\Debug\Commands {

	use Inspector\InspectorOperand;
	use Inspector\InspectorInstruction;

	class NextCommand extends \Inspector\Debug\Command {

		public function match(string $line, array &$argv) : bool {
			return preg_match("~^(n|next)$~", $line);
		}

		public function requiresFrame() : bool {
			return true;
		}

		public function __invoke(\Inspector\Debug\Debugger $debugger, 
					 \Inspector\Debug\BreakPoint $bp = null, 
					 \Inspector\InspectorFrame &$frame = null, 
					 array $argv = []) : int {
			$inspector = $frame->getFunction();
			$opline = $frame->getInstruction();
			$next = $opline->getNext();

			switch ($opline->getOpcode()) {
				case InspectorInstruction::ZEND_NEW:
					$class = 
						$opline->getOperand(InspectorOperand::OP1)->getValue($frame);

					if (is_string($class)) {
						$class = new \Inspector\InspectorClass($class);
					}

					if (!$class->getConstructor()) { /* no constructor */
						if ($opline->getExtendedValue() == 0 && /* no args */
						    $next->getOpcode() == InspectorInstruction::ZEND_DO_FCALL) { /* to constructor */
							$next = $next->getNext(); /* so skip it */
						}
					}
				break;

				case InspectorInstruction::ZEND_JMPZNZ:
					$ext = $inspector->getInstruction($opline->getExtendedValue());

					if (($bp = $ext->getBreakPoint())) {
						$bp->enable();
					} else {
						$debugger->createBreakPoint($ext, true);
					}

					$next = $inspector->getInstruction(
						$opline->getOperand(InspectorOperand::OP2)->getNumber());
				break;

				case InspectorInstruction::ZEND_JMP:
				case InspectorInstruction::ZEND_JMPZ:
				case InspectorInstruction::ZEND_JMPNZ:
				case InspectorInstruction::ZEND_JMPZ_EX:
				case InspectorInstruction::ZEND_JMPNZ_EX:
				case InspectorInstruction::ZEND_FE_RESET_R:
				case InspectorInstruction::ZEND_FE_RESET_RW:
				case InspectorInstruction::ZEND_JMP_SET:
				case InspectorInstruction::ZEND_COALESCE:
				case InspectorInstruction::ZEND_FAST_CALL:
				case InspectorInstruction::ZEND_ASSERT_CHECK:
					$ext = $inspector->getInstruction(
						$opline->getOperand(InspectorOperand::OP2)->getNumber());

					if (($bp = $ext->getBreakPoint())) {
						$bp->enable();
					} else {
						$debugger->createBreakPoint($ext, true);
					}
				break;

				default: if (!$next) {
					$previous = $frame->getPrevious();
					if ($previous) {
						$opline = $previous->getInstruction();
						if ($opline) {
							$next = $opline->getNext();
						}
					}
				}	
			}
			
			if ($next) {
				$idx = ($bp = $next->getBreakPoint()) ? 
					0 : $debugger->createBreakPoint($next, true);

				if ($idx > 0) {
					return self::DebuggerCommandReturn;
				} else {
					$bp->enable();
					return self::DebuggerCommandReturn;
				}
			}

			printf("end of opline\n");
			return self::DebuggerCommandInteract;
		}
	}
}
