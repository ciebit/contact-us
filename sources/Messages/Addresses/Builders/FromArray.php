<?php
declare(strict_types=1);
namespace Ciebit\ContactUs\Messages\Addresses\Builders;

use Ciebit\ContactUs\Messages\Addresses\Address;
use Exception;

class FromArray implements Builder
{
    private $data; #:Array

    public function setData(array $data): Builder
    {
        $this->data = $data;
        return $this;
    }

    public function build(): Address
    {
        if (
            ! is_array($this->data) OR
            ! is_string($this->data['place'] ?? false) OR
            ! is_numeric($this->data['number'] ?? false) OR
            ! is_string($this->data['neighborhood'] ?? false) OR
            ! is_string($this->data['city_name'] ?? false) OR
            ! is_string($this->data['state_name'] ?? false)
        ) {
            throw new Exception('ciebit.contactus.messages.addresses.builders.invalid', 3);
        }

        $address = new Address(
            $this->data['place'],
            (int) $this->data['number'],
            $this->data['neighborhood'],
            $this->data['city_name'],
            $this->data['state_name']
        );
        
        $this->data['complement'] && $address->setComplement($this->data['complement']);
        $this->data['cep'] && $address->setCep($this->data['cep']);
        $this->data['city_id'] && $address->setCityId((int) $this->data['city_id']);
        
        return $address;
    }
}
