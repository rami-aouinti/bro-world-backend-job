<?php

namespace App\Job\Infrastructure\DataFixtures\ORM;

use App\Job\Domain\Entity\JobApplication;
use App\Job\Domain\Enum\ApplicationStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class JobApplicationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach ($this->applications() as $applicationData) {
            /** @var \App\Job\Domain\Entity\Job $job */
            $job = $this->getReference($applicationData['job']);
            /** @var \App\Job\Domain\Entity\Applicant $applicant */
            $applicant = $this->getReference($applicationData['applicant']);

            $application = (new JobApplication())
                ->setJob($job)
                ->setApplicant($applicant)
                ->setStatus($applicationData['status'])
            ;

            $manager->persist($application);
        }

        $manager->flush();
    }

    private function applications(): array
    {
        return [
            [
                'job' => 'job_senior_backend_arcadia',
                'applicant' => 'applicant_amelia',
                'status' => ApplicationStatus::Progress,
            ],
            [
                'job' => 'job_data_platform_arcadia',
                'applicant' => 'applicant_ines',
                'status' => ApplicationStatus::Request,
            ],
            [
                'job' => 'job_health_pm_lumina',
                'applicant' => 'applicant_ines',
                'status' => ApplicationStatus::Accept,
            ],
            [
                'job' => 'job_devops_lumina',
                'applicant' => 'applicant_malik',
                'status' => ApplicationStatus::Progress,
            ],
            [
                'job' => 'job_omnicanal_lead_northwind',
                'applicant' => 'applicant_malik',
                'status' => ApplicationStatus::Request,
            ],
            [
                'job' => 'job_frontend_northwind',
                'applicant' => 'applicant_jade',
                'status' => ApplicationStatus::Request,
            ],
            [
                'job' => 'job_embedded_summit',
                'applicant' => 'applicant_jade',
                'status' => ApplicationStatus::Declined,
            ],
            [
                'job' => 'job_reliability_summit',
                'applicant' => 'applicant_lucas',
                'status' => ApplicationStatus::Progress,
            ],
            [
                'job' => 'job_geo_ai_terra',
                'applicant' => 'applicant_lucas',
                'status' => ApplicationStatus::Accept,
            ],
            [
                'job' => 'job_quant_finance',
                'applicant' => 'applicant_salma',
                'status' => ApplicationStatus::Progress,
            ],
            [
                'job' => 'job_risk_platform_quantum',
                'applicant' => 'applicant_salma',
                'status' => ApplicationStatus::Request,
            ],
        ];
    }

    public function getDependencies(): array
    {
        return [
            JobFixtures::class,
            ApplicantFixtures::class,
        ];
    }
}
