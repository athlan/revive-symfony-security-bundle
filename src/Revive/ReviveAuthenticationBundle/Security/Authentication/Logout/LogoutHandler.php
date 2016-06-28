<?php

namespace Revive\ReviveAuthenticationBundle\Security\Authentication\Logout;


use Revive\ReviveAuthenticationBundle\Repository\Exception\RepositoryInfrastructureException;
use Revive\ReviveAuthenticationBundle\Repository\UserSessionRepository;
use Revive\ReviveAuthenticationBundle\Security\Authentication\Token\ReviveAuthenticationToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

class LogoutHandler implements LogoutHandlerInterface {

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
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        if($token instanceof ReviveAuthenticationToken) {
            if($token->isAuthenticated()) {
                $sessionId = $token->getSessionId();

                try {
                    $this->userSessionRepository->invalidateSession($sessionId);
                }
                catch(RepositoryInfrastructureException $ignored) {
                }
                catch(\InvalidArgumentException $ignored) {
                }
            }
        }
    }
}
