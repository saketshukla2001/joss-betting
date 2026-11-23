<?php

namespace Database\Seeders;

use App\Models\OperationLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class OperationLogDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('username', 'admin')->first();
        OperationLog::where('is_demo', true)->delete();

        $rows = [
            ['CASINO STATUS UPDATED', 'LAST CASINO STATUS DISABLE, NEW CASINO STATUS ACTIVE BY [JMD OR,SU88623]', '2026-07-02 16:15:04'],
            ['BETTING STATUS UPDATED', 'LAST BETTING STATUS DISABLE, NEW BETTING STATUS ACTIVE BY [JMD OR,SU88623]', '2026-07-02 16:15:03'],
            ['CASINO STATUS UPDATED', 'LAST CASINO STATUS ACTIVE, NEW CASINO STATUS DISABLE BY [JMD OR,SU88623]', '2026-07-02 16:15:02'],
            ['BETTING STATUS UPDATED', 'LAST BETTING STATUS ACTIVE, NEW BETTING STATUS DISABLE BY [JMD OR,SU88623]', '2026-07-02 16:15:01'],
            ['PASSWORD HAS BEEN CHANGED', 'LAST PASSWORD ******, NEW PASSWORD IS ****** BY [JMD OR,SU88623]', '2026-06-26 03:12:00'],
        ];

        foreach ($rows as [$operation, $description, $date]) {
            $log = OperationLog::create([
                'user_id' => $admin?->id,
                'operation' => $operation,
                'description' => $description,
                'is_demo' => true,
                'meta' => ['demo' => true],
            ]);

            $log->timestamps = false;
            $log->forceFill(['created_at' => $date, 'updated_at' => $date])->save();
        }
    }
}
