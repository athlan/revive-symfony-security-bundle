<?php

namespace Revive\ReviveAuthenticationBundle\Repository\Impl;

use fXmlRpc\Client;
use Revive\ReviveAuthenticationBundle\Repository\Exception\RepositoryInfrastructureException;
use Revive\ReviveAuthenticationBundle\Repository\UserSessionRepository;

/**
 * This implementation connects to Revive via Xml-Rpc.
 *
 * @package Revive\ReviveAuthenticationBundle\Repository\Impl
 */
class XmlRpcUserSessionRepository implements UserSessionRepository {

    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     * @param string $url
     */
    public function __construct(Client $client, $url) {
        $this->client = $client;
        $this->client->setUri($url);
    }

    /**
     * @inheritdoc
     */
    function createSessionIdByCredentials($username, $password)
    {
        if("" == $username || "" == $password) {
            throw new \InvalidArgumentException();
        }

        $reviveSessionId = "";

        try {
            $reviveSessionId = $this->client->call('ox.logon', array($username, $password));
        }
        catch(\fXmlRpc\Exception\FaultException $e) {
            if($e->getFaultCode() == 801) {
                throw new \InvalidArgumentException("Invalid username or password");
            }

            throw new RepositoryInfrastructureException("Infrastructure exception.", 0, $e);
        }
        catch(\fXmlRpc\Exception\ExceptionInterface $e) {
            throw new RepositoryInfrastructureException("Infrastructure exception.", 0, $e);
        }

        return $reviveSessionId;
    }

    /**
     * @inheritdoc
     */
    function invalidateSession($sessionId)
    {
        $this->client->prependParams(array(
            $sessionId
        ));

        try {
            $result = $this->client->call('ox.logoff', array());

            if ($result !== true) {
                throw new \InvalidArgumentException();
            }
        }
        catch(\fXmlRpc\Exception\FaultException $e) {
            if($e->getFaultCode()) {
                throw new \InvalidArgumentException("Invalid sessionId");
            }

            throw new RepositoryInfrastructureException("Infrastructure exception.", 0, $e);
        }
        catch(\fXmlRpc\Exception\ExceptionInterface $e) {
            throw new RepositoryInfrastructureException("Infrastructure exception.", 0, $e);
        }

        return $result;
    }
}
