<?php

namespace App\Job\Application\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
        $file = $request->files->get('file');

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
            throw new FileException('Failed to upload company logo.', 0, $e);
        }
        $baseUrl = $request->getSchemeAndHttpHost();
        $relativePath = '/uploads/companies/logo/' . $newFilename;

        return $baseUrl . $relativePath;
    }
}
