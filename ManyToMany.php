<?php
//classLocal tem várias classOutra
//classOutra tem várias classLocal

class ManyToMany implements Relation
{
    private $classOutra, $objLocal, $tabelaRel, $foreignKeyOutra, $foreignKeyLocal,$pivotDefault, $lista = [], $pivots=[];

    public function __construct($classOutra, $objLocal, $tabelaRel, $foreignKeyOutra, $foreignKeyLocal, $pivotDefault=[])
    {
        $this->classOutra = $classOutra;
        $this->foreignKeyOutra = $foreignKeyOutra;
        $this->foreignKeyLocal = $foreignKeyLocal;
        $this->tabelaRel = $tabelaRel;
        $this->objLocal = $objLocal;
        $this->pivotDefault = $pivotDefault;
        if($this->objLocal->getPrimary()!==null)
            $this->refresh();
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

    public function table()
    {
        return DB::simpleJoin($this->classOutra::table, $this->classOutra::primary, $this->tabelaRel, $this->foreignKeyOutra);
    }

    public function condition($where = 'true')
    {
        return "$this->tabelaRel.$this->foreignKeyLocal = ".$this->objLocal->getPrimary()." and ($where)";
    }

    public function all()
    {
        return $this->where();
    }

    public function where($where = 'true')
    {
        if($this->objLocal->getPrimary()!==null)
            return DB::selectObject($this->classOutra, ['table'=>$this->table(), 'select'=>'distinct '.$this->classOutra::table.'.*','where'=> $this->condition($where)]);
        else
            return [];
    }

    public function select($queryData = [], $simpleData = false)
    {
        if($this->objLocal->getPrimary()!==null){
            if(! isset($queryData['select'])) {
                $queryData['select'] = 'distinct '.$this->classOutra::table.'.*';
            }
            if(! isset($queryData['table'])) {
                $queryData['table'] = $this->table();
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
            return [];
        }
    }

    public function first($where = 'true')
    {
        $lista = [];

        if($this->objLocal->getPrimary()!==null)
            $lista = DB::selectObject($this->classOutra,['table'=>$this->table(), 'select'=>'distinct '.$this->classOutra::table.'.*','where'=>$this->condition($where), 'limit'=>1]);

        return (sizeof($lista))? $lista[0] : null;
    }
    public function getLista()
    {
        return $this->lista;
    }

    public function get($where = 'true')
    {
        $primary = $this->classOutra::primary;
        //if($this->objLocal->getPrimary()!==null)
            return DB::selectObject($this->classOutra, ['where'=> DB::in($this->classOutra::primary, $this->lista) . " and $where"]);
        //else
            return [];
    }
    public function __set ($name, $value)
    {

    }
    public function __get ($name)
    {

    }
    public function __invoke($arguments) {

    }


    public function pivots($where = 'true')
    {
        if($this->objLocal->getPrimary()!==null) {
            $lista = DB::select($this->table(), ['select' => $this->tabelaRel . '.*', 'where' => $this->condition($where)]);
            $listaOrdenada = [];
            foreach($lista as $pivot)
            {
                $pos = $pivot[$this->foreignKeyOutra];
                $listaOrdenada[$pos] = $pivot;
            }
            ksort($listaOrdenada);
            return $listaOrdenada;
        }
        else
            return [];
    }

    public function pivot($obj)
    {
        $id = DBClass::onlyPrimary($obj);

        $where = "$this->tabelaRel.$this->foreignKeyOutra = $id";

        if($this->objLocal->getPrimary()!==null) {
            $lista = DB::select($this->table(), ['select' => $this->tabelaRel . '.*', 'where' => $this->condition($where), 'limit'=>1]);;
            return (sizeof($lista)) ? $lista[0] : null;
        }
        else
            return null;
    }


    public function setWithPivots($pivots)
    {
        $this->set(array_keys($pivots));

        $this->pivots = [];
        foreach($pivots as $id => $pivot){
            $this->pivots[$id] = $pivot;
        }
    }


    public function addWithPivots($pivots)
    {
        $this->add(array_keys($pivots));

        foreach($pivots as $id => $pivot){
            $this->pivots[$id] = $pivot;
        }
    }

    public function addWithPivot($obj, $data)
    {
        $id = DBClass::onlyPrimary($obj);
        $this->add($id);
        $this->pivots[$id] = $data;
    }

	public function getPivotField($obj, $field)
	{
		$pivot = $this->getPivot($obj);
		return  ($pivot!=null)? $pivot[$field] : null;
	}

    public function getPivot($obj)
    {
        $this->fillPivots();
        $id = DBClass::onlyPrimary($obj);
        return (isset($this->pivots[$id])) ? $this->pivots[$id] : null;
    }

    public function getPivots()
    {
        $this->fillPivots();
        return $this->pivots;
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
		$arguments = array_map(['DBClass', 'onlyPrimary'], $arguments);
		foreach($arguments as $item)
		{
			unset($this->pivots[$item]);
		}
        $this->lista = array_diff($this->lista, $arguments);
    }
    public function save()
    {
		if($this->objLocal->getPrimary()!==null){

			$this->fillPivots();
			if(!sizeof($this->lista))
				DB::delete($this->tabelaRel, $this->condition());
			else {
				$listaAntiga = $this->getIds();
				$insert = array_diff($this->lista, $listaAntiga);
				$update = array_intersect($listaAntiga, $this->lista);

				DB::delete($this->tabelaRel, $this->condition(DB::notIn($this->foreignKeyOutra, $this->lista)));
				//echo '<br><br>' . DB::getLastQuery();
				foreach ($insert as $id) {
					DB::insert($this->tabelaRel, $this->pivots[$id]);
					//echo '<br><br>'.DB::getLastQuery();
				}

				foreach ($update as $id) {
					DB::update($this->tabelaRel, $this->pivots[$id], $this->condition("$this->tabelaRel.$this->foreignKeyOutra = $id"));
					//echo '<br><br>'.DB::getLastQuery();
				}
			}
			return true;
		}
		else{
			return false;
		}
    }

    private function fillPivots()
    {
        $localId = $this->objLocal->getPrimary();

        foreach($this->lista as $id)
        {
            if(isset($this->pivots[$id])) {
                if (!isset($this->pivots[$id][$this->foreignKeyOutra]))
                    $this->pivots[$id][$this->foreignKeyOutra] = $id;

                if (!(isset($this->pivots[$id][$this->foreignKeyLocal]) && $this->pivots[$id][$this->foreignKeyLocal] != null))
                    $this->pivots[$id][$this->foreignKeyLocal] = $localId;
            }
            else
            {
                $this->pivots[$id] = [$this->foreignKeyOutra => $id, $this->foreignKeyLocal => $localId];
            }

            foreach($this->pivotDefault as $field=>$default)
            {
                if (!isset($this->pivots[$id][$field]))
                    $this->pivots[$id][$field] = $default;
            }

        }
    }

    public function refresh()
    {
        $this->pivots = $this->pivots();
        $this->lista = array_keys($this->pivots);
    }

}
?>