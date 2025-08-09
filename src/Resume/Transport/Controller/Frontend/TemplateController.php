<?php

namespace App\Resume\Transport\Controller\Frontend;


use App\Resume\Infrastructure\Repository\TemplateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/platform/resume/templates', name: 'resume_templates_')]
final class TemplateController extends AbstractController
{
    public function __construct(private readonly TemplateRepository $templates)
    {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $items = $this->templates->createQueryBuilder('t')
            ->orderBy('t.title', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json($items, Response::HTTP_OK, [], [
            'groups' => ['Template'],
        ]);
    }

    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(string $slug): JsonResponse
    {
        $template = $this->templates->findOneBy(['slug' => $slug]);
        if (!$template) {
            return $this->json([
                'error' => 'Template not found',
                'slug' => $slug,
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json($template, Response::HTTP_OK, [], [
            'groups' => ['Template'],
        ]);
    }
}
