<?php
declare(strict_types=1);

namespace Payment\StripeBundle\Service;

use Stripe\Account;
use Stripe\Balance;
use Stripe\Error\InvalidRequest;
use Stripe\FileUpload;
use Stripe\Stripe;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @see http://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations
 * @DI\Service(StripeAccount::SERVICE)
 */
class StripeAccount extends Stripe
{
    /**
     * Service name
     */
    const SERVICE = 'stripe.account';

    private $client;

    /**
     * StripeAccount constructor.
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
     * Retrieve a Account instance by its ID
     *
     * @throws HttpException:
     *     - If the accountId is invalid (the account does not exists...)
     *
     * @see https://stripe.com/docs/api#account
     *
     * @param string $accountId: The account ID

     * @return \Stripe\StripeObject
     */
    public function retrieve($accountId)
    {
        return Account::retrieve($accountId);
    }

    /**
     * Get Account pending and available balance by its ID
     *
     * @param string $accountId: The account ID
     * @return Balance
     */
    public function retrieveBalance($accountId)
    {
        return Balance::retrieve(
            array("stripe_account" => $accountId)
        );
    }

    /**
     * Create Stripe Account
     *
     * @see https://stripe.com/docs/api#create_account
     *
     * @param array $data: Artist data
     * @param string $clientIP: Acceptance IP
     * @return array
     */
    public function create(array $data, string $clientIP = '194.44.211.246')
    {
        $address = array(
            'city' => $data['city'],
            'country' => $data['country'],
            "line1" => $data['address'],
            "line2" => '',
            "postal_code" => $data['postal_code'],
        );

        $dob = array(
            'day' => $data['birthday']->format('d'),
            'month' => $data['birthday']->format('m'),
            'year' => $data['birthday']->format('Y')
        );
        $legalEntity = array(
            'address' => $address,
            'dob' => $dob,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'type' => 'individual',
            'phone_number' => $data['phone']
        );

        $tosAcceptance = array(
            'date' => (new \DateTime())->getTimestamp(),
            'ip' => $clientIP
        );

        $account = Account::create(array(
            "email" => $data['email'],
            "country" => $data['country'],
            "type" => $data['type'],
            "legal_entity" => $legalEntity,
            'tos_acceptance' => $tosAcceptance,
        ));

        return $this->client->save($account);
    }

    /**
     * Add new bank account
     *
     * https://stripe.com/docs/api#account_create_bank_account
     *
     * @param string|null $accountId: The account ID
     * @param array $data: User data
     * @return array
     */
    public function createExternalAccount($accountId, array $data)
    {
        $account = Account::retrieve($accountId);

        $account->external_accounts->create(array(
            "external_account" => array(
                "object" => "bank_account",
                "account_number" => $data['iban'],
                "country" => $data['country'],
                "currency" => $data['currency'],
            ),
            "default_for_currency" => true
        ));

        return $this->client->save($account);
    }

    /**
     * Upload verification document
     *
     * @param $accountId
     * @param $document
     * @param string $path
     * @return array
     */
    public function documentUploaded($accountId, $document, string $path)
    {
        $uploadedFile = null;
        $file = $document;
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
        $file->move($path, $fileName);
        $document = $path . '/' . $fileName;

        $fp = fopen($document, 'r');

        $account = Account::retrieve($accountId);

        try {
            $uploadedFile = FileUpload::create(
                array(
                    "file" => $fp,
                    "purpose" => "identity_document"
                ),
                array("stripe_account" => $account->id)
            );
            return ['success' => true, 'uploadedFile' => $uploadedFile, 'errors' => []];
        } catch (InvalidRequest $e) {
            $body = $e->getJsonBody();
            $err  = $body['error'];
        } catch (\Exception $e) {
            $err = $e->getTraceAsString();
        }

        return ['success' => false, 'uploadedFile' => $uploadedFile, 'errors' => $err];
    }

    /**
     * Attach verification document into stripe account
     *
     * @param string $accountId
     * @param $uploadedFile
     * @return array
     */
    public function documentAttachment($accountId, $uploadedFile)
    {
        $account = Account::retrieve($accountId);
        $account->legal_entity->verification->document = $uploadedFile ? $uploadedFile->id : '';

        return $this->client->save($account);
    }

    /**
     * Update address account
     **
     * @param string|null $accountId: The account ID
     * @param array $data: User data
     * @return array
     */
    public function updateAddress($accountId, array $data)
    {
        $account = Account::retrieve($accountId);

        $account->legal_entity->address->city = $data['city'];
        $account->legal_entity->address->line1 = $data['address'];
        $account->legal_entity->address->postal_code = $data['postal_code'];

        return $this->client->save($account);
    }
}
