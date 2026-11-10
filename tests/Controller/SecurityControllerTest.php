<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Login / logout flows, driven through the real forms like a user would.
 */
final class SecurityControllerTest extends WebTestCase
{
    private const EMAIL = 'camille@gestionprojet.test';
    private const PASSWORD = 'correct horse battery staple';

    private KernelBrowser $browser;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->browser = static::createClient();
        $this->browser->disableReboot();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->em->getConnection()->beginTransaction();

        $user = (new User())->setEmail(self::EMAIL);
        $user->setPassword(static::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, self::PASSWORD));
        $this->em->persist($user);
        $this->em->flush();
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->rollBack();
        parent::tearDown();
    }

    public function testAnonymousVisitorIsRedirectedToLogin(): void
    {
        foreach (['/', '/project', '/client'] as $url) {
            $this->browser->request('GET', $url);
            self::assertResponseRedirects('/login', message: $url);
        }
    }

    public function testLoginPageShowsTheForm(): void
    {
        $this->browser->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Connexion');
        self::assertSelectorExists('input[name="_username"][type="email"][required]');
        self::assertSelectorExists('input[name="_password"][type="password"][required]');
        self::assertSelectorExists('input[name="_csrf_token"]');
        self::assertSelectorNotExists('#sidebar');
    }

    public function testValidCredentialsLogTheUserIn(): void
    {
        $this->login(self::EMAIL, self::PASSWORD);

        self::assertResponseRedirects('/');
        $this->browser->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#sidebar', self::EMAIL);
        self::assertSelectorTextContains('#sidebar', 'Déconnexion');
    }

    public function testWrongPasswordShowsAnErrorAndKeepsTheEmail(): void
    {
        // Unique email so repeated runs never hit the login throttling limit
        $email = sprintf('inconnu-%s@gestionprojet.test', bin2hex(random_bytes(4)));
        $this->login($email, 'wrong password');

        self::assertResponseRedirects('/login');
        $this->browser->followRedirect();

        self::assertSelectorTextContains('[role="alert"]', 'Identifiants invalides.');
        self::assertInputValueSame('_username', $email);
    }

    public function testLoggedInUserVisitingLoginIsSentHome(): void
    {
        $this->login(self::EMAIL, self::PASSWORD);
        $this->browser->request('GET', '/login');

        self::assertResponseRedirects('/');
    }

    public function testLogoutFromTheSidebar(): void
    {
        $this->login(self::EMAIL, self::PASSWORD);
        $crawler = $this->browser->request('GET', '/');

        $this->browser->submit($crawler->selectButton('Déconnexion')->form());

        self::assertResponseRedirects('/login');
        $this->browser->request('GET', '/');
        self::assertResponseRedirects('/login');
    }

    public function testLogoutIsNotPossibleWithGet(): void
    {
        $this->login(self::EMAIL, self::PASSWORD);
        $this->browser->request('GET', '/logout');

        self::assertResponseStatusCodeSame(405);
    }

    private function login(string $email, string $password): void
    {
        $crawler = $this->browser->request('GET', '/login');
        $this->browser->submit($crawler->selectButton('Se connecter')->form([
            '_username' => $email,
            '_password' => $password,
        ]));
    }
}
