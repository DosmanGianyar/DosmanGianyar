<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        $users = DB::table('users')->whereNotNull('phone')->orWhereNotNull('parent_phone')->get();

        foreach ($users as $user) {
            $updates = [];

            if (! empty($user->phone)) {
                $newPhone = User::formatPhoneNumber($user->phone);
                if ($newPhone !== $user->phone) {
                    $updates['phone'] = $newPhone;
                }
            }

            if (! empty($user->parent_phone)) {
                $newParentPhone = User::formatPhoneNumber($user->parent_phone);
                if ($newParentPhone !== $user->parent_phone) {
                    $updates['parent_phone'] = $newParentPhone;
                }
            }

            if (! empty($updates)) {
                DB::table('users')->where('id', $user->id)->update($updates);
            }
        }
    }

    public function down(): void
    {
        // No revert needed
    }
};
