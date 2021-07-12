<?php

namespace App\Interfaces;

interface SocialClient
{
    public function getFirstName();
    public function getLastName();
    public function getEmail();
    public function makeClient();
}
