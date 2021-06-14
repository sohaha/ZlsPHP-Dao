<?php

namespace Zls\Dao;

use Z;

trait DaoUtil
{
    protected static $openDataTime = true;
    protected static $dataTimeFormat = [
        'createKey' => 'create_time',
        'updateKey' => 'update_time',
        'value' => '',
    ];

    final protected static function setDataTime($method, &$data)
    {
        if (!static::$openDataTime) {
            return;
        }
        $dao = Z::factory(__CLASS__, true);
        $columns = $dao->getColumns();
        $time = Z::arrayGet(static::$dataTimeFormat, 'value') ?: date('Y-m-d H:i:s');
        $updateKey = Z::arrayGet(static::$dataTimeFormat, 'updateKey');
        $hasUpdateKey = in_array($updateKey, $columns, true);
        $createKey = Z::arrayGet(static::$dataTimeFormat, 'createKey');
        $hasCreateKey = in_array($createKey, $columns, true);
        if (in_array($method, ['insert', 'update'], true)) {
            $hasUpdateKey && $data[$updateKey] = $time;
            if ($method === 'insert' && $hasCreateKey) {
                $data[$createKey] = $time;
            }
        } elseif (in_array($method, ['insertBatch', 'updateBatch'], true)) {
            foreach ($data as &$_data) {
                $hasUpdateKey && $_data[$updateKey] = $time;
                if ($method === 'insertBatch' && $hasCreateKey) {
                    $_data[$createKey] = $time;
                }
            }
        }
    }

    final protected static function setDataTimeFormat($insertKey, $updateKey, $value = null)
    {
        static::$dataTimeFormat = [
            'createKey' => $insertKey,
            'updateKey' => $updateKey,
            'value' => $value,
        ];
    }

    public static function insertBefore(\Zls_Database_ActiveRecord $db, $method, &$data)
    {
        static::setDataTime($method, $data);
    }

    public static function updateBefore(\Zls_Database_ActiveRecord $db, $method, &$data)
    {
        static::setDataTime($method, $data);
    }

    protected static $openSoftDelete = true;
    protected static $softDeleteFormat = [
        'key' => 'delete',
        'value' => 1,
    ];

    protected static function softFind(\Zls_Database_ActiveRecord $db, $method)
    {
        $dao = Z::factory(__CLASS__, true);
        $columns = $dao->getColumns();
        if (in_array(Z::arrayGet(self::$softDeleteFormat, 'key'), $columns)) {
            $db->where([static::$softDeleteFormat['key'] . ' !=' => static::$softDeleteFormat['value']]);
        }
    }

    protected static function softDelete(\Zls_Database_ActiveRecord $db, $wheres)
    {
        $dao = Z::factory(__CLASS__, true);
        $columns = $dao->getColumns();
        if (in_array(Z::arrayGet(self::$softDeleteFormat, 'key'), $columns)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $table = $dao->getTable();
            $data = [static::$softDeleteFormat['key'] => time()];
            $db->where([static::$softDeleteFormat['key'] . ' !=' => static::$softDeleteFormat['value']]);
            return $db->update($table, $data)->execute();
        }
    }

    public static function findBefore(\Zls_Database_ActiveRecord $db, $method)
    {
        return self::softFind($db, $method);
    }

    public static function deleteBefore(\Zls_Database_ActiveRecord $db, $wheres = [])
    {
        return self::softDelete($db, $wheres);
    }
}
