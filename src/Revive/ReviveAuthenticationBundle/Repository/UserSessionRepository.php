<?php

namespace Revive\ReviveAuthenticationBundle\Repository;

use Revive\ReviveAuthenticationBundle\Repository\Exception\RepositoryInfrastructureException;
use Revive\ReviveAuthenticationBundle\Repository\UserSession\UserSessionCreationResult;

interface UserSessionRepository {

    /**
     * @param $username
     * @param $password
     * @return UserSessionCreationResult
     *
     * @throws \InvalidArgumentException when username or password are incorrect
     * @throws RepositoryInfrastructureException when there is any infrastructure issue
     */
    function createSessionIdByCredentials($username, $password);

    /**
     * @param $sessionId
     * @return boolean
     *
     * @throws \InvalidArgumentException if session does not exists or already destroyed
     * @throws RepositoryInfrastructureException when there is any infrastructure issue
     */
    function invalidateSession($sessionId);
}
