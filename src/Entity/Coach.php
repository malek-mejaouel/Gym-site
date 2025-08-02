<?php
use App\Repository\CoachRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoachRepository::class)]
class Coach
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(length: 100)]
    private $firstName;

    #[ORM\Column(length: 100)]
    private $lastName;

    #[ORM\Column(type: 'integer')]
    private $age;

    #[ORM\Column(length: 180)]
    private $email;

    #[ORM\Column(length: 255)]
    private $degreePdf;

    #[ORM\Column(length: 50)]
    private $specialty;

    public function getFirstname(): ?string
{
    return $this->firstName;
}

    public function setFirstname(?string $firstName): self
{
    $this->firstName = $firstName;
    return $this;
}
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getlastName(): ?string
    {
        return $this->lastName;
    }

    public function setlastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getage(): ?int
    {
        return $this->age;
    }

    public function setage(int $age): self
    {
        $this->age = $age;
        return $this;
    }
    public function getdegreePdf(): ?string 
    {
        return $this->degreePdf;
    }
    public function setdegreePdf(string $degreePdf): self {
        $this->degreePdf= $degreePdf;
        return $this;
    }
    public function getspecaility(): ?string 
    {
        return $this->specialty;
    }
    public function setspecaility(string $specialty): self {
        $this->degreePdf= $specialty;
        return $this;
    } 

}
