<?php

namespace App\Job\Application\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

class CompanyService
{

    public function __construct(
        private string $logoCompanyDirectory,
        private readonly SluggerInterface $slugger
    ) {
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function uploadLogo(Request $request): string
    {
        $files = $request->files->get('file');

        $file = $files[0];
        if (!$file) {
            return '';
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid('', true).'.'.$file->guessExtension();

        try {
            $file->move(
                $this->logoCompanyDirectory,
                $newFilename
            );
        } catch (FileException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
        $baseUrl = $request->getSchemeAndHttpHost();
        $relativePath = '/uploads/companies/logo/' . $newFilename;

        return $baseUrl . $relativePath;
    }
}
