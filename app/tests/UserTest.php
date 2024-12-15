<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class UserTest extends ApiTestCase
{

    use RefreshDatabaseTrait;

    private const SUPER_ADMIN_API_TOKEN = 'Mjzuzui6OP4GTIzPyYFvDQfwDk3YB43A3GWIhyog8HzCqumM5c8cYK4RluQC50A3';
    private const COMPANY_ADMIN_API_TOKEN = '2aaOMGpjRKzi2korX1SW1Llt0UUCvo7xm4eOqKYF6bbG95AwBsglve8GTGGULVPC';
    private const USER_COMPANY1_API_TOKEN = 'ebFQqaYip973iwOYSo4GfTAlMheZToLot5EJXsbOPGKF3L6xaFSTQ8GrLktOdLBf';
    private const USER_COMPANY2_API_TOKEN = 'hDf4GZmUSIPdMP8y5z18OmZWE1r2XbcJXHFvBoXaVUjQ2pAIPC0pxHeSrJrRezM0';

    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    public function testCanAdminGetWholeListOfUsers(): void
    {
        $response = $this->client->request('GET', '/api/users', [
            'headers' => ['x-api-token' => self::SUPER_ADMIN_API_TOKEN]
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertCount(5, $response->toArray()['member']);
    }

    public function testDoesCompanyAdminJustGetListOfTheirOwnCompanyUsers(): void
    {
        $response = $this->client->request('GET', '/api/users', [
            'headers' => ['x-api-token' => self::COMPANY_ADMIN_API_TOKEN]
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertCount(3, $response->toArray()['member']);
    }

    public function testDoesUserJustGetListOfTheirOwnCompanyUsers(): void
    {
        $response = $this->client->request('GET', '/api/users', [
            'headers' => ['x-api-token' => self::USER_COMPANY2_API_TOKEN]
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertCount(1, $response->toArray()['member']);
    }

    public function testCanAdminImpersonateUserRoleAndGetListOfTheirOwnCompanyUsers(): void
    {
        $response = $this->client->request('GET', '/api/users', [
            'headers' => [
                'x-api-token' => self::SUPER_ADMIN_API_TOKEN,
                'x-switch-user' => 'company2user@example.com'
            ]
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertCount(1, $response->toArray()['member']);
    }

    public function testCanSuperAdminCreateUser(): void
    {
        $this->client->request('POST', '/api/users', [
            'headers' => [
                'content-type' => 'application/ld+json',
                'x-api-token' => self::SUPER_ADMIN_API_TOKEN
            ],
            'json'    => [
                'roles'   => ['ROLE_SUPER_ADMIN'],
                'name'    => 'Alexander',
                'email'   => 'alexander@example.com',
                'password'=> 'password'
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);

        $this->assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );

        $this->assertJsonContains([
            'email' => 'alexander@example.com',
            'name'  => 'Alexander',
            "roles" => [
                "ROLE_SUPER_ADMIN",
                "ROLE_USER"
            ],
        ]);
    }

    public function testDoesPreventUserRoleToCreateUser(): void
    {
        $this->client->request('POST', '/api/users', [
            'headers' => [
                'content-type' => 'application/ld+json',
                'x-api-token' => self::USER_COMPANY1_API_TOKEN
            ],
            'json'    => [
                'roles'   => ['ROLE_COMPANY_ADMIN'],
                'name'    => 'Alexander',
                'email'   => 'alexander@example.com',
                'password'=> 'password'
            ]
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCanAdminDeleteUser(): void
    {
        $user = $this->userRepository->findOneby(['email' => 'company2user@example.com']);
        $this->client->request('DELETE', '/api/users/'.$user->getId(), [
            'headers' => [
                'content-type' => 'application/ld+json',
                'x-api-token' => self::SUPER_ADMIN_API_TOKEN
            ],
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testDoesPreventCompanyAdminToDeleteUser(): void
    {
        $user = $this->userRepository->findOneby(['email' => 'company1user@example.com']);
        $this->client->request('DELETE', '/api/users/'.$user->getId(), [
            'headers' => [
                'content-type' => 'application/ld+json',
                'x-api-token' => self::COMPANY_ADMIN_API_TOKEN
            ],
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDoesInvalidTokenReturnUnauthorizedError(): void
    {
        $this->client->request('GET', '/api/users', [
            'headers' => ['x-api-token' => 'fake-token'],
        ]);

        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains([
            'message'         => 'Invalid credentials.',
        ]);
    }

    public function testDoesPreventUserCreationWithInvalidName(): void
    {
        $this->client->request('POST', '/api/users', [
            'headers' => [
                'content-type' => 'application/ld+json',
                'x-api-token' => self::SUPER_ADMIN_API_TOKEN
            ],
            'json'    => [
                'roles'   => ['ROLE_SUPER_ADMIN'],
                'name'    => 'alexander',
                'email'   => 'alexander@example.com',
                'password'=> 'password'
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
    }
}