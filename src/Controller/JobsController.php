<?php

namespace App\Controller;

use App\Entity\Jobs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
        ], 200);
    }

    #[Route('/api/jobs', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $job = new Jobs();
        $job->setLogo($data['logo']);
        $job->setCompany($data['company']);
        $job->setContract($data['contract']);
        $job->setLocation($data['location']);
        $job->setPosition($data['position']);
        $job->setPostedAt(new \DateTimeImmutable());

        $entityManager->persist($job);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Job created!'], 201);
    }
}