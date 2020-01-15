<?php

namespace App\Repository;

use App\Services\Mysql;

class CallsRepository
{
    private $connection;

    public function __construct(Mysql $mysql)
    {
        $this->connection = $mysql;
    }

    public function findAll(array $filters = array())
    {
        $cond = array(
            'calldate >= :from',
            'disposition = "ANSWERED"'
        );

        $filters['from'] = isset($filters['from'])? $filters['from']: date('Y-m-d', strtotime('-1 month'));

        if (isset($filters['to']) && $filters['to']) {
            $cond[] = '`calldate` <= :to';
        }

        $query = 'SELECT * 
                  FROM cdr
                  WHERE 1 ';

        $query .= $this->formatConditions($cond);

        $query .= ' ORDER BY calldate DESC ';

        return $this->connection->fetchAll($query, $filters);
    }

    private function formatConditions(array $cond = array())
    {
        $result = '';
        if (count($cond)) {
            $result .= ' AND ' . implode(' AND ', $cond);
        }

        return $result;
    }
}
