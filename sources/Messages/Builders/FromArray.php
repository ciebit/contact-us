<?php
declare(strict_types=1);
namespace Ciebit\ContactUs\Messages\Builders;

use Ciebit\ContactUs\Messages\Addresses\Builders\FromArray as AddressBuilder;
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
            ! is_string($this->data['name'] ?? false) OR
            ! is_string($this->data['body'] ?? false) OR
            ! is_string($this->data['email'] ?? false)
        ) {
            throw new Exception('ciebit.contactus.messages.builders.invalid', 3);
        }
        
        $status = is_numeric($this->data['status'] ?? null) ? new Status((int) $this->data['status']) : Status::DRAFT();

        $message = new Message(
            $this->data['name'],
            $this->data['body'],
            $this->data['email'],
            $status
        );
        
        $this->data['id'] && $message->setId((int) $this->data['id']);
        $this->data['address'] && $message->setAddress($this->data['address']);
        $this->data['phone'] && $message->setPhone($this->data['phone']);
        $this->data['subject'] && $message->setSubject($this->data['subject']);
        $this->data['date_hour'] && $message->setDateHour(new DateTime($this->data['date_hour']));
        
        return $message;
    }
}
