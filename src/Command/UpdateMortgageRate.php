<?php

namespace App\Command;

use App\Model\Mortgage;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;


class UpdateMortgageRate extends Command
{
    protected $mortgage;

    public function __construct(Mortgage $mortgage)
    {
        $this->mortgage = $mortgage;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "app/console")
            ->setName('app:mortgage-rate')

            // the short description shown while running "php app/console list"
            ->setDescription('Updates the current Mortgage Rate.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command updates the Mortgage Rate Database/')
            ->addOption('set-30yr', null, InputOption::VALUE_NONE,'Set 30-yr Freddie Mac Rate')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $update = '';
        $set_30yr = $input->getOption('set-30yr');

        $output->writeln("Script: updateHmls: Started at " . date("Y-m-d H:i:s"));
        $output->writeln('x-----x-----x-----x-----x-----x');
        $output->writeln('Start Memory: '. round(memory_get_usage()/1048576, 2) . ' MB');

        if ($set_30yr){
            $update .= $this->mortgage->setMortgageRate();
        }

        $output->writeln($update);

        $output->writeln('Memory Peak: '. round(memory_get_peak_usage()/1048576,2) . ' MB');
        $output->writeln('x-----x-----x-----x-----x-----x');
        $output->writeln("Script: updateHmls: Ended at " . date("Y-m-d H:i:s"));
        $output->writeln(' ');
    }


}
