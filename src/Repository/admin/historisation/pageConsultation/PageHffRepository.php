<?php

namespace App\Repository\admin\historisation\pageConsultation;

use Doctrine\ORM\EntityRepository;
use App\Entity\admin\historisation\pageConsultation\PageHff;

class PageHffRepository extends EntityRepository
{
    public function findPageByRouteName(string $nomRoute)
    {
        return $this->_em->getRepository(PageHff::class)->findOneBy(['nomRoute' => $nomRoute]);
    }
}
