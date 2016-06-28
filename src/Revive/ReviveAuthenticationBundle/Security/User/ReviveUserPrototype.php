<?php

namespace Revive\ReviveAuthenticationBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

/**
 * This class represents Revive user during authentication.
 *
 * It is fake user that contains only username, because password is provided in login process.
 *
 * @see ReviveUserPrototypeUserProvider
 * @package Revive\ReviveAuthenticationBundle\Security\User
 */
class ReviveUserPrototype implements UserInterface, EquatableInterface
{
    private $username;
    private $password;
    private $salt;
    private $roles;

    public function __construct($username)
    {
        $this->username = $username;
        $this->password = null;
        $this->salt = null;
        $this->roles = array();
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof ReviveUserPrototype) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }
}
