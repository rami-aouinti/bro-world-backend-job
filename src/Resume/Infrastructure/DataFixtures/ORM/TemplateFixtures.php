<?php

namespace App\Resume\Infrastructure\DataFixtures\ORM;

use App\Resume\Domain\Entity\Template;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class TemplateFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $data = [
            [
                'slug' => 'cv-2025',
                'title' => 'Lebenslauf 2025',
                'subtitle' => 'Moderne, sauber',
                'category' => 'Kreativ',
                'badge' => 'TOP',
                'previewImg' => 'https://picsum.photos/seed/cv2025/800/1130',
                'pdfUrl' => '/samples/cv-2025.pdf',
                'pages' => 2,
                'tags' => ['Modern', 'ATS-friendly'],
            ],
            [
                'slug' => 'kopfzeile',
                'title' => 'Lebenslauf Kopfzeile',
                'subtitle' => 'Seriös, schlicht',
                'category' => 'Klassisch',
                'badge' => null,
                'previewImg' => 'https://picsum.photos/seed/kopf/800/1130',
                'pdfUrl' => '/samples/cv-kopfzeile.pdf',
                'pages' => 1,
                'tags' => ['Klassisch'],
            ],
            [
                'slug' => 'cv-2024',
                'title' => 'Lebenslauf 2024',
                'subtitle' => 'Kompakt, klar',
                'category' => 'Klassisch',
                'badge' => 'NEU',
                'previewImg' => 'https://picsum.photos/seed/2024/800/1130',
                'pdfUrl' => '/samples/cv-2024.pdf',
                'pages' => 1,
                'tags' => ['Einseitig'],
            ],
            [
                'slug' => 'cv-2026',
                'title' => 'Lebenslauf 2026',
                'subtitle' => 'Moderne, sauber',
                'category' => 'Kreativ',
                'badge' => 'TOP',
                'previewImg' => 'https://picsum.photos/seed/cv2026/800/1130',
                'pdfUrl' => '/samples/cv-2026.pdf',
                'pages' => 2,
                'tags' => ['Modern', 'ATS-friendly'],
            ],
            [
                'slug' => 'kopfzeile5',
                'title' => 'Lebenslauf Kopfzeile',
                'subtitle' => 'Seriös, schlicht',
                'category' => 'Klassisch',
                'badge' => null,
                'previewImg' => 'https://picsum.photos/seed/men/800/1130',
                'pdfUrl' => '/samples/cv-kopfzeile.pdf',
                'pages' => 1,
                'tags' => ['Klassisch'],
            ],
            [
                'slug' => 'cv-2026-1',
                'title' => 'Lebenslauf 2024',
                'subtitle' => 'Kompakt, klar',
                'category' => 'Klassisch',
                'badge' => 'NEU',
                'previewImg' => 'https://picsum.photos/seed/2026/800/1130',
                'pdfUrl' => '/samples/cv-2024.pdf',
                'pages' => 1,
                'tags' => ['Einseitig'],
            ],
        ];

        foreach ($data as $row) {
            $t = new Template();
            $t->setSlug($row['slug'])
                ->setTitle($row['title'])
                ->setSubtitle($row['subtitle'])
                ->setCategory($row['category'])
                ->setBadge($row['badge'])
                ->setPreviewImg($row['previewImg'])
                ->setPdfUrl($row['pdfUrl'])
                ->setPages($row['pages'])
                ->setTags($row['tags']);
            $manager->persist($t);
        }

        $manager->flush();
    }
}
