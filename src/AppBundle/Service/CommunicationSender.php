<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 18.10.2018
 * Time: 16:50
 */
declare(strict_types=1);

namespace AppBundle\Service;

use Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @see http://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations
 * @DI\Service(ServiceI::COMMUNICATION_SENDER)
 */
class CommunicationSender
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DocumentManager
     */
    private $mongo;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $input;

    /**
     * @var string
     */
    private $output;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @param $output
     *
     * @return $this
     */
    public function setOutput($output)
    {
        $this->output = $output;
        return $this;
    }
    /**
     * @param $input
     *
     * @return $this
     */
    public function setInput($input)
    {
        $this->input = $input;
        return $this;
    }

    /**
     * EventSubscriberCharge constructor.
     *
     * @param EntityManager $em
     * @param ContainerInterface $container
     * @param DocumentManager $mongo
     *
     * @DI\InjectParams({
     *     "em"    = @DI\Inject("doctrine.orm.entity_manager"),
     *     "container"    = @DI\Inject("service_container"),
     *     "mongo"    = @DI\Inject("doctrine_mongodb.odm.document_manager")
     * })
     */
    public function __construct(EntityManager $em, ContainerInterface $container, DocumentManager $mongo)
    {
        $this->container = $container;
        $this->em = $em;
        $this->mongo = $mongo;
    }

    /**
     * @return bool
     */
    public function run()
    {
        $this->io = new SymfonyStyle($this->input, $this->output);

        // Output start message into console
        $this->io->title('Start...');

        $chats = $this->mongo->getRepository('ChatBundle:Messages')->getAllUnread();

        if (empty($chats)) {
            $this->io->success('No unread massages!');
            return false;
        }

        $iteratorArr = new \ArrayIterator($chats);

        // Start progress
        $this->io->createProgressBar(iterator_count($iteratorArr));
        $this->io->progressStart(iterator_count($iteratorArr));

        foreach ($chats as $chat) {
            try {
                $subject = $chat->getSenderName() . ' sent you a new message';
                $emailFrom =  $this->container->getParameter(
                    $this->container->getParameter('kernel.environment') . '_mailer_address'
                );
                $emailTo = $this->em->getRepository("AppBundle:User")->find($chat->getTargetId())->getEmail();

                $sender = $this->container->get('email_sender');
                $sender->sendEmail(
                    $emailFrom,
                    $emailTo,
                    $subject,
                    '@App/emails/communication_message.html.twig',
                    [
                        'targetName' => $chat->getTargetName(),
                        'senderName' => $chat->getSenderName(),
                    ]
                );

                $chat->setIsSend(true);
                $this->mongo->persist($chat);
            } catch (\Exception $e) {
                $this->io->error($e->getTraceAsString());
            }

            $this->io->progressAdvance();
        }

        $this->mongo->flush();

        // End progress
        $this->io->progressFinish();

        // Success message
        $this->io->success('Everything went well!');

        return true;
    }
}
