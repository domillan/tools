<?php
//classLocal tem várias classOutra
//classOutra tem uma classLocal

class OneToMany implements Relation
{
    private $classOutra, $objLocal, $foreignKey, $lista = [];
    public function __construct($classOutra, $objLocal, $foreignKey)
    {
        $this->classOutra = $classOutra;
        $this->foreignKey = $foreignKey;
        $this->objLocal = $objLocal;
        if($this->objLocal->getPrimary()!==null)
        $this->lista = $this->getIds();
    }

    public function getIds()
    {
        $ids = [];
        foreach($this->all() as $obj)
        {
            $ids[]=$obj->getPrimary();
        }
        return $ids;
    }

    public function condition($where = 'true')
    {
        return "$this->foreignKey = ".$this->objLocal->getPrimary()." and ($where)";
    }

    public function all()
    {
        return $this->where();
    }

    public function where($where = 'true')
    {
        if($this->objLocal->getPrimary()!==null)
            return DB::selectObject($this->classOutra, ['where'=> $this->condition($where)]);
        else
            return [];
    }

    public function select($queryData = [], $simpleData = false)
    {
        if($this->objLocal->getPrimary()!==null){
            if(! isset($queryData['where'])) {
                $queryData['where'] = $this->condition('true');
            }
            else {
                $queryData['where'] = $this->condition($queryData['where']);
            }

            if($simpleData)
                return DB::select($this->classOutra::table, $queryData);
            else
                return DB::selectObject($this->classOutra, $queryData);
        }
        else{
            return [];
        }
    }

    public function first($where = 'true')
    {
        $lista = [];

        if($this->objLocal->getPrimary()!==null)
        $lista = DB::selectObject($this->classOutra,['where'=>$this->condition($where), 'limit'=>1]);

        return (sizeof($lista))? $lista[0] : null;
    }
    public function getLista()
    {
        return $this->lista;
    }

    public function get($where = 'true')
    {
        $primary = $this->classOutra::primary;
        return DB::selectObject($this->classOutra, ['where'=> DB::in($this->classOutra::primary, $this->lista) . " and $where"]);
    }
    public function __set ($name, $value)
    {

    }
    public function __get ($name)
    {

    }
    public function __invoke($arguments) {
        
    }

    public function set(...$arguments)
    {
        if(sizeof($arguments)==1 and is_array($arguments[0]))
        {
            $arguments = $arguments[0];
        }

        $this->lista = array_unique(array_map(['DBClass', 'onlyPrimary'], $arguments));
    }


    public function add(...$arguments)
    {
        if(sizeof($arguments)==1 and is_array($arguments[0]))
        {
            $arguments = $arguments[0];
        }
        $this->lista = array_unique(array_merge(array_map(['DBClass', 'onlyPrimary'], $arguments), $this->lista));

    }
    public function remove(...$arguments)
    {
        if(sizeof($arguments)==1 and is_array($arguments[0]))
        {
            $arguments = $arguments[0];
        }
        $this->lista = array_diff($this->lista, array_map(['DBClass', 'onlyPrimary'], $arguments));
    }
    public function save()
    {
		if($this->objLocal->getPrimary()!==null){
            $delete = array_diff($this->getIds(), $this->lista);
            $primary = $this->classOutra::primary;
            DB::update($this->classOutra::table, [$this->foreignKey=>$this->objLocal->getPrimary()], DB::in($primary, $this->lista));
            DB::update($this->classOutra::table, [$this->foreignKey=>null], DB::in($primary, $delete));
			return true;
		}
		else{
			return false;
		}
    }

    public function refresh()
    {
        $this->lista = $this->getIds();
    }

}
?>