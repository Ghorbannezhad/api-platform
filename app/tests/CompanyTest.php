<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Repository\CompanyRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class CompanyTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private const SUPER_ADMIN_API_TOKEN = 'Mjzuzui6OP4GTIzPyYFvDQfwDk3YB43A3GWIhyog8HzCqumM5c8cYK4RluQC50A3';
    private const COMPANY_ADMIN_API_TOKEN = '2aaOMGpjRKzi2korX1SW1Llt0UUCvo7xm4eOqKYF6bbG95AwBsglve8GTGGULVPC';
    private const USER_COMPANY1_API_TOKEN = 'ebFQqaYip973iwOYSo4GfTAlMheZToLot5EJXsbOPGKF3L6xaFSTQ8GrLktOdLBf';

    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    public function testCanUserGetWholeListOfUsers(): void
    {
        $response = $this->client->request('GET', '/api/companies', [
            'headers' => ['x-api-token' => self::USER_COMPANY1_API_TOKEN]
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertCount(10, $response->toArray()['member']);
    }

    public function testCanAdminCreateNewCompany(): void
    {
        $response = $this->client->request('POST', '/api/companies', [
            'headers' => [
                'content-type' => 'application/ld+json',
                'x-api-token' => self::SUPER_ADMIN_API_TOKEN
            ],
            'json'    => [
                'name'    => 'Super Admin Co.',
            ]
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testDoesPreventCompanyAdminToCreateCompany(): void
    {
        $response = $this->client->request('POST', '/api/companies', [
            'headers' => [
                'content-type' => 'application/ld+json',
                'x-api-token' => self::COMPANY_ADMIN_API_TOKEN
            ],
            'json'    => [
                'name'    => 'Company Admin Co.',
            ]
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

}
