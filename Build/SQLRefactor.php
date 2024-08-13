<?php

/**
 * Class SQLRefactor
 *
 * This class is responsible for refactoring an SQL statement into a PHP array
 * with unique identifier keys and values that represent the components of the SQL statement.
 */
class SQLRefactor
{
    /**
     * Refactors the given SQL statement into an associative array.
     *
     * @param string $sql The SQL statement to refactor.
     * @return array The refactored SQL statement as an associative array.
     */
    public function refactorSQL($sql)
    {
        $sqlArray = [];

        // Extract SELECT clause
        if (preg_match('/SELECT\s+(.+?)\s+FROM\s+/is', $sql, $matches)) {
            $sqlArray['SELECT'] = $this->parseSelectColumns($matches[1]);
        }

        // Extract FROM clause
        if (preg_match('/FROM\s+([^\s]+)/is', $sql, $matches)) {
            $sqlArray['FROM'] = $matches[1];
        }

        // Extract WHERE clause
        if (preg_match('/WHERE\s+(.+?)(\s+GROUP BY|\s+ORDER BY|\s+HAVING|$)/is', $sql, $matches)) {
            $sqlArray['WHERE'] = $this->parseConditions($matches[1]);
        }

        // Extract GROUP BY clause
        if (preg_match('/GROUP BY\s+(.+?)(\s+HAVING|\s+ORDER BY|$)/is', $sql, $matches)) {
            $sqlArray['GROUP BY'] = $this->parseGroupBy($matches[1]);
        }

        // Extract HAVING clause
        if (preg_match('/HAVING\s+(.+?)(\s+ORDER BY|$)/is', $sql, $matches)) {
            $sqlArray['HAVING'] = $this->parseConditions($matches[1]);
        }

        // Extract ORDER BY clause
        if (preg_match('/ORDER BY\s+(.+)$/is', $sql, $matches)) {
            $sqlArray['ORDER BY'] = $this->parseOrderBy($matches[1]);
        }

        return $sqlArray;
    }

    /**
     * Parses the columns in the SELECT clause.
     *
     * @param string $columns The columns to parse.
     * @return array An array of columns.
     */
    private function parseSelectColumns($columns)
    {
        return array_map('trim', explode(',', $columns));
    }

    /**
     * Parses the conditions in WHERE or HAVING clauses.
     *
     * @param string $conditions The conditions to parse.
     * @return array An array of conditions with unique identifier keys.
     */
    private function parseConditions($conditions)
    {
        $conditionArray = [];
        $pattern = '/\(([^()]*\bSELECT\b[^()]*)\)|(\w+\s*(?:[<>!=]=?|LIKE|IN|NOT IN|BETWEEN|IS NOT|IS)\s*[^()]+)(\s+AND|\s+OR|$)/is';

        if (preg_match_all($pattern, $conditions, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (!empty($match[1])) {
                    // Nested condition (e.g., subquery or compound condition)
                    $conditionArray[] = [
                        'type' => 'nested',
                        'condition' => $this->parseConditions(trim($match[1], '()'))
                    ];
                } else {
                    // Simple condition
                    $condition = trim($match[2]);
                    $logicalOperator = isset($match[3]) ? trim($match[3]) : null;
                    $operator = $this->findOperator($condition);
                    list($key, $value) = $this->splitCondition($condition, $operator);
                    $conditionArray[] = [
                        'key' => trim($key),
                        'operator' => $operator,
                        'value' => $this->validateValue($value),
                        'logical_operator' => $logicalOperator
                    ];
                }
            }
        }

        return $conditionArray;
    }

    /**
     * Parses the columns in the GROUP BY clause.
     *
     * @param string $groupBy The GROUP BY clause to parse.
     * @return array An array of columns used for grouping.
     */
    private function parseGroupBy($groupBy)
    {
        return array_map('trim', explode(',', $groupBy));
    }

    /**
     * Parses the columns and sorting order in the ORDER BY clause.
     *
     * @param string $orderBy The ORDER BY clause to parse.
     * @return array An array of columns with sorting order.
     */
    private function parseOrderBy($orderBy)
    {
        $orderArray = [];
        $orderItems = explode(',', $orderBy);

        foreach ($orderItems as $item) {
            $parts = array_map('trim', explode(' ', trim($item)));
            $orderArray[] = [
                'column' => $parts[0],
                'direction' => isset($parts[1]) ? strtoupper($parts[1]) : 'ASC'
            ];
        }

        return $orderArray;
    }

    /**
     * Identifies the operator in a condition.
     *
     * @param string $condition The condition string to analyze.
     * @return string|null The identified operator or null if not found.
     */
    private function findOperator($condition)
    {
        $operators = ['>=', '<=', '<>', '!=', '=', '<', '>', 'LIKE', 'IN', 'NOT IN', 'BETWEEN', 'IS NOT', 'IS'];
        foreach ($operators as $operator) {
            if (stripos($condition, $operator) !== false) {
                return $operator;
            }
        }
        return null;
    }

    /**
     * Splits a condition into its key and value based on the operator.
     *
     * @param string $condition The condition string to split.
     * @param string $operator The operator found in the condition.
     * @return array An array with the key and value.
     */
    private function splitCondition($condition, $operator)
    {
        return explode($operator, $condition, 2);
    }

    /**
     * Validates the value in a condition, ensuring it is correctly formatted for SQL.
     *
     * @param string $value The value to validate.
     * @return string The validated value.
     */
    private function validateValue($value)
    {
        // Ensure the value is correctly quoted for strings or not quoted for numbers
        if (is_numeric($value)) {
            return $value;
        } elseif (preg_match('/^\'[^\']*\'$/', $value)) {
            return $value;
        } else {
            return "'$value'";
        }
    }
}

// Example usage
$sql = "
    SELECT name, age FROM users
    WHERE (age >= 21 AND (country = 'USA' OR country = 'Canada'))
    AND (city LIKE 'New%' OR city IN ('Los Angeles', 'Chicago'))
    AND (status = 'active' OR (status = 'pending' AND last_login > '2023-01-01'))
    OR id IN (SELECT user_id FROM orders WHERE (total > 100 AND order_date > '2024-01-01'))
    GROUP BY age
    HAVING COUNT(*) > 1
    ORDER BY name DESC;
";

// Instantiate the class and refactor the SQL statement
$refactor = new SQLRefactor();
$refactoredArray = $refactor->refactorSQL($sql);

// Print the refactored SQL array
print_r($refactoredArray);
