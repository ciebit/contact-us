<?php
namespace Ciebit\ContactUs\Messages;

use DateTime;
use Ciebit\ContactUs\Status;
use Ciebit\ContactUs\Messages\Addresses\Address;

class Message
{
    private $id; #int
    private $name; #string
    private $phone; #string
    private $email; #string
    private $subject; #string
    private $body; #string
    private $address; #Address
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
        $this->id = 0;
        $this->subject = '';
        $this->phone = '';
        $this->address = new Address('', 0, '', '', '');
        $this->date_hour = new DateTime;
    }

    //Setters

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setAddress(Address $address): self
    {
        $this->address = $address;
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

    public function getAddress(): Address
    {
        return $this->address;
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
