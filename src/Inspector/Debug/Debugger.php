<?php
namespace Inspector\Debug {
	use \Inspector\Debug\Command;
	use \Inspector\Debug\BreakPoint;
	use \Inspector\InspectorFrame as Frame;
	use \Inspector\InspectorInstruction;

	class Debugger {

		public function __construct(string $prompt) {
			$this->prompt = $prompt;

		}

		private function prompt() : string {
			printf("%s> ", $this->prompt);

			$line = fgets(STDIN);

			if (!$line || !($line = trim($line))) {
				if ($this->last) {
					return $this->last;
				}
			}

			$this->last = $line;

			return $line;
		}

		public function createBreakPoint(InspectorInstruction $opline, bool $temporary = false) : int {
			$break = new BreakPoint(
				$this, $opline, count($this->breaks)+1, $temporary);

			if ($break->isEnabled()) {
				$this->breaks[] = $break;

				return $break->getId();
			}

			return 0;
		}

		public function removeBreakPoint(int $id) {
			if (!isset($this->breaks[$id - 1])) {
				return false;
			}

			$this->breaks[$id - 1]->disable();

			unset($this->breaks[$id - 1]);

			return true;
		}

		public function enableBreakPoint(int $id) : bool {
			if (!isset($this->breaks[$id - 1])) {
				return false;
			}
			return $this->breaks[$id - 1]->enable();
		}

		public function disableBreakPoint(int $id) : bool {
			if (!isset($this->breaks[$id - 1])) {
				return false;
			}

			return $this->breaks[$id - 1]->disable();
		}

		public function getBreakPoints() {
			return $this->breaks;
		}

		public function interact(BreakPoint $bp = null, Frame &$frame = null) {
			try {
				$line = $this->prompt();

				if (!$line) {
					return;
				}

				$argv = [];
				$command = $this->findCommand($line, $argv);

				if ($command->requiresFrame() && !$frame) {
					printf("%s requires a frame\n", 
						$command->getName());
				} else {
					if ($command($this, $bp, $frame, $argv) == Command::CommandReturn) {
						return;
					}
				}
			} catch (\Throwable $ex) {
				$this->exception($ex);
			}

			$this->interact($bp, $frame);
		}

		public function exception($ex) {
			echo (string) $ex . "\n";
		}

		public function	hit(BreakPoint $bp, Frame $frame) {
			$function = $frame->getFunction();
			$opline = $frame->getInstruction();

			printf("hit %s at %s#%d (%s) in %s on line %d\n", 
				$bp->isTemporary() ? 
					"next" : 
					sprintf("breakpoint #%d", $bp->getId()),
				($name = $function->getName()) ? 
					$name : "main()",
				$opline->getOffset(),
				$opline->getOpcodeName(),
				$function->getFileName(),			
				$opline->getLine());

			$this->listOpline($opline);
			$this->interact($bp, $frame);
		}

		public function addCommand(Command $command) {
			$this->commands[get_class($command)] = $command;
		}

		public function listSource(string $file, int $start = 0, int $end = -1, int $highlight = -1) {
			if (!isset($this->sources[$file])) {
				$this->sources[$file] = file($file);
			}

			if (!isset($this->sources[$file])) {
				return;
			}

			$source = $this->sources[$file];
			$limit  = count($source);
			if ($end == -1) {
				$end = $limit;
			}

			for ($line = $start; $line < $end; $line++) {
				if ($line >= $limit)
					break;


				printf("%s%s%s", 
					($highlight > 0) ? 
						$line == $highlight ?
								sprintf("=> :%03d ", $line) : 
								sprintf("   :%03d ", $line) :
						sprintf(":%03d ", $line),
					rtrim($source[$line - 1]), PHP_EOL);
			}
		}

		private function listOpline(InspectorInstruction $opline) {
			$function     = $opline->getFunction();

			$highlight = $opline->getLine();
			$start = $highlight - 3;
			$end   = $highlight + 3;

			$this->listSource($function->getFileName(), $start, $end, $highlight);
		}

		private function findCommand(string $line, array &$argv) : Command {
			foreach ($this->commands as $command) {
				if ($command->match($line, $argv)) {
					return $command;
				}
			}

			return new class extends Command {
				public function match(string $line, array &$argv) : bool 
				{
					return true;
				}

				public function __invoke(Debugger $debugger, BreakPoint $bp = null, Frame &$frame = null, array $argv = []) : int
				{
					printf("invalid command\n");

					return self::CommandInteract;
				}
			};
		}

		private $prompt;
		private $commands = [];
		private $breaks = [];
		private $sources = [];
	}
}
