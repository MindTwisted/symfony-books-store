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
use App\Entity\Author;
use App\Form\AuthorType;
use App\Serializer\FormErrorSerializer;

class AuthorController extends AbstractController
{
    protected $serializer;

    public function __construct()
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @Route("/api/authors", name="api.authors.index", methods={"GET"})
     */
    public function index(Request $request): JsonResponse
    {
        $offset = $request->get('offset', 0);
        $authors = $this->getDoctrine()
            ->getRepository(Author::class)
            ->findBy(
                [],
                null,
                50,
                $offset
            );

        $authorsNormalized = $this->serializer->normalize($authors, null, [
            'attributes' => ['id', 'name']
        ]);

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'data' => $authorsNormalized
                ]
            ]
        );
    }

    /**
     * @Route("/api/authors/{id}", name="api.authors.show", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function show(
        Author $author, 
        Request $request
    ): JsonResponse
    {
        $authorNormalized = $this->serializer->normalize($author, null, [
            'attributes' => ['id', 'name']
        ]);

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'data' => $authorNormalized
                ]
            ]
        );
    }

    /**
     * @Route("/api/authors/{id}/books", name="api.authors.showBooks", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function showBooks(
        Author $author, 
        Request $request
    ): JsonResponse
    {
        $books = $author->getBooks();

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
     * @Route("/api/authors", name="api.authors.store", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function store(
        Request $request,
        FormErrorSerializer $serializer
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);
        
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
        $entityManager->persist($author);
        $entityManager->flush();

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'text' => "Author was successfully added.",
                    'data' => [
                        'id' => $author->getId(),
                        'name' => $author->getName()
                    ]
                ]
            ]
        );
    }

    /**
     * @Route("/api/authors/{id}", name="api.authors.update", methods={"PUT"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function update(
        Author $author, 
        Request $request,
        FormErrorSerializer $serializer
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(AuthorType::class, $author);
        
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
        $entityManager->persist($author);
        $entityManager->flush();

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'text' => "Author was successfully updated.",
                    'data' => [
                        'id' => $author->getId(),
                        'name' => $author->getName()
                    ]
                ]
            ]
        );
    }

    /**
     * @Route("/api/authors/{id}", name="api.authors.destroy", methods={"DELETE"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function destroy(
        Author $author, 
        Request $request
    ): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($author);
        $entityManager->flush();

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'text' => "Author was successfully deleted.",
                    'data' => [
                        'name' => $author->getName()
                    ]
                ]
            ]
        );
    }
}