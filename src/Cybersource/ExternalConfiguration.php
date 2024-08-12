<?php

namespace Placetopay\ClicktopayClient\Cybersource;

use CyberSource\Authentication\Core\MerchantConfiguration;
use CyberSource\Configuration;

class ExternalConfiguration
{
    private MerchantConfiguration $merchantConfig;
    private string $authType;
    private string $merchantID;
    private string $apiKeyID;
    private string $secretKey;
    private string $runEnv;

    public function __construct()
    {
        $this->authType = 'http_signature';
        $this->merchantID = 'testgetnetcl_sandbox001';
        $this->apiKeyID = 'd5f80542-fa77-4cd6-9621-6546cdf26d9b';
        $this->secretKey = 'o1bjil39jdmPDtoRgsgzZ4H0LpnXVGagXRIJuj/0pAs=';
        $this->runEnv = 'apitest.cybersource.com';

        $this->merchantConfigObject();
    }

    public function merchantConfigObject(): MerchantConfiguration
    {
        if (! isset($this->merchantConfig)) {
            $config = new MerchantConfiguration();
            $config->setauthenticationType(strtoupper(trim($this->authType)));
            $config->setMerchantID(trim($this->merchantID));
            $config->setApiKeyID($this->apiKeyID);
            $config->setSecretKey($this->secretKey);
            $config->setRunEnvironment($this->runEnv);

            $this->merchantConfig = $config;
        }

        return $this->merchantConfig;
    }

    public function ConnectionHost(): Configuration
    {
        $merchantConf = $this->merchantConfigObject();
        $config = new Configuration();
        $config->setHost($merchantConf->getHost());

        return $config;
    }
}
