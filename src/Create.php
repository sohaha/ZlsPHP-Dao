<?php

namespace Zls\Dao;

use Z;

class Create
{
    public function bean($columns)
    {
        $fields = [];
        $fieldTemplate = "    //{comment}\n    protected \${column0};";
        foreach ($columns as $value) {
            $column = str_replace(' ', '', ucwords(str_replace('_', ' ', $value['name'])));
            $column0 = $value['name'];
            /*$column1 = lcfirst($column);*/
            $fields[] = str_replace(
                ['{column0}', '{comment}'],
                [$column0, $value['comment']],
                $fieldTemplate
            );
        }
        $code = "\n{fields}\n\n";
        $code = str_replace(
            ['{fields}'],
            [implode("\n\n", $fields)],
            $code
        );

        return $code;
    }

    public function dao($columns, $table, $isAfresh = false)
    {
        $primaryKey = '';
        $_columns = [];
        foreach ($columns as $value) {
            if ($value['primary']) {
                $primaryKey = $value['name'];
            }
            $_columns[] = '\'' . $value['name'] . "',//" . $value['comment'] . PHP_EOL . '            ';
        }
        $columnsString = '[' . PHP_EOL . '            ' . implode('', $_columns) . ']';
        $code = $isAfresh ? '' : 'use \Zls\Dao\DaoUtil;';
        $code = "public function getColumns() {
        return {columns};
    }

    public function getPrimaryKey() {
        return '{primaryKey}';
    }

    public function getTable() {
        return '{table}';
    }
";
        if (!$isAfresh) {
            $code .= "
    public function getRules() {
        \$rules = [];

        // your rules ...
        // \$rules['fieldName']   = [
        //     'functions[strip_tags,trim]' => '',
        //     'required'                   => 'can not be empty',
        //     'max_len[200]'               => 'up to 200 characters',
        // ];

        return \$rules;
    }

    public function getHideColumns() {
        return [];
    }

    // public static function findBefore(\Zls_Database_ActiveRecord \$db, \$method) {

    // }

    // public static function deleteBefore(\Zls_Database_ActiveRecord \$db, \$wheres = []) {

    // }

    // public static function insertBefore(\Zls_Database_ActiveRecord \$db, \$method, &\$data) {

    // }

    // public static function updateBefore(\Zls_Database_ActiveRecord \$db, \$method, &\$data) {

    // }
";
        }
        if (false !== strpos(Z::getOpt(1), 'bean')) {
            $code .= "
    public function getBean() {
        return parent::getBean();
    }";
        }
        $code = str_replace(['{columns}', '{primaryKey}', '{table}'], [$columnsString, $primaryKey, $table], $code);
        $methods = ['getColumns', 'getPrimaryKey', 'getTable'];

        return [$code, $primaryKey ? '' : 'Did not find the primary key, please fill in manually.', $methods];
    }
}
