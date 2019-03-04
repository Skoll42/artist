<?php
declare(strict_types=1);

namespace Payment\StripeBundle\Service;

use Stripe\Account;
use Stripe\Balance;
use Stripe\Error\ApiConnection;
use Stripe\Error\Authentication;
use Stripe\Error\Base;
use Stripe\Error\Card;
use Stripe\Error\InvalidRequest;
use Stripe\Error\RateLimit;
use Stripe\FileUpload;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Coupon;
use Stripe\Plan;
use Stripe\Subscription;
use Stripe\Refund;
use Stripe\Transfer;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * An extension of the Stripe PHP SDK, including an API key parameter to automatically authenticate.
 *
 * This class will provide helper methods to use the Stripe SDK
 *
 * @see http://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations
 * @DI\Service(StripeClient::SERVICE)
 */
class StripeClient extends Stripe
{
    /**
     * Service name
     */
    const SERVICE = 'stripe.client';

    /**
     * StripeClient constructor.
     * @param $apiKey
     *
     * @DI\InjectParams({
     *     "apiKey" = @DI\Inject("%stripe.stripe_private_api_key%"),
     * })
     */
    public function __construct($apiKey)
    {
        self::setApiKey($apiKey);
        return $this;
    }

    /**
     * @param $data
     * @return array
     */
    public function save($data)
    {
        try {
            $data->save();
            return ['success' => true, 'data' => $data, 'errors' => []];
        } catch (Card | RateLimit | InvalidRequest | Authentication | ApiConnection | Base $e) {
            // Something happened with Stripe
            $body = $e->getJsonBody();
            $err  = $body['error'];
        } catch (\Exception $e) {
            // Something else happened, completely unrelated to Stripe
            $err = $e->getTraceAsString();
        }

        return ['success' => false, 'data' => null, 'errors' => $err];
    }















    /**
     * Retrieve a Customer instance by its ID
     *
     * @throws HttpException:
     *     - If the customerId is invalid
     *
     * @see https://stripe.com/docs/api#customers
     *
     * @param string $customerId: The customer ID
     *
     * @return \Stripe\StripeObject
     */
    public function retrieveCustomer($customerId)
    {
        return Customer::retrieve($customerId);
    }



    /**
     * Retrieve a Transfer instance by its ID
     *
     * @throws HttpException:
     *     - If the transferId is invalid
     *
     * @see https://stripe.com/docs/api#transfers
     *
     * @param string $transferId: The transfer ID
     *
     * @return \Stripe\StripeObject
     */
    public function retrieveTransfer($transferId)
    {
        return Transfer::retrieve($transferId);
    }




























    /**
     * Associate a new Customer object to an existing Plan.
     *
     * @throws HttpException:
     *     - If the planId is invalid (the plan does not exists...)
     *     - If the payment token is invalid (payment failed)
     *
     * @see https://stripe.com/docs/subscriptions/tutorial#create-subscription
     *
     * @param string $planId: The plan ID as defined in your Stripe dashboard
     * @param string $paymentToken: The payment token returned by the payment form (Stripe.js)
     * @param string $customerEmail: The customer email
     * @param string|null $couponId: An optional coupon ID
     *
     * @return \Stripe\StripeObject
     */
    public function subscribeCustomerToPlan($planId, $paymentToken, $customerEmail, $couponId = null)
    {
        $customer = Customer::create([
            'source'    => $paymentToken,
            'email'     => $customerEmail
        ]);
        $data = [
            'customer'  => $customer->id,
            'plan'      => $planId,
        ];
        if ($couponId) {
            $data['coupon'] = $couponId;
        }

        Subscription::create($data);

        return $customer;
    }

    /**
     * Associate an existing Customer object to an existing Plan.
     *
     * @throws HttpException:
     *      - If the customerId is invalid (the customer does not exists...)
     *      - If the planId is invalid (the plan does not exists...)
     *
     * @see https://stripe.com/docs/api#create_subscription
     *
     * @param string $customerId: The customer ID as defined in your Stripe dashboard
     * @param string $planId: The plan ID as defined in your Stripe dashboard
     * @param array $parameters: Optional additional parameters, the complete list is available here: https://stripe.com/docs/api#create_subscription
     *
     * @return \Stripe\StripeObject
     */
    public function subscribeExistingCustomerToPlan($customerId, $planId, $parameters = [])
    {
        $data = [
            'customer'      => $customerId,
            'plan'          => $planId
        ];
        if ($parameters && is_array($parameters)) {
            $data = array_merge($parameters, $data);
        }
        return Subscription::create($data);
    }

    /**
     * Create a new Charge from a payment token, to an optional connected stripe account, with an optional application fee.
     *
     * @throws HttpException:
     *     - If the payment token is invalid (payment failed)
     *
     * @see https://stripe.com/docs/charges#saving-credit-card-details-for-later
     *
     * @param string $paymentToken: The payment token returned by the payment form (Stripe.js)
     * @param string $email: An optional customer e-mail
     *
     * @return \Stripe\StripeObject
     */
    public function createCustomer($paymentToken, $email = null)
    {
        return Customer::create([
            'source' => $paymentToken,
            'email' => $email
        ]);
    }

    /**
     * Create a new Charge on an existing Customer object, to an optional connected stripe account, with an optional application fee
     *
     * @throws HttpException:
     *     - If the payment token is invalid (payment failed)
     *
     * @see https://stripe.com/docs/charges#saving-credit-card-details-for-later
     *
     * @param int    $chargeAmount: The charge amount in cents
     * @param string $chargeCurrency: The charge currency to use
     * @param string $customerId: The Stripe Customer object ID
     * @param string $stripeAccountId: The connected stripe account ID
     * @param int    $applicationFee: The fee taken by the platform, in cents
     * @param string $chargeDescription: An optional charge description
     * @param array  $chargeMetadata: An optional array of metadata
     *
     * @return \Stripe\StripeObject
     */
    public function chargeCustomer($chargeAmount, $chargeCurrency, $customerId, $stripeAccountId = null, $applicationFee = 0, $chargeDescription = '', $chargeMetadata = [])
    {
        $chargeOptions = [
            'amount'            => $chargeAmount,
            'currency'          => $chargeCurrency,
            'customer'          => $customerId,
            'description'       => $chargeDescription,
            'metadata'          => $chargeMetadata
        ];
        if ($applicationFee && intval($applicationFee) > 0) {
            $chargeOptions['application_fee'] = intval($applicationFee);
        }
        $connectedAccountOptions = [];
        if ($stripeAccountId) {
            $connectedAccountOptions['stripe_account'] = $stripeAccountId;
        }
        return Charge::create($chargeOptions, $connectedAccountOptions);
    }
}
