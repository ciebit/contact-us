<?php
namespace Ciebit\ContactUs\Messages\Storages\Database;

use Ciebit\ContactUs\Messages\Message;
use Ciebit\ContactUs\Messages\Collection;
use Ciebit\ContactUs\Messages\Storages\Storage;

interface Database extends Storage
{
    // public function delete(Message $message): self;
    public function get(): ?Message;
    public function getAll(): Collection;
    // public function save(Message $message): self;
}
