<?php

namespace Igorsgm\GitHooks\Traits;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\Concerns\InteractsWithSignals;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;

trait ConsoleHelper
{
    use ProcessHelper,
        InteractsWithIO,
        InteractsWithSignals;

    /**
     * Holds an instance of the console output.
     *
     * @var \Symfony\Component\Console\Output\ConsoleOutput
     */
    protected $consoleOutput;

    /**
     * The output interface implementation.
     *
     * @var \Illuminate\Console\OutputStyle
     */
    protected $output;

    /**
     * Create a new console command instance.
     *
     * @return void
     */
    public function initConsole($argInput = '')
    {
        $this->input = new StringInput($argInput);
        $this->consoleOutput = new ConsoleOutput();
        $this->output = new OutputStyle($this->input, $this->consoleOutput);
    }
}
