<?php

namespace App\Dto;

use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterDto
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6)]
    public string $password;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    public string $firstname;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    public string $lastname;

    #[Assert\NotBlank]
    #[Assert\Type(type: "DateTimeInterface")]
    #[Assert\GreaterThanOrEqual("1900-01-01")]
    public ?\DateTimeInterface $birthdate = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    public string $city;
    

}