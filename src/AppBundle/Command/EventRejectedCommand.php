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
 * USAGE: php bin/console app:event_subscriber:rejected
 * CRONTAB: 0 * * * * php /var/www/stage/bin/console app:event_subscriber:rejected
 *
 * @package AppBundle\Command
 */
class EventRejectedCommand extends ContainerAwareCommand
{
    const COMMAND_NAME = 'app:event_subscriber:rejected';

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Completed events subscriber')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get(ServiceI::EVENT_SUBSCRIBER_REJECTED)
            ->setOutput($output)
            ->setInput($input)
            ->run();
    }
}