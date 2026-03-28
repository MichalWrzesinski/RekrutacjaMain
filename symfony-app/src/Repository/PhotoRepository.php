<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\Input\PhotoFiltersInput;
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

    public function findAllWithUsersByFilters(PhotoFiltersInput $filters): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.id', 'ASC');

        if ($filters->location !== '') {
            $queryBuilder
                ->andWhere('LOWER(p.location) LIKE LOWER(:location)')
                ->setParameter('location', '%' . $filters->location . '%');
        }

        if ($filters->camera !== '') {
            $queryBuilder
                ->andWhere('LOWER(p.camera) LIKE LOWER(:camera)')
                ->setParameter('camera', '%' . $filters->camera . '%');
        }

        if ($filters->description !== '') {
            $queryBuilder
                ->andWhere('LOWER(p.description) LIKE LOWER(:description)')
                ->setParameter('description', '%' . $filters->description . '%');
        }

        if ($filters->username !== '') {
            $queryBuilder
                ->andWhere('LOWER(u.username) LIKE LOWER(:username)')
                ->setParameter('username', '%' . $filters->username . '%');
        }

        if ($filters->takenAt !== '') {
            $from = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $filters->takenAt . ' 00:00:00');
            $to = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $filters->takenAt . ' 23:59:59');

            if ($from instanceof \DateTimeImmutable && $to instanceof \DateTimeImmutable) {
                $queryBuilder
                    ->andWhere('p.takenAt BETWEEN :takenAtFrom AND :takenAtTo')
                    ->setParameter('takenAtFrom', $from)
                    ->setParameter('takenAtTo', $to);
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
