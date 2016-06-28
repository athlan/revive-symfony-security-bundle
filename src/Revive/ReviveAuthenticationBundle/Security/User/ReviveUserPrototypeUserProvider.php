<?php

namespace Revive\ReviveAuthenticationBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

/**
 * The provider of ReviveUserPrototype.
 *
 * Simply provides fake created ReviveUserPrototype only by username.
 *
 * @see ReviveUserPrototype
 * @package Revive\ReviveAuthenticationBundle\Security\User
 */
class ReviveUserPrototypeUserProvider implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {
        if(null === $username) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $username)
            );
        }

        return new ReviveUserPrototype($username);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof ReviveUserPrototype) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Revive\ReviveAuthenticationBundle\Security\User\ReviveUserPrototype';
    }
}
