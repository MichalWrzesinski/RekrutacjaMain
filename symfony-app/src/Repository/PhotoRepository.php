<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Photo;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    public function findAllWithUsers(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function existsByUserAndImageUrl(User $user, string $imageUrl): bool
    {
        return null !== $this->createQueryBuilder('p')
            ->select('1')
            ->where('p.user = :user')
            ->andWhere('p.imageUrl = :imageUrl')
            ->setParameter('user', $user)
            ->setParameter('imageUrl', $imageUrl)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
