<?php

namespace SuperV\Platform\Domains\Database\Model;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;

class QueryBuilder extends Builder
{
    /**
       * Insert a new record into the database.
       *
       * @param  array  $values
       * @return bool
       */
      public function insert(array $values)
      {
          // Since every insert gets treated like a batch insert, we will make sure the
          // bindings are structured in a way that is convenient when building these
          // inserts statements by verifying these elements are actually an array.
          if (empty($values)) {
              return true;
          }

          if (! is_array(reset($values))) {
              $values = [$values];
          }

          // Here, we will sort the insert keys for every record so that each insert is
          // in the same order for the record. We need to make sure this is the case
          // so there are not any errors or problems when inserting these records.
          else {
              foreach ($values as $key => $value) {
                  ksort($value);

                  $values[$key] = $value;
              }
          }

          // Finally, we will run this query against the database connection and return
          // the results. We will need to also flatten these bindings before running
          // the query so they are all in one huge, flattened array for execution.
          $compiledInsert = $this->grammar->compileInsert($this, $values);

          return $this->connection->insert(
              $compiledInsert,
              $this->cleanBindings(Arr::flatten($values, 1))
          );
      }

    /**
     * Insert a new record and get the value of the primary key.
     *
     * @param  array   $values
     * @param  string|null  $sequence
     * @return int
     */
    public function insertGetId(array $values, $sequence = null)
    {
        $sql = $this->grammar->compileInsertGetId($this, $values, $sequence);

        $values = $this->cleanBindings($values);

        return $this->processor->processInsertGetId($this, $sql, $values, $sequence);
    }
}