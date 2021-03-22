<?php

namespace App\Repository;

use App\Entity\Cabinet;
use App\Entity\Licence;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Licence|null find($id, $lockMode = null, $lockVersion = null)
 * @method Licence|null findOneBy(array $criteria, array $orderBy = null)
 * @method Licence[]    findAll()
 * @method Licence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LicenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Licence::class);
    }

    // /**
    //  * @return Licence[] Returns an array of Licence objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Licence
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getAllLicences(Cabinet $cabinet)
    {
        return $this->createQueryBuilder('l')
            ->addSelect('CASE WHEN l.dateFin IS NULL THEN 1 ELSE 0 END AS HIDDEN DATEFIN_NULL')
            ->andWhere('l.cabinet = :cabinet')
            ->setParameter('cabinet', $cabinet)
            ->orderBy('DATEFIN_NULL','DESC')
            ->addOrderBy('l.dateFin', 'DESC')
            ->addOrderBy('l.deltaJourFin', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getActiveLicences(Cabinet $cabinet)
    {
        $dateNow = new DateTime();
        return $this->createQueryBuilder('l')
            ->addSelect('CASE WHEN l.dateFin IS NULL THEN 1 ELSE 0 END AS HIDDEN DATEFIN_NULL')
            ->andWhere('DATE_ADD(COALESCE(l.dateFin, :date), COALESCE(l.deltaJourFin, 0), \'day\') >= :date')
            ->andWhere('l.cabinet = :cabinet')
            ->setParameter('date', $dateNow)
            ->setParameter('cabinet', $cabinet)
            ->orderBy('DATEFIN_NULL','ASC')
            ->addOrderBy('l.dateFin', 'ASC')
            ->addOrderBy('l.deltaJourFin', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
