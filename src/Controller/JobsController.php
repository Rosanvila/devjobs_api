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
    public function getAll(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {

        $offset = max(0, (int) $request->query->get('offset', 0));
        $limit = min(25, max(1, (int) $request->query->get('limit', 10)));

        $jobsRepository = $entityManager->getRepository(Jobs::class);

        $jobs = $jobsRepository->findBy([], null, $limit, $offset);
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
                'description' => $job->getDescription(),
                'requirements' => [
                    'content' => $job->getRequirementsContent(),
                    'items' => $job->getRequirementsItems()
                ],
                'role' => [
                    'content' => $job->getRoleContent(),
                    'items' => $job->getRoleItems()
                ],
                'website' => $job->getWebsite(),
                'apply' => $job->getApply()
            ], $jobs)
        ];

        return new JsonResponse($data, 200);
    }

    #[Route('/api/job/{id}', methods: ['GET'])]
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
            'description' => $job->getDescription(),
            'requirements' => [
                'content' => $job->getRequirementsContent(),
                'items' => $job->getRequirementsItems()
            ],
            'role' => [
                'content' => $job->getRoleContent(),
                'items' => $job->getRoleItems()
            ],
            'website' => $job->getWebsite(),
            'apply' => $job->getApply()
        ], 200);
    }

    #[Route('/api/jobs/search', methods: ['GET'])]
    public function searchJobs(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $text = $request->query->get('text', null);
        $location = $request->query->get('location', null);
        $fulltime = $request->query->getBoolean('fulltime', false);
        $offset = $request->query->getInt('offset', 0);
        $limit = $request->query->getInt('limit', 10);

        $qb = $entityManager->createQueryBuilder();
        $qb->select('j')
            ->from(Jobs::class, 'j');

        if (!empty($text)) {
            $qb->andWhere('j.company LIKE :text')
                ->setParameter('text', '%' . $text . '%');
        }


        if (!empty($location)) {
            $qb->andWhere('j.location LIKE :location')
                ->setParameter('location', '%' . $location . '%');
        }

        if ($fulltime !== null) {
            $qb->andWhere('j.contract LIKE :contract')
                ->setParameter('contract', $fulltime ? '%Full-Time%' : '%Part-Time%');
        }

        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        $jobs = $qb->getQuery()->getResult();

        $data = array_map(function (Jobs $job) {
            return [
                'id' => $job->getId(),
                'company' => $job->getCompany(),
                'contract' => $job->getContract(),
                'location' => $job->getLocation(),
                'position' => $job->getPosition(),
                'postedAt' => $job->getPostedAt()->format('Y-m-d H:i:s'),
                'logo' => $job->getLogo(),
                'logoBackground' => $job->getLogoBackground(),
                'description' => $job->getDescription(),
                'requirements' => [
                    'content' => $job->getRequirementsContent(),
                    'items' => $job->getRequirementsItems()
                ],
                'role' => [
                    'content' => $job->getRoleContent(),
                    'items' => $job->getRoleItems()
                ],
                'website' => $job->getWebsite(),
                'apply' => $job->getApply()
            ];
        }, $jobs);

        return new JsonResponse([
            'total' => count($jobs),
            'jobs' => $data
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
            'logo' => new Assert\NotBlank(),
            'logoBackground' => new Assert\NotBlank(),
            'description' => new Assert\NotBlank(),
            'requirements' => new Assert\Collection([
                'content' => new Assert\NotBlank(),
                'items' => new Assert\All([
                    new Assert\NotBlank()
                ])
            ]),
            'role' => new Assert\Collection([
                'content' => new Assert\NotBlank(),
                'items' => new Assert\All([
                    new Assert\NotBlank()
                ])
            ]),
            'website' => new Assert\NotBlank(),
            'apply' => new Assert\NotBlank()
        ]);

        $violations = $validator->validate($data, $constraint);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }
            return new JsonResponse(['errors' => $errors], 400);
        }

        $job = new Jobs();

        $fields = [
            'company',
            'contract',
            'location',
            'position',
            'logo',
            'logoBackground',
            'apply',
            'description',
            'website',
            'requirements' => [
                'content',
                'items'
            ],
            'role' => [
                'content',
                'items'
            ]
        ];

        foreach ($fields as $key => $field) {
            if (is_array($field)) {
                foreach ($field as $subField) {
                    $setter = 'set' . ucfirst($key) . ucfirst($subField);
                    if (method_exists($job, $setter)) {
                        $job->$setter($data[$key][$subField]);
                    }
                }
            } else {
                $setter = 'set' . ucfirst($field);
                if (method_exists($job, $setter)) {
                    $job->$setter($data[$field]);
                }
            }
        }

        $job->setPostedAt(new \DateTimeImmutable());

        $entityManager->persist($job);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Job created!'], 201);
    }
}
