<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Fictional clients: invented people and businesses, ".example" email domains
 * (reserved, never delivered) and phone numbers from the ranges ARCEP sets aside
 * for fiction (01 99 00…, 06 39 98…).
 *
 * 24 clients = 3 pages on /client; the last three have no project yet.
 */
final class ClientFixtures extends Fixture
{
    public const PREFIX = 'client_';

    /** key => [firstName, lastName, company, email, phone] */
    private const CLIENTS = [
        'boulangerie' => ['Marie', 'Lemoine', 'Boulangerie Lemoine', 'marie@boulangerie-lemoine.example', '01 99 00 12 34'],
        'avocats' => ['Antoine', 'Dufresne', 'Cabinet Dufresne Avocats', 'a.dufresne@dufresne-avocats.example', '01 99 00 45 10'],
        'velo' => ['Lucas', 'Bernard', 'Atelier Vélo Nantais', 'lucas@atelier-velo-nantais.example', '06 39 98 21 07'],
        'domaine' => ['Hélène', 'Vasseur', 'Domaine des Trois Chênes', 'contact@trois-chenes.example', '01 99 00 78 52'],
        'veterinaire' => ['Thomas', 'Girard', 'Clinique vétérinaire du Parc', 'accueil@veto-du-parc.example', '01 99 00 33 61'],
        'librairie' => ['Claire', 'Fontaine', 'Librairie La Page Blanche', 'claire@la-page-blanche.example', '01 99 00 90 15'],
        'transports' => ['Philippe', 'Garnier', 'Transports Garnier', 'p.garnier@transports-garnier.example', '01 99 00 64 28'],
        'hotel' => ['Isabelle', 'Marchand', 'Hôtel Le Belvédère', 'direction@hotel-belvedere.example', '01 99 00 27 93'],
        'fromagerie' => ['Nicolas', 'Morel', 'Fromagerie Morel', 'nicolas@fromagerie-morel.example', '06 39 98 55 40'],
        'musique' => ['Sophie', 'Laurent', 'École de musique Allegro', 'secretariat@ecole-allegro.example', '01 99 00 11 72'],
        'brasserie' => ['Karim', 'Benali', 'Brasserie du Port', 'karim@brasserie-du-port.example', '06 39 98 76 03'],
        'garage' => ['Didier', 'Perrin', 'Garage Perrin Automobiles', 'atelier@garage-perrin.example', '01 99 00 58 46'],
        'fleurs' => ['Léa', 'Chevalier', 'Fleurs & Sens', 'lea@fleurs-et-sens.example', '06 39 98 14 89'],
        'yoga' => ['Emma', 'Rousseau', 'Studio Yoga Lumière', 'emma@yoga-lumiere.example', null],
        'menuiserie' => ['Pierre', 'Roux', 'Menuiserie Roux & Fils', 'devis@menuiserie-roux.example', '01 99 00 82 37'],
        'jardins' => ['Nathalie', 'Blanc', 'Association Les Jardins Partagés', 'bureau@jardins-partages.example', null],
        'houblon' => ['Hugo', 'Mercier', 'Houblon & Cie', 'hugo@houblon-et-cie.example', '06 39 98 62 15'],
        'photographe' => ['Manon', 'Dubois', null, 'manon.dubois@photo-manon.example', '06 39 98 30 58'],
        'dentiste' => ['Olivier', 'Faure', 'Cabinet dentaire du Centre', 'secretariat@dentaire-centre.example', '01 99 00 49 20'],
        'architecte' => ['Chloé', 'Lambert', 'Atelier Lambert Architecture', 'chloe@lambert-architecture.example', '01 99 00 36 84'],
        'traiteur' => ['Mehdi', 'Haddad', 'Haddad Traiteur', 'commandes@haddad-traiteur.example', '06 39 98 47 26'],
        'coiffure' => ['Julie', 'Moreau', "Salon Ciseaux d'Argent", 'julie@ciseaux-argent.example', '01 99 00 15 63'],
        'auto_ecole' => ['Sébastien', 'Petit', 'Auto-école Feu Vert', null, '01 99 00 70 91'],
        'camping' => ['Aurélie', 'Gauthier', 'Camping Les Pins', 'reservation@camping-les-pins.example', '01 99 00 23 45'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CLIENTS as $key => [$firstName, $lastName, $company, $email, $phone]) {
            $client = (new Client())
                ->setFirstName($firstName)
                ->setLastName($lastName)
                ->setCompany($company)
                ->setEmail($email)
                ->setPhone($phone);

            $manager->persist($client);
            $this->addReference(self::PREFIX.$key, $client);
        }

        $manager->flush();
    }
}
