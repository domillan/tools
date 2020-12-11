<?php
//classLocal tem uma classOutra
//classOutra tem várias classLocal
include_once('Relation.php');
class ManyToOne implements Relation
{
    private $classOutra, $objLocal, $foreignKey, $objeto;
    public function __construct($classOutra, $objLocal, $foreignKey)
    {
        $this->classOutra = $classOutra;
        $this->foreignKey = $foreignKey;
        $this->objLocal = $objLocal;
        if($this->objLocal->getPrimary()!==null)
        $this->objeto = $this->getId();
    }

    public function getId()
    {
        if($obj = $this->first())
        return $obj->getPrimary();
        else
            return null;
    }

    public function table()
    {
        return DB::simpleJoin($this->classOutra::table, $this->classOutra::primary, $this->objLocal::table, $this->foreignKey);
     }

    public function condition($where = 'true')
    {
        return $this->objLocal::table.'.'.$this->objLocal::primary.' = '.$this->objLocal->getPrimary()." and $where";
    }

    public function all()
    {
        return $this->where();
    }

    public function where($where = 'true')
    {
        if($this->objLocal->getPrimary()!==null)
            return DB::selectObject($this->classOutra, ['table'=> $this->table(),'select'=>'distinct '.$this->classOutra::table.'.*', 'where'=> $this->condition($where)]);
        else
            return null;
    }

    public function first($where = 'true')
    {
        $lista = [];

        if($this->objLocal->getPrimary()!==null)
            $lista = $this->where($where);

        return (sizeof($lista))? $lista[0] : null;
    }
    public function getIdObjeto()
    {
        return $this->objeto;
    }

    public function get()
    {
        $tableOutra = $this->classOutra::table;
        $tableLocal = $this->objLocal::table;
        return DB::selectObject($this->classOutra, [ 'where'=> $this->objeto." = ".$this->classOutra::primary]);
    }
    public function __set ($name, $value)
    {

    }
    public function __get ($name)
    {

    }
    public function __invoke($arguments) {

    }


    public function add(...$arguments)
    {
        $this->set(DBClass::onlyPrimary($arguments[0]));
    }
    public function set(...$arguments)
    {
        $this->objeto = DBClass::onlyPrimary($arguments[0]);
    }
    public function remove(...$arguments)
    {
        $this->objeto = null;
    }
    public function save()
    {
        $this->objLocal->set([$this->foreignKey => $this->objeto]);
        return DB::update($this->objLocal::table, [$this->foreignKey => $this->objeto],$this->objLocal::primary .' = ' . $this->objLocal->getPrimary());
    }

    public function refresh()
    {
        $this->objeto = $this->getId();
    }

}
?>