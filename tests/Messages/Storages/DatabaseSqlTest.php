<?php
namespace Ciebit\ContactUs\Tests\Messages\Storages;

use Ciebit\ContactUs\Messages\Collection;
use Ciebit\ContactUs\Status;
use Ciebit\ContactUs\Messages\Message;
use Ciebit\ContactUs\Messages\Builders\FromArray as MessageBuilder;
use Ciebit\ContactUs\Messages\Addresses\Builders\FromArray as AddressBuilder;
use Ciebit\ContactUs\Messages\Storages\Database\Sql as DatabaseSql;
use Ciebit\ContactUs\Tests\Messages\Connection;

class DatabaseSqlTest extends Connection
{
    public function getDatabase(): DatabaseSql
    {
        return new DatabaseSql(
            $this->getPdo(),
            new MessageBuilder,
            new AddressBuilder
        );
    }

    public function testGet(): void
    {
        $database = $this->getDatabase();
        $message = $database->get();
        $this->assertInstanceOf(Message::class, $message);
    }

    public function testGetAll(): void
    {
        $database = $this->getDatabase();
        $messages = $database->getAll();
        $this->assertInstanceOf(Collection::class, $messages);
        $this->assertCount(2, $messages->getIterator());
    }

    public function testGetAllFilterById(): void
    {
        $id = 2;
        $database = $this->getDatabase();
        $database->addFilterById($id+0);
        $messages = $database->getAll();
        $this->assertCount(1, $messages->getIterator());
        $this->assertEquals($id, $messages->getArrayObject()->offsetGet(0)->getId());
    }

    public function testGetAllFilterByStatus(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByStatus(Status::ACTIVE());
        $messages = $database->getAll();
        $this->assertCount(1, $messages->getIterator());
        $this->assertEquals(Status::ACTIVE(), $messages->getArrayObject()->offsetGet(0)->getStatus());
    }

    public function testGetFilterByBody(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByBody('Estou tendo um erro ao abrir a página de contato. Erro 404');
        $message = $database->get();
        $this->assertEquals(1, $message->getId());
        $database = $this->getDatabase();
        $database->addFilterByBody('%Erro 404', 'LIKE');
        $message = $database->get();
        $this->assertEquals(1, $message->getId());
        $database = $this->getDatabase();
        $database->addFilterByBody('A página inicial%', 'LIKE');
        $message = $database->get();
        $this->assertEquals(2, $message->getId());
        $database = $this->getDatabase();
        $database->addFilterByBody('%excessiva%', 'LIKE');
        $message = $database->get();
        $this->assertEquals(2, $message->getId());
    }

    public function testGetFilterById(): void
    {
        $id = 2;
        $database = $this->getDatabase();
        $database->addFilterById($id+0);
        $message = $database->get();
        $this->assertEquals($id, $message->getId());
    }

    public function testGetFilterByStatus(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByStatus(Status::ACTIVE());
        $message = $database->get();
        $this->assertEquals(Status::ACTIVE(), $message->getStatus());
    }
    
    public function testGetFilterByName(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByName('João da Silva Pereira');
        $message = $database->get();
        $this->assertEquals(1, $message->getId());
        $database = $this->getDatabase();
        $database->addFilterByName('%Pereira', 'LIKE');
        $message = $database->get();
        $this->assertEquals(1, $message->getId());
        $database = $this->getDatabase();
        $database->addFilterByName('Fátima%', 'LIKE');
        $message = $database->get();
        $this->assertEquals(2, $message->getId());
        $database = $this->getDatabase();
        $database->addFilterByName('%Maria%', 'LIKE');
        $message = $database->get();
        $this->assertEquals(2, $message->getId());
    }

    public function testGetAllByOrderDesc(): void
    {
        $database = $this->getDatabase();
        $database->setOrderBy('id', 'DESC');
        $message = $database->get();
        $this->assertEquals(2, $message->getId());
    }
}