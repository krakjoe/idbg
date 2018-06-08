<?php
namespace Inspector\Debug {

	use \Inspector\InspectorFrame as Frame;
	use \Inspector\InspectorInstruction;
	
	class Debugger {

		const Version = "0.0.1dev";

		public function __construct(string $prompt) {
			$this->prompt = $prompt;

			\Inspector\InspectorBreakPoint::onException(function(Frame $frame, \Throwable $exception) {
				$bp = new ExceptionBreakPoint(
					$this, 
					$frame->getInstruction(), 
					count($this->breaks)+1, false, $exception);

				$function = $frame->getFunction();
				$opline = $frame->getInstruction();

				printf("[%08x] exception at %s#%d (%s) in %s on line %d\n", 
					$opline->getAddress(),
					($name = $function->getName()) ? 
						$name : "main()",
					$opline->getOffset(),
					$opline->getOpcodeName(),
					$function->getFileName(),		
					$opline->getLine());

				$this->listOpline($opline);
				$this->interact($bp, $frame);
			});
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

				$parameters = [];

				if ($command = $this->findCommand($line, $parameters)) {
					if ($command->requiresFrame() && !$frame) {
						printf("%s requires a frame\n", 
							$command->getName());
					} else {
						if ($command($bp, $frame, ... $parameters) == Command::CommandReturn) {
							return;
						}
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

			printf("[%08x] hit %s at %s#%d (%s) in %s on line %d\n", 
				$opline->getAddress(),
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
			$this->commands[$command->getName()] = $command;
		}
		
		public function getCommands() : array {
			return $this->commands;
		}

		public function purgeSources() {
			$this->sources = [];
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
			if ($start < 1) {
				$end += +$start;
				$start = 1;
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

		private function findCommand(string $line, array &$parameters) : ?Command {
			$end        = strpos($line, " ");
			$name       = trim(substr($line, 0, $end ? $end : strlen($line)));
			$selected   = null;

			foreach ($this->commands as $command) {
				if (strncasecmp($name, $command->getName(), strlen($name)) == 0) {
					$selected = $command;
					break;
				}

				foreach ($command->getAbbreviations() as $abbreviation) {
					if (strncasecmp($name, $abbreviation, strlen($name)) == 0) {
						$selected = $command;
						break;
					}
				}
			}

			if (!$selected) {
				return new \Inspector\Debug\Commands\UnknownCommand($this);
			}

			if ($requiredParameters = $selected->requiresParameters()) {
				if (!$parameters = Parameter::parse(
						$requiredParameters, 
						trim(substr($line, strlen($name))))) {
					return null;
				}
			}

			return $selected;
		}

		public function welcome() {
			if (($optimizations = \ini_get("opcache.optimization_level"))) {
				\sscanf(\ini_get("opcache.optimization_level"), "0x%x", $optimizations);
			}

			printf("Welcome to idbg v%s:\n" .
			       "Inspector:\tv%s\n" . 
			       "Opcache:\t%s%s\n" .
			       "PHP:\t\tv%s\n",
				Debugger::Version, \Inspector\Version,
				\ini_get("opcache.enable_cli") ? "enabled" : "disabled",
				\ini_get("opcache.enable_cli") ? 
					($optimizations) ? 
						\sprintf(", Optimizations: 0x%X", $optimizations) : 
						", No Optimizations" : 
					null,
				\phpversion());
		}

		private $prompt;
		private $commands = [];
		private $breaks = [];
		private $sources = [];
	}
}
