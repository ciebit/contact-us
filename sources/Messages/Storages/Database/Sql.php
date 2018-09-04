<?php
declare(strict_types=1);
namespace Ciebit\ContactUs\Messages\Storages\Database;

use Ciebit\ContactUs\Messages\Collection;
use Ciebit\ContactUs\Messages\Builders\FromArray as MessageBuilder;
use Ciebit\ContactUs\Messages\Addresses\Builders\FromArray as AddressBuilder;
use Ciebit\ContactUs\Messages\Message;
use Ciebit\ContactUs\Status;
use Ciebit\ContactUs\Messages\Storages\Storage;
use Ciebit\ContactUs\Messages\Storages\Database\SqlFilters;
use Exception;
use PDO;

class Sql extends SqlFilters implements Database
{
    static private $counterKey = 0;
    private $pdo; #PDO
    private $table; #string
    private $messageBuilder; #MessageBuilder
    private $addressBuilder; #AddressBuilder

    public function __construct
    (
        PDO $pdo,
        MessageBuilder $messageBuilder,
        AddressBuilder $addressBuilder
    )
    {
        $this->pdo = $pdo;
        $this->messageBuilder = $messageBuilder;
        $this->addressBuilder = $addressBuilder;
        $this->table = 'cb_contactus_messages';
    }

    public function addFilterById(int $id, string $operator = '='): Storage
    {
        $key = 'id';
        $sql = "`message`.`id` $operator :{$key}";
        $this->addfilter($key, $sql, PDO::PARAM_INT, $id);
        return $this;
    }

    public function addFilterByIds(string $operator, int ...$ids): Storage
    {
        $keyPrefix = 'id';
        $keys = [];
        $operator = $operator == '!=' ? 'NOT IN' : 'IN';
        foreach ($ids as $id) {
            $key = $keyPrefix . self::$counterKey++;
            $this->addBind($key, PDO::PARAM_INT, $id);
            $keys[] = $key;
        }
        $keysStr = implode(', :', $keys);
        $this->addSqlFilter("`id` {$operator} (:{$keysStr})");
        return $this;
    }

    public function addFilterByStatus(Status $status, string $operator = '='): Storage
    {
        $key = 'status';
        $sql = "`message`.`status` {$operator} :{$key}";
        $this->addFilter($key, $sql, PDO::PARAM_INT, $status->getValue());
        return $this;
    }

    public function addFilterByName(string $name, string $operator = '='): Storage
    {
        $key = 'name';
        $sql = "`message`.`name` {$operator} :{$key}";
        $this->addFilter($key, $sql, PDO::PARAM_STR, $name);
        return $this;
    }

    public function addFilterByBody(string $body, string $operator = '='): Storage
    {
        $key = 'body';
        $sql = "`message`.`body` {$operator} :{$key}";
        $this->addFilter($key, $sql, PDO::PARAM_STR, $body);
        return $this;
    }

