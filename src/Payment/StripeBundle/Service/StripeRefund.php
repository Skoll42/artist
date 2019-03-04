<?php
declare(strict_types=1);

namespace Payment\StripeBundle\Service;

use Stripe\Refund;
use Stripe\Stripe;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @see http://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations
 * @DI\Service(StripeRefund::SERVICE)
 */
class StripeRefund extends Stripe
{
    /**
     * Service name
     */
    const SERVICE = 'stripe.refund';

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
     * Retrieve a Refund instance by its ID
     *
     * @throws HttpException:
     *     - If the refundId is invalid
     *
     * @see https://stripe.com/docs/api#refunds
     *
     * @param string $refundId: The refund ID
     *
     * @return \Stripe\StripeObject
     */
    public function retrieve($refundId)
    {
        return Refund::retrieve($refundId);
    }

    /**
     * Create a new Refund on an existing Charge (by its ID).
     *
     * @throws HttpException:
     *     - If the charge id is invalid (the charge does not exists...)
     *     - If the charge has already been refunded
     *
     * @see https://stripe.com/docs/connect/direct-charges#issuing-refunds
     *
     * @param string $chargeId: The charge ID
     * @param int    $amount: The charge amount in cents
     * @param array  $metadata: optional additional informations about the refund
     * @param string $reason: The reason of the refund, either "requested_by_customer", "duplicate" or "fraudulent"
     * @param bool   $refundApplicationFee: Wether the application_fee should be refunded aswell.
     * @param bool   $reverseTransfer: Wether the transfer should be reversed
     * @param string $stripeAccountId: The optional connected stripe account ID on which charge has been made.
     *
     * @return \Stripe\StripeObject
     */
    public function create(
        $chargeId,
        $amount = null,
        $metadata = [],
        $reason = 'requested_by_customer',
        $refundApplicationFee = true,
        $reverseTransfer = true,
        $stripeAccountId = null
    ) {
        $refundOptions = array(
            'charge'                    => $chargeId,
            'metadata'                  => $metadata,
            'reason'                    => $reason,
            'refund_application_fee'    => (bool) $refundApplicationFee,
            'reverse_transfer'          => (bool) $reverseTransfer
        );

        if ($amount) {
            $refundOptions['amount'] = intval($amount);
        }

        $connectedAccountOptions = [];
        if ($stripeAccountId) {
            $connectedAccountOptions['stripe_account'] = $stripeAccountId;
        }

        return Refund::create($refundOptions, $connectedAccountOptions);
    }
}
