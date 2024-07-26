<?php

namespace Placetopay\ClicktopayClient\Traits;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Placetopay\ClicktopayClient\Constants\Utils;
use Placetopay\ClicktopayClient\Contracts\RequestContract;
use Placetopay\ClicktopayClient\Contracts\ResponseContract;
use Placetopay\ClicktopayClient\Response\DataResponse;

trait SendRequest
{
    /**
     * @throws GuzzleException
     */
    protected function sendRequest(
        RequestContract $request,
        string $method,
        string $dataResponse = DataResponse::class
    ): ResponseContract {
        try {
            $response = $this->getResponse($request, $method);
        } catch (ClientException $e) {
            $response = $e->getResponse();
        }

        return new $dataResponse($response, $this->authData->getCredentials());
    }

    /**
     * @throws GuzzleException
     */
    private function getResponse(RequestContract $request, string $method): ResponseInterface
    {
        if ($request->getProcess() === Utils::PROCESS_MASTERCARD) {
            return $this->client->send(
                  $this->auth->singRequest($request->getRequest($method, $this->authData->getCredentials()))
            );
        }

        return $this->client->send($request->getRequest($method, $this->authData->getCredentials()));
    }
}