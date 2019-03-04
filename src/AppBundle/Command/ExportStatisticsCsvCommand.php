<?php
declare(strict_types=1);

namespace AppBundle\Command;

use AppBundle\Service\ServiceI;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Check whether the events that ended and create charge if is it
 *
 * USAGE: php bin/console app:event_subscriber:charge
 * CRONTAB: 0 0 *\/3 * * php /var/www/stage/bin/console app:export_statistics:new_user
 *
 * @package AppBundle\Command
 */
class ExportStatisticsCsvCommand extends ContainerAwareCommand
{
    const COMMAND_NAME = 'app:export_statistics:new_user';

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Completed export stats')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get(ServiceI::EXPORT_STATISTIC_CSV)
            ->setOutput($output)
            ->setInput($input)
            ->run();
    }
}