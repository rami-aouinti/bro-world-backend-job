<?php

namespace App\Job\Infrastructure\DataFixtures\ORM;

use App\Job\Domain\Entity\Company;
use App\Job\Domain\Entity\CompanyMedia;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

final class CompanyFixtures extends Fixture
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
        foreach ($this->companies() as $companyData) {
            $company = (new Company())
                ->setName($companyData['name'])
                ->setDescription($companyData['description'])
                ->setLocation($companyData['location'])
                ->setContactEmail($companyData['contactEmail'])
                ->setLogo($companyData['logo'])
                ->setSiteUrl($companyData['siteUrl'])
            ;
            $company->setUser(Uuid::fromString($companyData['user']));

            foreach ($companyData['medias'] as $mediaUrl) {
                $media = (new CompanyMedia())
                    ->setUrl($mediaUrl)
                    ->setCompany($company)
                ;
                $company->getMedias()->add($media);
            }

            $manager->persist($company);
            $this->addReference($companyData['reference'], $company);
        }

        $manager->flush();
    }

    private function companies(): array
    {
        return [
            [
                'reference' => 'company_arcadia',
                'name' => 'Arcadia Technologies',
                'description' => 'Arcadia conçoit des plateformes logistiques cloud pour les chaines retail et les marketplaces.',
                'location' => 'Paris, France',
                'contactEmail' => 'hello@arcadia-tech.example',
                'logo' => '/img/companies/arcadia-logo.svg',
                'siteUrl' => 'https://arcadia-tech.example',
                'user' => self::USERS[0],
                'medias' => [
                    '/img/companies/arcadia-campus.jpg',
                    '/img/companies/arcadia-team.jpg',
                ],
            ],
            [
                'reference' => 'company_lumina',
                'name' => 'Lumina Health',
                'description' => 'Lumina Health développe des outils SaaS pour automatiser les parcours patients dans les cliniques modernes.',
                'location' => 'Lyon, France',
                'contactEmail' => 'contact@lumina-health.example',
                'logo' => '/img/companies/lumina-logo.svg',
                'siteUrl' => 'https://lumina-health.example',
                'user' => self::USERS[1],
                'medias' => [
                    '/img/companies/lumina-lab.jpg',
                    '/img/companies/lumina-app.jpg',
                ],
            ],
            [
                'reference' => 'company_northwind',
                'name' => 'Northwind Retail',
                'description' => 'Northwind Retail connecte les boutiques physiques et e-commerce grâce à une suite omnicanale.',
                'location' => 'Bruxelles, Belgique',
                'contactEmail' => 'jobs@northwind-retail.example',
                'logo' => '/img/companies/northwind-logo.svg',
                'siteUrl' => 'https://northwind-retail.example',
                'user' => self::USERS[2],
                'medias' => [
                    '/img/companies/northwind-store.jpg',
                ],
            ],
            [
                'reference' => 'company_summit',
                'name' => 'Summit Aerospace',
                'description' => 'Summit Aerospace fournit des systèmes embarqués pour l’aviation verte et la maintenance prédictive.',
                'location' => 'Toulouse, France',
                'contactEmail' => 'talents@summit-aero.example',
                'logo' => '/img/companies/summit-logo.svg',
                'siteUrl' => 'https://summit-aero.example',
                'user' => self::USERS[3],
                'medias' => [
                    '/img/companies/summit-hangar.jpg',
                    '/img/companies/summit-lab.jpg',
                ],
            ],
            [
                'reference' => 'company_terra',
                'name' => 'Terra Analytics',
                'description' => 'Terra Analytics produit des jumeaux numériques pour les villes durables et les réseaux énergétiques.',
                'location' => 'Montréal, Canada',
                'contactEmail' => 'hr@terra-analytics.example',
                'logo' => '/img/companies/terra-logo.svg',
                'siteUrl' => 'https://terra-analytics.example',
                'user' => self::USERS[4],
                'medias' => [
                    '/img/companies/terra-dashboard.jpg',
                ],
            ],
            [
                'reference' => 'company_quantum',
                'name' => 'Quantum Finance Lab',
                'description' => 'Quantum Finance Lab bâtit des moteurs IA pour la gestion d’actifs et le scoring de risque en temps réel.',
                'location' => 'Genève, Suisse',
                'contactEmail' => 'careers@quantum-finance.example',
                'logo' => '/img/companies/quantum-logo.svg',
                'siteUrl' => 'https://quantum-finance.example',
                'user' => self::USERS[5],
                'medias' => [
                    '/img/companies/quantum-trading.jpg',
                    '/img/companies/quantum-meetup.jpg',
                ],
            ],
        ];
    }
}
