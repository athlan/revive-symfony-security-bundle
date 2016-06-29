<?php

namespace Revive\ReviveAuthenticationBundle\Repository\Impl;

use fXmlRpc\Client;
use fXmlRpc\ClientInterface;
use fXmlRpc\Exception\FaultException;
use fXmlRpc\Exception\TransportException;
use PHPUnit\Framework\TestCase;

use Mockery as m;
use Mockery\Adapter\PHPUnit\MockeryPHPUnitIntegration;
use Revive\ReviveAuthenticationBundle\Repository\Exception\RepositoryInfrastructureException;

class XmlRpcUserSessionRepositoryTest extends TestCase {

    use MockeryPHPUnitIntegration;

    /**
     * @var m\MockInterface
     */
    private $client;

    /**
     * @var XmlRpcUserSessionRepository
     */
    private $sut;

    /**
     * @before
     */
    public function prepareTest() {
        $this->client = m::mock(ClientInterface::class);
        $this->sut = new XmlRpcUserSessionRepository($this->client, 'any-url');
    }

    /**
     * @test
     */
    public function will_throw_ise_when_blank_username()
    {
        // given
        $blank_username = "";
        $password = "any_password";

        // when
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createSessionIdByCredentials($blank_username, $password);
    }

    /**
     * @test
     */
    public function will_throw_ise_when_blank_password()
    {
        // given
        $username = "any_username";
        $blank_password = "";

        // when
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createSessionIdByCredentials($username, $blank_password);
    }

    /**
     * @test
     */
    public function will_throw_ise_when_invalid_credentials()
    {
        // given
        $invalid_username = "any_username";
        $invalid_password = "any_password";
        $this->client->shouldReceive('call')->withAnyArgs()->andThrow(FaultException::fault([
            'faultCode' => 801,
        ]));

        // when
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createSessionIdByCredentials($invalid_username, $invalid_password);
    }

    /**
     * @test
     */
    public function will_throw_infrastructure_exception_when_connection_error()
    {
        // given
        $username = "any_username";
        $password = "any_password";
        $this->client->shouldReceive('call')->withAnyArgs()->andThrow(TransportException::transportError("Some error"));

        // when
        $this->expectException(RepositoryInfrastructureException::class);
        $this->sut->createSessionIdByCredentials($username, $password);
    }
}
