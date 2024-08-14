<?php

class ComprehensiveSQLParser
{
    public function parse($sql)
    {
        $sql = trim($sql);

        $result = [];

        // Extract command (SELECT, INSERT, UPDATE, DELETE)
        preg_match('/^\s*(SELECT|INSERT INTO|UPDATE|DELETE FROM)/i', $sql, $matches);
        if (isset($matches[1])) {
            $result['command'] = strtoupper($matches[1]);
        }

        // Extract subqueries
        $sql = $this->extractSubqueries($sql, $result);

        // Extract table name(s) with possible alias and joins
        $result['tables'] = $this->parseTables($sql);

        // Extract columns in SELECT
        if ($result['command'] === 'SELECT') {
            preg_match('/SELECT\s+(.*?)\s+FROM/i', $sql, $matches);
            if (isset($matches[1])) {
                $columns = $this->parseColumns($matches[1]);
                $result['columns'] = $columns;
            }
        }

        // Extract WHERE conditions
        preg_match('/\bWHERE\b\s*(.*?)(?:\bORDER\b|\bGROUP\b|\bHAVING\b|\bLIMIT\b|$)/i', $sql, $matches);
        if (isset($matches[1])) {
            $whereConditions = $this->parseConditions($matches[1]);
            $result['conditions'] = array_merge($result['conditions'] ?? [], $whereConditions);
        }

        // Extract ORDER BY
        preg_match('/\bORDER BY\s+(.*?)(?:\bGROUP\b|\bHAVING\b|\bLIMIT\b|$)/i', $sql, $matches);
        if (isset($matches[1])) {
            $result['order_by'] = $this->parseOrderBy($matches[1]);
        }

        // Extract GROUP BY
        preg_match('/\bGROUP BY\s+(.*?)(?:\bHAVING\b|\bLIMIT\b|$)/i', $sql, $matches);
        if (isset($matches[1])) {
            $result['group_by'] = $this->parseGroupBy($matches[1]);
        }

        // Extract HAVING
        preg_match('/\bHAVING\s+(.*?)(?:\bORDER\b|\bLIMIT\b|$)/i', $sql, $matches);
        if (isset($matches[1])) {
            $havingConditions = $this->parseConditions($matches[1]);
            $result['conditions'] = array_merge($result['conditions'] ?? [], $havingConditions);
        }

        // Extract LIMIT
        preg_match('/\bLIMIT\s+(.*)$/i', $sql, $matches);
        if (isset($matches[1])) {
            $result['limit'] = trim($matches[1]);
        }

        // Extract JOINs
        $joins = $this->parseJoins($sql);
        $result['joins'] = $joins;

        // Extract conditions from JOINs and merge with main conditions array
        foreach ($joins as $join) {
            if (isset($join['condition'])) {
                $result['conditions'] = array_merge($result['conditions'] ?? [], $join['condition']);
            }
        }

        return $result;
    }

    private function extractSubqueries($sql, &$result)
    {
        // Handle nested subqueries recursively
        preg_match_all('/\((SELECT.*?)\)/is', $sql, $matches);
        $subqueries = [];
        foreach ($matches[1] as $subquery) {
            $parsedSubquery = $this->parse($subquery);
            $subqueries[] = $parsedSubquery;
            $sql = str_replace($subquery, '[SUBQUERY]', $sql);
        }
        if (!empty($subqueries)) {
            $result['subqueries'] = $subqueries;
        }
        return $sql;
    }

    private function parseTables($sql)
    {
        $result = [];
        preg_match('/\bFROM\s+([a-zA-Z0-9_,\s]+)(?:\s+WHERE|\s+JOIN|\s+LEFT|\s+RIGHT|\s+INNER|\s+OUTER|\s+CROSS|\s+NATURAL|\s+ORDER|\s+GROUP|\s+HAVING|\s+LIMIT|$)/i', $sql, $matches);
        if (isset($matches[1])) {
            $tables = preg_split('/\s*,\s*/', $matches[1]);
            foreach ($tables as $table) {
                $result[] = trim($table);
            }
        }
        return $result;
    }

