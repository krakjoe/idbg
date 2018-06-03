<?php
namespace Inspector\Debug\Commands {
	
	use \Inspector\Debug\Command;
	use \Inspector\Debug\BreakPoint;
	use \Inspector\InspectorFrame as Frame;
	use \Inspector\Debug\Parameter;

	class HelpCommand extends Command {

		public function __invoke(BreakPoint $bp = null, Frame &$frame = null, Parameter ... $parameters) : int {
			printf("--------------------------------------------------------------------------%s", PHP_EOL);
			printf("%-10s%-14s%s%s", 
				"Command", "Abbreviations", "Parameters", PHP_EOL);
			printf("--------------------------------------------------------------------------%s", PHP_EOL);
			foreach ($this->debugger->getCommands() as $name => $command) {
				$abbreviations = $command->getAbbreviations();

				printf("%-10s%-14s%s%s", 
					$command->getName(), 
					$abbreviations ? 
						sprintf("(%s)", implode(", ", $command->getAbbreviations())) : 
						null,
					$command->getHelp(),
					PHP_EOL);
			}

			printf("%sTypes%s", PHP_EOL, PHP_EOL);
			printf("--------------------------------------------------------------------------%s", PHP_EOL);
			printf("%-10sA valid integer or floating point value%s", "Numeric", PHP_EOL);
			printf("%-10sA valid class, function, or variable name%s", "Symbol", PHP_EOL);
			printf("%-10sA valid path beginning with file://%s", "File", PHP_EOL);
			printf("%-10sA valid method name in the form class::method%s", "Method", PHP_EOL);
			printf("%sOffsets%s", PHP_EOL, PHP_EOL);
			printf("--------------------------------------------------------------------------%s", PHP_EOL);
			printf("%-14sA valid integer prefixed with :, example symbol:2%s", "Line", PHP_EOL);
			printf("%-14sA valid integer prefixed with #, example symbol#2%s", "Opline", PHP_EOL);
			printf("%-14sA range of integers prefixed with :, example symbol:2-3%s", "Line Range", PHP_EOL);
			printf("%-14sA range of integers prefixed with #, example symbol#2-3%s", "Opline Range", PHP_EOL);

			return NextCommand::CommandInteract;
		}
	}
}
