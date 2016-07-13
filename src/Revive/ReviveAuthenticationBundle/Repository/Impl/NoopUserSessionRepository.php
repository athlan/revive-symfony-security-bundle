<?php

namespace Revive\ReviveAuthenticationBundle\Repository\Impl;

use fXmlRpc\Client;
use Revive\ReviveAuthenticationBundle\Repository\Exception\RepositoryInfrastructureException;
use Revive\ReviveAuthenticationBundle\Repository\UserSession\UserSessionCreationAuthenticationResult;
use Revive\ReviveAuthenticationBundle\Repository\UserSession\UserSessionCreationAuthorizationSessionCreationResult;
use Revive\ReviveAuthenticationBundle\Repository\UserSession\UserSessionCreationResult;
use Revive\ReviveAuthenticationBundle\Repository\UserSessionRepository;

/**
 * No-op implementation.
 *
 * @package Revive\ReviveAuthenticationBundle\Repository\Impl
 */
class NoopUserSessionRepository implements UserSessionRepository {

    /**
     * @inheritdoc
     */
    function createSessionIdByCredentials($username, $password)
    {
        if("" == $username || "" == $password) {
            throw new \InvalidArgumentException();
        }

        $reviveSessionId = "mock-sessid";

        return new UserSessionCreationResult(
            UserSessionCreationAuthenticationResult::SUCCESS,
            UserSessionCreationAuthorizationSessionCreationResult::SUCCESS,
            $reviveSessionId
        );
    }

    /**
     * @inheritdoc
     */
    function invalidateSession($sessionId)
    {
        $result = true;
        return $result;
    }
}
