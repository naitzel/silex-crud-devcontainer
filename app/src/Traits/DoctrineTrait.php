<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Naitzel\SilexCrud\Traits;

use Naitzel\SilexCrud\Controller\ContainerAware;
use Doctrine\DBAL\Cache\QueryCacheProfile;

trait DoctrineTrait
{
    /**
     * Retona conexão com banco de dados.
     *
     * @return \Doctrine\DBAL\Connection
     */
    protected function db()
    {
        if ($this instanceof ContainerAware) {
            return $this->get('db');
        } else {
            throw new \DomainException(sprintf('A classe "%s" não extende de "Naitzel\SilexCrud\Controller\ContainerAware"', get_class($this)));
        }
    }

    /**
     * Prepares and executes an SQL query and returns the first row of the result
     * as an associative array.
     *
     * @param string $statement The SQL query.
     * @param array  $params    The query parameters.
     * @param array  $types     The query parameter types.
     *
     * @return array
     */
    public function fetchAssoc($statement, array $params = array(), array $types = array())
    {
        return $this->db()->fetchAssoc($statement, $params, $types);
    }

    /**
     * Prepares and executes an SQL query and returns the first row of the result
     * as a numerically indexed array.
     *
     * @param string $statement The SQL query to be executed.
     * @param array  $params    The prepared statement params.
     * @param array  $types     The query parameter types.
     *
     * @return array
     */
    public function fetchArray($statement, array $params = array(), array $types = array())
    {
        return $this->db()->fetchArray($statement, $params, $types);
    }

    /**
     * Prepares and executes an SQL query and returns the value of a single column
     * of the first row of the result.
     *
     * @param string $statement The SQL query to be executed.
     * @param array  $params    The prepared statement params.
     * @param int    $column    The 0-indexed column number to retrieve.
     * @param array  $types     The query parameter types.
     *
     * @return mixed
     */
    public function fetchColumn($statement, array $params = array(), $column = 0, array $types = array())
    {
        return $this->db()->fetchColumn($statement, $params, $column, $types);
    }

    /**
     * Prepares and executes an SQL query and returns the result as an associative array.
     *
     * @param string $sql    The SQL query.
     * @param array  $params The query parameters.
     * @param array  $types  The query parameter types.
     *
     * @return array
     */
    public function fetchAll($sql, array $params = array(), $types = array())
    {
        return $this->db()->fetchAll($sql, $params, $types);
    }

    /**
     * Executes an, optionally parametrized, SQL query.
     *
     * If the query is parametrized, a prepared statement is used.
     * If an SQLLogger is configured, the execution is logged.
     *
     * @param string                                      $query  The SQL query to execute.
     * @param array                                       $params The parameters to bind to the query, if any.
     * @param array                                       $types  The types the previous parameters are in.
     * @param \Doctrine\DBAL\Cache\QueryCacheProfile|null $qcp    The query cache profile, optional.
     *
     * @return \Doctrine\DBAL\Driver\Statement The executed statement.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function executeQuery($query, array $params = array(), $types = array(), QueryCacheProfile $qcp = null)
    {
        return $this->db()->executeQuery($query, $params, $types, $qcp);
    }

    /**
     * Executes an SQL INSERT/UPDATE/DELETE query with the given parameters
     * and returns the number of affected rows.
     *
     * This method supports PDO binding types as well as DBAL mapping types.
     *
     * @param string $query  The SQL query.
     * @param array  $params The query parameters.
     * @param array  $types  The parameter types.
     *
     * @return integer The number of affected rows.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function executeUpdate($query, array $params = array(), array $types = array())
    {
        return $this->db()->executeUpdate($query, $params, $types);
    }

    /**
     * Returns the ID of the last inserted row, or the last value from a sequence object,
     * depending on the underlying driver.
     *
     * Note: This method may not return a meaningful or consistent result across different drivers,
     * because the underlying database may not even support the notion of AUTO_INCREMENT/IDENTITY
     * columns or sequences.
     *
     * @param string|null $seqName Name of the sequence object from which the ID should be returned.
     *
     * @return string A string representation of the last inserted ID.
     */
    public function lastInsertId($seqName = null)
    {
        return $this->db()->lastInsertId($seqName);
    }
}
