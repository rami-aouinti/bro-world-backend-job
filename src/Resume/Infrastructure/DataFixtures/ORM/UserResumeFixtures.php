<?php

namespace App\Resume\Infrastructure\DataFixtures\ORM;

use App\Resume\Domain\Entity\Experience;
use App\Resume\Domain\Entity\Formation;
use App\Resume\Domain\Entity\Hobby;
use App\Resume\Domain\Entity\Language as ResumeLanguage;
use App\Resume\Domain\Entity\Skill;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

final class UserResumeFixtures extends Fixture
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
        foreach ($this->experiences() as $experienceData) {
            $experience = (new Experience())
                ->setTitle($experienceData['title'])
                ->setDescription($experienceData['description'])
                ->setCompany($experienceData['company'])
            ;
            $experience->setStartedAt(new DateTimeImmutable($experienceData['startedAt']));
            $experience->setEndedAt($experienceData['endedAt'] ? new DateTimeImmutable($experienceData['endedAt']) : null);
            $experience->setUser(Uuid::fromString($experienceData['user']));

            $manager->persist($experience);
        }

        foreach ($this->formations() as $formationData) {
            $formation = (new Formation())
                ->setName($formationData['name'])
                ->setSchool($formationData['school'])
                ->setGradeLevel($formationData['gradeLevel'])
                ->setDescription($formationData['description'])
            ;
            $formation->setStartedAt(new DateTimeImmutable($formationData['startedAt']));
            $formation->setEndedAt($formationData['endedAt'] ? new DateTimeImmutable($formationData['endedAt']) : null);
            $formation->setUser(Uuid::fromString($formationData['user']));

            $manager->persist($formation);
        }

        foreach ($this->skills() as $skillData) {
            $skill = (new Skill())
                ->setName($skillData['name'])
                ->setType($skillData['type'])
                ->setLevel($skillData['level'])
            ;
            $skill->setUser(Uuid::fromString($skillData['user']));
            $manager->persist($skill);
        }

        foreach ($this->languages() as $languageData) {
            $language = (new ResumeLanguage())
                ->setName($languageData['name'])
                ->setLevel($languageData['level'])
                ->setFlag($languageData['flag'])
            ;
            $language->setUser(Uuid::fromString($languageData['user']));
            $manager->persist($language);
        }

        foreach ($this->hobbies() as $hobbyData) {
            $hobby = (new Hobby())
                ->setName($hobbyData['name'])
                ->setIcon($hobbyData['icon'])
            ;
            $hobby->setUser(Uuid::fromString($hobbyData['user']));
            $manager->persist($hobby);
        }

        $manager->flush();
    }

    private function experiences(): array
    {
        return [
            [
                'title' => 'Engineering Manager - Plateformes logistiques',
                'company' => 'Arcadia Technologies',
                'description' => 'Animation d’une squad de 8 devs, mise en place d’une architecture event sourcing et coaching DevOps.',
                'startedAt' => '2019-04-01',
                'endedAt' => '2023-08-01',
                'user' => self::USERS[0],
            ],
            [
                'title' => 'Senior Backend Engineer',
                'company' => 'Flowship',
                'description' => 'Création d’APIs temps réel pour la livraison urbaine et industrialisation CI/CD.',
                'startedAt' => '2016-02-01',
                'endedAt' => '2019-03-01',
                'user' => self::USERS[0],
            ],
            [
                'title' => 'Product Manager Santé Digitale',
                'company' => 'Lumina Health',
                'description' => 'Définition de la roadmap patient, cadrage réglementaire et pilotage OKR.',
                'startedAt' => '2020-01-01',
                'endedAt' => null,
                'user' => self::USERS[1],
            ],
            [
                'title' => 'UX Researcher',
                'company' => 'MediPlus',
                'description' => 'Conception d’ateliers utilisateurs, synthèse des insights et priorisation backlog.',
                'startedAt' => '2017-05-01',
                'endedAt' => '2019-12-01',
                'user' => self::USERS[1],
            ],
            [
                'title' => 'Lead Software Engineer',
                'company' => 'Northwind Retail',
                'description' => 'Migration vers micro-frontends, gouvernance technique et mentoring de 6 développeurs.',
                'startedAt' => '2018-09-01',
                'endedAt' => null,
                'user' => self::USERS[2],
            ],
            [
                'title' => 'Consultant Cloud',
                'company' => 'Global Retail Partners',
                'description' => 'Déploiement de plateformes e-commerce en Europe et animation des comités architecture.',
                'startedAt' => '2014-03-01',
                'endedAt' => '2018-06-01',
                'user' => self::USERS[2],
            ],
            [
                'title' => 'Ingénieure Logiciel Embarqué',
                'company' => 'Summit Aerospace',
                'description' => 'Développement firmware DO-178C et outillage de tests HIL.',
                'startedAt' => '2019-06-01',
                'endedAt' => null,
                'user' => self::USERS[3],
            ],
            [
                'title' => 'Développeuse C++',
                'company' => 'Skyline Avionics',
                'description' => 'Maintenance des calculateurs avioniques et amélioration des outils de simulation.',
                'startedAt' => '2015-01-01',
                'endedAt' => '2019-05-01',
                'user' => self::USERS[3],
            ],
            [
                'title' => 'Lead Engineer Geo AI',
                'company' => 'Terra Analytics',
                'description' => 'Conception de jumeaux numériques urbains et coordination avec les urbanistes.',
                'startedAt' => '2021-02-01',
                'endedAt' => null,
                'user' => self::USERS[4],
            ],
            [
                'title' => 'Senior Data Scientist',
                'company' => 'BlueCity Labs',
                'description' => 'Développement de modèles d’optimisation énergétique pour les collectivités.',
                'startedAt' => '2017-04-01',
                'endedAt' => '2020-12-01',
                'user' => self::USERS[4],
            ],
            [
                'title' => 'Quantitative Engineer',
                'company' => 'Quantum Finance Lab',
                'description' => 'Industrialisation de modèles de pricing et supervision des pipelines temps réel.',
                'startedAt' => '2018-11-01',
                'endedAt' => null,
                'user' => self::USERS[5],
            ],
            [
                'title' => 'Software Developer',
                'company' => 'Helvetic Fintech',
                'description' => 'Création de services gérant le risque crédit et automatisation des rapports Bâle III.',
                'startedAt' => '2015-06-01',
                'endedAt' => '2018-10-01',
                'user' => self::USERS[5],
            ],
        ];
    }

    private function formations(): array
    {
        return [
            [
                'name' => 'Master Ingénierie des Systèmes Distribués',
                'school' => 'Université Paris Cité',
                'gradeLevel' => 5,
                'description' => 'Spécialité cloud, microservices et cybersécurité.',
                'startedAt' => '2011-09-01',
                'endedAt' => '2013-07-01',
                'user' => self::USERS[0],
            ],
            [
                'name' => 'MBA Santé Digitale',
                'school' => 'EM Lyon',
                'gradeLevel' => 6,
                'description' => 'Management de l’innovation et réglementation e-santé.',
                'startedAt' => '2016-09-01',
                'endedAt' => '2017-12-01',
                'user' => self::USERS[1],
            ],
            [
                'name' => 'Master Informatique & UX',
                'school' => 'Université de Montréal',
                'gradeLevel' => 5,
                'description' => 'Conception centrée utilisateur et analytics.',
                'startedAt' => '2012-09-01',
                'endedAt' => '2014-06-01',
                'user' => self::USERS[1],
            ],
            [
                'name' => 'Master Génie Logiciel',
                'school' => 'ULB Bruxelles',
                'gradeLevel' => 5,
                'description' => 'Approche DevOps et gouvernance IT.',
                'startedAt' => '2010-09-01',
                'endedAt' => '2012-06-01',
                'user' => self::USERS[2],
            ],
            [
                'name' => 'Diplôme Ingénieur Aéronautique',
                'school' => 'ISAE-Supaero',
                'gradeLevel' => 7,
                'description' => 'Spécialité systèmes embarqués critiques.',
                'startedAt' => '2012-09-01',
                'endedAt' => '2015-06-01',
                'user' => self::USERS[3],
            ],
            [
                'name' => 'Master Data Science',
                'school' => 'Polytechnique Montréal',
                'gradeLevel' => 5,
                'description' => 'Algorithmes géospatiaux et optimisation.',
                'startedAt' => '2013-09-01',
                'endedAt' => '2015-06-01',
                'user' => self::USERS[4],
            ],
            [
                'name' => 'Master Finance & Technologies',
                'school' => 'EPFL',
                'gradeLevel' => 5,
                'description' => 'Cours avancés de modélisation financière et IA.',
                'startedAt' => '2011-09-01',
                'endedAt' => '2013-06-01',
                'user' => self::USERS[5],
            ],
        ];
    }

    private function skills(): array
    {
        return [
            ['name' => 'Architecture microservices', 'type' => 'Technique', 'level' => 9, 'user' => self::USERS[0]],
            ['name' => 'Coaching d’équipe', 'type' => 'Soft', 'level' => 8, 'user' => self::USERS[0]],
            ['name' => 'Product discovery', 'type' => 'Soft', 'level' => 8, 'user' => self::USERS[1]],
            ['name' => 'Analytics produit', 'type' => 'Technique', 'level' => 7, 'user' => self::USERS[1]],
            ['name' => 'Symfony 7', 'type' => 'Technique', 'level' => 9, 'user' => self::USERS[2]],
            ['name' => 'React avancé', 'type' => 'Technique', 'level' => 8, 'user' => self::USERS[2]],
            ['name' => 'C++ temps réel', 'type' => 'Technique', 'level' => 9, 'user' => self::USERS[3]],
            ['name' => 'Certification DO-178C', 'type' => 'Technique', 'level' => 8, 'user' => self::USERS[3]],
            ['name' => 'PostGIS', 'type' => 'Technique', 'level' => 9, 'user' => self::USERS[4]],
            ['name' => 'Storytelling data', 'type' => 'Soft', 'level' => 7, 'user' => self::USERS[4]],
            ['name' => 'Rust', 'type' => 'Technique', 'level' => 8, 'user' => self::USERS[5]],
            ['name' => 'Gestion du risque', 'type' => 'Technique', 'level' => 8, 'user' => self::USERS[5]],
        ];
    }

    private function languages(): array
    {
        return [
            ['name' => 'Français', 'level' => 5, 'flag' => 'fr', 'user' => self::USERS[0]],
            ['name' => 'Anglais', 'level' => 4, 'flag' => 'gb', 'user' => self::USERS[0]],
            ['name' => 'Français', 'level' => 5, 'flag' => 'fr', 'user' => self::USERS[1]],
            ['name' => 'Anglais', 'level' => 4, 'flag' => 'gb', 'user' => self::USERS[1]],
            ['name' => 'Espagnol', 'level' => 3, 'flag' => 'es', 'user' => self::USERS[1]],
            ['name' => 'Français', 'level' => 5, 'flag' => 'fr', 'user' => self::USERS[2]],
            ['name' => 'Néerlandais', 'level' => 3, 'flag' => 'nl', 'user' => self::USERS[2]],
            ['name' => 'Anglais', 'level' => 4, 'flag' => 'gb', 'user' => self::USERS[2]],
            ['name' => 'Français', 'level' => 5, 'flag' => 'fr', 'user' => self::USERS[3]],
            ['name' => 'Anglais', 'level' => 4, 'flag' => 'gb', 'user' => self::USERS[3]],
            ['name' => 'Français', 'level' => 5, 'flag' => 'fr', 'user' => self::USERS[4]],
            ['name' => 'Anglais', 'level' => 5, 'flag' => 'gb', 'user' => self::USERS[4]],
            ['name' => 'Français', 'level' => 4, 'flag' => 'fr', 'user' => self::USERS[5]],
            ['name' => 'Anglais', 'level' => 5, 'flag' => 'gb', 'user' => self::USERS[5]],
            ['name' => 'Allemand', 'level' => 3, 'flag' => 'de', 'user' => self::USERS[5]],
        ];
    }

    private function hobbies(): array
    {
        return [
            ['name' => 'Cyclisme longue distance', 'icon' => 'mdi-bike-fast', 'user' => self::USERS[0]],
            ['name' => 'Cuisine créative', 'icon' => 'mdi-silverware-fork-knife', 'user' => self::USERS[0]],
            ['name' => 'Randonnée alpine', 'icon' => 'mdi-image-filter-hdr', 'user' => self::USERS[1]],
            ['name' => 'Photographie argentique', 'icon' => 'mdi-camera', 'user' => self::USERS[1]],
            ['name' => 'Jeux de rôle', 'icon' => 'mdi-drama-masks', 'user' => self::USERS[2]],
            ['name' => 'Conférences tech', 'icon' => 'mdi-presentation', 'user' => self::USERS[2]],
            ['name' => 'Atelier bois', 'icon' => 'mdi-hammer-screwdriver', 'user' => self::USERS[3]],
            ['name' => 'Triathlon', 'icon' => 'mdi-run-fast', 'user' => self::USERS[3]],
            ['name' => 'Voyage responsable', 'icon' => 'mdi-earth', 'user' => self::USERS[4]],
            ['name' => 'Cuisine végétale', 'icon' => 'mdi-leaf', 'user' => self::USERS[4]],
            ['name' => 'Piano jazz', 'icon' => 'mdi-piano', 'user' => self::USERS[5]],
            ['name' => 'Collecte d’art digital', 'icon' => 'mdi-image-multiple', 'user' => self::USERS[5]],
        ];
    }
}
