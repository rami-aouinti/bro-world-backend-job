<?php

namespace App\Job\Infrastructure\DataFixtures\ORM;

use App\Job\Domain\Entity\Company;
use App\Job\Domain\Entity\Job;
use App\Job\Domain\Entity\Language as JobLanguage;
use App\Job\Domain\Enum\ContractType;
use App\Job\Domain\Enum\LanguageLevel as JobLanguageLevel;
use App\Job\Domain\Enum\WorkType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

final class JobFixtures extends Fixture implements DependentFixtureInterface
{
    private const USERS = [
        '20000000-0000-1000-8000-000000000001',
        '20000000-0000-1000-8000-000000000002',
        '20000000-0000-1000-8000-000000000003',
        '20000000-0000-1000-8000-000000000004',
        '20000000-0000-1000-8000-000000000005',
        '20000000-0000-1000-8000-000000000006',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->jobs() as $jobData) {
            /** @var Company $company */
            $company = $this->getReference($jobData['company'], Company::class);

            $job = (new Job())
                ->setTitle($jobData['title'])
                ->setDescription($jobData['description'])
                ->setWork($jobData['work'])
                ->setRequiredSkills($jobData['requiredSkills'])
                ->setExperience($jobData['experience'])
                ->setWorkType($jobData['workType'])
                ->setWorkLocation($jobData['workLocation'])
                ->setSalaryRange($jobData['salaryRange'])
                ->setContractType($jobData['contractType'])
                ->setRequirements($jobData['requirements'])
                ->setBenefits($jobData['benefits'])
                ->setCompany($company)
            ;
            $job->setUser(Uuid::fromString($jobData['user']));

            foreach ($jobData['languages'] as $languageData) {
                $language = (new JobLanguage())
                    ->setName($languageData['name'])
                    ->setLevel(JobLanguageLevel::from($languageData['level']))
                ;
                $job->addLanguage($language);
            }

            $manager->persist($job);
            $this->addReference($jobData['reference'], $job);
        }

