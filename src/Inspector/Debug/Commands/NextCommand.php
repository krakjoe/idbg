<?php
namespace Inspector\Debug\Commands {
	
	use \Inspector\Debug\Command;
	use \Inspector\Debug\BreakPoint;
	use \Inspector\InspectorFrame as Frame;
	use \Inspector\Debug\Parameter;

	use \Inspector\InspectorClass;
	use \Inspector\InspectorOperand as Operand;
	use \Inspector\InspectorInstruction as Instruction;

	class NextCommand extends Command {

		public function requiresFrame() : bool {
			return true;
		}

		public function __invoke(BreakPoint $bp = null, Frame &$frame = null, Parameter ... $parameters) : int {
			$inspector = $frame->getFunction();
			$opline = $frame->getInstruction();
			$next = $opline->getNext();

			switch ($opline->getOpcode()) {
				case Instruction::ZEND_NEW:
					$class = 
						$opline->getOperand(Operand::OP1)->getValue($frame);

					if (is_string($class)) {
						$class = new InspectorClass($class);
					}

					if (!$class->getConstructor()) { /* no constructor */
						if ($opline->getExtendedValue() == 0 && /* no args */
						    $next->getOpcode() == Instruction::ZEND_DO_FCALL) { /* to constructor */
							$next = $next->getNext(); /* so skip it */
						}
					}
				break;

				case Instruction::ZEND_JMPZNZ:
					$ext = $inspector->getInstruction($opline->getExtendedValue());

					if (($bp = $ext->getBreakPoint())) {
						$bp->enable();
					} else {
						$debugger->createBreakPoint($ext, true);
					}

					$next = $inspector->getInstruction(
						$opline->getOperand(Operand::OP2)->getNumber());
				break;

				case Instruction::ZEND_JMP:
				case Instruction::ZEND_JMPZ:
				case Instruction::ZEND_JMPNZ:
				case Instruction::ZEND_JMPZ_EX:
				case Instruction::ZEND_JMPNZ_EX:
				case Instruction::ZEND_FE_RESET_R:
				case Instruction::ZEND_FE_RESET_RW:
				case Instruction::ZEND_JMP_SET:
				case Instruction::ZEND_COALESCE:
				case Instruction::ZEND_FAST_CALL:
				case Instruction::ZEND_ASSERT_CHECK:
					$ext = $inspector->getInstruction(
						$opline->getOperand(Operand::OP2)->getNumber());

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
					return NextCommand::CommandReturn;
				} else {
					$bp->enable();
					return NextCommand::CommandReturn;
				}
			}

			printf("end of opline\n");
			return NextCommand::CommandInteract;
		}
	}
}
