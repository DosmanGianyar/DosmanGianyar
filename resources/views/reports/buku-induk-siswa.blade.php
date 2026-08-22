<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Induk Siswa - {{ count($students) === 1 ? $students->first()->name : 'Per Kelas' }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 20mm 15mm 20mm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .page-container {
            position: relative;
            box-sizing: border-box;
            page-break-after: always;
            padding-bottom: 20px;
        }

        .page-container:last-child {
            page-break-after: avoid;
        }

        .header-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
            margin-top: 10px;
            letter-spacing: 0.5px;
        }

        .top-meta {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .nis-box {
            font-size: 11pt;
            font-weight: normal;
        }

        .photo-box {
            width: 3cm;
            height: 4cm;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 9pt;
            padding: 4px;
            box-sizing: border-box;
        }

        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .section-header {
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 4px;
            font-size: 11pt;
        }

        .field-row {
            display: flex;
            margin-bottom: 3px;
        }

        .field-num {
            width: 30px;
            text-align: right;
            padding-right: 8px;
            flex-shrink: 0;
        }

        .field-label {
            width: 250px;
            flex-shrink: 0;
        }

        .sub-label {
            padding-left: 20px;
            width: 230px;
            flex-shrink: 0;
        }

        .field-colon {
            width: 15px;
            flex-shrink: 0;
        }

        .field-value {
            flex-grow: 1;
            border-bottom: 1px dotted #555;
            min-height: 18px;
            padding-left: 4px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }

        .print-btn-bar {
            background: #1e293b;
            color: white;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .btn-print {
            background: #2563eb;
            color: white;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-print:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>

    <div class="print-btn-bar no-print">
        <div>
            <strong>Pratinjau Buku Induk Siswa</strong>
            <span style="opacity: 0.8; margin-left: 10px;">({{ count($students) }} siswa)</span>
        </div>
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
    </div>

    @foreach($students as $siswa)
    <div class="page-container">

        <div class="top-meta">
            <div class="nis-box">
                <strong>No. Induk Siswa (NIS) :</strong> {{ $siswa->nis ?: '........................' }}<br>
                <strong>NISN :</strong> {{ $siswa->nisn ?: '........................' }}
            </div>

            <div class="photo-box">
                @if($siswa->photo)
                    <img src="{{ asset('storage/' . $siswa->photo) }}" alt="Foto Siswa">
                @else
                    Pas Foto<br>3 cm x 4 cm<br>waktu diterima di sekolah ini
                @endif
            </div>
        </div>

        <div class="header-title">II. DATA PRIBADI SISWA</div>

        <!-- A. KETERANGAN TENTANG DIRI SISWA -->
        <div class="section-header">A. KETERANGAN TENTANG DIRI SISWA</div>

        <div class="field-row">
            <div class="field-num">1.</div>
            <div class="field-label">Nama Siswa</div>
            <div class="field-colon"></div>
            <div class="field-value"></div>
        </div>
        <div class="field-row">
            <div class="field-num"></div>
            <div class="sub-label">a. Nama Lengkap</div>
            <div class="field-colon">:</div>
            <div class="field-value"><strong>{{ $siswa->name }}</strong></div>
        </div>
        <div class="field-row">
            <div class="field-num"></div>
            <div class="sub-label">b. Nama Panggilan</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->nickname ?: '-' }}</div>
        </div>

        <div class="field-row">
            <div class="field-num">2.</div>
            <div class="field-label">Jenis Kelamin</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->gender === 'L' ? 'Laki-laki' : ($siswa->gender === 'P' ? 'Perempuan' : '-') }}</div>
        </div>

        <div class="field-row">
            <div class="field-num">3.</div>
            <div class="field-label">Tempat dan tanggal lahir</div>
            <div class="field-colon">:</div>
            <div class="field-value">
                {{ $siswa->birth_place ? $siswa->birth_place . ', ' : '' }}{{ $siswa->birth_date ? $siswa->birth_date->translatedFormat('d F Y') : '-' }}
            </div>
        </div>

        <div class="field-row">
            <div class="field-num">4.</div>
            <div class="field-label">Agama</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->religion ?: '-' }}</div>
        </div>

        <div class="field-row">
            <div class="field-num">5.</div>
            <div class="field-label">Kewarganegaraan</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->citizenship ?: 'WNI' }}</div>
        </div>

        <div class="field-row">
            <div class="field-num">6.</div>
            <div class="field-label">Anak keberapa</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->child_order ?: '-' }}</div>
        </div>

        <div class="field-row">
            <div class="field-num">7.</div>
            <div class="field-label">Jumlah saudara kandung</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->siblings_count !== null ? $siswa->siblings_count : '-' }}</div>
        </div>

        <div class="field-row">
            <div class="field-num">8.</div>
            <div class="field-label">Jumlah saudara Tiri</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->step_siblings_count !== null ? $siswa->step_siblings_count : '-' }}</div>
        </div>

        <div class="field-row">
            <div class="field-num">9.</div>
            <div class="field-label">Jumlah saudara angkat</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->foster_siblings_count !== null ? $siswa->foster_siblings_count : '-' }}</div>
        </div>

        <div class="field-row">
            <div class="field-num">10.</div>
            <div class="field-label">Anak Yatim / Yatim Piatu</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->orphan_status ?: '-' }}</div>
        </div>

        <div class="field-row">
            <div class="field-num">11.</div>
            <div class="field-label">Bahasa sehari-sehari dirumah</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->daily_language ?: '-' }}</div>
        </div>

        <!-- B. KETERANGAN TEMPAT TINGGAL -->
        <div class="section-header">B. KETERANGAN TEMPAT TINGGAL</div>

        <div class="field-row">
            <div class="field-num">12.</div>
            <div class="field-label">Alamat</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->address ?: '-' }}</div>
        </div>

        <div class="field-row">
            <div class="field-num">13.</div>
            <div class="field-label">Nomor Telepon</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->phone ?: '-' }}</div>
        </div>

        <div class="field-row">
            <div class="field-num">14.</div>
            <div class="field-label">Tinggal dengan orang tua / saudara / di Asrama / Kost</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->living_with ?: $siswa->residence_status ?: '-' }}</div>
        </div>

        <div class="field-row">
            <div class="field-num">15.</div>
            <div class="field-label">Jarak tempat tinggal ke Sekolah</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->distance_km ? $siswa->distance_km . ' km' : '-' }}</div>
        </div>

        <!-- C. KETERANGAN KESEHATAN -->
        <div class="section-header">C. KETERANGAN KESEHATAN</div>

        <div class="field-row">
            <div class="field-num">16.</div>
            <div class="field-label">Golongan Darah</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->blood_type ?: '-' }}</div>
        </div>

        <div class="field-row">
            <div class="field-num">17.</div>
            <div class="field-label">Penyakit yang pernah diderita (TBC / Cacar / Malaria / dll)</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->medical_history ?: '-' }}</div>
        </div>

        <div class="field-row">
            <div class="field-num">18.</div>
            <div class="field-label">Kelainan Jasmani</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->physical_disability ?: '-' }}</div>
        </div>

        <div class="field-row">
            <div class="field-num">19.</div>
            <div class="field-label">Tinggi dan berat badan</div>
            <div class="field-colon">:</div>
            <div class="field-value">
                {{ $siswa->height_cm ? $siswa->height_cm . ' cm' : '-' }} dan {{ $siswa->weight_kg ? $siswa->weight_kg . ' kg' : '-' }}
            </div>
        </div>

        <!-- D. KETERANGAN PENDIDIKAN -->
        <div class="section-header">D. KETERANGAN PENDIDIKAN</div>

        <div class="field-row">
            <div class="field-num">20.</div>
            <div class="field-label">Pendidikan sebelumnya</div>
            <div class="field-colon"></div>
            <div class="field-value"></div>
        </div>
        <div class="field-row">
            <div class="field-num"></div>
            <div class="sub-label">a. Lulusan dari</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->prev_school_name ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num"></div>
            <div class="sub-label">b. Tanggal dan Nomor STTB</div>
            <div class="field-colon">:</div>
            <div class="field-value">
                {{ $siswa->prev_sttb_date ? $siswa->prev_sttb_date->format('d/m/Y') : '' }}{{ $siswa->prev_sttb_no ? ' / ' . $siswa->prev_sttb_no : '-' }}
            </div>
        </div>
        <div class="field-row">
            <div class="field-num"></div>
            <div class="sub-label">c. Lama Belajar</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->prev_study_duration ?: '-' }}</div>
        </div>

        <div class="field-row">
            <div class="field-num">21.</div>
            <div class="field-label">Pindahan</div>
            <div class="field-colon"></div>
            <div class="field-value"></div>
        </div>
        <div class="field-row">
            <div class="field-num"></div>
            <div class="sub-label">a. Dari sekolah</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->transfer_from_school ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num"></div>
            <div class="sub-label">b. Alasan</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->transfer_reason ?: '-' }}</div>
        </div>

        <div class="field-row">
            <div class="field-num">22.</div>
            <div class="field-label">Diterima di sekolah ini</div>
            <div class="field-colon"></div>
            <div class="field-value"></div>
        </div>
        <div class="field-row">
            <div class="field-num"></div>
            <div class="sub-label">a. Di Tingkat</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->admission_grade ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num"></div>
            <div class="sub-label">b. Kelompok</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->admission_class_group ?: $siswa->schoolClass?->name ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num"></div>
            <div class="sub-label">c. Jurusan</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->admission_major ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num"></div>
            <div class="sub-label">d. Tanggal</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->admission_date ? $siswa->admission_date->translatedFormat('d F Y') : '-' }}</div>
        </div>

        <!-- E. KETERANGAN TENTANG AYAH KANDUNG -->
        <div class="section-header">E. KETERANGAN TENTANG AYAH KANDUNG</div>

        <div class="field-row">
            <div class="field-num">23.</div>
            <div class="field-label">Nama</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->father_name ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">24.</div>
            <div class="field-label">Tempat dan tanggal lahir</div>
            <div class="field-colon">:</div>
            <div class="field-value">
                {{ $siswa->father_birth_place ? $siswa->father_birth_place . ', ' : '' }}{{ $siswa->father_birth_date ? $siswa->father_birth_date->translatedFormat('d F Y') : '-' }}
            </div>
        </div>
        <div class="field-row">
            <div class="field-num">25.</div>
            <div class="field-label">Agama</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->father_religion ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">26.</div>
            <div class="field-label">Kewarganegaraan</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->father_citizenship ?: 'WNI' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">27.</div>
            <div class="field-label">Pendidikan</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->father_education ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">28.</div>
            <div class="field-label">Pekerjaan</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->father_job ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">29.</div>
            <div class="field-label">Penghasilan per bulan</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->father_income ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">30.</div>
            <div class="field-label">Alamat Lengkap</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->father_address ?: $siswa->address ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">31.</div>
            <div class="field-label">Nomor Telepon</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->father_phone ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">32.</div>
            <div class="field-label">Masih Hidup / meninggal dunia tahun</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->father_status ?: '-' }}</div>
        </div>

        <!-- F. KETERANGAN TENTANG IBU KANDUNG -->
        <div class="section-header">F. KETERANGAN TENTANG IBU KANDUNG</div>

        <div class="field-row">
            <div class="field-num">33.</div>
            <div class="field-label">Nama</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->mother_name ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">34.</div>
            <div class="field-label">Tempat dan tanggal lahir</div>
            <div class="field-colon">:</div>
            <div class="field-value">
                {{ $siswa->mother_birth_place ? $siswa->mother_birth_place . ', ' : '' }}{{ $siswa->mother_birth_date ? $siswa->mother_birth_date->translatedFormat('d F Y') : '-' }}
            </div>
        </div>
        <div class="field-row">
            <div class="field-num">35.</div>
            <div class="field-label">Agama</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->mother_religion ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">36.</div>
            <div class="field-label">Kewarganegaraan</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->mother_citizenship ?: 'WNI' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">37.</div>
            <div class="field-label">Pendidikan</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->mother_education ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">38.</div>
            <div class="field-label">Pekerjaan</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->mother_job ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">39.</div>
            <div class="field-label">Penghasilan per bulan</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->mother_income ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">40.</div>
            <div class="field-label">Alamat Lengkap</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->mother_address ?: $siswa->address ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">41.</div>
            <div class="field-label">Nomor Telepon</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->mother_phone ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">42.</div>
            <div class="field-label">Masih Hidup / meninggal dunia tahun</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->mother_status ?: '-' }}</div>
        </div>

        <!-- G. KETERANGAN TENTANG WALI -->
        <div class="section-header">G. KETERANGAN TENTANG WALI</div>

        <div class="field-row">
            <div class="field-num">43.</div>
            <div class="field-label">Nama</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->guardian_name ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">44.</div>
            <div class="field-label">Tempat dan tanggal lahir</div>
            <div class="field-colon">:</div>
            <div class="field-value">
                {{ $siswa->guardian_birth_place ? $siswa->guardian_birth_place . ', ' : '' }}{{ $siswa->guardian_birth_date ? $siswa->guardian_birth_date->translatedFormat('d F Y') : '-' }}
            </div>
        </div>
        <div class="field-row">
            <div class="field-num">45.</div>
            <div class="field-label">Agama</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->guardian_religion ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">46.</div>
            <div class="field-label">Kewarganegaraan</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->guardian_citizenship ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">47.</div>
            <div class="field-label">Pendidikan</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->guardian_education ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">48.</div>
            <div class="field-label">Pekerjaan</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->guardian_job ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">49.</div>
            <div class="field-label">Penghasilan per bulan</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->guardian_income ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">50.</div>
            <div class="field-label">Alamat Lengkap</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->guardian_address ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">51.</div>
            <div class="field-label">Nomor Telepon</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->guardian_phone ?: '-' }}</div>
        </div>
        <div class="field-row">
            <div class="field-num">52.</div>
            <div class="field-label">Hubungan dengan siswa</div>
            <div class="field-colon">:</div>
            <div class="field-value">{{ $siswa->guardian_relation ?: '-' }}</div>
        </div>

    </div>
    @endforeach

</body>
</html>
