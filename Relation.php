<?php

interface Relation
{
    public function all();

    public function select($queryData = [], $simpleData = false);
    
    public function where($where = 'true');

    public function first($where = 'true');
    
    public function get($where = 'true');

    public function set(...$arguments);
    
    public function add(...$arguments);
    
    public function remove(...$arguments);
    
    public function save();

    public function refresh();

}
?>