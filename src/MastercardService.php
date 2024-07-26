<?php

namespace Placetopay\ClicktopayClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Placetopay\ClicktopayClient\Contracts\ResponseContract;
use Placetopay\ClicktopayClient\Request\Mastercard\AuthRequest;
use Placetopay\ClicktopayClient\Request\Mastercard\CheckoutRequest;
use Placetopay\ClicktopayClient\Requests\Mastercard\ConfirmationRequest;
use Placetopay\ClicktopayClient\Requests\Mastercard\RegistrationRequest;
use Placetopay\ClicktopayClient\Response\CheckoutDataResponse;
use Placetopay\ClicktopayClient\Services\Mastercard\Auth;
use Placetopay\ClicktopayClient\traits\SendRequest;
use Symfony\Component\HttpFoundation\Request;

class MastercardService
{
    use SendRequest;

    protected Auth $auth;

    public function __construct(
        protected AuthRequest $authData,
        protected ClientInterface $client
    ) {
        $this->auth = new Auth($authData->getCredentials());
    }

    /**
     * @throws GuzzleException
     */
    public function checkout(CheckoutRequest $request): ResponseContract
    {
        return $this->sendRequest($request, Request::METHOD_POST, CheckoutDataResponse::class);
    }

    /**
     * @throws GuzzleException
     */
    public function DPARegistration(RegistrationRequest $request): ResponseContract
    {
        return $this->sendRequest($request, Request::METHOD_POST);
    }

    /**
     * @throws GuzzleException
     */
    public function confirmations(ConfirmationRequest $request): ResponseContract
    {
        return $this->sendRequest($request, Request::METHOD_POST);
    }
}