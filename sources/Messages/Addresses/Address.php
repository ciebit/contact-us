<?php
namespace Ciebit\ContactUs\Messages\Addresses;

class Address
{
    private $place; #string
    private $number; #int
    private $neighborhood; #string
    private $complement; #string
    private $cep; #string
    private $city_id; #int
    private $city_name; #string
    private $state_name; #string

    public function __construct
    (
        string $place,
        int $number,
        string $neighborhood,
        string $city_name,
        string $state_name
    )
    {
        $this->place = $place;
        $this->number = $number;
        $this->neighborhood = $neighborhood;
        $this->city_name = $city_name;
        $this->state_name = $state_name;
    }

    public function getPlace(): string
    {
        return $this->place;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getNeighborhood(): string
    {
        return $this->neighborhood;
    }

    public function getComplement(): ?string
    {
        return $this->complement;
    }

    public function getCep(): ?string
    {
        return $this->cep;
    }

    public function getCityId(): ?int
    {
        return $this->city_id;
    }

    public function getCityName(): string
    {
        return $this->city_name;
    }

    public function getStateName(): string
    {
        return $this->state_name;
    }

    public function setComplement(string $complement): self
    {
        $this->complement = $complement;
        return $this;
    }

    public function setCep(string $cep): self
    {
        $this->cep = $cep;
        return $this;
    }

    public function setCityId(int $id): self
    {
        $this->city_id = $id;
        return $this;
    }
}
