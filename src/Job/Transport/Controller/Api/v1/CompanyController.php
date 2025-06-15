<?php

namespace App\Job\Transport\Controller\Api\v1;

use App\Job\Domain\Entity\Company;
use App\Job\Infrastructure\Repository\CompanyRepository;
use DateTimeImmutable;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name: "Company")]
class CompanyController extends AbstractController
{
    /**
     * @throws JsonException
     */
    #[Route(path: "/api/v1/companies", methods: "POST")]
    #[OA\Post(description: "Create category.")]
    #[OA\RequestBody(
        description: "Json to create the company",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "CompanyName"),
                new OA\Property(property: "description", type: "string", example: "Description of the company"),
                new OA\Property(property: "location", type: "string", example: "CompanyLocation"),
                new OA\Property(property: "contactEmail", type: "string", example: "Company email"),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Returns the ID of the company',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "statusCode", type: "int", example: 201),
                new OA\Property(property: "message", type: "string", example: "Company created"),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid arguments',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "statusCode", type: "int", example: 400),
                new OA\Property(property: "message", type: "string", example: "Invalid arguments"),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    public function create(
        CompanyRepository $repository,
        Request $request,
        ValidatorInterface $validator
    ): Response
    {
        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $company = new Company();
        $company->setName($jsonParams['name']);
        $company->setDescription($jsonParams['description']);
        $company->setLocation($jsonParams['location']);
        $company->setContactEmail($jsonParams['contactEmail']);
        $company->setCreatedAt(new DateTimeImmutable());

        $violations = $validator->validate($company);

        if(count($violations) === 0){
            $repository->save($company, true);

            return $this->jsonResponse("Company created", [
                'id'=> (string)$company->getId()
            ], 201);
        }

        $errorData = [];
        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation){
            $errorData[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $this->jsonResponse("Invalid input", $errorData, 400);
    }

    #[Route(path: "/v1/companies", methods: "GET")]
    #[OA\Get(description: "Return all the companies.")]
    public function findAll(CompanyRepository $repository): Response
    {
        $companies = $repository->findAll();

        $response = [];
        foreach ($companies as $company){
            $response[] = $company->toArray();
        }

        return $this->jsonResponse("List of Companies", $response);
    }

    #[Route(path: "/v1/companies/{id}", methods: "GET")]
    #[OA\Get(description: "Return the company by ID")]
    public function findById(CompanyRepository $repository, string $id): Response
    {
        $company = $repository->find($id);

        if($company === null){
            return $this->jsonResponse("Company not found",['id'=>$id], 404);
        }

        return $this->jsonResponse("Company by ID", $company->toArray());
    }

    /**
     * @throws JsonException
     */
    #[Route(path: "/v1/companies/{id}", methods: "PUT")]
    #[OA\Put(description: "Updates the company by ID")]
    #[OA\RequestBody(
        description: "Json to update the company",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "description", type: "string", example: "Updated description of the company"),
                new OA\Property(property: "location", type: "string", example: "Updated Company Location"),
                new OA\Property(property: "contactEmail", type: "string", example: "company@company.com"),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the properties of the company',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "statusCode", type: "int", example: 200),
                new OA\Property(property: "message", type: "string", example: "Company Updated"),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid arguments',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "statusCode", type: "int", example: 400),
                new OA\Property(property: "message", type: "string", example: "Invalid arguments"),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    public function update(
        CompanyRepository $repository,
        Request $request,
        string $id,
        ValidatorInterface $validator
    ): Response
    {
        $company = $repository->find($id);

        if($company === null){
            return $this->jsonResponse("Company not found",['id'=>$id], 404);
        }

        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $company->setDescription($jsonParams['description']);
        $company->setLocation($jsonParams['location']);
        $company->setContactEmail($jsonParams['contactEmail']);
        $company->setUpdatedAt(new DateTimeImmutable());

        $violations = $validator->validate($company);

        if(count($violations) === 0){
            $repository->save($company, true);

            return $this->jsonResponse("Company updated",[
                $company->toArray()
            ]);
        }

        $errorData = [];
        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation){
            $errorData[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $this->jsonResponse("Invalid input", $errorData, 400);
    }

    #[Route(path: "/v1/companies/{id}", methods: "DELETE")]
    #[OA\Delete(description: "Deletes the company by ID")]
    public function remove(CompanyRepository $repository, string $id): Response
    {
        $company = $repository->find($id);

        if($company === null){
            return $this->jsonResponse("Company not found",['id'=>$id], 404);
        }

        $repository->remove($company, true);

        return $this->jsonResponse("Company deleted",[
            $company->toArray()
        ]);
    }

    private function jsonResponse(string $message, array $data, int $statusCode = 200): JsonResponse
    {
        return $this->json([
            "statusCode"=> $statusCode,
            "message"=> $message,
            "data"=> $data
        ], $statusCode);
    }
}
