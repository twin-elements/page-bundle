<?php

namespace TwinElements\PageBundle\Repository;

use TwinElements\PageBundle\Entity\Page\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method Page|null find($id)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    /**
     * @return Query
     * @throws Exception
     */
    public function findIndexListItemsQuery(string $locale)
    {
        if (is_null($locale)) {
            throw new Exception();
        }

        $qb = $this->createQueryBuilder('page');

        $qb
            ->select(['page', 'page_translations'])
            ->leftJoin('page.translations', 'page_translations')
            ->where(
                $qb->expr()->isNull('page.isContentFor')
            )
            ->orderBy('page.position', 'asc');

        return $qb->getQuery();
    }

    public function findIndexListItems(string $locale)
    {
        if (is_null($locale)) {
            throw new Exception();
        }

        $qb = $this->createQueryBuilder('page');

        $qb
            ->select(['page', 'page_translations'])
            ->join('page.translations', 'page_translations')
            ->where('page_translations.locale = :locale')
            ->andWhere(
                $qb->expr()->isNull('page.isContentFor')
            )
            ->setParameter('locale', $locale)
            ->orderBy('page.position', 'asc');

        return $qb->getQuery()->getResult();
    }

    public function findAllTranslated(string $locale)
    {
        $qb = $this->createQueryBuilder('page');
        $qb
            ->select(['page', 'page_translations'])
            ->join('page.translations', 'page_translations')
            ->where(
                $qb->expr()->eq('page_translations.enable', true)
            )
            ->andWhere(
                $qb->expr()->eq('page_translations.locale', $locale)
            );

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $code
     * @param string|null $locale
     * @return Page|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByCode(string $code, $locale = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p', 't')
            ->leftJoin('p.translations', 't');

        $qb
            ->where(
                $qb->expr()->eq('p.code', ':code')
            )
            ->setParameter('code', $code)
            ->andWhere(
                $qb->expr()->eq('t.enable', ':enable')
            )
            ->setParameter('enable', true);

        if ($locale) {
            $qb
                ->andWhere(
                    $qb->expr()->eq('t.locale', ':locale')
                )
                ->setParameter('locale', $locale);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $code
     * @param string|null $locale
     * @return Page[]|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByCode(string $code, $locale = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p', 't')
            ->leftJoin('p.translations', 't');

        $qb
            ->where(
                $qb->expr()->eq('p.code', ':code')
            )
            ->setParameter('code', $code)
            ->andWhere(
                $qb->expr()->eq('t.enable', ':enable')
            )
            ->setParameter('enable', true)
            ->orderBy('p.position', 'asc')
        ;

        if ($locale) {
            $qb
                ->andWhere(
                    $qb->expr()->eq('t.locale', ':locale')
                )
                ->setParameter('locale', $locale);
        }

        return $qb->getQuery()->getResult();
    }
}
