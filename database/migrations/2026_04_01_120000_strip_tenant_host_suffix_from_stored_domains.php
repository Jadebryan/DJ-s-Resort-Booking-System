<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Store tenant_domains.domain and tenant_registration_requests.primary_domain without the
     * configured suffix (e.g. jeddsresort instead of jeddsresort.localhost).
     */
    public function up(): void
    {
        $suffix = strtolower(trim((string) env('TENANT_HOST_SUFFIX', 'localhost')));
        if ($suffix === '') {
            return;
        }

        $suffixDot = '.'.$suffix;

        $this->stripSuffixColumn('tenant_domains', 'domain', $suffixDot);
        $this->stripSuffixColumn('tenant_registration_requests', 'primary_domain', $suffixDot);
    }

    public function down(): void
    {
        // Cannot reliably restore previous FQDNs; no-op.
    }

    private function stripSuffixColumn(string $table, string $column, string $suffixDot): void
    {
        $rows = DB::table($table)->select('id', $column)->get();
        foreach ($rows as $row) {
            $val = strtolower((string) ($row->{$column} ?? ''));
            if ($val === '' || ! str_ends_with($val, $suffixDot)) {
                continue;
            }
            $short = substr($val, 0, -strlen($suffixDot));
            if ($short === '') {
                continue;
            }
            DB::table($table)->where('id', $row->id)->update([$column => $short]);
        }
    }
};
