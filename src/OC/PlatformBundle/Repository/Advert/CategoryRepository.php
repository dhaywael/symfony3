<?php
// src/OC/PlatformBundle/Repository/CategoryRepository.php

namespace OC\PlatformBundle\Repository\Advert;

use Doctrine\ORM\EntityRepository;

class CategoryRepository extends \Doctrine\ORM\EntityRepository
{
    public function getLikeQueryBuilder($pattern)
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.name LIKE :pattern')
            ->setParameter('pattern', $pattern)
        ;
    }
}
