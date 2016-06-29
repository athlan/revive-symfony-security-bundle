<?php

namespace Revive\ReviveAuthenticationBundle\Security\Authentication\Logout;

use \Mockery as m;
use \Mockery\Adapter\PHPUnit\MockeryPHPUnitIntegration;
use Revive\ReviveAuthenticationBundle\Repository\Exception\RepositoryInfrastructureException;
use Revive\ReviveAuthenticationBundle\Repository\UserSessionRepository;
use Revive\ReviveAuthenticationBundle\Security\Authentication\Token\ReviveAuthenticationToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class LogoutHandlerTest extends \PHPUnit_Framework_TestCase {

    use MockeryPHPUnitIntegration;

    /**
     * @var m\MockInterface
     */
    private $userSessionRepoitory;

    /**
     * @var LogoutHandler
     */
    private $sut;

    /**
     * @before
     */
    public function prepareTest() {
        $this->userSessionRepoitory = m::mock(UserSessionRepository::class);
        $this->sut = new LogoutHandler($this->userSessionRepoitory);
    }

    /**
     * @test
     */
    public function will_call_session_invalidation_when_proper_token()
    {
        // given
        $sessionId = 'some_session_id';
        $anyRequest = m::mock(Request::class);
        $anyResponse = m::mock(Response::class);
        $token = new ReviveAuthenticationToken("user", $sessionId, "any_provider_key", ['USER']);

        $this->userSessionRepoitory->shouldReceive('invalidateSession')->with($sessionId)->once();

        // when
        $this->sut->logout($anyRequest, $anyResponse, $token);
    }

    /**
     * @test
     */
    public function will_not_call_session_invalidation_when_unsupported_token()
    {
        // given
        $anyRequest = m::mock(Request::class);
        $anyResponse = m::mock(Response::class);
        $token = new UsernamePasswordToken("user", "pass", "any_provider_key", ['USER']);

        $this->userSessionRepoitory->shouldReceive('invalidateSession')->withAnyArgs()->never();

        // when
        $this->sut->logout($anyRequest, $anyResponse, $token);
    }

    /**
     * @test
     */
    public function mutes_infratructure_exception()
    {
        // given
        $sessionId = 'some_session_id';
        $anyRequest = m::mock(Request::class);
        $anyResponse = m::mock(Response::class);
        $token = new ReviveAuthenticationToken("user", $sessionId, "any_provider_key", ['USER']);

        $this->userSessionRepoitory->shouldReceive('invalidateSession')->with($sessionId)->once()->andThrow(new RepositoryInfrastructureException());

        // when
        $this->sut->logout($anyRequest, $anyResponse, $token);
    }

    /**
     * @test
     */
    public function mutes_invalid_session_id_exception()
    {
        // given
        $sessionId = 'some_session_id';
        $anyRequest = m::mock(Request::class);
        $anyResponse = m::mock(Response::class);
        $token = new ReviveAuthenticationToken("user", $sessionId, "any_provider_key", ['USER']);

        $this->userSessionRepoitory->shouldReceive('invalidateSession')->with($sessionId)->once()->andThrow(new \InvalidArgumentException());

        // when
        $this->sut->logout($anyRequest, $anyResponse, $token);
    }
}
