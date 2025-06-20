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
     * @return string|JsonResponse
     */
    public function uploadLogo(Request $request): string|JsonResponse
    {
        $file = $request->files->get('file');

        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded.'], 400);
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
