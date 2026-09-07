<?php
//classLocal tem uma classOutra
//classOutra tem várias classLocal

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
        return $this->objLocal::table.'.'.$this->objLocal::primary.' = '.$this->objLocal->getPrimary()." and ($where)";
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

    public function select($queryData = [], $simpleData=false)
    {
        if($this->objLocal->getPrimary()!==null){
            if(! isset($queryData['table'])) {
                $queryData['table'] = 'distinct '.$this->classOutra::table.'.*';
            }
            if(! isset($queryData['select'])) {
                $queryData['select'] = $this->table();
            }
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

            return null;
        }
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

    public function get($where = 'true')
    {
        $tableOutra = $this->classOutra::table;
        $tableLocal = $this->objLocal::table;
        return DB::selectObject($this->classOutra, [ 'where'=> $this->objeto." = ".$this->classOutra::primary . " and $where"]);
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
		$this->updateObjLocal();
    }
    public function set(...$arguments)
    {
        $this->objeto = DBClass::onlyPrimary($arguments[0]);
		$this->updateObjLocal();
    }
    public function remove(...$arguments)
    {
        $this->objeto = null;
		$this->updateObjLocal();
    }
	
	public function updateObjLocal()
	{
		$fk = $this->foreignKey;
		$this->objLocal->$fk = $this->objeto;
	}
	
    public function save()
    {
		if($this->objLocal->getPrimary()!==null){  		
			$this->objLocal->set([$this->foreignKey => $this->objeto]);
			return DB::update($this->objLocal::table, [$this->foreignKey => $this->objeto],$this->objLocal::primary .' = ' . $this->objLocal->getPrimary());
		}
		else{
			return false;
		}
	}

    public function refresh()
    {
        $this->objeto = $this->getId();
    }

}
?>