<?php
namespace Inspector\Debug\Commands {

	class UnknownCommand extends \Inspector\Debug\Command {

		public function __invoke(\Inspector\Debug\BreakPoint $bp = null, 
					 \Inspector\InspectorFrame &$frame = null, 
					 \Inspector\Debug\Parameter ... $parameters) : int {

			printf("invalid command\n");

			return UnknownCommand::CommandInteract;
		}
	}
}
