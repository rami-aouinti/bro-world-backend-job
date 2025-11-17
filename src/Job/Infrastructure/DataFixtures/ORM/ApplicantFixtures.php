<?php

namespace App\Job\Infrastructure\DataFixtures\ORM;

use App\Job\Domain\Entity\Applicant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

final class ApplicantFixtures extends Fixture
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
        foreach ($this->applicants() as $applicantData) {
            $applicant = (new Applicant())
                ->setFirstName($applicantData['firstName'])
                ->setLastName($applicantData['lastName'])
                ->setContactEmail($applicantData['contactEmail'])
                ->setPhone($applicantData['phone'])
                ->setResume($applicantData['resume'])
            ;
            $applicant->setUser(Uuid::fromString($applicantData['user']));

            $manager->persist($applicant);
            $this->addReference($applicantData['reference'], $applicant);
        }

        $manager->flush();
    }

    private function applicants(): array
    {
        return [
            [
                'reference' => 'applicant_amelia',
                'firstName' => 'Amelia',
                'lastName' => 'Durand',
                'contactEmail' => 'amelia.durand@example.com',
                'phone' => '+33 6 77 88 99 01',
                'resume' => 'https://cdn.example.com/resume/amelia-durand.pdf',
                'user' => self::USERS[0],
            ],
            [
                'reference' => 'applicant_ines',
                'firstName' => 'Inès',
                'lastName' => 'Marchand',
                'contactEmail' => 'ines.marchand@example.com',
                'phone' => '+33 6 11 45 28 19',
                'resume' => 'https://cdn.example.com/resume/ines-marchand.pdf',
                'user' => self::USERS[1],
            ],
            [
                'reference' => 'applicant_malik',
                'firstName' => 'Malik',
                'lastName' => 'Benaïssa',
                'contactEmail' => 'malik.benaissa@example.com',
                'phone' => '+32 494 22 88 11',
                'resume' => 'https://cdn.example.com/resume/malik-benaissa.pdf',
                'user' => self::USERS[2],
            ],
            [
                'reference' => 'applicant_jade',
                'firstName' => 'Jade',
                'lastName' => 'Fontaine',
                'contactEmail' => 'jade.fontaine@example.com',
                'phone' => '+33 6 52 10 98 45',
                'resume' => 'https://cdn.example.com/resume/jade-fontaine.pdf',
                'user' => self::USERS[3],
            ],
            [
                'reference' => 'applicant_lucas',
                'firstName' => 'Lucas',
                'lastName' => 'Morel',
                'contactEmail' => 'lucas.morel@example.com',
                'phone' => '+1 438 555 11 87',
                'resume' => 'https://cdn.example.com/resume/lucas-morel.pdf',
                'user' => self::USERS[4],
            ],
            [
                'reference' => 'applicant_salma',
                'firstName' => 'Salma',
                'lastName' => 'El Idrissi',
                'contactEmail' => 'salma.elidrissi@example.com',
                'phone' => '+41 79 555 11 44',
                'resume' => 'https://cdn.example.com/resume/salma-elidrissi.pdf',
                'user' => self::USERS[5],
            ],
        ];
    }
}
