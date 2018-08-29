<?php
namespace Ciebit\ContactUs\Messages;

use DateTime;
use Ciebit\ContactUs\Status;

class Message
{
    private $id; #int
    private $name; #string
    private $address_place; #string
    private $address_number; #int
    private $address_neighborhood; #string
    private $address_complement; #string
    private $address_cep; #string
    private $address_city_id; #int
    private $address_city_name; #string
    private $address_state_name; #string
    private $phone; #string
    private $email; #string
    private $subject; #string
    private $body; #string
    private $date_hour; #DateTime
    private $status; #Status

    public function __construct
    (
        string $name,
        string $body,
        string $email,
        Status $status
    )
    {
        $this->name = $name;
        $this->body = $body;
        $this->email = $email;
        $this->status = $status;
    }

    //Setters

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setAddressPlace(string $place): self
    {
        $this->address_place = $place;
        return $this;
    }

    public function setAddressNumber(int $number): self
    {
        $this->address_number = $number;
        return $this;
    }

    public function setAddressNeighborhood(string $neighborhood): self
    {
        $this->address_neighborhood = $neighborhood;
        return $this;
    }

    public function setAddressComplement(string $complement): self
    {
        $this->address_complement = $complement;
        return $this;
    }

    public function setAddressCep(string $cep): self
    {
        $this->address_cep = $cep;
        return $this;
    }

    public function setAddressCityId(int $id): self
    {
        $this->address_city_id = $id;
        return $this;
    }

    public function setAddressCityName(string $name): self
    {
        $this->address_city_name = $name;
        return $this;
    }

    public function setAddressStateName(string $name): self
    {
        $this->address_state_name = $name;
        return $this;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function setDateHour(DateTime $date_hour): self
    {
        $this->date_hour = $date_hour;
        return $this;
    }

    //Getters

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddressPlace(): string
    {
        return $this->address_place;
    }

    public function getAddressNumber(): string
    {
        return $this->address_number;
    }

    public function getAddressNeighborhood(): string
    {
        return $this->address_neighborhood;
    }

    public function getAddressComplement(): string
    {
        return $this->address_complement;
    }

    public function getAddressCep(): string
    {
        return $this->address_cep;
    }

    public function getAddressCityId(): int
    {
        return $this->address_city_id;
    }

    public function getAddressCityName(): string
    {
        return $this->address_city_name;
    }

    public function getAddressStateName(): string
    {
        return $this->address_state_name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getDateHour(): DateTime
    {
        return $this->date_hour;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
