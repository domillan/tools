<?php
class DBClass
{
    const connection = new mysqli('localhost', 'root', '', 'aulas');

    public function __construct($arguments)
    {
        $this->set($arguments);
    }


    public static function all()
    {
        return DB::selectClass(get_called_class());
    }

    public static function where($where = 'true')
    {
        return DB::selectClass(get_called_class(), ['where'=>$where]);
    }

    public static function first($where = 'true')
    {
        return (DB::selectClass(get_called_class(), ['where'=>$where, 'limit'=>1]))[0];
    }
    public static function find($pk)
    {
        return (DB::selectClass(get_called_class(), ['where'=> ($this::primary." = '$pk'")]))[0];
    }
    public function __set ($name, $value)
    {
        if(isset($this::fields[$name]))
            $this->data[$name] = $value;
    }
    public function __get ($name)
    {
        if(isset($this::fields[$name]))
            return $this->data[$name];
    }
    public function __invoke($arguments = $this::fields) {
        $data = array();
        foreach($arguments as $key){
            if(isset($this::fields[$key]))
                $data[$key] = $this->data[$key];
        }
        return $data;
    }
    public function set($arguments)
    {
        foreach($arguments as $key => $value){
            if(isset($this::fields[$key]))
                $this->data[$key] = $value;
        }
    }
    public function save()
    {
        $pk = $this->data[$this::primary];
        if($pk!=null && $this::find($pk))
        {
            return $this->update();
        }
        else
        {
            return $this->insert();
        }
    }
    public function create(...$arguments)
    {
        $class = get_called_class();
        foreach($arguments as $data)
        {
            $obj = new $class($data);
            $obj->insert();
        }
        return true;
    }
    public function insert()
    {
        return DB::update($this::table, $this->data));
    }

    public function update()
    {
        return DB::insert($this::table, $this->data));
    }

    public function update()
    {
        return DB::insert($this::table, $this->data));
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



    public function __call($name, $arguments) {
    echo "Chamando método '$name' "
    . implode(', ', $arguments). "\n";
    }
    public static function __callStatic($name, $arguments) {
    echo "Chamando método estático '$name' "
    . implode(', ', $arguments). "\n";
    }





}
?>