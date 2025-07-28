<?php

namespace App\Repository\admin\historisation\pageConsultation;

use App\Entity\admin\historisation\pageConsultation\PageHff;
use Doctrine\ORM\EntityRepository;

class PageHffRepository extends EntityRepository
{
    public function findPageByRouteName(string $nomRoute)
    {
        return $this->_em->getRepository(PageHff::class)->findOneBy(['nomRoute' => $nomRoute]);
    }
}
