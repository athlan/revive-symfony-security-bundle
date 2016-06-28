<?php

namespace Revive\ReviveAuthenticationBundle\Repository\Impl;

use fXmlRpc\Client;
use Revive\ReviveAuthenticationBundle\Repository\Exception\RepositoryInfrastructureException;
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
        return $reviveSessionId;
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
