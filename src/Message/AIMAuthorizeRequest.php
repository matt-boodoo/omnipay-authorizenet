<?php

namespace Omnipay\AuthorizeNet\Message;

use Omnipay\Common\CreditCard;

/**
 * Authorize.Net AIM Authorize Request
 */
class AIMAuthorizeRequest extends AIMAbstractRequest
{
    protected $action = 'authOnlyTransaction';

    public function getData()
    {
        $this->validate('amount');
        $data = $this->getBaseData();
        $data->transactionRequest->amount = $this->getAmount();
        $this->addPayment($data);
        $this->addBillingData($data);
        $this->addCustomerIP($data);
        $this->add3dSecureData($data);
        $this->addTransactionSettings($data);

        return $data;
    }

    protected function addPayment(\SimpleXMLElement $data)
    {
        $this->validate('card');
        /** @var CreditCard $card */
        $card = $this->getCard();
        $card->validate();
        $data->transactionRequest->payment->creditCard->cardNumber = $card->getNumber();
        $data->transactionRequest->payment->creditCard->expirationDate = $card->getExpiryDate('my');
        $data->transactionRequest->payment->creditCard->cardCode = $card->getCvv();
    }
    /**
     * Adds 3dSecure values to data object
     *
     * @param \SimpleXMLElement $data
     * @return \SimpleXMLElement
     */
    protected function add3dSecureData(\SimpleXMLElement $data)
    {
        /**
         * @var CreditCard $card
         */
        if ($card = $this->getCard()) {
            $data->transactionRequest->cardholderAuthentication->authenticationIndicator = $this->getEci();
            $data->transactionRequest->cardholderAuthentication->cardholderAuthenticationValue = $this->getCavv();
        }
        return $data;
    }

    protected function addCustomerIP(\SimpleXMLElement $data)
    {
        $ip = $this->getClientIp();
        if (!empty($ip)) {
            $data->transactionRequest->customerIP = $ip;
        }
    }
}
