<?php

namespace App\Job\Application\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

class ResumeService
{

    public function __construct(
        private string $resumeDirectory,
        private readonly SluggerInterface $slugger
    ) {
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function uploadCV(Request $request): string
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
                $this->resumeDirectory,
                $newFilename
            );
        } catch (FileException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
        $baseUrl = $request->getSchemeAndHttpHost();
        $relativePath = '/uploads/resume/' . $newFilename;

        return $baseUrl . $relativePath;
    }
}