    public function get(): ?Message
    {
        $fields = $this->getFields('message');
        $statement = $this->pdo->prepare("
            SELECT SQL_CALC_FOUND_ROWS
            {$fields}
            FROM {$this->table} as `message`
            WHERE {$this->generateSqlFilters()}
            {$this->generateOrder()}
            LIMIT 1
        ");
        $this->bind($statement);
        if ($statement->execute() === false) {
            throw new Exception('ciebit.contactus.messages.storages.database.get_error', 2);
        }
        $messageData = $statement->fetch(PDO::FETCH_ASSOC);
        if ($messageData == false) {
            return null;
        }
        $messageData['address'] = $this->addressBuilder->setData($messageData)->build();
        return $this->messageBuilder->setData($messageData)->build();
    }

    public function getAll(): Collection
    {
        $fields = $this->getFields('message');
        $statement = $this->pdo->prepare("
            SELECT SQL_CALC_FOUND_ROWS
            {$fields}
            FROM {$this->table} as `message`
            WHERE {$this->generateSqlFilters()}
            {$this->generateOrder()}
            {$this->generateSqlLimit()}
        ");
        $this->bind($statement);
        if ($statement->execute() === false) {
            throw new Exception('ciebit.contactus.messages.storages.database.get_error', 2);
        }

        $collection = new Collection;
        while ($message = $statement->fetch(PDO::FETCH_ASSOC)) {
            $message['address'] = $this->addressBuilder->setData($message)->build();
            $this->messageBuilder->setData($message);
            $collection->add(
                $this->messageBuilder->build()
            );
        }
        return $collection;
    }

    public function insert(Message $message): self
    {
        $fields = $this->getFields(null, true);
        $binds = $this->getBinds();

        $statement = $this->pdo->prepare("
            INSERT INTO {$this->table}
            ({$fields})
            VALUES ({$binds})
        ");

        $statement->bindValue(':name', $message->getName(), PDO::PARAM_STR);
        $statement->bindValue(':address_place', $message->getAddress()->getPlace(), PDO::PARAM_STR);
        $statement->bindValue(':address_number', $message->getAddress()->getNumber(), PDO::PARAM_INT);
        $statement->bindValue(':address_neighborhood', $message->getAddress()->getNeighborhood(), PDO::PARAM_STR);
        $statement->bindValue(':address_complement', $message->getAddress()->getComplement(), PDO::PARAM_STR);
        $statement->bindValue(':address_cep', $message->getAddress()->getCep(), PDO::PARAM_STR);
        $statement->bindValue(':address_city_id', $message->getAddress()->getCityId(), PDO::PARAM_INT);
        $statement->bindValue(':address_city_name', $message->getAddress()->getCityName(), PDO::PARAM_STR);
        $statement->bindValue(':address_state_name', $message->getAddress()->getStateName(), PDO::PARAM_STR);
        $statement->bindValue(':phone', $message->getPhone(), PDO::PARAM_STR);
        $statement->bindValue(':email', $message->getEmail(), PDO::PARAM_STR);
        $statement->bindValue(':subject', $message->getSubject(), PDO::PARAM_STR);
        $statement->bindValue(':body', $message->getBody(), PDO::PARAM_STR);
        $statement->bindValue(':date_hour', $message->getDateHour()->format("Y-m-d H:i:s"), PDO::PARAM_STR);
        $statement->bindValue(':status', $message->getStatus()->getValue(), PDO::PARAM_INT);
        
        if ($statement->execute() === false) {
            throw new Exception('ciebit.contactus.messages.storages.database.insert_error', 2);
        }

        return $this;
    }

    private function getColumns(): array
    {
        return [
            'id',
            'name',
            'address_place',
            'address_number',
            'address_neighborhood',
            'address_complement',
            'address_cep',
            'address_city_id',
            'address_city_name',
            'address_state_name',
            'phone',
            'email',
            'subject',
            'body',
            'date_hour',
            'status'
        ];
    }

    private function getFields(string $aliasTable=null, bool $excludeId=false): string
    {
        $columns = $this->getColumns();
        if ($excludeId) {
            $columns = array_filter($columns, function($column) {
                return $column != 'id';
            });
        }
        $alias = $aliasTable ? $aliasTable.'.' : '';
        return $alias . implode(", {$alias}", $columns);
    }

    private function getBinds(): string
    {
        $columns = $this->getColumns();
        $columns = array_filter($columns, function($column) {
            return $column != 'id';
        });
        return ':'.implode(", :", $columns);
    }

    public function getTotalRows(): int
    {
        return $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
    }

    public function setStartingLine(int $lineInit): Storage
    {
        parent::setOffset($lineInit);
        return $this;
    }

    public function setTable(string $name): self
    {
        $this->table = $name;
        return $this;
    }

    public function setTotalLines(int $total): Storage
    {
        parent::setLimit($total);
        return $this;
    }
}
