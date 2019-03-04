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
 * CRONTAB: 50 23 * * * php /var/www/stage/bin/console app:event_subscriber:charge
 *
 * @package AppBundle\Command
 */
class EventSubscriberCommand extends ContainerAwareCommand
{
    const COMMAND_NAME = 'app:event_subscriber:charge';

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Completed events subscriber')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stripeCharge = $this->getContainer()->get('stripe.charge');

        $this->getContainer()->get(ServiceI::EVENT_SUBSCRIBER_CHARGE)
            ->setOutput($output)
            ->setInput($input)
            ->run($stripeCharge);
    }
}