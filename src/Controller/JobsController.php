<?php 
namespace App\Controller;

use App\Entity\Jobs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class JobsController extends AbstractController
{
    #[Route('/api/jobs', methods: ['GET'])]
    public function getAll(EntityManagerInterface $entityManager): JsonResponse
    {
        $jobs = $entityManager->getRepository(Jobs::class)->findAll();
        $total = count($jobs);

        // Sérialisation des données pour les envoyer en JSON au front
        $data = [
            'total' => $total,
            'jobs' => array_map(fn(Jobs $job) => [
                'id' => $job->getId(),
                'company' => $job->getCompany(),
                'contract' => $job->getContract(),
                'location' => $job->getLocation(),
                'position' => $job->getPosition(),
                'postedAt' => $job->getPostedAt()->format('Y-m-d H:i:s'),
                'logo' => $job->getLogo(),
                'logoBackground' => $job->getLogoBackground(),
                'apply' => $job->getApply(),
                'description' => $job->getDescription(),
                'requirementsContent' => $job->getRequirementsContent(),
                'requirementsItems' => $job->getRequirementsItems(),
                'roleContent' => $job->getRoleContent(),
                'roleItems' => $job->getRoleItems(),
                'website' => $job->getWebsite()
            ], $jobs)
        ];

        return new JsonResponse($data, 200);
    }

    #[Route('/api/jobs/{id}', methods: ['GET'])]
    public function getOne(Jobs $job): JsonResponse
    {
        return new JsonResponse([
            'id' => $job->getId(),
            'company' => $job->getCompany(),
            'contract' => $job->getContract(),
            'location' => $job->getLocation(),
            'position' => $job->getPosition(),
            'postedAt' => $job->getPostedAt()->format('Y-m-d H:i:s'),
            'logo' => $job->getLogo(),
            'logoBackground' => $job->getLogoBackground(),
            'apply' => $job->getApply(),
            'description' => $job->getDescription(),
            'requirementsContent' => $job->getRequirementsContent(),
            'requirementsItems' => $job->getRequirementsItems(),
            'roleContent' => $job->getRoleContent(),
            'roleItems' => $job->getRoleItems(),
            'website' => $job->getWebsite()
        ], 200);
    }

    #[Route('/api/jobs', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $constraint = new Assert\Collection([
            'company' => new Assert\NotBlank(),
            'contract' => new Assert\NotBlank(),
            'location' => new Assert\NotBlank(),
            'position' => new Assert\NotBlank(),
            'postedAt' => new Assert\NotBlank(),
            'logo' => new Assert\NotBlank(),
            'logoBackground' => new Assert\NotBlank(),
            'apply' => new Assert\NotBlank(),
            'description' => new Assert\NotBlank(),
            'requirementsContent' => new Assert\NotBlank(),
            'requirementsItems' => new Assert\NotBlank(),
            'roleContent' => new Assert\NotBlank(),
            'roleItems' => new Assert\NotBlank(),
            'website' => new Assert\NotBlank()
        ]);

        $violations = $validator->validate($data, $constraint);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], 400);
        }

        $job = new Jobs();
        $fields = [
            'logo', 'company', 'contract', 'location', 'position', 'postedAt',
            'logoBackground', 'apply', 'description', 'requirementsContent',
            'requirementsItems', 'roleContent', 'roleItems', 'website'
        ];

        foreach ($fields as $field) {
            $setter = 'set' . ucfirst($field);
            if (method_exists($job, $setter)) {
                $job->$setter($data[$field]);
            }
        }

        $entityManager->persist($job);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Job created!'], 201);
    }
}