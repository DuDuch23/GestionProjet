<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Team members. Every account's password is "password" (dev only).
 */
final class UserFixtures extends Fixture
{
    public const PASSWORD = 'password';

    public const ADMIN = 'user_admin';
    public const CAMILLE = 'user_camille';
    public const JULIEN = 'user_julien';
    public const SARAH = 'user_sarah';

    private const USERS = [
        self::ADMIN => ['admin@gestionprojet.test', ['ROLE_ADMIN']],
        self::CAMILLE => ['camille.martin@gestionprojet.test', []],
        self::JULIEN => ['julien.robert@gestionprojet.test', []],
        self::SARAH => ['sarah.lefebvre@gestionprojet.test', []],
    ];

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::USERS as $reference => [$email, $roles]) {
            $user = (new User())->setEmail($email)->setRoles($roles);
            $user->setPassword($this->passwordHasher->hashPassword($user, self::PASSWORD));

            $manager->persist($user);
            $this->addReference($reference, $user);
        }

        $manager->flush();
    }
}
