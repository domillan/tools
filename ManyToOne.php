<?php
//classLocal tem uma classOutra
//classOutra tem várias classLocal
class ManyToOne
{
    private $classOutra, $objLocal, $foreignKey, $objeto;
    public function __construct($classOutra, $objLocal, $foreignKey)
    {
        $this->classOutra = $classOutra;
        $this->foreignKey = $foreignKey;
        $this->objLocal = $objLocal;
        if($this->objLocal->getPrimary()!==false)
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
        return $this->classOutra::table." inner join ".$this->objLocal::table.' on '.$this->objLocal::table.".$this->foreignKey = ".$this->classOutra::table.".".$this->classOutra::primary;
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
        if($this->objLocal->getPrimary()!==false)
            return DB::selectObject($this->classOutra, ['table'=> $this->table(),'select'=>'distinct '.$this->classOutra::table.'.*', 'where'=> $this->condition($where)]);
        else
            return false;
    }

    public function first($where = 'true')
    {
        $lista = [];

        if($this->objLocal->getPrimary()!==false)
            $lista = $this->where($where);

        return (sizeof($lista))? $lista[0] : false;
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


    public function add($argument)
    {
        $this->set(DBClass::onlyPrimary($argument));
    }
    public function set($argument)
    {
        $this->objeto = DBClass::onlyPrimary($argument);
    }
    public function remove()
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