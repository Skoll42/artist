<?php
declare(strict_types=1);

namespace Payment\StripeBundle\Service;

use Stripe\Charge;
use Stripe\Customer;
use Stripe\Error\InvalidRequest;
use Stripe\Source;
use Stripe\Stripe;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @see http://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations
 * @DI\Service(StripeCustomer::SERVICE)
 */
class StripeCustomer extends Stripe
{
    /**
     * Service name
     */
    const SERVICE = 'stripe.customer';

    /**
     * @var StripeClient
     */
    private $client;

    /**
     * StripeCharge constructor.
     * @param StripeClient $client
     *
     * @DI\InjectParams({
     *     "client" = @DI\Inject(StripeClient::SERVICE)
     * })
     */
    public function __construct(StripeClient $client)
    {
        $this->client = $client;
        return $this;
    }

    public function retrieve($customerId)
    {
        return Customer::retrieve($customerId);
    }

    public function create(
        $email,
        $token,
        $username,
        $name,
        $registered
    ) {
        $customerOptions = array(
            'email'    => $email,
            'card'     => $token,
            'metadata' => [
                'username'   => $username,
                'name'       => $name,
                'registered' => $registered->format('d-m-Y H:i'),
                'user_email' => $email
            ]
        );

        try {
            $customer = Customer::create($customerOptions);
            return ['success' => true, 'data' => $customer, 'errors' => []];
        } catch (InvalidRequest $e) {
            $body = $e->getJsonBody();
            $err  = $body['error'];
        } catch (\Exception $e) {
            $err = $e->getMessage();
        }
        return ['success' => false, 'data' => null, 'errors' => $err];
    }

    public function update(
        $customer,
        $token
    ) {
        $customer->card = $token;
        return $this->client->save($customer);
    }
}
