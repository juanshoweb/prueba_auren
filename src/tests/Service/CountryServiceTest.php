<?php

namespace App\Tests\Service;

use App\Entity\Country;
use App\Service\CountryService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CountryServiceTest extends TestCase
{
    private $httpClient;
    private $entityManager;
    private $countryService;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->countryService = new CountryService($this->httpClient, $this->entityManager);
    }

    public function testFetchAndStoreCountries(): void
    {
        // Mock the response from the API
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')
            ->willReturn([
                [
                    'name' => ['common' => 'CountryName'],
                    'capital' => ['CapitalName'],
                    'region' => 'RegionName',
                    'flags' => ['png' => 'https://example.com/flag.png'],
                ],
            ]);

        $this->httpClient->method('request')
            ->willReturn($response);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Country::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->countryService->fetchAndStoreCountries();
    }
}