    private function parseColumns($columns)
    {
        // Handle SQL functions in columns
        $columns = explode(',', $columns);
        $result = [];
        foreach ($columns as $column) {
            $result[] = $this->parseFunction(trim($column));
        }
        return $result;
    }

    private function parseConditions($conditions)
    {
        // Handle subqueries in conditions
        $conditions = preg_replace_callback('/\((SELECT.*?)\)/is', function ($matches) {
            return '[SUBQUERY]';
        }, $conditions);

        $conditions = preg_split('/\s+(AND|OR)\s+/i', $conditions, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = [];
        $currentCondition = [];

        foreach ($conditions as $part) {
            if (strtoupper($part) === 'AND' || strtoupper($part) === 'OR') {
                $result[] = $currentCondition;
                $result[] = strtoupper($part);
                $currentCondition = [];
            } else {
                $currentCondition[] = $this->parseFunction(trim($part));
            }
        }

        if (!empty($currentCondition)) {
            $result[] = $currentCondition;
        }

        return $result;
    }

    private function parseOrderBy($orderBy)
    {
        $columns = explode(',', $orderBy);
        $result = [];

        foreach ($columns as $column) {
            $parts = preg_split('/\s+/', trim($column));
            $result[] = [
                'column' => $this->parseFunction($parts[0]),
                'direction' => isset($parts[1]) ? strtoupper($parts[1]) : 'ASC'
            ];
        }

        return $result;
    }

    private function parseGroupBy($groupBy)
    {
        $columns = explode(',', $groupBy);
        return array_map([$this, 'parseFunction'], array_map('trim', $columns));
    }

    private function parseJoins($sql)
    {
        $joins = [];
        preg_match_all('/\b(JOIN|LEFT JOIN|RIGHT JOIN|INNER JOIN|OUTER JOIN|CROSS JOIN|NATURAL JOIN)\s+([a-zA-Z0-9_]+)\s+(ON|USING)\s+(.+?)(?=\b(JOIN|LEFT JOIN|RIGHT JOIN|INNER JOIN|OUTER JOIN|CROSS JOIN|NATURAL JOIN|\bWHERE|\bORDER|\bGROUP|\bHAVING|\bLIMIT|$))/i', $sql, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $joins[] = [
                'type' => strtoupper($match[1]),
                'table' => $match[2],
                'condition_type' => strtoupper($match[3]),
                'condition' => $this->parseConditions(trim($match[4]))
            ];
        }

        return $joins;
    }

    private function parseFunction($expression)
    {
        // Detect and parse SQL functions
        preg_match('/([a-zA-Z_]+)\((.*)\)/i', $expression, $matches);
        if (isset($matches[1])) {
            return [
                'function' => strtoupper($matches[1]),
                'arguments' => $this->parseFunctionArguments($matches[2])
            ];
        }
        return $expression;
    }

    private function parseFunctionArguments($arguments)
    {
        $args = explode(',', $arguments);
        $parsedArgs = [];
        foreach ($args as $arg) {
            $parsedArgs[] = $this->parseFunction(trim($arg));
        }
        return $parsedArgs;
    }
}

// Example usage
$sql = "SELECT u.id, COUNT(u.id) as user_count, (SELECT p.name FROM profiles p WHERE p.user_id = u.id) as profile_name 
        FROM users u 
        INNER JOIN orders o ON u.id = o.user_id 
        WHERE u.age > 18 AND u.status = 'active' 
        GROUP BY u.id 
        HAVING COUNT(u.id) > 1 
        ORDER BY u.name ASC 
        LIMIT 10";

$parser = new ComprehensiveSQLParser();
$parsedSQL = $parser->parse($sql);

print_r($parsedSQL);