<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameComponentTotalQtyToQty extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $constraint = $this->getConstraintSql('components', 'total_qty');

        if (null !== $constraint) {
            $this->dropConstraint('components', $constraint);
        }

        Schema::table('components', function (Blueprint $table) {
            //
            $table->renameColumn('total_qty', 'qty');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('components', function (Blueprint $table) {
            //
            $table->renameColumn('qty', 'total_qty');
        });
    }

    final protected function getConstraintSql($table, $column)
    {
        if ($this->isSqlServer()) {
          return DB::select(DB::raw("declare @tableName varchar(max) = :tableName, @columnName varchar(max) = :columnName
          SELECT sysobjects.[Name]
                FROM sysobjects INNER JOIN (SELECT [Name],[ID] FROM sysobjects WHERE XType = 'U') AS Tab
                ON Tab.[ID] = sysobjects.[Parent_Obj]
                INNER JOIN sys.default_constraints DefCons ON DefCons.[object_id] = sysobjects.[ID]
                INNER JOIN syscolumns Col ON Col.[ColID] = DefCons.[parent_column_id] AND Col.[ID] = Tab.[ID]
                WHERE Col.[Name] = @columnName AND Tab.[Name] = @tableName
                ORDER BY Col.[Name]"), [
                  'tableName' => $table,
                  'columnName' => $column
                ])[0]->Name ?? null;
        }
        
        return null;
    }

    final protected function dropConstraint($table, $constraint)
    {
        if ($this->isSqlServer()) {
          DB::statement("EXECUTE('ALTER TABLE " . $table . " DROP CONSTRAINT [$constraint]')");
        }
    }

    final protected function getEngine()
    {
      $dbEngine = strtolower(config('database.default'));

      if (null === $dbEngine || strlen($dbEngine) === 0) {
        throw new \UnexpectedValueException("DB engine must not be null or empty.");
      }

      return $dbEngine;
    }

	  final protected function isSqlServer()
	  {
		  return $this->getEngine() === 'sqlsrv';
	  }
}
