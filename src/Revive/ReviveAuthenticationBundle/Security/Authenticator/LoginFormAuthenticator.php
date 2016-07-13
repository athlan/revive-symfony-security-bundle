<?php

namespace Revive\ReviveAuthenticationBundle\Security\Authenticator;

use Revive\ReviveAuthenticationBundle\Repository\Exception\RepositoryInfrastructureException;
use Revive\ReviveAuthenticationBundle\Repository\UserSession\UserSessionCreationAuthenticationResult;
use Revive\ReviveAuthenticationBundle\Repository\UserSession\UserSessionCreationAuthorizationSessionCreationResult;
use Revive\ReviveAuthenticationBundle\Repository\UserSessionRepository;
use Revive\ReviveAuthenticationBundle\Security\Authentication\Token\ReviveAuthenticationToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;

/**
 * This class can exchange user-password token provided by login form
 * to fully authenticated ReviveAuthenticationToken.
 *
 * @package Revive\ReviveAuthenticationBundle\Security\Authenticator
 * @see ReviveAuthenticationToken
 *
 * @author Athlan
 */
class LoginFormAuthenticator implements SimpleFormAuthenticatorInterface {

    private $userSessionRepository;

    /**
     * @param UserSessionRepository $userSessionRepository the user session repository
     */
    public function __construct(UserSessionRepository $userSessionRepository)
    {
        $this->userSessionRepository = $userSessionRepository;
    }

    /**
     * @inheritdoc
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        try {
            $user = $userProvider->loadUserByUsername($token->getUsername());
        } catch (UsernameNotFoundException $e) {
            throw new CustomUserMessageAuthenticationException('Invalid username or password');
        }

        $username = $token->getUsername();
        $password = $token->getCredentials();

        $sessionCreationResult = null;
        try {
            $sessionCreationResult = $this->userSessionRepository->createSessionIdByCredentials($username, $password);
        }
        catch(\InvalidArgumentException $e) {
            throw new CustomUserMessageAuthenticationException('Invalid username or password');
        }
        catch(RepositoryInfrastructureException $e) {
            throw new CustomUserMessageAuthenticationException('Cannot connect to Revive service');
        }

        $passwordValid = ($sessionCreationResult !== null) && UserSessionCreationAuthenticationResult::isSuccess($sessionCreationResult->getSessionCreationAuthenticationResult());

        if ($passwordValid) {
            $sessionId = $sessionCreationResult->getSessionId();
            $roles = [];

            $roles[] = 'USER';

            if(UserSessionCreationAuthorizationSessionCreationResult::isSuccess($sessionCreationResult->getSessionCreationAuthorizationSessionCreation())) {
                $roles[] = 'ADMIN';
            }

            $token = new ReviveAuthenticationToken(
                $user,
                $sessionId,
                $providerKey,
                $roles
            );

            return $token;
        }

        throw new CustomUserMessageAuthenticationException('Invalid username or password');
    }

    /**
     * @inheritdoc
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof UsernamePasswordToken
                && $token->getProviderKey() === $providerKey;
    }

    /**
     * @inheritdoc
     */
    public function createToken(Request $request, $username, $password, $providerKey)
    {
        return new UsernamePasswordToken($username, $password, $providerKey);
    }
}
