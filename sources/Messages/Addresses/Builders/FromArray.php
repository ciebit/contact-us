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
            ! is_string($this->data['address_place'] ?? false) OR
            ! is_numeric($this->data['address_number'] ?? false) OR
            ! is_string($this->data['address_neighborhood'] ?? false) OR
            ! is_string($this->data['address_city_name'] ?? false) OR
            ! is_string($this->data['address_state_name'] ?? false)
        ) {
            throw new Exception('ciebit.contactus.messages.addresses.builders.invalid', 3);
        }

        $address = new Address(
            $this->data['address_place'],
            (int) $this->data['address_number'],
            $this->data['address_neighborhood'],
            $this->data['address_city_name'],
            $this->data['address_state_name']
        );
        
        $this->data['address_complement'] && $address->setComplement($this->data['address_complement']);
        $this->data['address_cep'] && $address->setCep($this->data['address_cep']);
        $this->data['address_city_id'] && $address->setCityId((int) $this->data['address_city_id']);
        
        return $address;
    }
}
