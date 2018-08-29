<?php
declare(strict_types=1);
namespace Ciebit\ContactUs\Messages\Builders;

use Ciebit\ContactUs\Messages\Message;
use Ciebit\ContactUs\Status;
use DateTime;

class FromArray implements Builder
{
    private $data; #:array

    public function setData(array $data): Builder
    {
        $this->data = $data;
        return $this;
    }

    public function build(): Message
    {
        if (
            ! is_array($this->data) OR
            ! $this->data['name'] OR
            ! $this->data['body'] OR
            ! $this->data['email']
        ) {
            throw new Exception('ciebit.contactus.messages.builders.invalid', 3);
        }
        
        $status = $this->data['status'] ? new Status((int) $this->data['status']) : Status::DRAFT();

        $message = new Message(
            $this->data['name'],
            $this->data['body'],
            $this->data['email'],
            $status
        );
        
        $this->data['id'] && $message->setId((int) $this->data['id']);
        $this->data['address_place'] && $message->setAddressPlace($this->data['address_place']);
        $this->data['address_number'] && $message->setAddressNumber((int) $this->data['address_number']);
        $this->data['address_neighborhood'] && $message->setAddressNeighborhood($this->data['address_neighborhood']);
        $this->data['address_complement'] && $message->setAddressComplement($this->data['address_complement']);
        $this->data['address_cep'] && $message->setAddressCep($this->data['address_cep']);
        $this->data['address_city_id'] && $message->setAddressCityId((int) $this->data['address_city_id']);
        $this->data['address_city_name'] && $message->setAddressCityName($this->data['address_city_name']);
        $this->data['address_state_name'] && $message->setAddressStateName($this->data['address_state_name']);
        $this->data['phone'] && $message->setPhone($this->data['phone']);
        $this->data['subject'] && $message->setSubject($this->data['subject']);
        $this->data['date_hour'] && $message->setDateHour(new DateTime($this->data['date_hour']));
        
        return $message;
    }
}
