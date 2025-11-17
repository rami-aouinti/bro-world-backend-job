<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\v1;

use Bro\WorldCoreBundle\Domain\Utils\JSON;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Domain\Entity\Media;
use App\Resume\Domain\Entity\Project;
use App\Resume\Domain\Entity\Reference;
use App\Resume\Infrastructure\Repository\SkillRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JsonException;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package App\Resume
 */
#[AsController]
#[OA\Tag(name: 'Resume')]
class CreateReferenceController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Get current user profile data, accessible only for 'IS_AUTHENTICATED_FULLY' users.
     *
     * @throws JsonException
     * @throws Exception
     */
    #[Route(
        path: '/v1/resume/reference',
        methods: [Request::METHOD_POST],
    )]
    public function __invoke(
        SymfonyUser $loggedInUser,
        Request $request,
        SkillRepository $skillRepository,
        HubInterface $hub
    ): JsonResponse {

        $reference = new Reference();
        $reference->setTitle($request->request->get('referenceTitle'));
        $reference->setDescription($request->request->get('referenceDescription'));
        $reference->setCompany($request->request->get('referenceCompany'));
        $reference->setStartedAt(new DateTimeImmutable($request->request->get('referenceStartedAt') ?? 'now'));
        $endedAt = $request->request->get('referenceEndedAt');
        $reference->setEndedAt($endedAt ? new DateTimeImmutable($endedAt) : null);
        $reference->setUser(Uuid::fromString($loggedInUser->getId()));

        $files = $request->files->get('photo');

        if (!$files) {
            return new JsonResponse([
                'error' => 'No file uploaded',
            ], 400);
        }
        $uploadDir = $this->getParameter('uploads_directory');
        foreach ($files as $file){

            $media = new Media();
            $media->setPath('file_name');
            $media->setFile($file);
            $reference->addMedia($media);
            $this->entityManager->persist($media);
        }

        $this->entityManager->persist($reference);
        $this->entityManager->flush();


        /** @var array<string, string|array<string, string>> $output */
        $output = JSON::decode(
            $this->serializer->serialize(
                $reference,
                'json',
                [ 'groups' => 'Reference',]
            ),
            true,
        );

        return new JsonResponse($output);
    }
}
