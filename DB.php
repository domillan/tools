<?php
class MySQL
{
    const connection = new mysqli('localhost', 'root', '', 'aulas');
    const clausesSelect = ['select'=>'select ?', 'table'=>' from ?', 'where'=>' where ?', 'groupBy'=>' group by ?', 'having'=>' having ?', 'orderBy'=>' order by ?', 'offset'=>' offset ? rows', 'limit'=>' limit ?']


    public static function selectClass($class, $queryData = [])
    {
        $queryData['table'] = $class::table;
        foreach (self::select($queryData) as $row) {
            $retorno[] = new $class($row);
        }
        return $retorno;
    }

    public static function select($queryData)
    {
        $query = '';
        foreach($this::clausesSelect as $clause=>$sintaxe)
        {
            if(isset($queryData[$key]))
            {
                $query = $query.str_replace('?', $queryData[$key], $sintake)
            }
            elseif($clause=='select')
            {
                $query = $query.str_replace('?', '*', $sintaxe);
            }
        }
        $resultado = mysqli_query(self::connection, $query);
        $retorno = array();
        while ($row = mysqli_fetch_array($resultado)) {
            $retorno[] = $row;
        }
        return $retorno;
    }

    public static function insert($table, $fields)
    {
        $sql = "insert into ".$table.' ('

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
        if(mysqli_query(self::connection, $sql))
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

        if(mysqli_query(self::connection, $sql))
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
        if(mysqli_query(self::connection, substr($sql, 0, -1)."where $where"))
            return true;

        return false;
    }

    public static function __callStatic($name, $arguments) {
    echo "Chamando método estático '$name' "
    . implode(', ', $arguments). "\n";
    }
}
?>