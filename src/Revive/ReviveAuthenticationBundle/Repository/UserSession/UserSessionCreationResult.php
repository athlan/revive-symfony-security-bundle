<?php

namespace Revive\ReviveAuthenticationBundle\Repository\UserSession;


class UserSessionCreationResult {

    /**
     * @var int
     * @see UserSessionCreationAuthenticationResult
     */
    private $sessionCreationAuthenticationResult;

    /**
     * @var int
     * @see UserSessionCreationAuthorizationSessionCreationResult
     */
    private $sessionCreationAuthorizationSessionCreation;

    /**
     * @var string|null
     */
    private $sessionId;

    public function __construct($sessionCreationAuthenticationResult, $sessionCreationAuthorizationSessionCreation, $sessionId) {
        $this->sessionCreationAuthenticationResult = $sessionCreationAuthenticationResult;
        $this->sessionCreationAuthorizationSessionCreation = $sessionCreationAuthorizationSessionCreation;
        $this->sessionId = $sessionId;
    }

    /**
     * @return int
     * @see UserSessionCreationAuthenticationResult
     */
    public function getSessionCreationAuthenticationResult()
    {
        return $this->sessionCreationAuthenticationResult;
    }

    /**
     * @return int
     * @see UserSessionCreationAuthorizationSessionCreationResult
     */
    public function getSessionCreationAuthorizationSessionCreation()
    {
        return $this->sessionCreationAuthorizationSessionCreation;
    }

    /**
     * @return string|null
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }
}
