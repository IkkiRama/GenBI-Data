<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Models\Komentar;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Image;
use Mews\Purifier\Facades\Purifier;

class DashboardController extends Controller
{
    public function getArtikel(Request $request)
    {
        try {
            $query = Artikel::query()
                ->select([
                    'id',
                    'title',
                    'slug',
                    'kategori_id',
                    'views',
                    'is_published',
                    'status',
                    'created_at'
                ])
                ->with([
                    'kategori_artikel:id,nama'
                ])
                ->where('author_id', auth()->id());

            // FILTER STATUS
            if ($request->status === 'published') {
                $query->where('is_published', 1);
            } elseif ($request->status === 'pending') {
                $query->where('is_published', 0)
                    ->where('status', 'pending');
            } elseif ($request->status === 'draft') {
                $query->where('status', 'draft');
            }

            // SEARCH
            if ($request->search) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            $artikels = $query
                ->latest()
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $artikels->through(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'slug' => $item->slug,
                        'views' => $item->views,
                        'is_published' => $item->is_published,
                        'status' => $item->status,
                        'created_at' => $item->created_at->format('Y-m-d'),
                        'kategori' => $item->kategori_artikel?->nama,
                    ];
                }),
                'meta' => [
                    'current_page' => $artikels->currentPage(),
                    'last_page' => $artikels->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil artikel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeArtikel(Request $request)
    {
        try {
            // VALIDASI
            $request->validate([
                'title' => 'nullable|string|max:255',
                'kategori_id' => 'nullable|exists:kategori_artikels,id',
                'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'excerpt' => 'nullable|string|max:500',
                'keyword' => 'nullable|string|max:255',
                'content' => 'nullable|string',
                'status' => 'required|in:draft,pending',
            ]);

            // HANDLE THUMBNAIL
            $thumbnailPath = null;

            if ($request->status === 'pending') {
                $request->validate([
                    'title' => 'required',
                    'kategori_id' => 'required',
                    'content' => 'required',
                ]);
            }

            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')
                    ->store('artikel', 'public');
            }

            $status = in_array($request->status, ['draft', 'pending'])
                        ? $request->status
                        : 'draft';

            // SANITIZE CONTENT (ANTI XSS)
            $cleanContent = Purifier::clean($request->content);

            // GENERATE SLUG UNIQUE
            $slug = Str::slug($request->title);
            $count = Artikel::where('slug', 'LIKE', "{$slug}%")->count();
            $slug = $count ? "{$slug}-{$count}" : $slug;

            // SIMPAN DATA
            $artikel = Artikel::create([
                'title' => $request->title,
                'slug' => $request->title ? Str::slug($request->title) : null,
                'kategori_id' => $request->kategori_id,
                'thumbnail' => $thumbnailPath,
                'excerpt' => $request->excerpt,
                'keyword' => $request->keyword,
                'content' => $cleanContent,
                'author_id' => auth()->id(),
                'status' => $status,
                'is_published' => 0,
                'views' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Artikel berhasil dikirim ke admin',
                'data' => $artikel
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => $e->getMessage() // hapus di production kalau mau lebih aman
            ], 500);
        }
    }

    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $file = $request->file('image');

