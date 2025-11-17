<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use Bro\WorldCoreBundle\Domain\Utils\JSON;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Application\PdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * @package App\Resume
 */
#[AsController]
#[OA\Tag(name: 'Resume')]
class GenerateResumeController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly PdfGenerator $pdfGenerator
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     */
    #[Route(
        path: '/v1/resume/generate',
        methods: [Request::METHOD_GET],
    )]
    #[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
    public function __invoke(
        SymfonyUser $loggedInUser,
        Request $request,
        HubInterface $hub
    ): JsonResponse {
        $data = [
            'title' => 'Exemple de PDF',
            'content' => 'Ceci est un contenu PDF généré depuis Symfony.',
        ];

        $fileName = 'document.pdf';
        try {
            $this->pdfGenerator->generatePdf('Pdf/pdf_template.html.twig', $data, $fileName);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
        }

        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                'notification created',
                'json',
                []
            ),
            true,
        );

        return new JsonResponse($output);
    }
}
