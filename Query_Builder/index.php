<?php

class DB
{

    final public const HOSTNAME = 'localhost';
    final public const studentNAME = 'root';
    final public const PASSWORD = '';
    final public const DATABASE = 'CodingStudents';

    public $conn;

    private $table_name;
    private $columns    = [];
    private $values     = [];

    private $condition;
    private $limit;
    private $orderBy;
    private $groupBy;

    private $join;
    private $rightJoin;
    private $leftJoin;

    private $duplicate;
    private $columnCount;

    public $result;

    public function __construct()
    {
        try {
            $this->conn = new PDO("mysql:host=" . self::HOSTNAME . ";dbname=" . self::DATABASE . "", self::studentNAME, self::PASSWORD);
        } catch (PDOException $e) {
            echo "ERROR: " . $e->getMessage();
        }
    }

    public function table(string $table_name): DB
    {
        $this->table_name = $table_name;
        return $this;
    }

    public function select(string ...$column_name): DB
    {
        $this->columns = $column_name;
        return $this;
    }

    public function orderBy(string $order, string ...$column_name): DB
    {
        $this->orderBy = implode(',', $column_name) . " $order";
        return $this;
    }

    public function groupBy(string ...$column_name): DB
    {
        $this->groupBy = implode(',', $column_name);
        return $this;
    }

    public function where(string $column_name, string $opreation, $value): DB
    {
        $condition = $column_name . " " . $opreation . "  '$value'";

        $this->condition === null ?
            $this->condition = $condition :
            $this->condition .= ' AND ' . $condition;

        return $this;
    }

    public function orWhere(string $column, string $opreation, $value): DB
    {
        $condition = $column . " " . $opreation . "  '$value'";
        $this->condition = $this->condition . ' OR ' . $condition;

        return $this;
    }

    public function value(...$values): DB
    {
        $this->values[] = $values;
        return $this;
    }

    public function limit($number, $to = null): DB
    {
        $toRecord = $to === null ? '' : ",$to";
        $this->limit = "$number" . $toRecord;

        return $this;
    }

    public function leftJoin(string $table_name, $FK, $PK): DB
    {
        $this->leftJoin = " LEFT JOIN  $table_name  ON  $FK  =  $PK";
        return $this;
    }

    public function rightJoin(string $table_name, $FK, $PK): DB
    {
        $this->rightJoin = " RIGHT JOIN  $table_name  ON  $FK  =  $PK";
        return $this;
    }

    public function join(string $table_name, $FK, $PK): DB
    {
        $this->join = " JOIN  $table_name  ON  $FK  =  $PK";
        return $this;
    }

    public function get()
    {
        $this->initializStm();

        $sql = "SELECT " . $this->columns .
            " FROM " . $this->table_name
            . $this->join
            . $this->leftJoin
            . $this->rightJoin
            . $this->condition
            . $this->groupBy
            . $this->orderBy
            . $this->limit;

        $stm = $this->conn->prepare($sql);
        echo $sql;
        if ($stm->execute()) {
            $this->result = $stm->fetchAll();
        } else {
            $this->result = "error";
        }

        // $this->resetInput();
    }

    public function update()
    {

        $this->initializStm();

        $sql = "UPDATE " . $this->table_name
            . " SET "
            . $this->values
            . $this->condition;

        $this->conn->prepare($sql)->execute();

        $this->resetInput();
    }

    public function count(string $column = null, bool $duplicate = true)
    {
        $this->columnCount = $column;
        $this->duplicate   = $duplicate;

        $this->initializStm();

        $sql = "SELECT COUNT (" . $column . " )" .
            " FROM " . $this->table_name
            . $this->condition
            . $this->orderBy;

        $stm = $this->conn->prepare($sql);
        if ($stm->execute()) {
            $this->result = $stm->fetchAll();
        }

        $this->resetInput();
    }

    private function initializStm()
    {
        $this->table_name = $this->table_name === null ? ''  : $this->table_name;

        $this->columns    = $this->columns    === [] ? '*' : implode(', ', $this->columns);
        $this->values     = $this->values     === [] ? ''  : implode(', ', $this->values);

        $this->join      = $this->join      === null ? '' : $this->join;
        $this->rightJoin = $this->rightJoin === null ? '' : $this->rightJoin;
        $this->leftJoin  = $this->leftJoin  === null ? '' : $this->leftJoin;

        $this->condition = $this->condition === null ? ''  : " WHERE $this->condition ";
        $this->orderBy   = $this->orderBy   === null ? ''  : " ORDER BY $this->orderBy ";
        $this->limit     = $this->limit     === null ? ''  : " LIMIT $this->limit ";
        $this->groupBy   = $this->groupBy   === null ? ''  : " GROUP BY $this->groupBy ";

        $this->duplicate   = $this->duplicate   === true ? '' : 'DISTINCT';
        $this->columnCount = $this->columnCount === null ? " id " : "$this->duplicate  $this->columnCount";
    }

    private function resetInput()
    {
        $this->table_name = null;
        $this->columns    = [];
        $this->values     = [];

        $this->join      = null;
        $this->rightJoin = null;
        $this->leftJoin  = null;

        $this->condition   = null;
        $this->order       = null;
        $this->orderColumn = null;
        $this->limit       = null;

        $this->columnCount = null;
        $this->duplicate   = null;

        $this->result = [];
    }
}

$DB = new DB();

// Example JOIN
$DB->table('students')
    ->select('students.id', 'students.studentname', 'cities.name')
    ->join('cities', 'students.city_id', 'cities.id')
    ->get();

echo "<hr>";
echo "Example JOIN";
print_r($DB->result);
echo "<hr>";

// Example LEFT JOIN

$DB1 = new DB();
$DB1->table('students')
    ->select('students.id', 'students.studentname', 'cities.name')
    ->leftJoin('cities', 'students.city_id', 'cities.id')
    ->get();

echo "<hr>";
echo "Example JOIN";
print_r($DB1->result);
echo "<hr>";

// Example RIGHT JOIN

$DB2 = new DB();
$DB2->table('students')
    ->select('students.id', 'students.studentname', 'cities.name')
    ->rightJoin('cities', 'students.city_id', 'cities.id')
    ->get();

echo "<hr>";
echo "Example JOIN";
print_r($DB2->result);
echo "<hr>";
