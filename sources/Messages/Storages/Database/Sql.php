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
        $fields = implode(", ",$this->getFields('message'));
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
        $fields = implode(", ",$this->getFields('message'));
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
        $binds_array = array_keys($this->getBinds());
        $fields = implode(", ", $binds_array);
        $binds = implode(", ", $this->getBinds());

        $statement = $this->pdo->prepare("
            INSERT INTO {$this->table}
            ({$fields})
            VALUES ({$binds})
        ");

        $this->addBind($binds_array[0], PDO::PARAM_STR, $message->getName());
        $this->addBind($binds_array[1], PDO::PARAM_STR, $message->getAddress()->getPlace());
        $this->addBind($binds_array[2], PDO::PARAM_INT, $message->getAddress()->getNumber());
        $this->addBind($binds_array[3], PDO::PARAM_STR, $message->getAddress()->getNeighborhood());
        $this->addBind($binds_array[4], PDO::PARAM_STR, $message->getAddress()->getComplement());
        $this->addBind($binds_array[5], PDO::PARAM_STR, $message->getAddress()->getCep());
        $this->addBind($binds_array[6], PDO::PARAM_INT, $message->getAddress()->getCityId());
        $this->addBind($binds_array[7], PDO::PARAM_STR, $message->getAddress()->getCityName());
        $this->addBind($binds_array[8], PDO::PARAM_STR, $message->getAddress()->getStateName());
        $this->addBind($binds_array[9], PDO::PARAM_STR, $message->getPhone());
        $this->addBind($binds_array[10], PDO::PARAM_STR, $message->getEmail());
        $this->addBind($binds_array[11], PDO::PARAM_STR, $message->getSubject());
        $this->addBind($binds_array[12], PDO::PARAM_STR, $message->getBody());
        $this->addBind($binds_array[13], PDO::PARAM_STR, $message->getDateHour()->format("Y-m-d H:i:s"));
        $this->addBind($binds_array[14], PDO::PARAM_INT, $message->getStatus()->getValue());

        $this->bind($statement);
        
        if ($statement->execute() === false) {
            throw new Exception('ciebit.contactus.messages.storages.database.insert_error', 2);
        }

        return $this;
    }

    private function getBinds(): array
    {
        return [
            'name' =>':name',
            'address_place' =>':address_place',
            'address_number' =>':address_number',
            'address_neighborhood' =>':address_neighborhood',
            'address_complement' =>':address_complement',
            'address_cep' =>':address_cep',
            'address_city_id' =>':address_city_id',
            'address_city_name' =>':address_city_name',
            'address_state_name' =>':address_state_name',
            'phone' =>':phone',
            'email' =>':email',
            'subject' =>':subject',
            'body' =>':body',
            'date_hour' =>':date_hour',
            'status' =>':status'
        ];
    }

    private function getFields(string $alias=null): array
    {
        return [
            $alias ? "`{$alias}`".'.`id`' : ''.'`id`',
            $alias ? "`{$alias}`".'.`name`' : ''.'`name`',
            $alias ? "`{$alias}`".'.`address_place`' : ''.'`address_place`',
            $alias ? "`{$alias}`".'.`address_number`' : ''.'`address_number`',
            $alias ? "`{$alias}`".'.`address_neighborhood`' : ''.'`address_neighborhood`',
            $alias ? "`{$alias}`".'.`address_complement`' : ''.'`address_complement`',
            $alias ? "`{$alias}`".'.`address_cep`' : ''.'`address_cep`',
            $alias ? "`{$alias}`".'.`address_city_id`' : ''.'`address_city_id`',
            $alias ? "`{$alias}`".'.`address_city_name`' : ''.'`address_city_name`',
            $alias ? "`{$alias}`".'.`address_state_name`' : ''.'`address_state_name`',
            $alias ? "`{$alias}`".'.`phone`' : ''.'`phone`',
            $alias ? "`{$alias}`".'.`email`' : ''.'`email`',
            $alias ? "`{$alias}`".'.`subject`' : ''.'`subject`',
            $alias ? "`{$alias}`".'.`body`' : ''.'`body`',
            $alias ? "`{$alias}`".'.`date_hour`' : ''.'`date_hour`',
            $alias ? "`{$alias}`".'.`status`' : ''.'`status`'
        ];
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
