<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use App\Entity\Genre;
use App\Form\GenreType;
use App\Serializer\FormErrorSerializer;

class GenreController extends AbstractController
{
    protected $serializer;

    public function __construct()
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @Route("/api/genres", name="api.genres.index", methods={"GET"})
     */
    public function index(Request $request): JsonResponse
    {
        $offset = $request->get('offset', 0);
        $genres = $this->getDoctrine()
            ->getRepository(Genre::class)
            ->findBy(
                [],
                null,
                50,
                $offset
            );

        $genresNormalized = $this->serializer->normalize($genres, null, [
            'attributes' => ['id', 'name']
        ]);

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'data' => $genresNormalized
                ]
            ]
        );
    }

    /**
     * @Route("/api/genres/{id}", name="api.genres.show", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function show(
        Genre $genre, 
        Request $request
    ): JsonResponse
    {
        $genreNormalized = $this->serializer->normalize($genre, null, [
            'attributes' => ['id', 'name']
        ]);

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'data' => $genreNormalized
                ]
            ]
        );
    }

    /**
     * @Route("/api/genres/{id}/books", name="api.genres.showBooks", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function showBooks(
        Genre $genre, 
        Request $request
    ): JsonResponse
    {
        $books = $genre->getBooks();

        $booksNormalized = $this->serializer->normalize($books, null, [
            'attributes' => [
                'id', 
                'title', 
                'description', 
                'imagePath', 
                'price', 
                'discount', 
                'author' => ['id', 'name'],
                'genre' => ['id', 'name']
            ]
        ]);

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'data' => $booksNormalized
                ]
            ]
        );
    }

    /**
     * @Route("/api/genres", name="api.genres.store", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function store(
        Request $request,
        FormErrorSerializer $serializer
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $genre = new Genre();
        $form = $this->createForm(GenreType::class, $genre);
        
        $form->submit($data);

        if (false === $form->isValid()) 
        {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'errors' => $serializer->convertFormToArray($form),
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($genre);
        $entityManager->flush();

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'text' => "Genre was successfully added.",
                    'data' => [
                        'id' => $genre->getId(),
                        'name' => $genre->getName()
                    ]
                ]
            ]
        );
    }

    /**
     * @Route("/api/genres/{id}", name="api.genres.update", methods={"PUT"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function update(
        Genre $genre, 
        Request $request,
        FormErrorSerializer $serializer
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(GenreType::class, $genre);
        
        $form->submit($data);

        if (false === $form->isValid()) 
        {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'errors' => $serializer->convertFormToArray($form),
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($genre);
        $entityManager->flush();

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'text' => "Genre was successfully updated.",
                    'data' => [
                        'id' => $genre->getId(),
                        'name' => $genre->getName()
                    ]
                ]
            ]
        );
    }

    /**
     * @Route("/api/genres/{id}", name="api.genres.destroy", methods={"DELETE"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function destroy(
        Genre $genre, 
        Request $request
    ): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($genre);
        $entityManager->flush();

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'text' => "Genre was successfully deleted.",
                    'data' => [
                        'name' => $genre->getName()
                    ]
                ]
            ]
        );
    }
}