<?php
//DEPRECATED
class DBMySQLi
{
    private static $connection = null;
    private static $lastQuery = '""', $debug=false;
    const clausesSelect = ['select'=>'select ?', 'table'=>' from ?', 'where'=>' where ?', 'groupBy'=>' group by ?', 'having'=>' having ?', 'orderBy'=>' order by ?', 'limit'=>' limit ?', 'offset'=>' offset ?'];


    public static function debugOn()
    {
        self::$debug = true;
    }
    public static function debugOff()
    {
        self::$debug = false;
    }

    public static function setLastQuery($query)
    {
        self::$lastQuery = $query;
        if(self::$debug)
            echo self::getLastQuery();
    }

    public static function getLastQuery()
    {
        return '"'.self::$lastQuery.'"<br>';
    }

	public static function setConnection($endereco = 'localhost', $usuario = 'root', $senha='', $database='aulas')
	{
		self::$connection = new mysqli($endereco, $usuario, $senha, $database);
	}
	
	private static function getConnection()
	{
		if(!self::$connection)
		    self::setConnection();

        return self::$connection;
			//throw new Exception('Banco de dados não definido, execute a função DB::setConnection().');
	}
    public static function selectObject($class, $queryData = [])
    {
        $retorno = [];
        foreach (self::select($class::table, $queryData) as $row) {
            $retorno[] = new $class($row);
        }
        return $retorno;
    }

    public static function select($table,$queryData=[])
    {
        if(!isset($queryData['table']))
            $queryData['table'] = $table;
        $query = '';
        foreach(self::clausesSelect as $clause=>$sintaxe)
        {
            if(isset($queryData[$clause]))
            {
                $query = $query.str_replace('?', $queryData[$clause], $sintaxe);
            }
            elseif($clause=='select')
            {
                $query = $query.str_replace('?', '*', $sintaxe);
            }
        }
        $resultado = mysqli_query(self::getConnection(), $query);
        self::setLastQuery($query);
        $retorno = array();
        //echo $query.'<br>';
        while ($row = @mysqli_fetch_array($resultado)) {
            $retorno[] = $row;
        }
        return $retorno;
    }

    public static function insert($table, $fields)
    {
        $sql = "insert into ".$table.' (';

        foreach($fields as $field=>$value)
        {
            $sql = $sql."$field,";
        }
        $sql = substr($sql, 0, -1).') values (';
        foreach($fields as $value)
        {
            if($value != null)
                $sql = $sql."'$value',";
            else
                $sql = $sql."null,";
        }
        $sql = substr($sql, 0, -1).')';
        self::setLastQuery($sql);
        if(mysqli_query(self::getConnection(), $sql))
            return true;

        return false;
    }

    public static function delete($table, $where=null)
    {
        $sql = 'delete from '.$table;
        if($where!=null)
        {
            $sql = $sql." where $where";
        }
        self::setLastQuery($sql);
        if(mysqli_query(self::getConnection(), $sql))
            return true;

        return false;
    }

    public static function update($table, $fields, $where=null)
    {
        $sql = "update ".$table." set ";
        foreach($fields as $field => $value)
        {
            if($value != null)
            {
                $sql = $sql."$field = '$value',";
            }
            else
                $sql = $sql."$field = null,";
        }
        $sql = substr($sql, 0, -1)." where $where";
        self::setLastQuery($sql);
        if(mysqli_query(self::getConnection(), $sql))
            return true;

        return false;
    }

    public static function __callStatic($name, $arguments) {
    echo "Chamando método estático '$name' "
    . implode(', ', $arguments). "\n";
    }
}
?>