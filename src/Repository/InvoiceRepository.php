<?php

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    /**
     * Récupère toutes les factures triées par date descendante
     *
     * @return Invoice[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('i')
            ->orderBy('i.issueDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche par numéro de facture
     *
     * @param string $number
     * @return Invoice|null
     */
    public function findByInvoiceNumber(string $number): ?Invoice
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.invoiceNumber = :num')
            ->setParameter('num', $number)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupère les factures d’une année donnée
     *
     * @param int $year
     * @return Invoice[]
     */
    public function findByYear(int $year): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('YEAR(i.issueDate) = :year')
            ->setParameter('year', $year)
            ->orderBy('i.issueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