        $manager->flush();
    }

    private function jobs(): array
    {
        return [
            [
                'reference' => 'job_senior_backend_arcadia',
                'company' => 'company_arcadia',
                'user' => self::USERS[0],
                'title' => 'Senior Backend Engineer',
                'description' => 'Vous construisez des APIs logistiques temps réel pour relier entrepôts et partenaires européens.',
                'work' => 'Définir une architecture orientée événements, mener les revues de code et encadrer deux développeurs.',
                'requiredSkills' => ['PHP 8.3', 'Symfony 7', 'Kafka', 'MySQL', 'Terraform'],
                'experience' => '7+ ans en backend / microservices',
                'workType' => WorkType::REMOTE,
                'workLocation' => 'Remote Europe / Paris HQ',
                'salaryRange' => '€75k - €90k',
                'contractType' => ContractType::FULLTIME,
                'requirements' => ['Connaissance des patterns DDD', 'Expérience CI/CD', 'Français et anglais courant'],
                'benefits' => 'BSPCE, télétravail intégral, budget formation et conférences.',
                'languages' => [
                    ['name' => 'Français', 'level' => 'native'],
                    ['name' => 'Anglais', 'level' => 'fluent'],
                ],
            ],
            [
                'reference' => 'job_data_platform_arcadia',
                'company' => 'company_arcadia',
                'user' => self::USERS[0],
                'title' => 'Data Platform Architect',
                'description' => 'Arcadia cherche un profil data pour fiabiliser notre plateforme d’analytics temps réel.',
                'work' => 'Designer les pipelines batch/streaming, définir la gouvernance data et piloter l’infrastructure.',
                'requiredSkills' => ['Python', 'dbt', 'Airflow', 'BigQuery', 'Looker'],
                'experience' => '5 ans sur des stacks analytiques modernes',
                'workType' => WorkType::HYBRID,
                'workLocation' => 'Paris 10e',
                'salaryRange' => '€68k - €80k',
                'contractType' => ContractType::FULLTIME,
                'requirements' => ['Culture produit', 'Savoir vulgariser les KPIs', 'Appétence FinOps'],
                'benefits' => 'Mutuelle premium, titre mobilité, 2 jours de remote hebdo.',
                'languages' => [
                    ['name' => 'Anglais', 'level' => 'fluent'],
                ],
            ],
            [
                'reference' => 'job_health_pm_lumina',
                'company' => 'company_lumina',
                'user' => self::USERS[1],
                'title' => 'Product Manager Parcours Patient',
                'description' => 'Piloter la roadmap du portail patient omni-canal de Lumina Health.',
                'work' => 'Collecter les besoins des cliniques, prioriser la roadmap trimestrielle et suivre les rituels agiles.',
                'requiredSkills' => ['Product discovery', 'Notions UX', 'Reporting OKR'],
                'experience' => '4 ans minimum en environnement e-santé',
                'workType' => WorkType::ONSITE,
                'workLocation' => 'Lyon Part-Dieu',
                'salaryRange' => '€58k - €66k',
                'contractType' => ContractType::FULLTIME,
                'requirements' => ['Maîtrise RGPD santé', 'Goût pour les données', 'Capacité d’animation terrain'],
                'benefits' => 'Intéressement trimestriel, salle de sport, programme parentalité.',
                'languages' => [
                    ['name' => 'Français', 'level' => 'native'],
                    ['name' => 'Anglais', 'level' => 'intermediate'],
                ],
            ],
            [
                'reference' => 'job_devops_lumina',
                'company' => 'company_lumina',
                'user' => self::USERS[1],
                'title' => 'DevOps Engineer',
                'description' => 'Maintenir l’infrastructure cloud certifiée HDS et accélérer les déploiements.',
                'work' => 'Automatiser les tests de résilience, gérer les clusters Kubernetes et améliorer la supervision.',
                'requiredSkills' => ['Kubernetes', 'Helm', 'GitHub Actions', 'Prometheus', 'Snyk'],
                'experience' => '5 ans en SRE ou DevOps',
                'workType' => WorkType::HYBRID,
                'workLocation' => 'Lyon + remote',
                'salaryRange' => '€64k - €72k',
                'contractType' => ContractType::FULLTIME,
                'requirements' => ['Certif HDS ou ISO 27001 appréciée', 'Participation aux astreintes'],
                'benefits' => 'Prime d’astreinte, séminaire montagne annuel.',
                'languages' => [
                    ['name' => 'Français', 'level' => 'native'],
                    ['name' => 'Anglais', 'level' => 'fluent'],
                ],
            ],
            [
                'reference' => 'job_omnicanal_lead_northwind',
                'company' => 'company_northwind',
                'user' => self::USERS[2],
                'title' => 'Lead Engineer Plateforme Omnicanale',
                'description' => 'Coordonner la refonte du moteur de commandes unifiées pour 500 boutiques.',
                'work' => 'Définir les patterns event sourcing, coacher 6 développeurs et sécuriser les pics de trafic.',
                'requiredSkills' => ['Symfony', 'Redis', 'ElasticSearch', 'CQRS'],
                'experience' => '8 ans en environnement retail',
                'workType' => WorkType::HYBRID,
                'workLocation' => 'Bruxelles',
                'salaryRange' => '€82k - €95k',
                'contractType' => ContractType::FULLTIME,
                'requirements' => ['Leadership technique', 'Animation de guildes', 'Culture produit forte'],
                'benefits' => 'Voiture de fonction, plan de bonus trimestriel.',
                'languages' => [
                    ['name' => 'Français', 'level' => 'fluent'],
                    ['name' => 'Néerlandais', 'level' => 'intermediate'],
                ],
            ],
            [
                'reference' => 'job_frontend_northwind',
                'company' => 'company_northwind',
                'user' => self::USERS[2],
                'title' => 'Senior Frontend Engineer',
                'description' => 'Développer des expériences e-commerce haute performance avec React et Astro.',
                'work' => 'Livrer des micro-frontends, optimiser le CLS et industrialiser les tests visuels.',
                'requiredSkills' => ['React', 'TypeScript', 'Astro', 'Playwright'],
                'experience' => '6 ans sur des SPAs grand public',
                'workType' => WorkType::REMOTE,
                'workLocation' => 'Remote EU',
                'salaryRange' => '€70k - €82k',
                'contractType' => ContractType::FULLTIME,
                'requirements' => ['Sensibilité UX', 'Pratique du design system', 'Pair programming régulier'],
                'benefits' => 'Budget remote, carte coworking, conférence annuelle payée.',
                'languages' => [
                    ['name' => 'Anglais', 'level' => 'fluent'],
                ],
            ],
            [
                'reference' => 'job_embedded_summit',
                'company' => 'company_summit',
                'user' => self::USERS[3],
                'title' => 'Ingénieur Logiciel Embarqué',
                'description' => 'Développer le firmware qui pilote les actionneurs électriques nouvelle génération.',
                'work' => 'Coder en C++17, industrialiser les tests hardware-in-the-loop et documenter DO-178C.',
                'requiredSkills' => ['C++', 'QNX', 'CAN bus', 'Testing HIL'],
                'experience' => '5 ans en aéronautique ou automobile',
                'workType' => WorkType::ONSITE,
                'workLocation' => 'Toulouse',
                'salaryRange' => '€60k - €72k',
                'contractType' => ContractType::FULLTIME,
                'requirements' => ['Connaissance normes DO-178C', 'Anglais technique', 'Esprit rigoureux'],
                'benefits' => '13e mois, prime intéressement, laboratoire prototypage.',
                'languages' => [
                    ['name' => 'Français', 'level' => 'fluent'],
                    ['name' => 'Anglais', 'level' => 'intermediate'],
                ],
            ],
            [
                'reference' => 'job_reliability_summit',
                'company' => 'company_summit',
                'user' => self::USERS[3],
                'title' => 'Reliability Data Scientist',
                'description' => 'Construire les modèles prédictifs pour anticiper la maintenance flotte.',
                'work' => 'Analyser les signaux capteurs, développer des modèles bayésiens et industrialiser les notebooks.',
                'requiredSkills' => ['Python', 'PyMC', 'Edge AI', 'Snowflake'],
                'experience' => '4+ ans en data science industrielle',
                'workType' => WorkType::HYBRID,
                'workLocation' => 'Toulouse + 2 jours remote',
                'salaryRange' => '€66k - €78k',
                'contractType' => ContractType::FULLTIME,
                'requirements' => ['Statistiques avancées', 'Culture MLOps', 'Anglais courant'],
                'benefits' => 'Participation, congés additionnels, mobilité interne internationale.',
                'languages' => [
                    ['name' => 'Français', 'level' => 'fluent'],
                    ['name' => 'Anglais', 'level' => 'fluent'],
                ],
            ],
            [
                'reference' => 'job_geo_ai_terra',
                'company' => 'company_terra',
                'user' => self::USERS[4],
                'title' => 'Lead Engineer Geo AI',
                'description' => 'Imaginer les algorithmes qui optimisent les réseaux énergétiques urbains.',
                'work' => 'Assembler des pipelines geospatiaux, collaborer avec les urbanistes et industrialiser l’IA.',
                'requiredSkills' => ['Python', 'Rust', 'PostGIS', 'GraphQL'],
                'experience' => '7 ans en traitement géospatial',
                'workType' => WorkType::REMOTE,
                'workLocation' => 'Montréal ou remote Canada',
                'salaryRange' => '$120k - $140k CAD',
                'contractType' => ContractType::FULLTIME,
                'requirements' => ['Modélisation énergétique', 'Communication client', 'Disponibilité ateliers terrain'],
                'benefits' => 'Assurance premium, stock-options canadiennes, politique RSE active.',
                'languages' => [
                    ['name' => 'Français', 'level' => 'fluent'],
                    ['name' => 'Anglais', 'level' => 'fluent'],
                ],
            ],
            [
                'reference' => 'job_quant_finance',
                'company' => 'company_quantum',
                'user' => self::USERS[5],
                'title' => 'Quantitative Engineer',
                'description' => 'Développer le moteur de pricing en temps réel adossé aux modèles IA.',
                'work' => 'Coder des microservices Rust, intégrer les signaux alternatifs et superviser la latence.',
                'requiredSkills' => ['Rust', 'gRPC', 'Redis Cluster', 'AWS Lambda'],
                'experience' => '6 ans en trading ou fintech',
                'workType' => WorkType::HYBRID,
                'workLocation' => 'Genève',
                'salaryRange' => 'CHF 140k - 160k',
                'contractType' => ContractType::FULLTIME,
                'requirements' => ['Maths appliquées', 'Connaissance régulations FINMA', 'Veille cybersécurité'],
                'benefits' => 'Bonus garanti, coworking Zurich, mentoring exécutif.',
                'languages' => [
                    ['name' => 'Anglais', 'level' => 'fluent'],
                    ['name' => 'Allemand', 'level' => 'intermediate'],
                ],
            ],
            [
                'reference' => 'job_risk_platform_quantum',
                'company' => 'company_quantum',
                'user' => self::USERS[5],
                'title' => 'Risk Platform Product Owner',
                'description' => 'Structurer la plateforme de gestion de risque basée sur des modèles explainable AI.',
                'work' => 'Aligner les squads data/engineering, prioriser les fonctionnalités réglementaires et suivre l’adoption.',
                'requiredSkills' => ['Product Ownership', 'Finance de marché', 'Story mapping'],
                'experience' => '5 ans en PO fintech',
                'workType' => WorkType::HYBRID,
                'workLocation' => 'Genève + Zurich',
                'salaryRange' => 'CHF 120k - 135k',
                'contractType' => ContractType::FULLTIME,
                'requirements' => ['Culture risque bancaire', 'Aisance avec les comités de pilotage'],
                'benefits' => 'Programme actionnariat, 6 semaines de congés, budget formation data.',
                'languages' => [
                    ['name' => 'Anglais', 'level' => 'fluent'],
                    ['name' => 'Français', 'level' => 'fluent'],
                ],
            ],
        ];
    }

    public function getDependencies(): array
    {
        return [
            CompanyFixtures::class,
        ];
    }
}
