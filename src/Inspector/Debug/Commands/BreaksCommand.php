<?php
namespace Inspector\Debug\Commands {

	use \Inspector\Debug\Command;
	use \Inspector\Debug\BreakPoint;
	use \Inspector\InspectorFrame as Frame;
	use \Inspector\Debug\Parameter;

	class BreaksCommand extends Command {

		public function getAbbreviations() : array { return []; }

		public function __invoke(BreakPoint $bp = null, Frame &$frame = null, Parameter ... $parameters) : int {
			foreach ($this->debugger->getBreakPoints() as $break) {
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
