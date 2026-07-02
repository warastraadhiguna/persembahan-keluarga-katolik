<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function index()
    {
        return view('backup');
    }

    public function download()
    {
        $filename = 'backup-' . now()->format('Y-m-d_His') . '.sql';

        AuditLogger::log('backup.downloaded', null, "Download backup database: {$filename}");

        return response()->stream(
            fn () => $this->streamSql(),
            200,
            [
                'Content-Type'        => 'application/octet-stream',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Cache-Control'       => 'no-store, no-cache',
            ]
        );
    }

    public function downloadGz()
    {
        $filename = 'backup-' . now()->format('Y-m-d_His') . '.sql.gz';

        AuditLogger::log('backup.downloaded', null, "Download backup database (gzip): {$filename}");

        ob_start();
        $this->streamSql();
        $sql = ob_get_clean();

        return response(gzencode($sql, 9), 200, [
            'Content-Type'        => 'application/gzip',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-store, no-cache',
        ]);
    }

    private function streamSql(): void
    {
        $pdo    = DB::getPdo();
        $dbName = config('database.connections.mysql.database');

        echo "-- ================================================\n";
        echo "-- " . config('app.name') . " — Backup Database\n";
        echo "-- Tanggal  : " . now()->format('d/m/Y H:i:s') . "\n";
        echo "-- Database : {$dbName}\n";
        echo "-- ================================================\n\n";
        echo "SET NAMES utf8mb4;\n";
        echo "SET FOREIGN_KEY_CHECKS = 0;\n";
        echo "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n";

        $tables = DB::select("SHOW FULL TABLES FROM `{$dbName}` WHERE Table_type = 'BASE TABLE'");

        foreach ($tables as $tableRow) {
            $table = array_values((array) $tableRow)[0];

            // DROP + CREATE
            $createRow = DB::select("SHOW CREATE TABLE `{$table}`");
            $createSql = $createRow[0]->{'Create Table'};

            echo "\n-- ------------------------------------------------\n";
            echo "-- Table: `{$table}`\n";
            echo "-- ------------------------------------------------\n";
            echo "DROP TABLE IF EXISTS `{$table}`;\n";
            echo $createSql . ";\n";

            // Data
            $total = DB::table($table)->count();
            if ($total === 0) {
                echo "\n";
                continue;
            }

            echo "\n";
            DB::table($table)->orderByRaw('1')->chunk(500, function ($rows) use ($table, $pdo) {
                $columns = array_keys((array) $rows->first());
                $colList = '`' . implode('`, `', $columns) . '`';

                foreach ($rows as $row) {
                    $values = array_map(function ($val) use ($pdo) {
                        if ($val === null) {
                            return 'NULL';
                        }
                        return $pdo->quote((string) $val);
                    }, (array) $row);

                    echo "INSERT INTO `{$table}` ({$colList}) VALUES (" . implode(', ', $values) . ");\n";
                }

                ob_flush();
                flush();
            });

            echo "\n";
        }

        echo "SET FOREIGN_KEY_CHECKS = 1;\n";
        echo "\n-- Backup selesai: " . now()->format('Y-m-d H:i:s') . "\n";
    }
}
