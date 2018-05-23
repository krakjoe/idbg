<?php
namespace Inspector\Debug\Commands {

	class BreaksCommand extends \Inspector\Debug\Command {

		public function match(string $line, array &$argv = []) : bool {
			return preg_match("~^(breaks)$~", $line);
		}

		public function __invoke(\Inspector\Debug\Debugger $debugger, 
					 \Inspector\Debug\BreakPoint $bp = null, 
					 \Inspector\InspectorFrame &$frame = null, 
					 array $config = []) : int {

			foreach ($debugger->getBreakPoints() as $break) {
				if ($break->isEnabled()) {
					$opline   = $break->getInstruction();
					$function = $opline->getFunction();

					printf("#%d %s#%d (%s) in %s on line %d\n",
						$break->getId(),
						$function instanceof \Inspector\InspectorMethod ?
							sprintf("%s::%s", 
								$function->getDeclaringClass()->getName(), 
								$function->getName()
							) : $function->getName(),
						$opline->getOffset(),
						$opline->getOpcodeName(),
						$function->getFileName(),
						$opline->getLine());
				}
			}

			return BreakCommand::CommandInteract;
		}
	}
}
