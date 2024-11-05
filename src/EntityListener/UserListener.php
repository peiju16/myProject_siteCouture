<?php

namespace App\EntityListener;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserListener {

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function prePersist(User $user) {

        $this->encodePassword($user);
    }

    public function preUpdate(User $user) {

        $this->encodePassword($user);
    }

        
    /**
     * encodePassword from pleinpassword
     *
     * @param  mixed $user
     * @return void
     */
    public function encodePassword(User $user) {

        if ($user->getPleinPassword() === null) {
            return;
        }

        $user->setPassword(
            $this->hasher->hashPassword($user, $user->getPleinPassword())
        );

        $user->setPleinPassword('null');

    }
}