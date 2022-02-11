<?php

namespace App\Event;

use App\Entity\Vacances;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class MailEvent extends Event{
    protected Vacances $vacances;
    protected User $user;

    public function __construct(Vacances $vacances, User $user){
        $this->vacances = $vacances;
        $this->user = $user;
    }

    public function getVacances(): Vacances{ 
        return $this->vacances;
    }
    public function getUser(): User{
        return $this->user;
    }
}
