<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CountryControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertSelectorTextContains('h1', 'Country index');
    }

    public function testNewCountry(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/new');

        $this->assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->filter('form[name=country]')->form([
            'country[name]' => 'TestCountry',
            'country[capital]' => 'TestCapital',
            'country[region]' => 'TestRegion',
            'country[flag]' => 'https://example.com/flag.png',
        ]);

        $client->submit($form);

        $this->assertTrue($client->getResponse()->isRedirect('/'));

        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Country created successfully!');
    }
}