            // generate nama unik
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            // resize (optional tapi recommended)
            // $image = \Intervention\Image\Facades\Image::make($file)
            $image = Image::make($file)
                ->resize(1200, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode();

            $path = "artikel-content/{$filename}";

            Storage::disk('public')->put($path, $image);

            return response()->json([
                'location' => asset("storage/{$path}") // format TinyMCE
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Upload gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function editArtikel($slug)
    {
        try {
            $artikel = Artikel::select([
                    'id',
                    'title',
                    'kategori_id',
                    'thumbnail',
                    'excerpt',
                    'keyword',
                    'content',
                    'status',
                    'created_at',
                    'updated_at'
                ])
                ->where('slug', $slug)
                ->where('author_id', auth()->id())
                ->with([
                    'kategori_artikel:id,nama'
                ])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $artikel->id,
                    'title' => $artikel->title,
                    'kategori_id' => $artikel->kategori_id,
                    'thumbnail' => $artikel->thumbnail,
                    'excerpt' => $artikel->excerpt,
                    'keyword' => $artikel->keyword,
                    'content' => $artikel->content,
                    'status' => $artikel->status,
                    'kategori' => $artikel->kategori_artikel?->nama,
                    'created_at' => $artikel->created_at->format('Y-m-d H:i'),
                    'updated_at' => $artikel->updated_at->format('Y-m-d H:i'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Artikel tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function updateArtikel(Request $request, $id)
    {
        try {
            $artikel = Artikel::where('author_id', auth()->id())
                ->findOrFail($id);

            // VALIDASI
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'kategori_id' => 'required|exists:kategori_artikels,id',
                'excerpt' => 'nullable|string',
                'keyword' => 'nullable|string',
                'content' => 'required|string',
                'thumbnail' => 'nullable|image|max:2048',
                'status' => 'required|in:draft,pending,published',
            ]);

            // HANDLE THUMBNAIL
            if ($request->hasFile('thumbnail')) {
                // hapus lama
                if ($artikel->thumbnail) {
                    Storage::delete($artikel->thumbnail);
                }

                $path = $request->file('thumbnail')->store('artikel', 'public');
                $validated['thumbnail'] = $path;
            }

            // SLUG UPDATE JIKA TITLE BERUBAH
            if ($validated['title'] !== $artikel->title) {
                $validated['slug'] = Str::slug($validated['title']);
            }

            // UPDATE
            $artikel->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Artikel berhasil diperbarui',
                'data' => [
                    'id' => $artikel->id,
                    'title' => $artikel->title,
                    'status' => $artikel->status,
                    'updated_at' => $artikel->updated_at->format('Y-m-d H:i'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update artikel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getKomentarDashboard()
    {
        try {
            // KOMENTAR MASUK (ke artikel kamu)
            $incoming = Komentar::query()
                ->select([
                    'id',
                    'user_id',
                    'artikel_id',
                    'komentar',
                    'created_at'
                ])
                ->with([
                    'user:id,name',
                    'artikel:id,title,author_id'
                ])
                ->whereHas('artikel', function ($q) {
                    $q->where('author_id', auth()->id());
                })
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nama' => $item->user?->name,
                        'artikel' => $item->artikel?->title,
                        'komentar' => $item->komentar,
                        'created_at' => $item->created_at->format('Y-m-d H:i'),
                    ];
                });

            // KOMENTAR SAYA (yang user ini tulis)
            $mine = Komentar::query()
                ->select([
                    'id',
                    'user_id',
                    'artikel_id',
                    'komentar',
                    'created_at'
                ])
                ->with([
                    'artikel:id,title'
                ])
                ->where('user_id', auth()->id())
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'artikel' => $item->artikel?->title,
                        'komentar' => $item->komentar,
                        'created_at' => $item->created_at->format('Y-m-d H:i'),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'incoming' => $incoming,
                    'mine' => $mine,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil komentar',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Update profile user
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            "name" => "required|string|max:255",
            "deskripsi" => "nullable|string",
            "foto" => "nullable|image|max:2048", // max 2MB
        ]);

        $user->name = $request->name;
        $user->deskripsi = $request->deskripsi;

        if ($request->hasFile('foto')) {
            // hapus foto lama jika ada
            if ($user->foto && Storage::exists($user->foto)) {
                Storage::delete($user->foto);
            }

            $path = $request->file('foto')->store('user', 'public');
            $user->foto = $path;
        }

        $user->save();

        return response()->json([
            "message" => "Profile updated successfully",
            "user" => $user,
        ]);
    }

    public function destroyArtikel($slug)
    {
        $artikel = Artikel::where('slug', $slug)->first();

        if (!$artikel) {
            return response()->json([
                'message' => 'Artikel tidak ditemukan'
            ], 404);
        }

        // Optional: cek apakah user punya hak hapus artikel ini
        if ($artikel->author_id !== auth()->id()) {
            return response()->json([
                'message' => 'Kamu tidak punya akses menghapus artikel ini'
            ], 403);
        }

        $artikel->delete();

        return response()->json([
            'message' => 'Artikel berhasil dihapus'
        ], 200);
    }

    public function komentar(Request $request)
    {
        $type = $request->query('type', 'incoming');

        if ($type === 'mine') {
            $comments = Komentar::where('user_id', auth()->id())->latest()->get();
        } else {
            // incoming = komentar di artikel user lain / milik user
            $comments = Komentar::whereHas('artikel', function ($q) {
                $q->where('author_id', auth()->id());
            })->latest()->get();
        }

        return response()->json($comments);
    }

    // DELETE /api/dashboard/artikel/komentar/{id}
    public function destroyKomentar($id)
    {
        $comment = Komentar::find($id);

        if (!$comment) {
            return response()->json(['message' => 'Komentar tidak ditemukan'], 404);
        }

        // cek kepemilikan artikel atau komentar
        if ($comment->user_id !== auth()->id() && $comment->artikel->user_id !== auth()->id()) {
            return response()->json(['message' => 'Tidak punya akses'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Komentar berhasil dihapus']);
    }

    // UPDATE /api/dashboard/komentar/{id}
    public function updateKomentar(Request $request, $id)
    {
        $comment = Komentar::find($id);

        if (!$comment) {
            return response()->json(['message' => 'Komentar tidak ditemukan'], 404);
        }

        // cek kepemilikan
        if ($comment->user_id !== auth()->id()) {
            return response()->json(['message' => 'Tidak punya akses'], 403);
        }

        $request->validate([
            'komentar' => 'required|string|max:5000',
        ]);

        $comment->komentar = $request->komentar;
        $comment->save();

        return response()->json($comment);
    }
}
