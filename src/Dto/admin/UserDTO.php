<?php

namespace App\Dto\admin;

use App\Entity\admin\Personnel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class UserDTO
{
    public string $username = '';
    public string $email = '';
    public ?Personnel $personnel = null;
    public Collection $profils;

    public function __construct()
    {
        $this->profils = new ArrayCollection();
    }
}
