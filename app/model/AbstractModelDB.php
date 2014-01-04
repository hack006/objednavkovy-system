<?php
namespace ResSys;
use Nette;

/**
 * Provádí operace nad databázovou tabulkou.
 */
abstract class AbstractModelDB extends Nette\Object
{
    /** @var \Nette\Database\Connection */
    protected $connection;

    function __construct(\Nette\Database\Connection $db)
    {
        $this->connection = $db;
    }

    /**
     * Vrací objekt reprezentující databázovou tabulku.
     * @return Nette\Database\Table\Selection
     */
    protected function getTable()
    {
        // název tabulky odvodíme z názvu třídy
        $class_name_split = explode("\\", get_class($this));
        $table_name = $this::from_camel_case($class_name_split[count($class_name_split) - 1]); // odstranění namespacu
        return $this->connection->context->table($table_name);
    }

    /**
     * Vrací všechny řádky z tabulky.
     * @return Nette\Database\Table\Selection
     */
    public function findAll()
    {
        return $this->getTable();
    }

    /**
     * Vrací řádky podle filtru, např. array('name' => 'John').
     * @return Nette\Database\Table\Selection
     */
    public function findBy(array $by)
    {
        return $this->getTable()->where($by);
    }

    /**
     * Převede CamelCase řetězec na camel_case vhodný pro pojmenování db tabulek
     *
     * Převzato z: http://stackoverflow.com/questions/1993721/how-to-convert-camelcase-to-camel-case
     *
     * @param $input Vstupní řetězec ve formátu CamelCase
     * @return string Převedený řetězec do formátu "camel_case"
     */
    public static function from_camel_case($input) {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        if ($ret[count($ret)-1] == 'model') // remove model from table name
            unset($ret[count($ret)-1]);
        return implode('_', $ret) . 's'; // plural
    }

}