<?php

namespace App\Services;

use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\StudentAchievement;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AchievementPembinaMatcher
{
    /**
     * Peta kata kunci lomba ke kandidat nama/topik ekstrakurikuler yang mendekati.
     * Urutan disusun dari yang paling spesifik ke umum.
     *
     * @var array<string, array{extra_keywords: array<int, string>, match_terms: array<int, string>, field: string}>
     */
    public const KEYWORD_GROUPS = [
        'catur' => [
            'extra_keywords' => ['catur', 'chess'],
            'match_terms'    => ['catur', 'chess', 'percasi', 'grandmaster'],
            'field'          => 'olahraga',
        ],
        'taekwondo' => [
            'extra_keywords' => ['taekwondo', 'tae kwon do', 'bela diri', 'beladiri'],
            'match_terms'    => ['taekwondo', 'tae kwon do', 'kyorugi', 'poomsae'],
            'field'          => 'olahraga',
        ],
        'silat' => [
            'extra_keywords' => ['silat', 'pencak silat', 'bakti negara', 'perisai diri', 'merpati putih', 'tapak suci', 'bela diri'],
            'match_terms'    => ['pencak silat', 'silat', 'bakti negara', 'perisai diri', 'merpati putih', 'tapak suci', 'ipsi', 'jurus tunggal', 'tanding kelas'],
            'field'          => 'olahraga',
        ],
        'karate' => [
            'extra_keywords' => ['karate', 'forki', 'inkai', 'kki', 'lemkari', 'bela diri'],
            'match_terms'    => ['karate', 'forki', 'kumite', 'inkai', 'lemkari', 'gojukai', 'shito'],
            'field'          => 'olahraga',
        ],
        'kempo_judo_beladiri' => [
            'extra_keywords' => ['kempo', 'judo', 'tarung derajat', 'wushu', 'muaythai', 'tinju', 'gulat', 'kabaddi', 'bela diri', 'silat', 'karate'],
            'match_terms'    => ['kempo', 'shorinji', 'judo', 'tarung derajat', 'kodrat', 'wushu', 'muaythai', 'muay thai', 'kickboxing', 'tinju', 'boxing', 'gulat', 'sambo', 'jiu jitsu', 'kabaddi'],
            'field'          => 'olahraga',
        ],
        'basket' => [
            'extra_keywords' => ['basket', 'bola basket', 'basketball'],
            'match_terms'    => ['basket', 'basketball', 'dbl', 'perbasi', '3x3'],
            'field'          => 'olahraga',
        ],
        'futsal_sepakbola' => [
            'extra_keywords' => ['futsal', 'sepak bola', 'sepakbola', 'soccer', 'football'],
            'match_terms'    => ['futsal', 'sepak bola', 'sepakbola', 'mini soccer', 'liga pelajar', 'pssi'],
            'field'          => 'olahraga',
        ],
        'voli' => [
            'extra_keywords' => ['voli', 'bola voli', 'volleyball'],
            'match_terms'    => ['voli', 'volleyball', 'pbvsi', 'voli pasir', 'voli pantai'],
            'field'          => 'olahraga',
        ],
        'bulutangkis' => [
            'extra_keywords' => ['bulutangkis', 'bulu tangkis', 'badminton'],
            'match_terms'    => ['bulutangkis', 'bulu tangkis', 'badminton', 'pbsi', 'ganda putra', 'ganda putri', 'tunggal putra', 'tunggal putri'],
            'field'          => 'olahraga',
        ],
        'tenis_meja' => [
            'extra_keywords' => ['tenis meja', 'pingpong', 'tenis'],
            'match_terms'    => ['tenis meja', 'pingpong', 'ping pong', 'ptmsi', 'tenis lapangan', 'pelti'],
            'field'          => 'olahraga',
        ],
        'panahan' => [
            'extra_keywords' => ['panahan', 'archery'],
            'match_terms'    => ['panahan', 'archery', 'perpani', 'recurve', 'compound', 'barebow', 'standard bow', 'jemparingan'],
            'field'          => 'olahraga',
        ],
        'renang' => [
            'extra_keywords' => ['renang', 'swimming', 'akuatik', 'aquatik', 'selam', 'atletik', 'olahraga'],
            'match_terms'    => ['renang', 'swimming', 'akuatik', 'aquatik', 'prsi', 'gaya bebas', 'gaya dada', 'gaya punggung', 'gaya kupu', 'finswimming', 'selam'],
            'field'          => 'olahraga',
        ],
        'atletik' => [
            'extra_keywords' => ['atletik', 'lari', 'olahraga'],
            'match_terms'    => ['atletik', 'lari', 'sprint', 'marathon', 'estafet', 'lompat jauh', 'lompat tinggi', 'tolak peluru', 'lempar lembing', 'lempar cakram', 'jalan cepat', 'pasi', '100m', '200m', '400m', '800m', '1500m', '5000m'],
            'field'          => 'olahraga',
        ],
        'cricket_woodball_petanque' => [
            'extra_keywords' => ['cricket', 'kriket', 'woodball', 'gateball', 'petanque', 'softball', 'baseball', 'atletik', 'olahraga'],
            'match_terms'    => ['cricket', 'kriket', 'woodball', 'gateball', 'petanque', 'softball', 'baseball', 'bola tangan', 'handball', 'sepatu roda', 'panjat tebing', 'fpti', 'senam', 'gymnastic', 'dancesport'],
            'field'          => 'olahraga',
        ],
        'esport' => [
            'extra_keywords' => ['esport', 'e-sport', 'e-sports', 'game', 'komputer', 'it', 'informatika'],
            'match_terms'    => ['esport', 'e-sport', 'e-sports', 'mobile legends', 'mlbb', 'pubg', 'free fire', 'valorant', 'efootball'],
            'field'          => 'teknologi',
        ],
        'tari' => [
            'extra_keywords' => ['tari', 'seni tari', 'dance', 'modern dance', 'kesenian', 'seni'],
            'match_terms'    => ['tari', 'penari', 'dance', 'koreografi', 'condong', 'legong', 'baris', 'jauk', 'kebyar', 'puspanjali', 'margapati', 'wirayuda', 'fls2n tari', 'taruna jaya', 'cendrawasih', 'oleg'],
            'field'          => 'seni_budaya',
        ],
        'tabuh_karawitan' => [
            'extra_keywords' => ['tabuh', 'karawitan', 'baleganjur', 'beleganjur', 'gamelan', 'gender', 'rindik', 'musik', 'seni'],
            'match_terms'    => ['tabuh', 'baleganjur', 'beleganjur', 'karawitan', 'gamelan', 'gender wayang', 'kendang', 'rindik', 'gong kebyar', 'megambel'],
            'field'          => 'seni_budaya',
        ],
        'musik_vokal' => [
            'extra_keywords' => ['musik', 'vokal', 'paduan suara', 'choir', 'band', 'seni suara', 'karawitan', 'seni'],
            'match_terms'    => ['musik', 'vokal', 'vocal', 'menyanyi', 'nyanyi', 'solo song', 'solo vocal', 'paduan suara', 'choir', 'band', 'akustik', 'gitar', 'piano', 'cipta lagu', 'lagu pop'],
            'field'          => 'seni_budaya',
        ],
        'teater_drama' => [
            'extra_keywords' => ['teater', 'drama', 'sastra', 'seni', 'jurnalistik'],
            'match_terms'    => ['teater', 'drama', 'monolog', 'akting', 'operet', 'kabaret', 'fragmen'],
            'field'          => 'seni_budaya',
        ],
        'seni_lukis_rupa' => [
            'extra_keywords' => ['lukis', 'seni rupa', 'kriya', 'desain', 'jurnalistik', 'mading', 'seni'],
            'match_terms'    => ['lukis', 'melukis', 'menggambar', 'seni rupa', 'kriya', 'kerajinan', 'sketsa', 'kaligrafi', 'komik', 'ilustrasi'],
            'field'          => 'seni_budaya',
        ],
        'jurnalistik_media' => [
            'extra_keywords' => ['jurnalistik', 'mading', 'fotografi', 'multimedia', 'broadcast', 'pers', 'desain', 'komputer'],
            'match_terms'    => ['jurnalistik', 'mading', 'majalah dinding', 'fotografi', 'photography', 'videografi', 'film pendek', 'short movie', 'sinematografi', 'poster', 'desain grafis', 'infografis', 'berita'],
            'field'          => 'seni_budaya',
        ],
        'bahasa_bali_hindu' => [
            'extra_keywords' => ['bahasa bali', 'sastra bali', 'dharma wacana', 'sloka', 'pesantian', 'dharma gita', 'kerohanian', 'agama', 'nyurat', 'mesatua', 'tari', 'tabuh'],
            'match_terms'    => ['bahasa bali', 'aksara bali', 'nyurat lontar', 'nyurat aksara', 'mesatua', 'masatua', 'pidarta', 'sambrama wacana', 'dharma wacana', 'sloka', 'kidung', 'kekawin', 'kakawin', 'wirama', 'palawakya', 'utsawa dharma gita', 'udg', 'agama hindu', 'pesantian'],
            'field'          => 'keagamaan',
        ],
        'english_debat' => [
            'extra_keywords' => ['english', 'bahasa inggris', 'debat', 'debate', 'speech'],
            'match_terms'    => ['english', 'bahasa inggris', 'ndc', 'nsdc', 'speech', 'story telling', 'storytelling', 'newscasting', 'news anchor', 'scrabble', 'spelling bee', 'toefl'],
            'field'          => 'bahasa_debat',
        ],
        'bahasa_jepang_asing' => [
            'extra_keywords' => ['jepang', 'nihongo', 'bahasa jepang', 'jerman', 'korea', 'mandarin', 'bahasa'],
            'match_terms'    => ['jepang', 'nihongo', 'kanji', 'shodo', 'kana', 'rodoku', 'bunkasai', 'jerman', 'mandarin'],
            'field'          => 'bahasa_debat',
        ],
        'debat_indonesia_sastra' => [
            'extra_keywords' => ['debat', 'ldbi', 'bahasa indonesia', 'sastra', 'literasi', 'jurnalistik', 'teater', 'kir'],
            'match_terms'    => ['debat bahasa indonesia', 'ldbi', 'debat', 'pidato', 'puisi', 'cipta puisi', 'baca puisi', 'cerpen', 'sastra', 'literasi', 'bulan bahasa', 'musikalisasi puisi'],
            'field'          => 'bahasa_debat',
        ],
        'pramuka' => [
            'extra_keywords' => ['pramuka', 'scout', 'ambalan'],
            'match_terms'    => ['pramuka', 'scout', 'kepanduan', 'pionering', 'semaphore', 'morse', 'penegak', 'ambalan', 'kemah'],
            'field'          => 'organisasi',
        ],
        'pmr_uks' => [
            'extra_keywords' => ['pmr', 'palang merah', 'uks', 'kkr', 'kesehatan'],
            'match_terms'    => ['pmr', 'palang merah', 'kepalangmerahan', 'pertolongan pertama', 'ppgd', 'donor darah', 'kesehatan remaja', 'uks', 'kkr'],
            'field'          => 'organisasi',
        ],
        'paskibra_lkbb' => [
            'extra_keywords' => ['paskibra', 'paskibraka', 'lkbb', 'pbb', 'pramuka'],
            'match_terms'    => ['paskibra', 'paskibraka', 'lkbb', 'baris berbaris', 'pbb', 'formasi barisan', 'variasi barisan'],
            'field'          => 'organisasi',
        ],
        'kir_riset' => [
            'extra_keywords' => ['kir', 'karya ilmiah', 'riset', 'penelitian', 'sains', 'kti'],
            'match_terms'    => ['kir', 'karya ilmiah', 'riset', 'penelitian', 'kti', 'lkir', 'opsi', 'nyia', 'fihsi', 'esai', 'essay', 'inovasi', 'karya tulis', 'ilmiah remaja', 'pir'],
            'field'          => 'sains_riset',
        ],
        'informatika_komputer' => [
            'extra_keywords' => ['informatika', 'komputer', 'tik', 'coding', 'robotik', 'it', 'kir'],
            'match_terms'    => ['informatika', 'komputer', 'coding', 'pemrograman', 'programming', 'robotik', 'robot', 'iot', 'web design', 'ui/ux', 'cyber', 'osn informatika', 'ksn informatika'],
            'field'          => 'teknologi',
        ],
        'matematika' => [
            'extra_keywords' => ['matematika', 'math', 'olimpiade', 'osn', 'ksn', 'sains', 'kir'],
            'match_terms'    => ['matematika', 'math', 'mathematics', 'aljabar', 'geometri', 'kalkulus', 'aritmatika'],
            'field'          => 'sains_riset',
        ],
        'fisika' => [
            'extra_keywords' => ['fisika', 'physics', 'olimpiade', 'osn', 'ksn', 'sains', 'kir'],
            'match_terms'    => ['fisika', 'physics', 'mekanika'],
            'field'          => 'sains_riset',
        ],
        'kimia' => [
            'extra_keywords' => ['kimia', 'chemistry', 'olimpiade', 'osn', 'ksn', 'sains', 'kir'],
            'match_terms'    => ['kimia', 'chemistry'],
            'field'          => 'sains_riset',
        ],
        'biologi' => [
            'extra_keywords' => ['biologi', 'biology', 'olimpiade', 'osn', 'ksn', 'sains', 'kir', 'pmr'],
            'match_terms'    => ['biologi', 'biology', 'kedokteran dasar', 'anatomi', 'lingkungan hidup'],
            'field'          => 'sains_riset',
        ],
        'astronomi_kebumian' => [
            'extra_keywords' => ['astronomi', 'kebumian', 'geografi', 'fisika', 'olimpiade', 'osn', 'sains', 'kir'],
            'match_terms'    => ['astronomi', 'astronomy', 'kebumian', 'earth science', 'geologi', 'meteorologi'],
            'field'          => 'sains_riset',
        ],
        'ekonomi_akuntansi' => [
            'extra_keywords' => ['ekonomi', 'akuntansi', 'kewirausahaan', 'koperasi', 'olimpiade', 'osn', 'kir'],
            'match_terms'    => ['ekonomi', 'economy', 'akuntansi', 'accounting', 'bisnis', 'business plan', 'wirausaha', 'kewirausahaan', 'fiksi', 'koperasi', 'pasar modal', 'perpajakan'],
            'field'          => 'sains_riset',
        ],
        'geografi_sejarah_sosial' => [
            'extra_keywords' => ['geografi', 'sejarah', 'sosiologi', 'ppkn', 'cerdas cermat', 'olimpiade', 'osn', 'kir', 'debat'],
            'match_terms'    => ['geografi', 'geography', 'sejarah', 'sosiologi', 'antropologi', 'ppkn', 'empat pilar', 'cerdas cermat', 'lcc', 'konstitusi'],
            'field'          => 'sains_riset',
        ],
    ];

    /**
     * Cari ekstrakurikuler dan guru pembina yang paling mendekati untuk satu prestasi.
     *
     * @return array{extracurricular_id: int|null, pembina_id: int|null, matched_by: string}
     */
    public static function matchForAchievement(
        ?string $title,
        ?string $eventName = null,
        ?string $description = null,
        ?string $fieldCategory = null,
        ?int $studentId = null,
        ?int $existingExtracurricularId = null,
        ?int $existingPembinaId = null,
        ?Collection $extracurriculars = null
    ): array {
        $extras = $extracurriculars ?? Extracurricular::with(['pembina', 'teachers'])->orderByDesc('is_active')->orderBy('name')->get();

        // 1. Jika sudah ada extracurricular_id, pastikan pembina_id terisi dari ekstra tersebut jika kosong
        if ($existingExtracurricularId) {
            $extra = $extras->firstWhere('id', $existingExtracurricularId);
            if ($extra) {
                $pembinaId = $existingPembinaId ?: static::resolvePembinaFromExtra($extra);
                return [
                    'extracurricular_id' => $extra->id,
                    'pembina_id'         => $pembinaId,
                    'matched_by'         => 'existing_extracurricular',
                ];
            }
        }

        // 2. Jika sudah ada pembina_id tapi belum ada extracurricular_id, coba cari ekstra dari kata kunci lomba dulu,
        //    atau ambil ekstra yang dibina oleh guru tersebut
        if ($existingPembinaId && ! $existingExtracurricularId) {
            $matchedExtra = static::findBestExtracurricularByText($title, $eventName, $description, $fieldCategory, $extras, $existingPembinaId);
            if (! $matchedExtra) {
                $matchedExtra = $extras->first(fn (Extracurricular $e) => $e->isTeacherPembina($existingPembinaId));
            }
            return [
                'extracurricular_id' => $matchedExtra?->id,
                'pembina_id'         => $existingPembinaId,
                'matched_by'         => 'existing_pembina',
            ];
        }

        // 3. Pencocokan kata kunci lomba (judul, nama ajang, deskripsi) ke ekstrakurikuler yang paling mendekati
        $matchedExtra = static::findBestExtracurricularByText($title, $eventName, $description, $fieldCategory, $extras);
        if ($matchedExtra) {
            return [
                'extracurricular_id' => $matchedExtra->id,
                'pembina_id'         => static::resolvePembinaFromExtra($matchedExtra),
                'matched_by'         => 'keyword_match',
            ];
        }

        // 4. Jika dari judul belum ketemu, cek ekstrakurikuler yang diikuti oleh siswa tersebut
        if ($studentId) {
            $studentExtra = static::findStudentExtracurricular($studentId, $extras);
            if ($studentExtra) {
                return [
                    'extracurricular_id' => $studentExtra->id,
                    'pembina_id'         => static::resolvePembinaFromExtra($studentExtra),
                    'matched_by'         => 'student_membership',
                ];
            }
        }

        // 5. Fallback berdasarkan field_category (olahraga, seni_budaya, sains_riset, bahasa_debat, keagamaan, dll.)
        $fallbackExtra = static::findFallbackExtracurricularByField($fieldCategory, $extras);
        if ($fallbackExtra) {
            return [
                'extracurricular_id' => $fallbackExtra->id,
                'pembina_id'         => static::resolvePembinaFromExtra($fallbackExtra),
                'matched_by'         => 'field_category_fallback',
            ];
        }

        // 6. Fallback terakhir: jika tidak ada ekstra di DB namun ada guru pembina / guru aktif
        $fallbackPembinaId = User::where('role', 'guru')->orderBy('id')->value('id');

        return [
            'extracurricular_id' => null,
            'pembina_id'         => $fallbackPembinaId,
            'matched_by'         => 'guru_fallback',
        ];
    }

    /**
     * Cari ekstrakurikuler terbaik berdasarkan teks prestasi (judul, nama event, deskripsi).
     */
    public static function findBestExtracurricularByText(
        ?string $title,
        ?string $eventName,
        ?string $description,
        ?string $fieldCategory,
        Collection $extras,
        ?int $preferredPembinaId = null
    ): ?Extracurricular {
        if ($extras->isEmpty()) {
            return null;
        }

        $haystack = mb_strtolower(trim(implode(' ', array_filter([$title, $eventName, $description]))));
        if ($haystack === '') {
            return null;
        }

        // Tahap A: Cek kecocokan langsung nama ekstrakurikuler di dalam judul/event prestasi
        $bestDirect = null;
        $bestDirectLen = 0;

        foreach ($extras as $extra) {
            $extraName = mb_strtolower(trim($extra->name));
            // Bersihkan awalan umum seperti "ekskul", "ekstrakurikuler", "klub", "club", "seni", "olahraga"
            $cleanExtra = trim((string) preg_replace('/^(ekskul|ekstrakurikuler|ekstra|club|klub|seni|olahraga|cabang)\s+/i', '', $extraName));

            foreach (array_filter([$extraName, $cleanExtra]) as $candidate) {
                if (mb_strlen($candidate) >= 3 && str_contains($haystack, $candidate)) {
                    $score = mb_strlen($candidate) + (static::resolvePembinaFromExtra($extra) ? 10 : 0);
                    if ($preferredPembinaId && $extra->isTeacherPembina($preferredPembinaId)) {
                        $score += 20;
                    }
                    if ($score > $bestDirectLen) {
                        $bestDirectLen = $score;
                        $bestDirect = $extra;
                    }
                }
            }
        }

        if ($bestDirect) {
            return $bestDirect;
        }

        // Tahap B: Cek melalui kamus sinonim lomba -> kandidat ekstrakurikuler terdekat
        foreach (self::KEYWORD_GROUPS as $group) {
            $matchedTerm = false;
            foreach ($group['match_terms'] as $term) {
                if (str_contains($haystack, $term)) {
                    $matchedTerm = true;
                    break;
                }
            }

            if (! $matchedTerm) {
                continue;
            }

            // Cari ekstra yang namanya mengandung salah satu extra_keywords (sesuai urutan prioritas)
            foreach ($group['extra_keywords'] as $extraKw) {
                $found = $extras->first(function (Extracurricular $e) use ($extraKw) {
                    $eName = mb_strtolower($e->name . ' ' . ($e->description ?? ''));
                    return str_contains($eName, $extraKw);
                });

                if ($found) {
                    return $found;
                }
            }
        }

        // Tahap C: Cek token kata (>= 4 huruf) antara judul lomba dan nama ekstra
        $stopWords = ['juara', 'lomba', 'tingkat', 'nasional', 'provinsi', 'kabupaten', 'sekolah', 'internasional', 'piala', 'kejuaraan', 'festival', 'olimpiade', 'pekan', 'siswa', 'sman', 'gianyar', 'bali', 'tahun', 'putra', 'putri', 'beregu', 'individu', 'medali', 'emas', 'perak', 'perunggu', 'harapan', 'peserta', 'finalis', 'terbaik', 'umum', 'open', 'tournament', 'turnamen', 'competition', 'championship'];
        $words = preg_split('/[^a-z0-9]+/u', $haystack, -1, PREG_SPLIT_NO_EMPTY);
        $meaningfulWords = array_values(array_filter($words, fn ($w) => mb_strlen($w) >= 4 && ! in_array($w, $stopWords, true)));

        foreach ($meaningfulWords as $word) {
            $found = $extras->first(fn (Extracurricular $e) => str_contains(mb_strtolower($e->name), $word));
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    /**
     * Cari ekstrakurikuler yang diikuti oleh siswa.
     */
    public static function findStudentExtracurricular(int $studentId, Collection $extras): ?Extracurricular
    {
        if (Schema::hasTable('extracurricular_members')) {
            $memberExtraIds = ExtracurricularMember::where('user_id', $studentId)
                ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                ->pluck('extracurricular_id');

            foreach ($memberExtraIds as $extraId) {
                $extra = $extras->firstWhere('id', $extraId);
                if ($extra) {
                    return $extra;
                }
            }
        }

        if (Schema::hasTable('extracurricular_students')) {
            $studentExtraIds = DB::table('extracurricular_students')
                ->where('student_id', $studentId)
                ->pluck('extracurricular_id');

            foreach ($studentExtraIds as $extraId) {
                $extra = $extras->firstWhere('id', $extraId);
                if ($extra) {
                    return $extra;
                }
            }
        }

        return null;
    }

    /**
     * Cari ekstrakurikuler terdekat berdasarkan field_category jika judul/anggota tidak spesifik.
     */
    public static function findFallbackExtracurricularByField(?string $fieldCategory, Collection $extras): ?Extracurricular
    {
        if ($extras->isEmpty()) {
            return null;
        }

        $categoryHints = match ($fieldCategory) {
            'olahraga'     => ['olahraga', 'atletik', 'basket', 'futsal', 'voli', 'silat', 'karate', 'taekwondo', 'bulutangkis', 'catur', 'renang', 'panahan'],
            'seni_budaya'  => ['tari', 'musik', 'tabuh', 'karawitan', 'vokal', 'paduan suara', 'teater', 'lukis', 'seni', 'jurnalistik'],
            'sains_riset'  => ['kir', 'karya ilmiah', 'riset', 'sains', 'matematika', 'fisika', 'kimia', 'biologi', 'ekonomi', 'geografi', 'informatika', 'olimpiade'],
            'bahasa_debat' => ['debat', 'english', 'bahasa', 'jurnalistik', 'sastra', 'jepang', 'bali'],
            'keagamaan'    => ['dharma', 'sloka', 'pesantian', 'agama', 'rohani', 'rohis', 'bahasa bali', 'tari'],
            'teknologi'    => ['komputer', 'informatika', 'tik', 'robotik', 'coding', 'multimedia', 'jurnalistik', 'kir'],
            'organisasi'   => ['pramuka', 'pmr', 'paskibra', 'uks', 'osis'],
            default        => ['kir', 'pramuka', 'olahraga', 'seni', 'tari'],
        };

        foreach ($categoryHints as $hint) {
            $found = $extras->first(function (Extracurricular $e) use ($hint) {
                return str_contains(mb_strtolower($e->name . ' ' . ($e->category ?? '')), $hint)
                    && static::resolvePembinaFromExtra($e) !== null;
            });
            if ($found) {
                return $found;
            }
        }

        // Ambil ekstra pertama yang memiliki guru pembina
        $withPembina = $extras->first(fn (Extracurricular $e) => static::resolvePembinaFromExtra($e) !== null);

        return $withPembina ?: $extras->first();
    }

    /**
     * Ambil ID guru pembina utama dari suatu ekstrakurikuler.
     */
    public static function resolvePembinaFromExtra(?Extracurricular $extra): ?int
    {
        if (! $extra) {
            return null;
        }

        if ($extra->pembina_id) {
            return (int) $extra->pembina_id;
        }

        if ($extra->relationLoaded('teachers')) {
            $firstTeacher = $extra->teachers->first();
            if ($firstTeacher) {
                return (int) $firstTeacher->id;
            }
        } else {
            $teacherId = $extra->teachers()->value('users.id');
            if ($teacherId) {
                return (int) $teacherId;
            }
        }

        return null;
    }

    /**
     * Gabungkan dan bersihkan duplikasi data lomba (baik beregu yang diinput berulang kali
     * dengan team_code/variasi judul berbeda, maupun lomba individu yang terinput ganda oleh siswa yang sama).
     *
     * @return array{merged_groups: int, unified_rows: int, deleted_duplicates: int}
     */
    public static function deduplicateCompetitions(): array
    {
        if (! Schema::hasTable('student_achievements')) {
            return ['merged_groups' => 0, 'unified_rows' => 0, 'deleted_duplicates' => 0];
        }

        $all = StudentAchievement::orderBy('id')->get();
        $groups = $all->groupBy(fn (StudentAchievement $a) => $a->competitionGroupKey());

        $mergedGroups = 0;
        $unifiedRows = 0;
        $deletedDuplicates = 0;

        foreach ($groups as $key => $group) {
            if ($group->count() <= 1) {
                continue;
            }

            $isBereguGroup = str_starts_with($key, 'beregu:');

            // Pilih judul kanonik terbaik (prioritaskan mixed-case dibanding HURUF KAPITAL SEMUA)
            $canonicalTitle = $group->pluck('title')->filter()->sortByDesc(function (string $t) {
                $isMixedCase = ($t !== mb_strtoupper($t) && $t !== mb_strtolower($t)) ? 1000 : 0;
                return $isMixedCase + mb_strlen($t);
            })->first() ?? $group->first()->title;

            // Pilih penyelenggara kanonik terbaik
            $canonicalOrganizer = $group->pluck('organizer')->filter()->sortByDesc(function (string $o) {
                $isMixedCase = ($o !== mb_strtoupper($o) && $o !== mb_strtolower($o)) ? 1000 : 0;
                return $isMixedCase + mb_strlen($o);
            })->first() ?? $group->first()->organizer;

            // Pilih format rank kanonik terbaik (prioritaskan yang mengandung kata "Juara" / "Medali" dibanding angka saja)
            $canonicalRank = $group->pluck('rank')->filter()->sortByDesc(function (string $r) {
                $hasPrefix = preg_match('/(juara|medali|harapan|finalis)/i', $r) ? 1000 : 0;
                $isMixedCase = ($r !== mb_strtoupper($r)) ? 100 : 0;
                return $hasPrefix + $isMixedCase + mb_strlen($r);
            })->first() ?? $group->first()->rank;

            $canonicalExtraId = $group->pluck('extracurricular_id')->filter()->first();
            $canonicalPembinaId = $group->pluck('pembina_id')->filter()->first();

            $canonicalTeamCode = null;
            if ($isBereguGroup) {
                $canonicalTeamCode = $group->pluck('team_code')->filter()->first()
                    ?: ('TEAM-' . strtoupper(substr(md5($key), 0, 8)));
            }

            $distinctTeamCodes = $group->pluck('team_code')->unique()->count();
            $distinctTitles = $group->pluck('title')->unique()->count();
            if ($distinctTeamCodes > 1 || $distinctTitles > 1 || $group->count() > $group->pluck('student_id')->unique()->count()) {
                $mergedGroups++;
            }

            // Kelompokkan per student_id agar 1 siswa hanya punya maksimal 1 baris pada lomba yang sama
            $byStudent = $group->groupBy('student_id');

            foreach ($byStudent as $studentId => $studentRows) {
                /** @var StudentAchievement $primary */
                $primary = $studentRows->first();

                // Jika baris duplikat punya foto/sertifikat/file yang lebih lengkap, wariskan ke baris utama
                $bestCert = $studentRows->pluck('certificate_path')->filter()->first();
                $bestDoc  = $studentRows->pluck('documentation_path')->filter()->first();
                $bestDisp = $studentRows->pluck('winner_display_photo')->filter()->first();

                $updates = [];
                if ($canonicalTitle && $primary->title !== $canonicalTitle) {
                    $updates['title'] = $canonicalTitle;
                }
                if ($canonicalOrganizer && $primary->organizer !== $canonicalOrganizer) {
                    $updates['organizer'] = $canonicalOrganizer;
                }
                if ($canonicalRank && $primary->rank !== $canonicalRank) {
                    $updates['rank'] = $canonicalRank;
                }
                if ($isBereguGroup && $canonicalTeamCode && $primary->team_code !== $canonicalTeamCode) {
                    $updates['team_code'] = $canonicalTeamCode;
                }
                if ($isBereguGroup && $primary->participation_type !== 'beregu') {
                    $updates['participation_type'] = 'beregu';
                }
                if ($canonicalExtraId && ! $primary->extracurricular_id) {
                    $updates['extracurricular_id'] = $canonicalExtraId;
                }
                if ($canonicalPembinaId && ! $primary->pembina_id) {
                    $updates['pembina_id'] = $canonicalPembinaId;
                }
                if ($bestCert && ! $primary->certificate_path) {
                    $updates['certificate_path'] = $bestCert;
                }
                if ($bestDoc && ! $primary->documentation_path) {
                    $updates['documentation_path'] = $bestDoc;
                }
                if ($bestDisp && ! $primary->winner_display_photo) {
                    $updates['winner_display_photo'] = $bestDisp;
                }

                if (! empty($updates)) {
                    $primary->updateQuietly($updates);
                    $unifiedRows++;
                }

                // Hapus baris ganda untuk siswa yang sama pada lomba yang sama
                if ($studentRows->count() > 1) {
                    foreach ($studentRows->slice(1) as $duplicateRow) {
                        $duplicateRow->delete();
                        $deletedDuplicates++;
                    }
                }
            }
        }

        return [
            'merged_groups'      => $mergedGroups,
            'unified_rows'       => $unifiedRows,
            'deleted_duplicates' => $deletedDuplicates,
        ];
    }

    /**
     * Sinkronkan semua data prestasi siswa yang belum memiliki ekstrakurikuler atau guru pembina.
     *
     * @param  bool  $overwriteExisting  Jika true, perbarui juga yang sudah ada namun salah satunya masih kosong.
     * @return int   Jumlah prestasi yang berhasil diperbarui.
     */
    public static function syncAllAchievements(bool $overwriteExisting = false): int
    {
        if (! Schema::hasTable('student_achievements') || ! Schema::hasColumn('student_achievements', 'pembina_id')) {
            return 0;
        }

        // Bersihkan & satukan terlebih dahulu lomba beregu/individu yang terduplikasi
        $dedupStats = static::deduplicateCompetitions();

        $extras = Schema::hasTable('extracurriculars')
            ? Extracurricular::with(['pembina', 'teachers'])->orderByDesc('is_active')->orderBy('name')->get()
            : collect();

        $query = StudentAchievement::query();
        if (! $overwriteExisting) {
            $query->where(function ($q) {
                $q->whereNull('pembina_id')
                  ->orWhereNull('extracurricular_id');
            });
        }

        $achievements = $query->orderBy('id')->get();
        $updatedCount = 0;
        $teamCache = [];

        foreach ($achievements as $achievement) {
            // Jika beregu dengan team_code yang sama sudah diputuskan, gunakan hasil yang sama agar konsisten
            if ($achievement->participation_type === 'beregu' && ! empty($achievement->team_code)) {
                if (isset($teamCache[$achievement->team_code])) {
                    $cached = $teamCache[$achievement->team_code];
                    $newExtraId   = $achievement->extracurricular_id ?: $cached['extracurricular_id'];
                    $newPembinaId = $achievement->pembina_id ?: $cached['pembina_id'];

                    if ($newExtraId !== $achievement->extracurricular_id || $newPembinaId !== $achievement->pembina_id) {
                        $achievement->updateQuietly([
                            'extracurricular_id' => $newExtraId,
                            'pembina_id'         => $newPembinaId,
                        ]);
                        $updatedCount++;
                    }
                    continue;
                }

                // Cek apakah ada rekan satu tim yang sudah punya pembina_id / extracurricular_id
                $teammateWithData = StudentAchievement::where('team_code', $achievement->team_code)
                    ->where(function ($q) {
                        $q->whereNotNull('pembina_id')->orWhereNotNull('extracurricular_id');
                    })
                    ->first();

                if ($teammateWithData && ($teammateWithData->pembina_id || $teammateWithData->extracurricular_id)) {
                    $matched = static::matchForAchievement(
                        title: $achievement->title,
                        eventName: $achievement->event_name,
                        description: $achievement->description,
                        fieldCategory: $achievement->field_category,
                        studentId: $achievement->student_id,
                        existingExtracurricularId: $achievement->extracurricular_id ?: $teammateWithData->extracurricular_id,
                        existingPembinaId: $achievement->pembina_id ?: $teammateWithData->pembina_id,
                        extracurriculars: $extras
                    );

                    $teamCache[$achievement->team_code] = $matched;
                    $achievement->updateQuietly([
                        'extracurricular_id' => $matched['extracurricular_id'],
                        'pembina_id'         => $matched['pembina_id'],
                    ]);
                    $updatedCount++;
                    continue;
                }
            }

            $matched = static::matchForAchievement(
                title: $achievement->title,
                eventName: $achievement->event_name,
                description: $achievement->description,
                fieldCategory: $achievement->field_category,
                studentId: $achievement->student_id,
                existingExtracurricularId: $achievement->extracurricular_id,
                existingPembinaId: $achievement->pembina_id,
                extracurriculars: $extras
            );

            $newExtraId   = $achievement->extracurricular_id ?: $matched['extracurricular_id'];
            $newPembinaId = $achievement->pembina_id ?: $matched['pembina_id'];

            if ($achievement->participation_type === 'beregu' && ! empty($achievement->team_code)) {
                $teamCache[$achievement->team_code] = [
                    'extracurricular_id' => $newExtraId,
                    'pembina_id'         => $newPembinaId,
                ];
            }

            if ($newExtraId !== $achievement->extracurricular_id || $newPembinaId !== $achievement->pembina_id) {
                $achievement->updateQuietly([
                    'extracurricular_id' => $newExtraId,
                    'pembina_id'         => $newPembinaId,
                ]);
                $updatedCount++;
            }
        }

        return $updatedCount;
    }
}

