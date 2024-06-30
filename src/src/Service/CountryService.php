<?php

namespace App\Service;

use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class CountryService
{
    private $client;
    private $entityManager;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $entityManager)
    {
        $this->client = $client;
        $this->entityManager = $entityManager;
    }

    public function fetchAndStoreCountries(): void
    {
        try {

            // Verificar si los datos ya están inicializados
            $initialized = $this->areCountriesInitialized();
            if ($initialized) {
                return;
            }
                
            $apiUrl = $_ENV['COUNTRY_API_URL']; // Obtener la URL de la API desde variables de entorno
            $response = $this->client->request('GET', $apiUrl);
            $countriesData = $response->toArray();

            foreach ($countriesData as $countryData) {
                $country = new Country();
                $country->setName($countryData['name']['common'] ?? 'N/A');
                $country->setCapital($countryData['capital'][0] ?? 'N/A');
                $country->setRegion($countryData['region'] ?? 'N/A');
                $flag = $this->extractFlagUrl($countryData);
                $country->setFlag($flag);
                $country->setIsInitialized(true);
                $this->entityManager->persist($country);
            }

            $this->entityManager->flush();

        } catch (HttpExceptionInterface $e) {
            throw new \RuntimeException('Error al obtener datos de la API: ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new \RuntimeException('Error inesperado: ' . $e->getMessage());
        }
    }

    private function areCountriesInitialized(): bool
    {
        try {
            $query = $this->entityManager->createQueryBuilder()
                ->select('c')
                ->from(Country::class, 'c')
                ->where('c.isInitialized = :isInitialized')
                ->setParameter('isInitialized', true)
                ->setMaxResults(1)
                ->getQuery();

            $result = $query->getSingleResult();

            return $result !== null;
        } catch (NoResultException | NonUniqueResultException $e) {
            return false;
        }
    }


    private function extractFlagUrl(array $countryData): ?string
    {
        return $countryData['flags']['png'] ?? null;
    }

    public function getAllCountries()
    {
        return $this->entityManager->getRepository(Country::class)->findAll();
    }

    public function truncateCountryTable(): void
    {
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeStatement($platform->getTruncateTableSQL('country', true));
    }
}
