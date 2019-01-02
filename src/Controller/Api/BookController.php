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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Book;
use App\Form\BookType;
use App\Serializer\FormErrorSerializer;

class BookController extends AbstractController
{
    protected $serializer;

    public function __construct()
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @Route("/api/books", name="api.books.index", methods={"GET"})
     */
    public function index(Request $request): JsonResponse
    {
        $author_id = $request->get('author_id');
        $genre_id = $request->get('genre_id');
        $title = $request->get('title');
        $offset = $request->get('offset', 0);
        $books = $this->getDoctrine()
            ->getRepository(Book::class)
            ->findByWithRelations(
                compact('author_id', 'genre_id', 'title'),
                50,
                $offset
            );

        $booksNormalized = $this->serializer->normalize($books, null, [
            'attributes' => [
                'id', 
                'title', 
                'description', 
                'price', 
                'discount', 
                'imagePath', 
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
     * @Route("/api/books/{id}", name="api.books.show", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function show(
        int $id,
        Request $request
    ): JsonResponse
    {
        $book = $this->getDoctrine()
            ->getRepository(Book::class)
            ->findWithRelations($id);

        if (!$book)
        {
            throw $this->createNotFoundException("Book with id $id doesn't exist.");
        }

        $bookNormalized = $this->serializer->normalize($book, null, [
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
                    'data' => $bookNormalized
                ]
            ]
        );
    }

    /**
     * @Route("/api/books", name="api.books.store", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function store(
        Request $request,
        FormErrorSerializer $serializer
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        
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
        $entityManager->persist($book);
        $entityManager->flush();

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'text' => "Book was successfully added.",
                    'data' => [
                        'id' => $book->getId(),
                        'title' => $book->getTitle(),
                        'description' => $book->getDescription(),
                        'price' => $book->getPrice(),
                        'discount' => $book->getDiscount()
                    ]
                ]
            ]
        );
    }

    /**
     * @Route("/api/books/{id}/image", name="api.books.storeImage", methods={"POST"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function storeImage(
        Book $book,
        Request $request,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $file = $request->files->get('image');
        $book->setImagePath($file);
        $errors = $validator->validate($book);

        if (count($errors) > 0) {
            $errorMessage =  $this->serializer
                ->normalize($errors, null, ['attributes' => ['message']])[0]['message'];

            return new JsonResponse(
                [
                    'status' => 'error',
                    'errors' => [
                        "children" => [
                            "image" => [
                                "errors" => [
                                    $errorMessage
                                ]
                            ]
                        ]
                    ],
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        $file->move(
            $this->getParameter('images_directory'),
            $fileName
        );

        $book->setImagePath('/uploads/images/' . $fileName);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($book);
        $entityManager->flush();

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'text' => "Book image was successfully updated.",
                    'data' => [
                        'id' => $book->getId(),
                        'title' => $book->getTitle(),
                        'description' => $book->getDescription(),
                        'price' => $book->getPrice(),
                        'discount' => $book->getDiscount(),
                        'image_path' => $book->getImagePath()
                    ]
                ]
            ]
        );
    }

    /**
     * @Route("/api/books/{id}", name="api.books.update", methods={"PUT"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function update(
        Book $book,
        Request $request,
        FormErrorSerializer $serializer
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(BookType::class, $book);
        
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
        $entityManager->persist($book);
        $entityManager->flush();

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'text' => "Book was successfully updated.",
                    'data' => [
                        'id' => $book->getId(),
                        'title' => $book->getTitle(),
                        'description' => $book->getDescription(),
                        'price' => $book->getPrice(),
                        'discount' => $book->getDiscount()
                    ]
                ]
            ]
        );
    }

    /**
     * @Route("/api/books/{id}", name="api.books.destroy", methods={"DELETE"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function destroy(
        Book $book, 
        Request $request
    ): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($book);
        $entityManager->flush();

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'text' => "Book was successfully deleted.",
                    'data' => [
                        'title' => $book->getTitle(),
                        'description' => $book->getDescription(),
                        'price' => $book->getPrice(),
                        'discount' => $book->getDiscount()
                    ]
                ]
            ]
        );
    }
}
