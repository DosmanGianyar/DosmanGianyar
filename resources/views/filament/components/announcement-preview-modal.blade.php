<div style="font-family: inherit; width: 100%; box-sizing: border-box;">
    {{-- Header Info & Badges --}}
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 16px; padding: 10px 14px; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; font-size: 12px;">
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            {{-- Target Audience --}}
            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 9999px; font-weight: 600; font-size: 11px;
                @if($announcement->target === 'siswa') background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe;
                @elseif($announcement->target === 'guru') background: #fef3c7; color: #92400e; border: 1px solid #fde68a;
                @else background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; @endif">
                👥 Target:
                @if($announcement->target === 'siswa') Khusus Siswa
                @elseif($announcement->target === 'guru') Khusus Guru
                @else Semua Pengguna @endif
            </span>

            {{-- Status Aktif --}}
            @if($announcement->is_active)
                <span style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                    <span style="width: 7px; height: 7px; border-radius: 50%; background: #10b981; display: inline-block;"></span> Aktif
                </span>
            @else
                <span style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; background: #fff1f2; color: #be123c; border: 1px solid #fecdd3;">
                    <span style="width: 7px; height: 7px; border-radius: 50%; background: #f43f5e; display: inline-block;"></span> Nonaktif
                </span>
            @endif

            {{-- Pop-Up Status --}}
            @if($announcement->show_as_modal)
                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; background: #faf5ff; color: #6b21a8; border: 1px solid #e9d5ff;">
                    ✨ Pop-Up Modal (15s)
                </span>
            @endif

            {{-- Pinned --}}
            @if($announcement->is_pinned)
                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; background: #fefce8; color: #854d0e; border: 1px solid #fef08a;">
                    📌 Pinned
                </span>
            @endif
        </div>

        <div>
            @if(Route::has('siswa.humas.announcements.show'))
                <a href="{{ route('siswa.humas.announcements.show', $announcement) }}" target="_blank"
                   style="display: inline-flex; align-items: center; gap: 5px; color: #3b82f6; font-weight: 600; font-size: 12px; text-decoration: none;">
                    <span>Lihat Halaman Siswa</span>
                    <svg style="width: 14px; height: 14px; min-width: 14px; max-width: 14px; display: inline-block; vertical-align: middle;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            @endif
        </div>
    </div>

    {{-- Frame Simulasi Pop-Up Modal --}}
    <div style="background: #0b1120; border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 20px; padding: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4);">
        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
            <span style="display: flex; align-items: center; gap: 6px;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #3b82f6; display: inline-block;"></span>
                Simulasi Pop-Up Modal di Aplikasi / Web
            </span>
            <span style="font-size: 10px; font-weight: normal; color: #64748b; text-transform: none;">Auto-close 15 detik</span>
        </div>

        {{-- Kartu Modal Siswa --}}
        <div style="background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.2); max-width: 540px; margin: 0 auto; color: #0f172a;">
            
            {{-- Header Gradien Modal --}}
            <div style="position: relative; background: linear-gradient(135deg, #1e3a8a 0%, #312e81 50%, #0f172a 100%); color: #ffffff; padding: 14px 18px; overflow: hidden;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                        <div style="width: 38px; height: 38px; border-radius: 12px; background: rgba(255, 255, 255, 0.15); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; border: 1px solid rgba(255, 255, 255, 0.2);">
                            📢
                        </div>
                        <div style="min-width: 0;">
                            <div style="font-size: 10px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: #bfdbfe;">
                                Pengumuman Sekolah
                            </div>
                            <div style="font-size: 14px; font-weight: 700; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                SMAN 1 Gianyar
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                        <div style="background: #fbbf24; color: #451a03; font-size: 11px; font-weight: 900; padding: 3px 8px; border-radius: 9999px; display: flex; align-items: center; gap: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                            <span>⏳</span>
                            <span style="font-family: monospace;">15</span>s
                        </div>
                        <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(255, 255, 255, 0.15); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; cursor: pointer;">
                            ✕
                        </div>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: rgba(255, 255, 255, 0.15); overflow: hidden;">
                    <div style="height: 100%; width: 70%; background: linear-gradient(90deg, #fbbf24, #fde68a);"></div>
                </div>
            </div>

            {{-- Isi Modal --}}
            <div style="padding: 16px 18px; max-height: 400px; overflow-y: auto; background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);">
                {{-- Foto Poster --}}
                @if($announcement->image)
                    <div style="background: #020617; border-radius: 14px; overflow: hidden; margin-bottom: 14px; display: flex; justify-content: center; align-items: center; border: 1px solid #e2e8f0; min-height: 140px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                        <img src="{{ $announcement->image_url }}" alt="{{ $announcement->title }}"
                             style="max-height: 260px; width: auto; max-width: 100%; object-fit: contain; display: block;">
                    </div>
                @else
                    <div style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 14px; text-align: center; margin-bottom: 14px;">
                        <span style="font-size: 24px;">🖼️</span>
                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Tidak ada gambar poster diunggah</div>
                    </div>
                @endif

                {{-- Meta --}}
                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 8px;">
                    @if($announcement->is_pinned)
                        <span style="background: #fef3c7; color: #92400e; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 9999px; border: 1px solid #fde68a;">
                            📌 Pinned
                        </span>
                    @endif
                    <span style="background: #eff6ff; color: #1d4ed8; font-size: 10px; font-weight: 600; padding: 2px 7px; border-radius: 9999px; border: 1px solid #dbeafe;">
                        🗓️ {{ $announcement->published_at?->isoFormat('D MMMM Y, HH:mm') ?? now()->isoFormat('D MMMM Y, HH:mm') }} WITA
                    </span>
                    @if($announcement->author)
                        <span style="color: #64748b; font-size: 10px; font-weight: 500;">
                            • Oleh: {{ $announcement->author->name }}
                        </span>
                    @endif
                </div>

                {{-- Judul --}}
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 10px 0; line-height: 1.35;">
                    {{ $announcement->title }}
                </h3>

                {{-- Deskripsi / Teks --}}
                <div style="font-size: 13px; color: #334155; line-height: 1.6; white-space: pre-line; background: #ffffff; padding: 12px 14px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                    {!! nl2br(e($announcement->body)) !!}
                </div>
            </div>

            {{-- Footer Modal --}}
            <div style="padding: 12px 18px; background: #ffffff; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                <span style="font-size: 12px; font-weight: 700; color: #2563eb;">
                    Daftar Pengumuman →
                </span>
                <span style="display: inline-block; padding: 6px 14px; background: linear-gradient(135deg, #2563eb, #4f46e5); color: #ffffff; border-radius: 10px; font-size: 12px; font-weight: 700; box-shadow: 0 2px 4px rgba(37,99,235,0.3);">
                    Mengerti & Tutup
                </span>
            </div>

        </div>
    </div>
</div>
