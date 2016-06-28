<?php

namespace Revive\ReviveAuthenticationBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * This class represents all authenticated Revive user tokens
 * in Security Context.
 *
 * Token contains sessionId and username.
 *
 * @package Revive\ReviveAuthenticationBundle\Security\Authentication\Token
 */
class ReviveAuthenticationToken extends UsernamePasswordToken {

    /**
     * Constructor.
     *
     * @param string|object            $user        The username (like a nickname, email address, etc.), or a UserInterface instance or an object implementing a __toString method.
     * @param string                   $sessionId   Session id
     * @param string                   $providerKey The provider key
     * @param RoleInterface[]|string[] $roles       An array of roles
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($user, $sessionId, $providerKey, array $roles = array())
    {
        parent::__construct($user, $sessionId, $providerKey, $roles);

        $this->setAttribute("sessionId", $sessionId);
    }

    /**
     * @return mixed|string sessionId
     */
    public function getSessionId()
    {
        return $this->getAttribute("sessionId");
    }
}
