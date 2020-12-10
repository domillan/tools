<?php
spl_autoload_register(function ($name) {
    include_once($name . '.php');
});

class DBClass
{

    protected $data = [],$relations=[];

    public function __construct($arguments=[])
    {
        $this->set($arguments);
    }
    public static function create(...$arguments)
    {
        $class = get_called_class();
        foreach($arguments as $data)
        {
            $obj = new $class($data);
            $obj->insert();
        }
        return true;
    }

    public static function onlyPrimary($value)
    {
        if(is_object($value))
            return $value->getPrimary();
        return $value;
    }

    public static function all()
    {
        return DB::selectObject(get_called_class());
    }

    public static function where($where = 'true')
    {
        return DB::selectObject(get_called_class(), ['where'=>$where]);
    }

    public static function first($where = 'true')
    {
        $lista = (DB::selectObject(get_called_class(), ['where'=>$where, 'limit'=>1]));
        return (sizeof($lista))? $lista[0] : false;
    }
    public static function find($pk)
    {
        $class = get_called_class();
        $lista = (DB::selectObject($class, ['where'=> ($class::primary." = '$pk'")]));
        return (sizeof($lista))? $lista[0] : false;
    }
    
    public static function paginate($pular, $quantidade)
    {
        return DB::select('pessoa', ['limit'=>$quantidade,'offset'=>$pular]);
    }
    
    public function __set ($name, $value)
    {
        if(isset($this::fields[$name]))
            $this->data[$name] = $value;
    }
    public function __get ($name)
    {

        if(in_array($name, $this::fields))
        {
            if(isset($this->data[$name]))
                return $this->data[$name];
            return false;
        }
        else
            return ($this->$name())->get();
    }
    public function __invoke($arguments = null) {
        if($arguments == null)
            $arguments = $this::fields;

        $data = array();
        foreach($arguments as $key){
            if(in_array($key, $this::fields)){
                if(isset($this->data[$key]))
                    $data[$key] = $this->data[$key];
                else
                    $data[$key] = false;
            }

        }
        return $data;
    }
    public function set($arguments)
    {
        foreach($arguments as $key => $value){
            if(in_array($key, $this::fields))
            {
                $this->data[$key] = $value;
            }
        }
    }
    public function save($saveRelations = true)
    {
        $pk = $this->data[$this::primary];
        if($pk!=null && $this::find($pk))
        {
            $r = $this->update();
        }
        else
        {
            $r = $this->insert();
        }
        if($saveRelations)
            $this->saveRelations();

        return $r;

    }

    public function saveRelations()
    {
        foreach($this->relations as $relation)
        {
            $relation->save();
        }
        return true;
    }

    public function insert()
    {
        return DB::insert($this::table, $this());
    }

    public function update()
    {
        $pk = $this->data[$this::primary];
        return DB::update($this::table, $this(),$this::primary." = '$pk'");
    }

    public function delete()
    {
        $pk = $this->data[$this::primary];
        return DB::delete($this::table,$this::primary." = '$pk'");
    }


    public function refresh()
    {
        $pk = $this->data[$this->primary];
        if($self = $this::find($pk))
        {
            $this->set($self());
            return true;
        }
        return false;
    }

    public function oneToMany($classOutra, $fk)
    {
        $function = debug_backtrace()[1]["function"];
        if(!isset($this->relations[$function]))
            $this->relations[$function] = new ManyToOne($classOutra, $this, $fk);
        return $this->relations[$function];
    }

    public function ManyToOne($classOutra, $fk)
    {
        $function = debug_backtrace()[1]["function"];
        if(!isset($this->relations[$function]))
            $this->relations[$function] = new ManyToOne($classOutra, $this, $fk);
        return $this->relations[$function];
    }

    public function ManyToMany($classOutra, $tabelaRel, $fkOutra, $fkLocal)
    {
        $function = debug_backtrace()[1]["function"];
        if(!isset($this->relations[$function]))
            $this->relations[$function] = new ManyToMany($classOutra, $this, $tabelaRel, $fkOutra, $fkLocal);
        return $this->relations[$function];
    }



    public function __call($name, $arguments) {
        throw new Exception("Não existe '$name' na classe ".get_called_class());
    }
    public static function __callStatic($name, $arguments) {
    echo "Chamando método estático '$name' "
    . implode(', ', $arguments). "\n";
    }

    public function getPrimary()
    {
        $primary = $this::primary;
        return $this->__get($primary);
    }

}
?>