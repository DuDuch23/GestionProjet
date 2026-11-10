<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Base class for pages behind the login: every test runs inside a rolled-back
 * transaction, with a logged-in user.
 */
abstract class AuthenticatedWebTestCase extends WebTestCase
{
    protected KernelBrowser $browser;
    protected EntityManagerInterface $em;
    protected User $user;

    protected function setUp(): void
    {
        $this->browser = static::createClient();
        $this->browser->disableReboot(); // same DB connection for every request, so the transaction is visible
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->em->getConnection()->beginTransaction();

        $this->user = (new User())->setEmail('test.user@gestionprojet.test')->setPassword('unused-hash');
        $this->em->persist($this->user);
        $this->em->flush();

        $this->browser->loginUser($this->user);
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->rollBack();
        parent::tearDown();
    }
}
