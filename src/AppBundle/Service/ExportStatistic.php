<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 18.10.2018
 * Time: 16:50
 */
declare(strict_types=1);

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use League\Csv\Writer;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * @see http://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations
 * @DI\Service(ServiceI::EXPORT_STATISTIC_CSV)
 */
class ExportStatistic
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var TwigEngine
     */
    private $engine;

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
     * @param \Swift_Mailer $mailer
     * @param TwigEngine $engine
     *
     * @DI\InjectParams({
     *     "em"    = @DI\Inject("doctrine.orm.entity_manager"),
     *     "container"    = @DI\Inject("service_container"),
     *     "mailer"    = @DI\Inject("mailer"),
     *     "engine"    = @DI\Inject("Symfony\Component\Templating\EngineInterface")
     * })
     */
    public function __construct(
        EntityManager $em,
        ContainerInterface $container,
        \Swift_Mailer $mailer,
        TwigEngine $engine
    ) {
        $this->container = $container;
        $this->em = $em;
        $this->mailer = $mailer;
        $this->engine = $engine;
    }

    public function run()
    {
        $this->io = new SymfonyStyle($this->input, $this->output);

        // Output start message into console
        $this->io->title('Start...');

        $customers = $this->newCustomers();
        $artists = $this->newArtists();

        $this->sendStatistic($customers, $artists);

        // Success message
        $this->io->success('Everything went well!');

        return true;
    }

    private function newCustomers()
    {
        $customers = $this->em->getRepository("AppBundle:UserData")->findAllCustomers();
        $header = [
            'Email',
            'Registered on',
            'First Name',
            'Last Name',
            'Verified?',
            'Booked?',
            'Deleted?'
        ];

        $writer = Writer::createFromFileObject(new \SplTempFileObject());
        $writer->insertOne($header);

        foreach ($customers as $customer) {
            $records = [
                $customer['email'] ?? "",
                $customer['registered_on'] ?? "",
                $customer['firstName'] ?? "",
                $customer['lastName'] ?? "",
                $customer['verified'] ? "Yes" : "No",
                $customer['booked'] ?? "",
                $customer['deleted'] ? "Yes" : "No"
            ];
            $writer->insertOne($records);
        }

        return $writer->getContent();
    }

    private function newArtists()
    {
        $artists = $this->em->getRepository("AppBundle:ArtistData")->findAllArtists();
        $header = [
            'Email',
            'Registered on',
            'First Name',
            'Last Name',
            'Verified?',
            'Filled in profile?',
            'Verified by Stripe?',
            'Visible on Website?',
            'Deleted?'
        ];

        $writer = Writer::createFromFileObject(new \SplTempFileObject());
        $writer->insertOne($header);

        foreach ($artists as $artist) {
            $records = [
                $artist['email'] ?? "",
                $artist['registered_on'] ?? "",
                $artist['firstName'] ?? "",
                $artist['lastName'] ?? "",
                $artist['verified'] ? "Yes" : "No",
                $artist['filled'] ? "Yes" : "No",
                $artist['stripeId'] ?? "",
                $artist['verified'] ? "Yes" : "No",
                $artist['deleted'] ? "Yes" : "No"
            ];
            $writer->insertOne($records);
        }

        return $writer->getContent();
    }

    private function sendStatistic($customers, $artists)
    {
        $template = $this->getTemplate('@App/emails/statistic_new_user.html.twig', []);
        $subject = 'AND: Statistics of all users';
        $emailFrom =  $this->container->getParameter(
            $this->container->getParameter('kernel.environment') . '_mailer_address'
        );
        $emailTo = "olga@audioland.no";

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($emailFrom, $this->container->getParameter('site_name'))
            ->setTo($emailTo)
            ->setBody($template, 'text/html')
            ->attach(\Swift_Attachment::newInstance($customers)
                ->setFilename('customers.csv')
                ->setContentType('application/csv'))
            ->attach(\Swift_Attachment::newInstance($artists)
                ->setFilename('artists.csv')
                ->setContentType('application/csv'));


        return $this->mailer->send($message);
    }

    private function getTemplate($templateName, $templateData)
    {
        return $this->engine->render($templateName, $templateData);
    }
}
