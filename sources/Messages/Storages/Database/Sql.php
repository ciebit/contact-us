<?php
declare(strict_types=1);
namespace Ciebit\ContactUs\Messages\Storages\Database;

use Ciebit\ContactUs\Messages\Collection;
use Ciebit\ContactUs\Messages\Builders\FromArray as Builder;
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

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
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
        $statement = $this->pdo->prepare("
            SELECT SQL_CALC_FOUND_ROWS
            {$this->getFields()}
            FROM {$this->table} as `message`
            WHERE {$this->generateSqlFilters()}
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
        return (new Builder)->setData($messageData)->build();
    }

    public function getAll(): Collection
    {
        $statement = $this->pdo->prepare("
            SELECT SQL_CALC_FOUND_ROWS
            {$this->getFields()}
            FROM {$this->table} as `message`
            WHERE {$this->generateSqlFilters()}
            {$this->generateSqlLimit()}
        ");
        $this->bind($statement);
        if ($statement->execute() === false) {
            throw new Exception('ciebit.contactus.messages.storages.database.get_error', 2);
        }
        $collection = new Collection;
        $builder = new Builder;
        while ($message = $statement->fetch(PDO::FETCH_ASSOC)) {
            $builder->setData($message);
            $collection->add(
                $builder->build()
            );
        }
        return $collection;
    }

    private function getFields(): string
    {
        return '
            `message`.`id`,
            `message`.`name`,
            `message`.`address_place`,
            `message`.`address_number`,
            `message`.`address_neighborhood`,
            `message`.`address_complement`,
            `message`.`address_cep`,
            `message`.`address_city_id`,
            `message`.`address_city_name`,
            `message`.`address_state_name`,
            `message`.`phone`,
            `message`.`email`,
            `message`.`subject`,
            `message`.`body`,
            `message`.`date_hour`,
            `message`.`status`
        ';
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

    public function orderBy(string $column, string $order = "ASC"): self
    {
        $this->orderBy[] = [$column, $order];
        return $this;
    }
}
