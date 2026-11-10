<?php

namespace App\DataFixtures;

use App\Entity\Attachment;
use App\Entity\Client;
use App\Entity\Log;
use App\Entity\Project;
use App\Entity\User;
use App\Enum\LogAction;
use App\Enum\Priority;
use App\Enum\ProjectStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Realistic projects covering every status and priority, with overdue
 * deadlines, attachments (documents and images), history logs and two
 * soft-deleted projects.
 *
 * Due dates are relative ("+3 weeks", "-4 days") so "overdue" stays meaningful
 * whenever the fixtures are loaded. Attachments are metadata only: no file
 * exists on disk for them.
 */
final class ProjectFixtures extends Fixture implements DependentFixtureInterface
{
    private const PDF = 'application/pdf';
    private const PNG = 'image/png';
    private const JPG = 'image/jpeg';
    private const XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    private const DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    private const ZIP = 'application/zip';

    /**
     * client, name, description, priority, status, due (relative date or null),
     * by (creator), files [[name, mime, bytes]], deleted.
     */
    private const PROJECTS = [
        [
            'client' => 'boulangerie', 'name' => 'Site vitrine et précommande en ligne',
            'description' => 'Site vitrine avec module de précommande des viennoiseries et retrait en boutique le lendemain matin.',
            'priority' => Priority::High, 'status' => ProjectStatus::InProgress, 'due' => '+3 weeks', 'by' => UserFixtures::CAMILLE,
            'files' => [['cahier-des-charges.pdf', self::PDF, 482_310], ['maquette-accueil.png', self::PNG, 1_240_552]],
        ],
        [
            'client' => 'boulangerie', 'name' => 'Fiches produits et photos',
            'description' => 'Rédaction des fiches des 40 produits phares et séance photo en boutique.',
            'priority' => Priority::Low, 'status' => ProjectStatus::Todo, 'due' => '+2 months', 'by' => UserFixtures::CAMILLE,
        ],
        [
            'client' => 'avocats', 'name' => "Refonte de l'espace client",
            'description' => "Espace sécurisé pour l'échange de documents avec les clients du cabinet, avec authentification à deux facteurs.",
            'priority' => Priority::Critical, 'status' => ProjectStatus::InProgress, 'due' => '-4 days', 'by' => UserFixtures::JULIEN,
            'files' => [['specifications-securite.pdf', self::PDF, 936_004], ['devis-signe.pdf', self::PDF, 214_870]],
        ],
        [
            'client' => 'avocats', 'name' => 'Mise en conformité RGPD',
            'description' => 'Audit des traitements de données, registre, mentions légales et bandeau de consentement aux cookies.',
            'priority' => Priority::High, 'status' => ProjectStatus::Done, 'due' => '-1 month', 'by' => UserFixtures::SARAH,
            'files' => [['registre-traitements.xlsx', self::XLSX, 58_312]],
        ],
        [
            'client' => 'velo', 'name' => 'Réservation de réparations en ligne',
            'description' => "Prise de rendez-vous en ligne pour l'atelier, avec rappel par SMS la veille.",
            'priority' => Priority::Medium, 'status' => ProjectStatus::InProgress, 'due' => '+5 weeks', 'by' => UserFixtures::JULIEN,
        ],
        [
            'client' => 'domaine', 'name' => 'Boutique en ligne des vins',
            'description' => "Vente en ligne par millésime, frais de port par zone et vérification de l'âge à la commande.",
            'priority' => Priority::High, 'status' => ProjectStatus::OnHold, 'due' => '+3 months', 'by' => UserFixtures::SARAH,
            'files' => [['catalogue-2026.pdf', self::PDF, 3_412_908], ['photos-bouteilles.zip', self::ZIP, 18_204_311]],
        ],
        [
            'client' => 'domaine', 'name' => 'Étiquettes de la cuvée 2026',
            'description' => 'Création des étiquettes rouge, blanc et rosé pour la nouvelle cuvée.',
            'priority' => Priority::Medium, 'status' => ProjectStatus::Done, 'due' => '-2 weeks', 'by' => UserFixtures::CAMILLE,
            'files' => [['etiquette-rouge.png', self::PNG, 845_120], ['etiquette-blanc.png', self::PNG, 812_664]],
        ],
        [
            'client' => 'veterinaire', 'name' => 'Rappels de vaccination automatiques',
            'description' => "Envoi automatique d'emails de rappel de vaccination aux propriétaires d'animaux.",
            'priority' => Priority::Medium, 'status' => ProjectStatus::Todo, 'due' => '+6 weeks', 'by' => UserFixtures::JULIEN,
        ],
        [
            'client' => 'librairie', 'name' => 'Catalogue et réservation de livres',
            'description' => 'Recherche dans le stock de la librairie et réservation pour un retrait sous 48 h.',
            'priority' => Priority::Medium, 'status' => ProjectStatus::InProgress, 'due' => '+1 month', 'by' => UserFixtures::SARAH,
        ],
        [
            'client' => 'librairie', 'name' => 'Newsletter mensuelle',
            'description' => 'Modèle de newsletter avec les coups de cœur du mois et l\'agenda des dédicaces.',
            'priority' => Priority::Low, 'status' => ProjectStatus::Done, 'due' => null, 'by' => UserFixtures::CAMILLE,
        ],
        [
            'client' => 'transports', 'name' => 'Suivi des livraisons en temps réel',
            'description' => 'Tableau de bord des tournées et lien de suivi envoyé automatiquement aux destinataires.',
            'priority' => Priority::Critical, 'status' => ProjectStatus::InProgress, 'due' => '+10 days', 'by' => UserFixtures::JULIEN,
            'files' => [['architecture-technique.pdf', self::PDF, 1_102_437], ['export-tournees.xlsx', self::XLSX, 241_900]],
        ],
        [
            'client' => 'transports', 'name' => "Migration de l'ancien logiciel de facturation",
            'description' => 'Reprise des factures depuis 2019 et bascule vers le nouvel outil. En attente des accès au serveur du client.',
            'priority' => Priority::High, 'status' => ProjectStatus::OnHold, 'due' => '-10 days', 'by' => UserFixtures::SARAH,
        ],
        [
            'client' => 'hotel', 'name' => 'Moteur de réservation',
            'description' => 'Réservation directe des chambres, synchronisation avec les plateformes et paiement d\'un acompte.',
            'priority' => Priority::Critical, 'status' => ProjectStatus::InProgress, 'due' => '+2 weeks', 'by' => UserFixtures::JULIEN,
            'files' => [['maquette-reservation.png', self::PNG, 1_530_018], ['contrat-maintenance.docx', self::DOCX, 96_544]],
        ],
        [
            'client' => 'hotel', 'name' => 'Visite virtuelle des chambres',
            'description' => null,
            'priority' => Priority::Low, 'status' => ProjectStatus::Todo, 'due' => null, 'by' => UserFixtures::CAMILLE,
        ],
        [
            'client' => 'fromagerie', 'name' => 'Abonnement box fromages',
            'description' => 'Abonnement mensuel avec choix de la formule, paiement récurrent et possibilité de mettre en pause.',
            'priority' => Priority::Medium, 'status' => ProjectStatus::Todo, 'due' => '+2 months', 'by' => UserFixtures::SARAH,
        ],
        [
            'client' => 'musique', 'name' => 'Inscriptions et planning des cours',
            'description' => 'Inscription en ligne aux cours, planning des professeurs et paiement en trois fois.',
            'priority' => Priority::High, 'status' => ProjectStatus::InProgress, 'due' => '+3 weeks', 'by' => UserFixtures::CAMILLE,
            'files' => [['planning-2026-2027.xlsx', self::XLSX, 74_118]],
        ],
        [
            'client' => 'brasserie', 'name' => 'Carte en ligne avec QR code',
            'description' => 'Carte consultable depuis un QR code sur les tables, mise à jour par le gérant.',
            'priority' => Priority::Low, 'status' => ProjectStatus::Done, 'due' => '-3 weeks', 'by' => UserFixtures::JULIEN,
        ],
        [
            'client' => 'garage', 'name' => "Prise de rendez-vous pour l'entretien",
            'description' => 'Choix de la prestation, du créneau et rappel la veille du rendez-vous.',
            'priority' => Priority::Medium, 'status' => ProjectStatus::Todo, 'due' => '+1 month', 'by' => UserFixtures::SARAH,
        ],
        [
            'client' => 'garage', 'name' => 'Devis carrosserie en ligne',
            'description' => 'Envoi de photos des dégâts pour obtenir un devis estimatif sous 24 h. En pause à la demande du client.',
            'priority' => Priority::Medium, 'status' => ProjectStatus::OnHold, 'due' => null, 'by' => UserFixtures::JULIEN,
        ],
        [
            'client' => 'fleurs', 'name' => 'Boutique en ligne et livraison locale',
            'description' => 'Commande de bouquets avec créneaux de livraison dans un rayon de 15 km.',
            'priority' => Priority::High, 'status' => ProjectStatus::InProgress, 'due' => '-2 days', 'by' => UserFixtures::CAMILLE,
            'files' => [['charte-graphique.pdf', self::PDF, 2_048_331], ['logo-fleurs-et-sens.png', self::PNG, 156_772]],
        ],
        [
            'client' => 'yoga', 'name' => 'Réservation des séances',
            'description' => 'Réservation des cours collectifs avec carnet de 10 séances.',
            'priority' => Priority::Medium, 'status' => ProjectStatus::Done, 'due' => '-6 weeks', 'by' => UserFixtures::SARAH,
        ],
        [
            'client' => 'menuiserie', 'name' => 'Portfolio de réalisations',
            'description' => 'Galerie des chantiers réalisés, classés par type : cuisines, escaliers, dressings.',
            'priority' => Priority::Low, 'status' => ProjectStatus::InProgress, 'due' => '+1 month', 'by' => UserFixtures::CAMILLE,
            'files' => [['chantier-cuisine-chene.jpg', self::JPG, 2_871_406], ['chantier-escalier.jpg', self::JPG, 2_406_993]],
        ],
        [
            'client' => 'jardins', 'name' => "Site de l'association et adhésions",
            'description' => 'Présentation des parcelles, agenda des ateliers et adhésion en ligne.',
            'priority' => Priority::Medium, 'status' => ProjectStatus::Todo, 'due' => '+2 months', 'by' => UserFixtures::JULIEN,
        ],
        [
            'client' => 'houblon', 'name' => 'Programme de fidélité',
            'description' => null,
            'priority' => Priority::Low, 'status' => ProjectStatus::Todo, 'due' => null, 'by' => UserFixtures::SARAH,
        ],
        [
            'client' => 'photographe', 'name' => 'Portfolio de photographe',
            'description' => 'Portfolio mariages et portraits, avec galeries privées protégées par mot de passe.',
            'priority' => Priority::Medium, 'status' => ProjectStatus::Done, 'due' => '-2 months', 'by' => UserFixtures::CAMILLE,
            'files' => [['selection-portfolio.zip', self::ZIP, 24_551_870]],
        ],
        [
            'client' => 'dentiste', 'name' => 'Prise de rendez-vous en ligne',
            'description' => 'Agenda des trois praticiens, motifs de consultation et rappels par SMS.',
            'priority' => Priority::High, 'status' => ProjectStatus::InProgress, 'due' => '+2 weeks', 'by' => UserFixtures::JULIEN,
        ],
        [
            'client' => 'architecte', 'name' => "Refonte de l'identité visuelle",
            'description' => 'Nouveau logo, papeterie et modèles de présentation. En attente du choix entre deux pistes.',
            'priority' => Priority::Medium, 'status' => ProjectStatus::OnHold, 'due' => '+1 month', 'by' => UserFixtures::SARAH,
            'files' => [['pistes-logo.pdf', self::PDF, 5_302_118]],
        ],
        [
            'client' => 'traiteur', 'name' => 'Configurateur de devis événementiel',
            'description' => 'Devis automatique selon le nombre d\'invités, le menu et les options de service.',
            'priority' => Priority::High, 'status' => ProjectStatus::Todo, 'due' => '+5 weeks', 'by' => UserFixtures::CAMILLE,
        ],
        [
            'client' => 'avocats', 'name' => 'Ancien blog du cabinet',
            'description' => "Blog abandonné au profit d'une rubrique actualités dans l'espace client.",
            'priority' => Priority::Low, 'status' => ProjectStatus::Done, 'due' => null, 'by' => UserFixtures::JULIEN,
            'deleted' => true,
        ],
        [
            'client' => 'brasserie', 'name' => "Jeu concours de l'été",
            'description' => 'Annulé par le client.',
            'priority' => Priority::Low, 'status' => ProjectStatus::Todo, 'due' => '+1 month', 'by' => UserFixtures::SARAH,
            'deleted' => true,
        ],
    ];

