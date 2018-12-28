<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Book|null findWithRelations(int $id)
 * @method Book[]    findByWithRelations(array $criteria, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * @return Book Returns Book object
     */
    public function findWithRelations(int $id): ?Book
    {
        $query = $this->createQueryBuilder('b')
            ->leftJoin('b.author', 'a')
            ->leftJoin('b.genre', 'g')
            ->addSelect('a', 'g')
            ->andWhere("b.id = :val")
            ->setParameter('val', $id)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @return Book[] Returns an array of Book objects
     */
    public function findByWithRelations(
        array $criteria, 
        int $limit = null, 
        int $offset = null
    ): array
    {
        $query = $this->createQueryBuilder('b')
            ->leftJoin('b.author', 'a')
            ->leftJoin('b.genre', 'g')
            ->addSelect('a', 'g');

        if ($criteria['title'])
        {
            $query = $query->andWhere("b.title LIKE :val")
                ->setParameter('val', "%{$criteria['title']}%");
        }

        if ($criteria['author_id'])
        {
            $query = $query->andWhere("a.id = :val")
                ->setParameter('val', "{$criteria['author_id']}");
        }

        if ($criteria['genre_id'])
        {
            $query = $query->andWhere("g.id = :val")
                ->setParameter('val', "{$criteria['genre_id']}");
        }

        $query = $query->orderBy('b.id', 'ASC')->setMaxResults($limit)->setFirstResult($offset)->getQuery();

        return $query->getResult();
    }
}
