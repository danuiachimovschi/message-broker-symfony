<?php

declare(strict_types=1);

namespace App\Infrastructure\Elastic\Core;

use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\ClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class ElasticSearchConnection implements ElasticSearchConnectionInterface
{
    private string $host;

    private string $user;

    private string $password;

    private ClientInterface $client;

    public function __construct(ParameterBagInterface $params)
    {
        $this->host = $params->get('elastic_search.host');
        $this->user = $params->get('elastic_search.user');
        $this->password = $params->get('elastic_search.password');

        $this->client = ClientBuilder::create()
            ->setHosts([$this->host])
            ->setSSLVerification(false)
            ->setBasicAuthentication($this->user, $this->password)
            ->build();
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }
}