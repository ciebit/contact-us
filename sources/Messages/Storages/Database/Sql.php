<?php
declare(strict_types=1);
namespace Ciebit\ContactUs\Messages\Storages\Database;

use Ciebit\ContactUs\Messages\Collection;
use Ciebit\ContactUs\Messages\Builders\FromArray as MessageBuilder;
use Ciebit\ContactUs\Messages\Addresses\Builders\FromArray as AddressBuilder;
use Ciebit\ContactUs\Messages\Addresses\Address;
use Ciebit\ContactUs\Messages\Message;
use Ciebit\ContactUs\Status;
use Ciebit\ContactUs\Messages\Storages\Storage;
use Ciebit\ContactUs\Messages\Storages\Database\SqlFilters;
use Exception;
use PDO;

use function array_diff;
use function implode;

class Sql extends SqlFilters implements Database
{
    static private $counterKey = 0;
    private $pdo; #PDO
    private $table; #string
    private $messageBuilder; #MessageBuilder
    private $addressBuilder; #AddressBuilder

    public function __construct (
        PDO $pdo,
        MessageBuilder $messageBuilder,
        AddressBuilder $addressBuilder
    ) {
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

    private function buildAddress(array $data): ?Address
    {
        if (
            $data['address_place'] ||
            $data['address_number'] ||
            $data['address_neighborhood'] ||
            $data['address_city_name'] ||
            $data['address_state_name']
        ) {
            return $this->addressBuilder->setData($data)->build();
        }

        return null;
    }

    public function get(): ?Message
    {
        $fields = implode('`,`', $this->getColumns());
        $statement = $this->pdo->prepare("
            SELECT SQL_CALC_FOUND_ROWS
            `{$fields}`
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
        $messageData['address'] = $this->buildAddress($messageData);
        return $this->messageBuilder->setData($messageData)->build();
    }

    public function getAll(): Collection
    {
        $fields = implode('`,`', $this->getColumns());
        $statement = $this->pdo->prepare("
            SELECT SQL_CALC_FOUND_ROWS
            `{$fields}`
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
            $message['address'] = $this->buildAddress($message);
            $this->messageBuilder->setData($message);
            $collection->add(
                $this->messageBuilder->build()
            );
        }
        return $collection;
    }

    private function getColumns(): array
    {
        return [
            'id',
            'address_cep',
            'address_city_id',
            'address_city_name',
            'address_complement',
            'address_neighborhood',
            'address_number',
            'address_place',
            'address_state_name',
            'body',
            'date_hour',
            'email',
            'name',
            'phone',
            'subject',
            'status'
        ];
    }

    public function insert(Message $message): self
    {
        $columns = array_diff($this->getColumns(), ['id']);
        $fields = implode('`,`', $columns);
        $values = implode(', :', $columns);

        $statement = $this->pdo->prepare(
            "INSERT INTO {$this->table} (
                `{$fields}`
            ) VALUES (
                :{$values}
            )"
        );

        if ($message->getAddress() == null) {
            $cep =
            $cityId =
            $cityName =
            $complement =
            $neighborhood =
            $number =
            $place =
            $stateName = null;
        } else {
            $cep = $message->getAddress()->getCep();
            $cityId = $message->getAddress()->getCityId();
            $cityName = $message->getAddress()->getCityName();
            $complement = $message->getAddress()->getComplement();
            $neighborhood = $message->getAddress()->getNeighborhood();
            $number = $message->getAddress()->getNumber();
            $place = $message->getAddress()->getPlace();
            $stateName = $message->getAddress()->getStateName();
        }

        $statement->bindValue(':address_cep', $cep , PDO::PARAM_STR);
        $statement->bindValue(':address_city_id', $cityId , PDO::PARAM_INT);
        $statement->bindValue(':address_city_name', $cityName , PDO::PARAM_STR);
        $statement->bindValue(':address_complement', $complement , PDO::PARAM_STR);
        $statement->bindValue(':address_neighborhood', $neighborhood , PDO::PARAM_STR);
        $statement->bindValue(':address_number', $number , PDO::PARAM_INT);
        $statement->bindValue(':address_place', $place , PDO::PARAM_STR);
        $statement->bindValue(':address_state_name', $stateName , PDO::PARAM_STR);
        $statement->bindValue(':body', $message->getBody(), PDO::PARAM_STR);
        $statement->bindValue(':date_hour', $message->getDateHour()->format("Y-m-d H:i:s"), PDO::PARAM_STR);
        $statement->bindValue(':email', $message->getEmail(), PDO::PARAM_STR);
        $statement->bindValue(':name', $message->getName(), PDO::PARAM_STR);
        $statement->bindValue(':phone', $message->getPhone(), PDO::PARAM_STR);
        $statement->bindValue(':subject', $message->getSubject(), PDO::PARAM_STR);
        $statement->bindValue(':status', $message->getStatus()->getValue(), PDO::PARAM_INT);

        if ($statement->execute() === false) {
            throw new Exception('ciebit.contactus.messages.storages.database.insert_error', 2);
        }

        $message->setId((int) $this->pdo->lastInsertId());

        return $this;
    }

    public function getTotalRows(): int
    {
        return (int) $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
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
