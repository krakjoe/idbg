# idbg
*the debugger you embed into your project with composer*

Inspector Debugger (idbg) is a debugger for PHP 7.1+ written in PHP.

# Alpha Software

`idbg` is alpha software, still under heavy development: Please do not design workflows around `idbg` yet ...

Please do test, at this stage, we ar reliant upon the reports *you* make to improve `idbg`.

# Requirements

  * PHP 7.1+
  * krakjoe/inspector

# Introduction

`idbg` is a Debugger akin to phpdbg or XDebug, though nowhere near as advanced as either of those pieces of software (yet?). `idbg` is written entirely in PHP, and can be embedded into your project (and workflow, eventually) using Composer. The complexity of debugging Zend is isolated within the only extension which `idbg` relies on.

Executing `composer require krakjoe/idbg dev-master` in your project will install `vendor/bin/idbg`, executing `vendor/bin/idbg` will present you with:

[![bin/idbg](https://i.imgur.com/LbxBMMk.png](https://github.com/krakjoe/idbg/blob/master/bin/idbg)

`idbg` is ready to accept commands; For a list of commands, and a little help, type `help` and press enter:

[![help](https://i.imgur.com/UFUsFWQ.png](https://github.com/krakjoe/idbg/blob/master/src/Inspector/Debug/Commands/HelpCommand.php)

You probably want to set Break Points before executing `run file://path/to/my/script.php`, or using `eval` to enter into some code.

# Get Involved

`idbg` is *your* debugger ... open a PR ...



