<?php

namespace App\Tests\Controller;

final class HomeControllerTest extends AuthenticatedWebTestCase
{
    public function testIndex(): void
    {
        $this->browser->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Bienvenue sur GestionProjet');
        self::assertSelectorExists('#sidebar a[aria-current="page"][href="/"]');
        self::assertSelectorExists('#sidebar a[href="/project"]');
        self::assertSelectorExists('#sidebar a[href="/client"]');
    }
}
