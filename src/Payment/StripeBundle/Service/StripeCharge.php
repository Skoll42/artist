<?php
declare(strict_types=1);

namespace Payment\StripeBundle\Service;

use Stripe\Charge;
use Stripe\Error\InvalidRequest;
use Stripe\Stripe;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @see http://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations
 * @DI\Service(StripeCharge::SERVICE)
 */
class StripeCharge extends Stripe
{
    /**
     * Service name
     */
    const SERVICE = 'stripe.charge';

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

    /**
     * Retrieve a Charge instance by its ID
     *
     * @throws HttpException:
     *     - If the chargeId is invalid
     *
     * @see https://stripe.com/docs/api#charges
     *
     * @param string $chargeId: The charge ID
     *
     * @return \Stripe\StripeObject
     */
    public function retrieve($chargeId)
    {
        return Charge::retrieve($chargeId);
    }

    /**
     * Create a new Charge from a payment token, to an optional connected stripe account,
     * with an optional application fee.
     *
     * @throws HttpException:
     *     - If the payment token is invalid (payment failed)
     *
     * @see https://stripe.com/docs/charges
     *
     * @param int    $amount: The charge amount in cents
     * @param object $customer: The customer object from stripe
     * @param string $destinationAmount:
     * @param string $destinationAccount: The connected stripe account ID
     * @param string $description: An optional charge description
     * @param string $currency: The charge currency to use
     * @param boolean $capture: The charge or authorization only
     *
     * @return array
     */
    public function create(
        $amount,
        $customer,
        $destinationAmount,
        $destinationAccount,
        $description = '',
        $currency = 'nok',
        $capture = false
    ) {
        $chargeOptions = array(
            'amount' => $amount,
            'currency' => $currency,
            'customer' => $customer->id,
            'description' => $description,
            'capture' => $capture,
        );

        $destination = array(
            "amount" => $destinationAmount,
            "account" => $destinationAccount,
        );

        $chargeOptions['destination'] = $destination;

        try {
            $charge = Charge::create($chargeOptions);
            return ['success' => true, 'data' => $charge, 'errors' => []];
        } catch (InvalidRequest $e) {
            $body = $e->getJsonBody();
            $err  = $body['error'];
        } catch (\Exception $e) {
            $err = $e->getMessage();
        }
        return ['success' => false, 'data' => null, 'errors' => $err];
    }
}
