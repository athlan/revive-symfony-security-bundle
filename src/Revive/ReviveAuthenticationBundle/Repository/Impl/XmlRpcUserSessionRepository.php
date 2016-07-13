<?php

namespace Revive\ReviveAuthenticationBundle\Repository\Impl;

use fXmlRpc\Client;
use fXmlRpc\ClientInterface;
use Revive\ReviveAuthenticationBundle\Repository\Exception\RepositoryInfrastructureException;
use Revive\ReviveAuthenticationBundle\Repository\UserSession\UserSessionCreationAuthenticationResult;
use Revive\ReviveAuthenticationBundle\Repository\UserSession\UserSessionCreationAuthorizationSessionCreationResult;
use Revive\ReviveAuthenticationBundle\Repository\UserSession\UserSessionCreationResult;
use Revive\ReviveAuthenticationBundle\Repository\UserSessionRepository;

/**
 * This implementation connects to Revive via Xml-Rpc.
 *
 * @package Revive\ReviveAuthenticationBundle\Repository\Impl
 */
class XmlRpcUserSessionRepository implements UserSessionRepository {

    const AUTHORIZATION_FAILURE_NOT_ADMIN_FAULT_MESSAGE = "User must be OA installation admin";

    /**
     * @var Client
     */
    private $client;

    /**
     * @param ClientInterface $client
     * @param string $url
     */
    public function __construct(ClientInterface $client, $url) {
        $this->client = $client;

        if($client instanceof Client) {
            $this->client->setUri($url);
        }
    }

    /**
     * @inheritdoc
     */
    function createSessionIdByCredentials($username, $password)
    {
        if("" == $username || "" == $password) {
            throw new \InvalidArgumentException();
        }

        try {
            $reviveSessionId = $this->client->call('ox.logon', array($username, $password));

            return new UserSessionCreationResult(
                UserSessionCreationAuthenticationResult::SUCCESS,
                UserSessionCreationAuthorizationSessionCreationResult::SUCCESS,
                $reviveSessionId
            );
        }
        catch(\fXmlRpc\Exception\FaultException $e) {
            if($e->getFaultCode() == 801) {
                if($e->getFaultString() === self::AUTHORIZATION_FAILURE_NOT_ADMIN_FAULT_MESSAGE) {
                    return new UserSessionCreationResult(
                        UserSessionCreationAuthenticationResult::SUCCESS,
                        UserSessionCreationAuthorizationSessionCreationResult::FAILED_NOT_ADMIN,
                        null
                    );
                }

                throw new \InvalidArgumentException("Invalid username or password");
            }

            throw new RepositoryInfrastructureException("Infrastructure exception.", 0, $e);
        }
        catch(\fXmlRpc\Exception\ExceptionInterface $e) {
            throw new RepositoryInfrastructureException("Infrastructure exception.", 0, $e);
        }
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
