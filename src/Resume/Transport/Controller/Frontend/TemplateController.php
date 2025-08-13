<?php

namespace App\Resume\Transport\Controller\Frontend;


use App\Resume\Infrastructure\Repository\TemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/platform/resume/templates', name: 'resume_templates_')]
final class TemplateController extends AbstractController
{
    public function __construct(
        private readonly TemplateRepository $templateRepository,
        private readonly EntityManagerInterface $em)
    {
    }
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $presets = $this->templateRepository->findBy([], ['label' => 'ASC']);

        // On expose directement les entités : les colonnes JSON sortent telles quelles
        return $this->json($presets, 200, [], ['groups' => ['cv:read']]);
    }

    #[Route('/{key}', name: 'show', methods: ['GET'])]
    public function show(string $key): JsonResponse
    {
        $preset = $this->templateRepository->findOneBy(['key' => $key]);
        if (!$preset) {
            return $this->json(['message' => 'Preset not found'], 404);
        }

        return $this->json($preset, 200, [], ['groups' => ['cv:read']]);
    }

    #[Route('/{key}/view', name: 'view', methods: ['POST'])]
    public function incView(string $key): JsonResponse
    {
        $preset = $this->templateRepository->findOneBy(['key' => $key]);
        if (!$preset) {
            return $this->json(['message' => 'Preset not found'], 404);
        }
        $preset->incViews();
        $this->em->flush();

        return $this->json(['views' => $preset->getViews()]);
    }

    #[Route('/{key}/download', name: 'download', methods: ['POST'])]
    public function incDownload(string $key): JsonResponse
    {
        $preset = $this->templateRepository->findOneBy(['key' => $key]);
        if (!$preset) {
            return $this->json(['message' => 'Preset not found'], 404);
        }
        $preset->incDownloads();
        $this->em->flush();

        return $this->json(['downloads' => $preset->getDownloads()]);
    }
}
