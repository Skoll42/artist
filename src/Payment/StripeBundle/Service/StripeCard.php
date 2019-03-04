<?php
declare(strict_types=1);

namespace Payment\StripeBundle\Service;

use Stripe\Card;
use Stripe\Charge;
use Stripe\Stripe;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @see http://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations
 * @DI\Service(StripeCard::SERVICE)
 */
class StripeCard extends Stripe
{
    /**
     * Service name
     */
    const SERVICE = 'stripe.card';

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
     * Retrieve a Card instance by its ID
     *
     * @throws HttpException:
     *     - If the cardId is invalid
     *
     * @see https://stripe.com/docs/api#charges
     *
     * @param string $cardId: The card ID
     *
     * @throws \Stripe\Error\InvalidRequest
     */
    public function retrieve($cardId)
    {
        return Card::retrieve($cardId);
    }

    /**
     * Create a new Card from a payment token, to an optional connected stripe account.
     *
     * @throws HttpException:
     *     - If the payment token is invalid (payment failed)
     *
     * @see https://stripe.com/docs/charges
     *
     * @param int    $amount: The charge amount in cents
     * @param string $token: The payment token returned by the payment form (Stripe.js)
     * @param string $destinationAmount:
     * @param string $destinationAccount: The connected stripe account ID
     * @param string $description: An optional charge description
     * @param string $currency: The charge currency to use
     * @param boolean $capture: The charge or authorization only
     *
     * @return \Stripe\StripeObject
     */
    public function create(
        $amount,
        $token,
        $destinationAmount,
        $destinationAccount,
        $description = '',
        $currency = 'nok',
        $capture = false
    ) {
        $chargeOptions = array(
            'amount' => $amount,
            'currency' => $currency,
            'source' => $token,
            'description' => $description,
            'capture' => $capture,
        );

        $destination = array(
            "amount" => $destinationAmount,
            "account" => $destinationAccount,
        );

        $chargeOptions['destination'] = $destination;

        return Charge::create($chargeOptions);
    }
}
