<?php
namespace Ciebit\ContactUs\Messages\Storages;

use Ciebit\ContactUs\Messages\Collection;
use Ciebit\ContactUs\Messages\Message;
use Ciebit\ContactUs\Status;

interface Storage
{
    public function addFilterById(int $id, string $operator = '='): self;

    public function addFilterByIds(string $operator, int ...$id): self;

    public function addFilterByStatus(Status $status, string $operator = '='): self;

    public function get(): ?Message;

    public function getAll(): Collection;

    public function setStartingLine(int $lineInit): self;

    public function setTotalLines(int $total): self;
}
