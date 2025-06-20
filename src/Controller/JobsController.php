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
        // Pagination optimisée pour offset
        $offset = max(0, (int) $request->query->get('offset', 0));
        $limit = min(25, max(1, (int) $request->query->get('limit', 10)));

        $jobsRepository = $entityManager->getRepository(Jobs::class);

        // Compter le total
        $total = $jobsRepository->count([]);

        // Récupérer les jobs avec offset
        $jobs = $jobsRepository->findBy([], ['postedAt' => 'DESC'], $limit, $offset);

        // Calculer les métadonnées de pagination pour offset
        $currentPage = floor($offset / $limit) + 1;
        $totalPages = ceil($total / $limit);
        $hasNextPage = $offset + $limit < $total;
        $hasPrevPage = $offset > 0;

        // Sérialisation des données pour les envoyer en JSON au front
        $data = [
            'data' => array_map(fn(Jobs $job) => [
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
            ], $jobs),
            'pagination' => [
                'offset' => $offset,
                'limit' => $limit,
                'total' => $total,
                'hasNextPage' => $hasNextPage,
                'hasPrevPage' => $hasPrevPage,
                'nextOffset' => $hasNextPage ? $offset + $limit : null,
                'prevOffset' => $hasPrevPage ? max(0, $offset - $limit) : null,
                // Informations supplémentaires pour compatibilité
                'currentPage' => $currentPage,
                'totalPages' => $totalPages
            ]
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
        } elseif (!empty($location)) {
            $qb->andWhere('j.location LIKE :location')
                ->setParameter('location', '%' . $location . '%');
        } elseif ($fulltime !== null) {
            $qb->andWhere('j.contract LIKE :contract')
                ->setParameter('contract', $fulltime ? '%Full-Time%' : '%Part-Time%');
        }

        // Si aucun paramètre n'est renseigné, on renvoie une erreur
        if (empty($jobs)) {
            return new JsonResponse(['error' => 'No jobs found'], 404);
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

    #[Route('/api/jobs/filter', methods: ['GET'])]
    public function filterJobs(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Pagination optimisée pour offset
        $offset = max(0, (int) $request->query->get('offset', 0));
        $limit = min(25, max(1, (int) $request->query->get('limit', 10)));

        // Paramètres de filtrage
        $company = $request->query->get('company');
        $position = $request->query->get('position');
        $location = $request->query->get('location');
        $contract = $request->query->get('contract');
        $sortBy = $request->query->get('sortBy', 'postedAt');
        $sortOrder = $request->query->get('sortOrder', 'DESC');

        // Validation du tri
        $allowedSortFields = ['postedAt', 'company', 'position', 'location'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'postedAt';
        }
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $qb = $entityManager->createQueryBuilder();
        $qb->select('j')
            ->from(Jobs::class, 'j');

        // Application des filtres
        if (!empty($company)) {
            $qb->andWhere('LOWER(j.company) LIKE LOWER(:company)')
                ->setParameter('company', '%' . $company . '%');
        }

        if (!empty($position)) {
            $qb->andWhere('LOWER(j.position) LIKE LOWER(:position)')
                ->setParameter('position', '%' . $position . '%');
        }

        if (!empty($location)) {
            $qb->andWhere('LOWER(j.location) LIKE LOWER(:location)')
                ->setParameter('location', '%' . $location . '%');
        }

        if (!empty($contract)) {
            $qb->andWhere('LOWER(j.contract) LIKE LOWER(:contract)')
                ->setParameter('contract', '%' . $contract . '%');
        }

        // Compter le total avec les filtres
        $countQb = clone $qb;
        $total = $countQb->select('COUNT(j.id)')->getQuery()->getSingleScalarResult();

        // Tri et pagination avec offset
        $qb->orderBy('j.' . $sortBy, $sortOrder)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $jobs = $qb->getQuery()->getResult();

        // Calculer les métadonnées de pagination pour offset
        $currentPage = floor($offset / $limit) + 1;
        $totalPages = ceil($total / $limit);
        $hasNextPage = $offset + $limit < $total;
        $hasPrevPage = $offset > 0;

        $data = [
            'data' => array_map(function (Jobs $job) {
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
            }, $jobs),
            'pagination' => [
                'offset' => $offset,
                'limit' => $limit,
                'total' => $total,
                'hasNextPage' => $hasNextPage,
                'hasPrevPage' => $hasPrevPage,
                'nextOffset' => $hasNextPage ? $offset + $limit : null,
                'prevOffset' => $hasPrevPage ? max(0, $offset - $limit) : null,
                // Informations supplémentaires pour compatibilité
                'currentPage' => $currentPage,
                'totalPages' => $totalPages
            ],
            'filters' => [
                'applied' => array_filter([
                    'company' => $company,
                    'position' => $position,
                    'location' => $location,
                    'contract' => $contract
                ]),
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder
            ]
        ];

        return new JsonResponse($data, 200);
    }
}
