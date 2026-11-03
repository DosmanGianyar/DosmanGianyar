<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TeacherCodeSyncService
{
    /**
     * Data pemetaan Kode Guru ke Nama Lengkap dari dokumen kurikulum/jadwal.
     *
     * @var array<int, array{code: string, name: string}>
     */
    public const TEACHER_DATA = [
        // Kolom 1
        ['code' => 'P. SUDARMA',       'name' => 'I Nyoman Sudarma, S.Ag'],
        ['code' => 'P. Sutarjana',     'name' => 'I Nyoman Sutarjana, S.Pd.H'],
        ['code' => 'B. Lindawati',     'name' => 'Ni Putu Lindawati, S.Pd.H'],
        ['code' => 'P. Windu',         'name' => 'Made Windu Agung Purwanto, S.Pd'],
        ['code' => 'P. Ariana',        'name' => 'I Wayan Ariana, S. Sn., M.Pd.'],
        ['code' => 'P. Ngr Afriadi',   'name' => 'I Gusti Ngurah Ari Afriadi, S.Sn'],
        ['code' => 'P. Madra',         'name' => 'I Ketut Madra, S.Pd., M.Pd'],
        ['code' => 'P. Reta',          'name' => 'Drs. I Ketut Reta, M.Pd'],
        ['code' => 'B. GA Eka Dewi',   'name' => 'Ni Gusti Ayu Eka Dewi, S.Pd, M.Pd'],
        ['code' => 'B. Riska',         'name' => 'Ni Putu Riska Pradanita, S.Pd., M.Pd'],
        ['code' => 'B. Suparini',      'name' => 'Putu Suparini, S.Pd., M.Pd'],
        ['code' => 'B. DA Wisnontari', 'name' => 'Dewa Ayu Sri Wisnontari, S.Pd.'],
        ['code' => 'B. Mini',          'name' => 'Ni Luh Putu Warmini, S.Pd'],
        ['code' => 'B. Agung Ria M',   'name' => 'I Gusti Agung Ayu Ria Mahayani, S.Pd'],
        ['code' => 'B. Novi Ari',      'name' => 'Ni Kadek Novi Ari, S.Pd'],
        ['code' => 'P. Suwila',        'name' => 'I Nyoman Suwila, S.Pd, M.Pd'],
        ['code' => 'P. Raka Asmara',   'name' => 'Kadek Raka Asmara, S.Pd'],
        ['code' => 'P. Sudiarta',      'name' => 'I Gede Sudiarta, S. Pd., M. Pd'],
        ['code' => 'B. Giri Wahyuni',  'name' => 'Ni Wayan Giri Wahyuni, S.Pd'],
        ['code' => 'B. Yuliantari',    'name' => 'Putu Yuliantari, S.Pd'],

        // Kolom 2
        ['code' => 'B. Eka W',         'name' => 'Ni Putu Eka Widiantari, S.Pd'],
        ['code' => 'P. Darma',         'name' => 'I Putu Darma Putra, S.Pd.'],
        ['code' => 'P. Dipta',         'name' => 'I Wayan Dipta Samsidim, S.Pd'],
        ['code' => 'B. Risma',         'name' => 'Ni Kadek Risma Sagitarini, S.Pd.M.Pd'],
        ['code' => 'B. Somya',         'name' => 'Ni Wayan Somyantari S.Pd'],
        ['code' => 'B. Vesta',         'name' => 'Vesta Rotua Sirait, S.Pd'],
        ['code' => 'B. Esti',          'name' => 'Ni Made Esti Dwijayanti, S.Pd'],
        ['code' => 'P. Purna',         'name' => 'I Wayan Gede Purna Martha S.Pd'],
        ['code' => 'B. Lakhsmi',       'name' => 'Luh Gede Wijayanti Lakhsmi Dewi, S.Pd'],
        ['code' => 'P. Susila',        'name' => 'I Ketut Susila, S.Pd., M.Pd'],
        ['code' => 'P. Suranata',      'name' => 'I Nyoman Suranata, S.Si'],
        ['code' => 'P. GN Susanta',    'name' => 'I Gusti Nyoman Susanta, S.Pd'],
        ['code' => 'B. G Indra',       'name' => 'A.A Sri D. Indrayanti, S.Pd., M.Pd'],
        ['code' => 'B. Nike',          'name' => 'Ni Putu Nike Mayasari S.Pd'],
        ['code' => 'B. Kencana',       'name' => 'Ni Made Adi Kencana Wati Tira, S.Pd'],
        ['code' => 'P. Widi',          'name' => 'I Made Widiana, S.Pd'],
        ['code' => 'B. Valentini',     'name' => 'Ni Wayan Sri Valentini, S.Pd'],
        ['code' => 'B. Meli',          'name' => 'Ni Komang Ayu Melisusanti, S.Pd'],
        ['code' => 'P. DW Anom',       'name' => 'I Dewa Nyoman Anom, S.Pd., M.Pd'],
        ['code' => 'P. Surata',        'name' => 'I Ketut Surata, S.S.'],

        // Kolom 3
        ['code' => 'B. Suwarastuti',   'name' => 'Nyoman Suwarastuti Kusumarini, S.Pd'],
        ['code' => 'B. Diah Vigayanti', 'name' => 'Luh Putu Diah Vigayanti, S.Pd'],
        ['code' => 'B. Dayu Surya',    'name' => 'Ida Ayu Made Surya Astini, S.Pd.'],
        ['code' => 'P. Sucita',        'name' => 'I Made Sucita, S.Pd'],
        ['code' => 'P. Ananta',        'name' => 'I Dewa Gde Agung Ananta Kusuma, S.Pd.'],
        ['code' => 'B. Mawar',         'name' => 'Ni Luh Eka Manik Mawarini, S.Pd'],
        ['code' => 'P. AG Juniawan',   'name' => 'Anak Agung Gede Juniawan, S.Pd'],
        ['code' => 'B. Abas',          'name' => 'Ni Putu Abas Sugiasih, S.Pd'],
        ['code' => 'P. GN Suyadnya',   'name' => 'I Gusti Putu Ngurah Suyadnya, S.Pd.'],
        ['code' => 'B. Sarti',         'name' => 'Sarti, S.Pd'],
        ['code' => 'B. Eneng',         'name' => 'Eneng Sismayanti Rahayu, S.Pd'],
        ['code' => 'P. Aryadi',        'name' => 'I Nyoman Aryadi, S.Pd'],
        ['code' => 'B. Puspita',       'name' => 'Putu Ayu Dewi Puspita Wulandari, S.Sos.'],
        ['code' => 'B. Katarina',      'name' => 'Katarina Melia Indah Sari, S.Pd'],
        ['code' => 'B. Listiani',      'name' => 'Ni Wayan Listiani, S.Pd'],
        ['code' => 'B. Yuliandari',    'name' => 'Ni Kadek Yuliandari, S.Pd'],
        ['code' => 'B. Setiawati',     'name' => 'Ni Wayan Setiawati, S.Pd'],
        ['code' => 'P. Gede Pramana',  'name' => 'Putu Gede Pramana Putra, S.Pd'],
        ['code' => 'B. Yasinta',       'name' => 'Ni Kadek Ayu Yasinta Dewi, S.Pd'],
        ['code' => 'P. Sumartika',     'name' => 'I Wayan Sumartika, S.Pd., M.Pd'],

        // Kolom 4
        ['code' => 'P. Alit Juniarta', 'name' => 'I Ketut Alit Juniartha, S.Pd'],
        ['code' => 'B. Dini',          'name' => 'Evyluk Meilian Syahdini, S.Pd'],
        ['code' => 'P. Sabda',         'name' => 'Komang Sabda Kusumantara, S.Pd'],
        ['code' => 'B. Md Setyawati',  'name' => 'Ni Made Setyawati, S.Kom'],
    ];

    /**
     * Normalisasi nama untuk pencocokan toleran terhadap gelar, spasi, dan tanda baca.
     */
    public static function normalizeName(string $name): string
    {
        $n = mb_strtolower(trim($name));
        $n = preg_replace('/[\.,]/', ' ', $n);
        $n = preg_replace('/\b(s\s*pd|m\s*pd|s\s*pd\s*h|s\s*ag|s\s*sn|s\s*si|s\s*sos|s\s*kom|s\s*s|drs)\b/i', ' ', $n);
        $n = preg_replace('/\s+/', ' ', $n);
        return trim($n);
    }

    /**
     * Sinkronkan seluruh kode guru ke tabel users.
     *
     * @return array{total: int, matched: int, updated: int, details: array<int, array>}
     */
    public static function syncAll(): array
    {
        $teachers = User::whereIn('role', ['guru', 'wali_kelas', 'kepala_sekolah', 'admin'])->get();

        $matchedCount = 0;
        $updatedCount = 0;
        $details = [];

        foreach (self::TEACHER_DATA as $item) {
            $code = trim($item['code']);
            $targetName = trim($item['name']);
            $normTarget = self::normalizeName($targetName);

            // 1. Exact match
            $found = $teachers->first(fn ($t) => mb_strtolower(trim($t->name)) === mb_strtolower($targetName));

            // 2. Normalized match (tanpa gelar & tanda baca)
            if (! $found) {
                $found = $teachers->first(fn ($t) => self::normalizeName($t->name) === $normTarget);
            }

            // 3. Substring match
            if (! $found) {
                $found = $teachers->first(function ($t) use ($normTarget) {
                    $normT = self::normalizeName($t->name);
                    return str_contains($normT, $normTarget) || str_contains($normTarget, $normT);
                });
            }

            // 4. Known typo / alias fallback (e.g. Suwila di daftar vs Suwita di database Dapodik)
            if (! $found) {
                $aliasMap = [
                    'i nyoman suwila' => 'i nyoman suwita',
                ];
                if (isset($aliasMap[$normTarget])) {
                    $targetAlias = $aliasMap[$normTarget];
                    $found = $teachers->first(fn ($t) => self::normalizeName($t->name) === $targetAlias);
                }
            }

            if ($found) {
                $matchedCount++;
                $wasChanged = false;

                if ($found->teacher_code !== $code) {
                    $found->teacher_code = $code;
                    $found->saveQuietly();
                    $updatedCount++;
                    $wasChanged = true;
                }

                $details[] = [
                    'code'       => $code,
                    'input_name' => $targetName,
                    'user_id'    => $found->id,
                    'db_name'    => $found->name,
                    'nip'        => $found->nip,
                    'status'     => $wasChanged ? 'updated' : 'already_set',
                ];
            } else {
                $details[] = [
                    'code'       => $code,
                    'input_name' => $targetName,
                    'user_id'    => null,
                    'db_name'    => null,
                    'nip'        => null,
                    'status'     => 'not_found',
                ];
            }
        }

        return [
            'total'   => count(self::TEACHER_DATA),
            'matched' => $matchedCount,
            'updated' => $updatedCount,
            'details' => $details,
        ];
    }
}
