<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Bienvenue sur GestionProjet');
        self::assertSelectorExists('a[aria-current="page"][href="/"]');
        self::assertSelectorExists('a[href="/project"]');
    }
}
