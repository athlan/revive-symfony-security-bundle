<?php

namespace Revive\ReviveAuthenticationBundle\Security\Authenticator;

use Mockery as m;
use Mockery\Adapter\PHPUnit\MockeryPHPUnitIntegration;
use Revive\ReviveAuthenticationBundle\Repository\Exception\RepositoryInfrastructureException;
use Revive\ReviveAuthenticationBundle\Repository\UserSession\UserSessionCreationAuthenticationResult;
use Revive\ReviveAuthenticationBundle\Repository\UserSession\UserSessionCreationAuthorizationSessionCreationResult;
use Revive\ReviveAuthenticationBundle\Repository\UserSession\UserSessionCreationResult;
use Revive\ReviveAuthenticationBundle\Repository\UserSessionRepository;
use Revive\ReviveAuthenticationBundle\Security\Authentication\Token\ReviveAuthenticationToken;
use Revive\ReviveAuthenticationBundle\Security\User\ReviveUserPrototype;
use Revive\ReviveAuthenticationBundle\Security\User\ReviveUserPrototypeUserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class LoginFormAuthenticatorTest extends \PHPUnit_Framework_TestCase {

    use MockeryPHPUnitIntegration;

    /**
     * @var m\MockInterface
     */
    private $userSessionRepoitory;

    /**
     * @var m\MockInterface
     */
    private $userProvider;

    /**
     * @var LoginFormAuthenticator
     */
    private $sut;

    /**
     * @before
     */
    public function prepareTest() {
        $this->userSessionRepoitory = m::mock(UserSessionRepository::class);
        $this->userProvider = m::mock(ReviveUserPrototypeUserProvider::class);
        $this->sut = new LoginFormAuthenticator($this->userSessionRepoitory);
    }

    /**
     * @test
     */
    public function authenticates_successfully()
    {
        // given
        $username = "any_username";
        $password = "any_password";
        $providerKey = "any_provider_key";
        $token = new UsernamePasswordToken($username, $password, $providerKey);
        $sessionCreationResult = new UserSessionCreationResult(
            UserSessionCreationAuthenticationResult::SUCCESS,
            UserSessionCreationAuthorizationSessionCreationResult::SUCCESS,
            'some_session_id'
        );
        $prototypeToken = new ReviveUserPrototype($username);

        $this->userProvider->shouldReceive('loadUserByUsername')->with($username)->once()->andReturn($prototypeToken);
        $this->userSessionRepoitory->shouldReceive('createSessionIdByCredentials')->with($username, $password)->once()->andReturn($sessionCreationResult);

        // when
        $authenticatedToken = $this->sut->authenticateToken($token, $this->userProvider, $providerKey);

        $this->assertTrue($authenticatedToken->isAuthenticated(),
            "Token should not be authenticated.");

        $this->assertEquals($sessionCreationResult->getSessionId(), $authenticatedToken->getSessionId(),
            "Token should contains valid session id.");
    }

    /**
     * @test
     */
    public function authenticates_successfully_while_not_admin()
    {
        // given
        $username = "any_username";
        $password = "any_password";
        $providerKey = "any_provider_key";
        $token = new UsernamePasswordToken($username, $password, $providerKey);
        $sessionCreationResult = new UserSessionCreationResult(
            UserSessionCreationAuthenticationResult::SUCCESS,
            UserSessionCreationAuthorizationSessionCreationResult::FAILED_NOT_ADMIN,
            null
        );
        $prototypeToken = new ReviveUserPrototype($username);

        $this->userProvider->shouldReceive('loadUserByUsername')->with($username)->once()->andReturn($prototypeToken);
        $this->userSessionRepoitory->shouldReceive('createSessionIdByCredentials')->with($username, $password)->once()->andReturn($sessionCreationResult);

        // when
        $authenticatedToken = $this->sut->authenticateToken($token, $this->userProvider, $providerKey);

        $this->assertTrue($authenticatedToken->isAuthenticated(),
            "Token should not be authenticated.");

        $this->assertEquals(null, $authenticatedToken->getSessionId(),
            "Token should contains valid session id.");
    }

    /**
     * @test
     */
    public function supports_valid_token()
    {
        // given
        $providerKey = "any_provider_key";
        $anotherProviderKey = "another_provider_key";
        $unsupportedToken = new AnonymousToken("any_secret", "anu_user", ['USER']);
        $token = new UsernamePasswordToken("user", "pass", "any_provider_key", ['USER']);

        // when
        $this->assertTrue($this->sut->supportsToken($token, $providerKey),
            "Returns true when supported token and the same provider key.");

        $this->assertFalse($this->sut->supportsToken($token, $anotherProviderKey),
            "Returns false when supported token and different provider key.");

        $this->assertFalse($this->sut->supportsToken($unsupportedToken, $providerKey),
            "Returns false when unsupported token and the same provider key.");

        $this->assertFalse($this->sut->supportsToken($unsupportedToken, $anotherProviderKey),
            "Returns false when unsupported token and different provider key.");
    }


    /**
     * @test
     */
    public function creates_valid_token()
    {
        // given
        $request = m::mock(Request::class);
        $username = "any_username";
        $password = "any_password";
        $providerKey = "any_provider_key";

        // when
        $generatedToken = $this->sut->createToken($request, $username, $password, $providerKey);

        $this->assertTrue($generatedToken instanceof UsernamePasswordToken,
            "Returns token instance of UsernamePasswordToken.");

        $this->assertEquals($username, $generatedToken->getUser(),
            "Returns token with proper username.");

        $this->assertEquals($password, $generatedToken->getCredentials(),
            "Returns token with proper password.");

        $this->assertEquals($providerKey, $generatedToken->getProviderKey(),
            "Returns token with proper provider key.");
    }

    /**
     * @test
     */
    public function handles_invalid_username_and_throws_exception()
    {
        // given
        $username = "any_username";
        $password = "any_password";
        $providerKey = "any_provider_key";
        $token = new UsernamePasswordToken($username, $password, $providerKey);

        $this->userProvider->shouldReceive('loadUserByUsername')->with($username)->once()->andThrow(new UsernameNotFoundException());

        // when
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $authenticatedToken = $this->sut->authenticateToken($token, $this->userProvider, $providerKey);

        $this->assertFalse($authenticatedToken->isAuthenticated(),
            "Token should not be authenticated.");
    }

    /**
     * @test
     */
    public function handles_invalid_credentials_and_throws_exception()
    {
        // given
        $username = "any_username";
        $password = "any_password";
        $providerKey = "any_provider_key";
        $token = new UsernamePasswordToken($username, $password, $providerKey);
        $prototypeToken = new ReviveUserPrototype($username);

        $this->userProvider->shouldReceive('loadUserByUsername')->with($username)->once()->andReturn($prototypeToken);
        $this->userSessionRepoitory->shouldReceive('createSessionIdByCredentials')->with($username, $password)->andThrow(new \InvalidArgumentException());

        // when
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $authenticatedToken = $this->sut->authenticateToken($token, $this->userProvider, $providerKey);

        $this->assertFalse($authenticatedToken->isAuthenticated(),
            "Token should not be authenticated.");
    }

    /**
     * @test
     */
    public function handles_infrastructure_error_and_throws_exception()
    {
        // given
        $username = "any_username";
        $password = "any_password";
        $providerKey = "any_provider_key";
        $token = new UsernamePasswordToken($username, $password, $providerKey);
        $prototypeToken = new ReviveUserPrototype($username);

        $this->userProvider->shouldReceive('loadUserByUsername')->with($username)->once()->andReturn($prototypeToken);
        $this->userSessionRepoitory->shouldReceive('createSessionIdByCredentials')->with($username, $password)->andThrow(new RepositoryInfrastructureException());

        // when
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $authenticatedToken = $this->sut->authenticateToken($token, $this->userProvider, $providerKey);

        $this->assertFalse($authenticatedToken->isAuthenticated(),
            "Token should not be authenticated.");
    }
}
