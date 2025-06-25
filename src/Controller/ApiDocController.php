<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiDocController extends AbstractController
{
    #[Route('/api/docs', methods: ['GET'])]
    public function getApiDocs(): JsonResponse
    {
        $docs = [
            'title' => 'DevJobs API Documentation',
            'version' => '1.0.0',
            'description' => 'API REST pour la gestion des offres d\'emploi',
            'baseUrl' => '/api',
            'endpoints' => [
                [
                    'method' => 'GET',
                    'path' => '/jobs',
                    'description' => 'Récupérer la liste paginée des offres d\'emploi',
                    'parameters' => [
                        'offset' => ['type' => 'integer', 'default' => 0, 'description' => 'Position de départ (0, 12, 24, ...)'],
                        'limit' => ['type' => 'integer', 'default' => 10, 'max' => 25, 'description' => 'Nombre d\'éléments par page']
                    ],
                    'response' => [
                        'data' => 'Array of job objects',
                        'pagination' => 'Pagination metadata with offset'
                    ]
                ],
                [
                    'method' => 'GET',
                    'path' => '/job/{id}',
                    'description' => 'Récupérer une offre d\'emploi spécifique',
                    'parameters' => [
                        'id' => ['type' => 'integer', 'description' => 'ID de l\'offre']
                    ],
                    'response' => 'Job object'
                ],
                [
                    'method' => 'GET',
                    'path' => '/jobs/filter',
                    'description' => 'Filtrer et trier les offres d\'emploi',
                    'parameters' => [
                        'company' => ['type' => 'string', 'description' => 'Nom de l\'entreprise'],
                        'position' => ['type' => 'string', 'description' => 'Titre du poste'],
                        'location' => ['type' => 'string', 'description' => 'Localisation'],
                        'contract' => ['type' => 'string', 'description' => 'Type de contrat'],
                        'sortBy' => ['type' => 'string', 'default' => 'postedAt', 'options' => ['postedAt', 'company', 'position', 'location']],
                        'sortOrder' => ['type' => 'string', 'default' => 'DESC', 'options' => ['ASC', 'DESC']],
                        'offset' => ['type' => 'integer', 'default' => 0, 'description' => 'Position de départ'],
                        'limit' => ['type' => 'integer', 'default' => 10, 'max' => 25, 'description' => 'Nombre d\'éléments']
                    ],
                    'response' => [
                        'data' => 'Array of filtered job objects',
                        'pagination' => 'Pagination metadata with offset',
                        'filters' => 'Applied filters information'
                    ]
                ],
                [
                    'method' => 'POST',
                    'path' => '/jobs',
                    'description' => 'Créer une nouvelle offre d\'emploi',
                    'parameters' => [
                        'body' => [
                            'company' => 'string (required)',
                            'contract' => 'string (required)',
                            'location' => 'string (required)',
                            'position' => 'string (required)',
                            'logo' => 'string (required)',
                            'logoBackground' => 'string (required)',
                            'description' => 'string (required)',
                            'requirements' => [
                                'content' => 'string (required)',
                                'items' => 'array of strings (required)'
                            ],
                            'role' => [
                                'content' => 'string (required)',
                                'items' => 'array of strings (required)'
                            ],
                            'website' => 'string (required)',
                            'apply' => 'string (required)'
                        ]
                    ],
                    'response' => ['status' => 'Job created!']
                ],
                [
                    'method' => 'GET',
                    'path' => '/jobs/search',
                    'description' => 'Rechercher des offres d\'emploi (compatible frontend existant)',
                    'parameters' => [
                        'text' => ['type' => 'string', 'description' => 'Recherche dans entreprise et poste'],
                        'location' => ['type' => 'string', 'description' => 'Localisation'],
                        'fulltime' => ['type' => 'string', 'description' => 'Type de contrat (1=true, 0=false)'],
                        'offset' => ['type' => 'integer', 'default' => 0, 'description' => 'Position de départ'],
                        'limit' => ['type' => 'integer', 'default' => 10, 'max' => 25, 'description' => 'Nombre d\'éléments']
                    ],
                    'response' => [
                        'data' => 'Array of filtered job objects',
                        'pagination' => 'Pagination metadata with offset'
                    ]
                ]
            ],
            'errorResponses' => [
                '400' => 'Bad Request - Données invalides',
                '404' => 'Not Found - Ressource introuvable',
                '422' => 'Unprocessable Entity - Données non traitées',
                '429' => 'Too Many Requests - Trop de requêtes',
                '500' => 'Internal Server Error - Erreur serveur'
            ],
            'examples' => [
                'getJobs' => [
                    'url' => '/api/jobs?page=1&limit=10',
                    'response' => [
                        'data' => [
                            [
                                'id' => 1,
                                'company' => 'Example Corp',
                                'position' => 'Développeur Full Stack',
                                'contract' => 'Full Time',
                                'location' => 'Paris, France',
                                'postedAt' => '2024-01-15 10:30:00',
                                'logo' => 'https://example.com/logo.png',
                                'logoBackground' => '#FF6B6B'
                            ]
                        ],
                        'pagination' => [
                            'currentPage' => 1,
                            'totalPages' => 5,
                            'totalItems' => 50,
                            'itemsPerPage' => 10
                        ]
                    ]
                ]
            ]
        ];

        return new JsonResponse($docs, 200);
    }
}
