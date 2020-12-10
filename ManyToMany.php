<?php
//classLocal tem várias classOutra
//classOutra tem várias classLocal
include_once('Relation.php');
class ManyToMany implements Relation
{
    private $classOutra, $objLocal, $tabelaRel, $foreignKeyOutra, $foreignKeyLocal, $lista = [];
    public function __construct($classOutra, $objLocal, $tabelaRel, $foreignKeyOutra, $foreignKeyLocal)
    {
        $this->classOutra = $classOutra;
        $this->foreignKeyOutra = $foreignKeyOutra;
        $this->foreignKeyLocal = $foreignKeyLocal;
        $this->tabelaRel = $tabelaRel;
        $this->objLocal = $objLocal;
        if($this->objLocal->getPrimary()!==false)
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

    public function table()
    {
        $joins = $this->classOutra::table." inner join $this->tabelaRel on $this->tabelaRel.$this->foreignKeyOutra = ".$this->classOutra::table.'.'.$this->classOutra::primary;
        //. ' inner join '.$this->objLocal::table.' on '.$this->objLocal::table.'.'.$this->objLocal::primary. " = $this->tabelaRel.$this->foreignKeyLocal";

        return $joins;
    }

    public function condition($where = 'true')
    {
        return "$this->tabelaRel.$this->foreignKeyLocal = ".$this->objLocal->getPrimary()." and $where";
    }

    public function all()
    {
        return $this->where();
    }

    public function where($where = 'true')
    {
        if($this->objLocal->getPrimary()!==false)
            return DB::selectObject($this->classOutra, ['table'=>$this->table(), 'select'=>'distinct '.$this->classOutra::table.'.*','where'=> $this->condition($where)]);
        else
            return [];
    }

    public function first($where = 'true')
    {
        $lista = [];

        if($this->objLocal->getPrimary()!==false)
            $lista = DB::selectObject($this->classOutra,['table'=>$this->table(), 'select'=>'distinct '.$this->classOutra::table.'.*','where'=>$this->condition($where), 'limit'=>1]);

        return (sizeof($lista))? $lista[0] : false;
    }
    public function getLista()
    {
        return $this->lista;
    }

    public function get()
    {
        $primary = $this->classOutra::primary;
        if($this->objLocal->getPrimary()!==false)
            return DB::selectObject($this->classOutra, ['where'=> $this->classOutra::primary.' in ('.implode(',',$this->lista).')']);
        else
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
        if(!sizeof($this->lista))
            DB::delete($this->tabelaRel, $this->condition());
        else {
            $listaAntiga = $this->getIds();
            $insert = array_diff($this->lista, $listaAntiga);
            DB::delete($this->tabelaRel, $this->condition("$this->foreignKeyOutra not in (" . implode($this->lista, ',') . ')'));
            //echo '<br><br>' . DB::getLastQuery();
            foreach ($insert as $id) {
                DB::insert($this->tabelaRel, [$this->foreignKeyLocal => $this->objLocal->getPrimary(), $this->foreignKeyOutra => $id]);
                //echo '<br><br>'.DB::getLastQuery();
            }
        }
    }

    public function refresh()
    {
        $this->lista = $this->getIds();
    }

}
?>