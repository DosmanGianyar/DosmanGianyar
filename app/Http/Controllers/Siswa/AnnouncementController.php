<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::published()
            ->forRole('siswa')
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('siswa.announcements.index', compact('announcements'));
    }

    public function show(Announcement $announcement): View
    {
        abort_unless($announcement->isPublished(), 404);

        return view('siswa.announcements.show', compact('announcement'));
    }

    // ─── Pengelola / Humas only ───────────────────────────────────────────────

    public function manageIndex(): View
    {
        $this->checkPengelola();
        $announcements = Announcement::where('author_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('siswa.announcements.manage', compact('announcements'));
    }

    public function create(): View
    {
        $this->checkPengelola();
        $classes = SchoolClass::orderBy('name')->get();
        return view('siswa.announcements.form', ['announcement' => null, 'classes' => $classes]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkPengelola();

        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'body'             => 'required|string',
            'target'           => 'required|in:all,siswa,guru,orangtua',
            'target_class_ids' => 'nullable|array',
            'target_class_ids.*' => 'integer|exists:classes,id',
            'is_pinned'        => 'boolean',
            'published_at'     => 'nullable|date',
        ]);

        $data['author_id'] = Auth::id();
        $data['is_pinned'] = $request->boolean('is_pinned');

        if (empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        Announcement::create($data);

        return redirect()->route('siswa.announcements.manage')
            ->with('success', 'Pengumuman berhasil dipublikasikan.');
    }

    public function edit(Announcement $announcement): View
    {
        $this->checkPengelola();
        abort_unless($announcement->author_id === Auth::id(), 403);
        $classes = SchoolClass::orderBy('name')->get();
        return view('siswa.announcements.form', compact('announcement', 'classes'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->checkPengelola();
        abort_unless($announcement->author_id === Auth::id(), 403);

        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'body'             => 'required|string',
            'target'           => 'required|in:all,siswa,guru,orangtua',
            'target_class_ids' => 'nullable|array',
            'target_class_ids.*' => 'integer|exists:classes,id',
            'is_pinned'        => 'boolean',
            'published_at'     => 'nullable|date',
        ]);

        $data['is_pinned'] = $request->boolean('is_pinned');

        $announcement->update($data);

        return redirect()->route('siswa.announcements.manage')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->checkPengelola();
        abort_unless($announcement->author_id === Auth::id(), 403);

        $announcement->delete();

        return redirect()->route('siswa.announcements.manage')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }

    private function checkPengelola(): void
    {
        abort_unless(in_array(Auth::user()->role, ['pengelola', 'admin']), 403);
    }
}