    /** Who moves a project forward after its creation (round-robin). */
    private const TEAM = [UserFixtures::CAMILLE, UserFixtures::JULIEN, UserFixtures::SARAH];

    public function load(ObjectManager $manager): void
    {
        foreach (self::PROJECTS as $i => $data) {
            $author = $this->getReference($data['by'], User::class);
            $editor = $this->getReference(self::TEAM[$i % \count(self::TEAM)], User::class);

            $project = (new Project())
                ->setName($data['name'])
                ->setDescription($data['description'])
                ->setPriority($data['priority'])
                ->setStatus($data['status'])
                ->setDueDate(null !== $data['due'] ? new \DateTimeImmutable($data['due']) : null)
                ->setClient($this->getReference(ClientFixtures::PREFIX.$data['client'], Client::class));

            $project->addLog((new Log())->setAction(LogAction::Created)->setPerformedBy($author));

            foreach ($data['files'] ?? [] as $n => [$name, $mimeType, $size]) {
                $project->addAttachment((new Attachment())
                    ->setOriginalName($name)
                    ->setFileName(sprintf('fixture-%02d-%d.%s', $i, $n, pathinfo($name, \PATHINFO_EXTENSION)))
                    ->setMimeType($mimeType)
                    ->setSize($size)
                    ->setUploadedBy($author));
                $project->addLog((new Log())
                    ->setAction(LogAction::AttachmentAdded)
                    ->setPerformedBy($author)
                    ->setDetails(['file' => $name]));
            }

            if (ProjectStatus::Todo !== $data['status']) {
                $project->addLog((new Log())
                    ->setAction(LogAction::Updated)
                    ->setPerformedBy($editor)
                    ->setDetails(['status' => [ProjectStatus::Todo->value, $data['status']->value]]));
            }

            if ($data['deleted'] ?? false) {
                $project->softDelete();
                $project->addLog((new Log())->setAction(LogAction::Deleted)->setPerformedBy($this->getReference(UserFixtures::ADMIN, User::class)));
            }

            $manager->persist($project);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class, ClientFixtures::class];
    }
}
