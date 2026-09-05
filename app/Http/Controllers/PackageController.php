<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Smalot\PdfParser\Parser;
use thiagoalessio\TesseractOCR;
use ZipArchive;

class PackageController extends Controller
{
    public function index()
    {
        $query = Package::where('is_active', true);

        if (request('search')) {
            $search = request('search');
            $escapedSearch = \App\Support\SearchHelper::escapeLike($search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('title', 'like', "%{$escapedSearch}%")
                  ->orWhere('description', 'like', "%{$escapedSearch}%");
            });
        }

        if (request('kelas')) {
            $query->where('kelas', request('kelas'));
        }
        if (request('bidang')) {
            $query->where('bidang', request('bidang'));
        }
        if (request('level')) {
            $query->where('level', request('level'));
        }

        $packages = $query->latest()->paginate(12)->withQueryString();
        $allKelas = Package::where('is_active', true)->whereNotNull('kelas')->where('kelas', '!=', '')->distinct()->pluck('kelas')->sort()->values();
        $allBidang = Package::where('is_active', true)->whereNotNull('bidang')->where('bidang', '!=', '')->distinct()->pluck('bidang')->sort()->values();
        $allLevel = Package::where('is_active', true)->whereNotNull('level')->where('level', '!=', '')->distinct()->pluck('level')->sort()->values();

        return Inertia::render('Packages/PackageList', [
            'packages' => $packages,
            'allKelas' => $allKelas,
            'allBidang' => $allBidang,
            'allLevel' => $allLevel,
            'filters' => request()->only(['search', 'kelas', 'bidang', 'level']),
        ]);
    }

    public function adminIndex(Request $request)
    {
        $query = Package::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $escapedSearch = \App\Support\SearchHelper::escapeLike($search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('title', 'like', "%{$escapedSearch}%")
                  ->orWhere('description', 'like', "%{$escapedSearch}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'aktif');
        }

        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        if ($request->filled('bidang')) {
            $query->where('bidang', $request->bidang);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        $packages = $query->latest()->paginate(10)->withQueryString();
        $allKelas = Package::whereNotNull('kelas')->where('kelas', '!=', '')->distinct()->pluck('kelas')->sort()->values();
        $allBidang = Package::whereNotNull('bidang')->where('bidang', '!=', '')->distinct()->pluck('bidang')->sort()->values();
        $allLevel = Package::whereNotNull('level')->where('level', '!=', '')->distinct()->pluck('level')->sort()->values();

        $totalQuestions = 0;
        foreach ($packages as $pkg) {
            $totalQuestions += count($pkg->questions ?? []);
        }

        return Inertia::render('Admin/Packages/PackageIndex', [
            'packages' => $packages,
            'allKelas' => $allKelas,
            'allBidang' => $allBidang,
            'allLevel' => $allLevel,
            'filters' => $request->only(['search', 'status', 'kelas', 'bidang', 'level']),
            'stats' => [
                'total' => $packages->total(),
                'active' => Package::where('is_active', true)->count(),
                'inactive' => Package::where('is_active', false)->count(),
                'totalQuestions' => $totalQuestions,
            ],
        ]);
    }

    public function adminShow(Package $package)
    {
        $cards = $package->cards ?? [];
        $allQuestions = $package->questions ?? [];
        $questionsByCard = collect($allQuestions)->groupBy('card_id');
        $totalCards = count($cards);
        $totalQuestions = count($allQuestions);
        $totalPracticeSessions = $package->practiceSessions()->count();

        return Inertia::render('Admin/Packages/PackageShow', [
            'package' => $package,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Packages/PackageCreate');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'kelas' => 'nullable|string|max:255',
            'bidang' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'show_answer_key' => 'nullable|boolean',
            'show_explanation' => 'nullable|boolean',
            'show_score' => 'nullable|boolean',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'end_date.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $package = Package::create([
            'title' => $request->title,
            'description' => $request->description,
            'kelas' => $request->kelas ?: null,
            'bidang' => $request->bidang ?: null,
            'level' => $request->level ?: null,
            'is_active' => $request->is_active ?? true,
            'start_date' => $request->start_date ?: null,
            'end_date' => $request->end_date ?: null,
            'start_time' => $request->start_time ?: null,
            'end_time' => $request->end_time ?: null,
            'show_answer_key' => $request->boolean('show_answer_key'),
            'show_explanation' => $request->boolean('show_explanation', true),
            'show_score' => $request->boolean('show_score', true),
            'cards' => [],
            'questions' => [],
            'reviews' => [],
        ]);

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $package->update(['thumbnail' => $path]);
        }

        return redirect()->route('admin.packages.index')->with('success', 'Paket berhasil dibuat!');
    }

    // ===================== EDIT PAGES =====================

    public function edit(Package $package)
    {
        return redirect()->route('admin.packages.edit.informasi', $package);
    }

    public function editInformasi(Package $package)
    {
        return Inertia::render('Admin/Packages/PackageEditInfo', [
            'package' => $package,
        ]);
    }

    public function editCards(Package $package)
    {
        $allQuestions = array_values($package->questions ?? []);
        $questionsByCard = collect($allQuestions)->groupBy('card_id');

        return Inertia::render('Admin/Packages/PackageEditCards', [
            'package' => $package,
            'questionsByCard' => $questionsByCard,
        ]);
    }

    public function editQuestions(Package $package)
    {
        $allQuestions = array_values($package->questions ?? []);
        $questionsByCard = collect($allQuestions)->groupBy('card_id');
        $totalCards = count($package->cards ?? []);
        $totalQuestions = count($allQuestions);

        return Inertia::render('Admin/Packages/PackageEditQuestions', [
            'package' => $package,
            'allQuestions' => $allQuestions,
            'cards' => array_values($package->cards ?? []),
            'questionsByCard' => $questionsByCard,
            'totalCards' => $totalCards,
            'totalQuestions' => $totalQuestions,
        ]);
    }

    // ===================== UPDATE METHODS =====================

    public function update(Request $request, Package $package)
    {
        $input = $request->all();
        foreach (['kelas', 'bidang', 'level', 'start_date', 'end_date', 'start_time', 'end_time'] as $field) {
            if (isset($input[$field]) && trim((string) $input[$field]) === '') {
                $input[$field] = null;
            }
        }

        $validated = Validator::make($input, [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'kelas' => 'nullable|string|max:255',
            'bidang' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'show_answer_key' => 'nullable|boolean',
            'show_explanation' => 'nullable|boolean',
            'show_score' => 'nullable|boolean',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'end_date.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
        ])->validate();

        $validated['show_answer_key'] = $request->boolean('show_answer_key');
        $validated['show_explanation'] = $request->boolean('show_explanation', true);
        $validated['show_score'] = $request->boolean('show_score', true);

        $package->update($validated);

        if ($request->hasFile('thumbnail')) {
            if ($package->thumbnail) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($package->thumbnail);
            }
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $package->update(['thumbnail' => $path]);
        }

        return redirect()->route('admin.packages.edit.informasi', $package)->with('success', 'Informasi paket berhasil diperbarui!');
    }

    // ===================== AJAX REALTIME TOGGLE =====================

    /**
     * Toggle pengaturan paket secara AJAX (realtime).
     * Field yang didukung: show_answer_key, show_explanation, show_score, is_active
     */
    public function ajaxToggleSetting(Request $request, Package $package)
    {
        $validator = Validator::make($request->all(), [
            'field' => 'required|in:show_answer_key,show_explanation,show_score,is_active',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Field tidak valid.'], 422);
        }

        $field = $request->field;
        $newValue = !$package->{$field};
        $package->update([$field => $newValue]);

        return response()->json([
            'success' => true,
            'field' => $field,
            'value' => $newValue,
            'message' => 'Pengaturan berhasil diperbarui.',
        ]);
    }

    /**
     * Update jadwal pengerjaan secara AJAX (realtime).
     */
    public function ajaxUpdateSchedule(Request $request, Package $package)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time'   => 'nullable|date_format:H:i',
        ], [
            'end_date.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $data = [];
        if ($request->has('start_date')) $data['start_date'] = $request->start_date ?: null;
        if ($request->has('end_date')) $data['end_date'] = $request->end_date ?: null;
        if ($request->has('start_time')) $data['start_time'] = $request->start_time ?: null;
        if ($request->has('end_time')) $data['end_time'] = $request->end_time ?: null;

        if (!empty($data)) {
            $package->update($data);
        }

        // Refresh model
        $package->refresh();

        return response()->json([
            'success'         => true,
            'schedule_status' => $package->schedule_status,
            'schedule_label'  => $package->schedule_label,
            'message'         => 'Jadwal berhasil diperbarui.',
        ]);
    }

    /**
     * Upload image for question editor (AJAX).
     */
    public function uploadImage(Request $request, Package $package)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $path = $request->file('image')->store('question_images/' . $package->id, 'public');

        return response()->json([
            'success' => true,
            'url' => Storage::disk('public')->url($path),
        ]);
    }

    public function confirmDelete(Package $package)
    {
        return redirect()->route('admin.packages.index');
    }

    public function destroy(Package $package)
    {
        $package->delete();
        return redirect()->route('admin.packages.index')->with('success', 'Paket berhasil dihapus!');
    }

    public function show(Package $package)
    {
        if (!$package->is_active) {
            return redirect()->route('packages.index')->with('error', 'Paket tidak tersedia!');
        }

        $totalCards = count($package->cards ?? []);
        $totalQuestions = count($package->questions ?? []);

        $completedSession = $package->practiceSessions()
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->latest()
            ->first();

        $inProgressSession = $package->practiceSessions()
            ->where('user_id', auth()->id())
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        // Per-card completion status (1-attempt restriction)
        $completedCardIds = $package->practiceSessions()
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->whereNotNull('card_id')
            ->pluck('id', 'card_id')
            ->toArray();

        $inProgressCardIds = $package->practiceSessions()
            ->where('user_id', auth()->id())
            ->where('status', 'in_progress')
            ->whereNotNull('card_id')
            ->pluck('id', 'card_id')
            ->toArray();

        return Inertia::render('Packages/PackageDetail', [
            'package' => $package,
            'totalCards' => $totalCards,
            'totalQuestions' => $totalQuestions,
            'completedSession' => $completedSession,
            'inProgressSession' => $inProgressSession,
            'completedCardIds' => $completedCardIds,
            'inProgressCardIds' => $inProgressCardIds,
        ]);
    }

    // ===================== CARD METHODS =====================

    public function addCard(Request $request, Package $package)
    {
        $validator = Validator::make($request->all(), [
            'card_title' => 'required|string|max:255',
            'card_description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $cards = $package->cards ?? [];
        $cards[] = [
            'id' => (string) Str::uuid(),
            'title' => $request->card_title,
            'description' => $request->card_description,
            'created_at' => now()->toDateTimeString(),
        ];

        $package->update(['cards' => $cards]);

        return redirect()->route('admin.packages.edit.cards', $package)->with('success', 'Card berhasil ditambahkan!');
    }

    public function removeCard(Package $package, $cardId)
    {
        $cards = $package->cards ?? [];
        $cards = array_filter($cards, function ($card) use ($cardId) {
            return $card['id'] !== $cardId;
        });

        $questions = $package->questions ?? [];
        $questions = array_values(array_filter($questions, function ($q) use ($cardId) {
            return $q['card_id'] !== $cardId;
        }));

        $package->update([
            'cards' => array_values($cards),
            'questions' => $questions,
        ]);

        return redirect()->route('admin.packages.edit.cards', $package)->with('success', 'Card berhasil dihapus! Soal pada card tersebut juga telah dihapus.');
    }

    // ===================== QUESTION METHODS =====================

    public function createQuestion(Package $package)
    {
        $cards = array_values($package->cards ?? []);
        return Inertia::render('Admin/Packages/QuestionForm', [
            'package' => $package,
            'cards' => $cards,
        ]);
    }

    public function editQuestion(Package $package, $questionId)
    {
        $cards = array_values($package->cards ?? []);
        $questions = $package->questions ?? [];
        $question = null;

        foreach ($questions as $q) {
            if (($q['id'] ?? null) === $questionId) {
                $question = $q;
                break;
            }
        }

        if (!$question) {
            return redirect()->route('admin.packages.edit.questions', $package)->with('error', 'Soal tidak ditemukan!');
        }

        $existingImages = [];
        $imageDir = 'question_images/' . $package->id;
        if (Storage::disk('public')->exists($imageDir)) {
            $files = Storage::disk('public')->allFiles($imageDir);
            foreach ($files as $file) {
                $filename = basename($file);
                if (str_starts_with($filename, 'auto_')) {
                    $existingImages[] = [
                        'filename' => $filename,
                        'url' => Storage::disk('public')->url($file),
                    ];
                }
            }
        }

        return Inertia::render('Admin/Packages/QuestionForm', [
            'package' => $package,
            'cards' => $cards,
            'question' => $question,
            'existingImages' => $existingImages,
        ]);
    }

    public function addQuestion(Request $request, Package $package)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
            'type' => 'nullable|string|in:pilihan_ganda,isian_singkat',
            'options' => 'required_unless:type,isian_singkat|array',
            'correct_answer' => 'required|string',
            'explanation' => 'nullable|string',
            'card_id' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('question_images/' . $package->id, 'public');
        }

        $questionType = $request->type ?: 'pilihan_ganda';
        $questions = $package->questions ?? [];
        $questions[] = [
            'id' => (string) Str::uuid(),
            'card_id' => $request->card_id,
            'question' => $request->question,
            'type' => $questionType,
            'options' => $questionType === 'isian_singkat' ? [] : $request->options,
            'correct_answer' => $request->correct_answer,
            'explanation' => $request->explanation,
            'image' => $imagePath,
            'created_at' => now()->toDateTimeString(),
        ];

        $package->update(['questions' => $questions]);

        return redirect()->route('admin.packages.edit.questions', $package)->with('success', 'Soal berhasil ditambahkan!');
    }

    public function updateQuestion(Request $request, Package $package, $questionId)
    {
        $validator = Validator::make($request->all(), [
            'card_id' => 'required|string',
            'question' => 'required|string',
            'type' => 'nullable|string|in:pilihan_ganda,isian_singkat',
            'options' => 'required_unless:type,isian_singkat|array',
            'correct_answer' => 'required|string',
            'explanation' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'remove_image' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $questions = $package->questions ?? [];
        $updated = false;

        foreach ($questions as $i => $question) {
            if (($question['id'] ?? null) !== $questionId) {
                continue;
            }

            $imagePath = $question['image'] ?? null;

            if ($request->boolean('remove_image') && $imagePath) {
                Storage::disk('public')->delete($imagePath);
                $imagePath = null;
            }

            if ($request->hasFile('image')) {
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $request->file('image')->store('question_images/' . $package->id, 'public');
            }

            $questionType = $request->type ?: 'pilihan_ganda';
            $questions[$i]['card_id'] = $request->card_id;
            $questions[$i]['question'] = $request->question;
            $questions[$i]['type'] = $questionType;
            $questions[$i]['options'] = $questionType === 'isian_singkat' ? [] : $request->options;
            $questions[$i]['correct_answer'] = $request->correct_answer;
            $questions[$i]['explanation'] = $request->explanation;
            $questions[$i]['image'] = $imagePath;

            $updated = true;
            break;
        }

        if (!$updated) {
            return back()->with('error', 'Soal tidak ditemukan!');
        }

        $package->update(['questions' => $questions]);

        return redirect()->route('admin.packages.edit.questions', $package)->with('success', 'Soal berhasil diperbarui!');
    }

    public function removeQuestion(Package $package, $questionId)
    {
        $questions = $package->questions ?? [];

        foreach ($questions as $question) {
            if (($question['id'] ?? null) === $questionId && !empty($question['image'])) {
                Storage::disk('public')->delete($question['image']);
            }
        }

        $questions = array_filter($questions, function ($question) use ($questionId) {
            return $question['id'] !== $questionId;
        });

        $package->update(['questions' => array_values($questions)]);

        return redirect()->route('admin.packages.edit.questions', $package)->with('success', 'Soal berhasil dihapus!');
    }

    // ===================== IMPORT PDF =====================

    public function showImportForm(Package $package)
    {
        $cards = array_values($package->cards ?? []);
        return Inertia::render('Admin/Packages/ImportPdf', [
            'package' => $package,
            'cards' => $cards,
        ]);
    }

    public function importPdf(Request $request, Package $package)
    {
        $validator = Validator::make($request->all(), [
            'card_id' => 'required|string',
            'pdf_file' => 'required|file|mimes:pdf|max:2048',
            'answer_key_pdf' => 'nullable|file|mimes:pdf|max:2048',
            'images_zip' => 'nullable|file|mimes:zip|max:20480',
        ], [
            'pdf_file.max' => 'Ukuran file PDF soal maksimal 2MB.',
            'answer_key_pdf.max' => 'Ukuran file PDF kunci jawaban maksimal 2MB.',
            'images_zip.max' => 'Ukuran file ZIP gambar maksimal 20MB.',
            'images_zip.mimes' => 'File gambar harus diupload dalam format ZIP (.zip).',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $parser = new Parser();
            $pdfPath = $request->file('pdf_file')->getPathname();
            $pdf = $parser->parseFile($pdfPath);
            $text = $pdf->getText();

            if (trim($text) === '') {
                $text = $this->ocrPdfText($pdfPath);
            }

            if (trim($text) === '') {
                return back()->with('error', 'PDF tidak mengandung teks yang bisa dibaca dan OCR juga tidak menemukan teks. Pastikan PDF berisi teks atau gambar yang jelas.');
            }

            $questions = $this->extractQuestionsFromText($text);

            if (empty($questions)) {
                return back()->with('error', 'Tidak dapat mengekstrak soal dari PDF. Pastikan format PDF sesuai template: setiap soal diawali nomor + titik (contoh "1. Ibu kota Indonesia adalah...."). Untuk pilihan ganda, pilihan jawaban di baris terpisah diawali huruf A-E + titik. Untuk isian singkat, tanpa pilihan A-E lalu beri jawaban di baris berikut (contoh "Jawaban: Jakarta"). Nomor soal harus berurutan (1, 2, 3, dst).');
            }

            $answerKey = [];
            if ($request->hasFile('answer_key_pdf')) {
                $answerPdf = $parser->parseFile($request->file('answer_key_pdf')->getPathname());
                $answerText = $answerPdf->getText();
                if (trim($answerText) === '') {
                    $answerText = $this->ocrPdfText($request->file('answer_key_pdf')->getPathname());
                }
                $answerKey = $this->extractAnswerKey($answerText);
            }

            $imageMap = [];
            if ($request->hasFile('images_zip')) {
                $imageMap = $this->extractImagesZip($request->file('images_zip'), $package->id);
            }

            $pdfPath = $request->file('pdf_file')->getPathname();
            $embeddedImages = $this->extractEmbeddedImagesFromPdf($pdfPath, $package->id);
            $questionPageMap = $this->mapQuestionNumbersToPages($pdfPath, $questions);
            $autoImageMap = $this->assignAutoImagesToQuestions($embeddedImages, $questionPageMap, $questions);

            $pageImages = [];
            $questionsNeedingImages = array_diff(
                array_column($questions, 'number'),
                array_keys($autoImageMap)
            );
            if (!empty($questionsNeedingImages)) {
                $rawPageImages = $this->renderPdfPagesAsImages($pdfPath, $package->id, $questionPageMap);
                $pageImages = [];
                foreach ($rawPageImages as $pi) {
                    $pageImages[$pi['page']] = $pi;
                }
                foreach ($questionsNeedingImages as $qNum) {
                    if (isset($questionPageMap[$qNum]) && isset($pageImages[$questionPageMap[$qNum]])) {
                        $autoImageMap[$qNum] = $pageImages[$questionPageMap[$qNum]]['path'];
                    }
                }
            }

            $existingQuestions = $package->questions ?? [];
            $newQuestions = [];
            $skippedNoAnswer = 0;
            $missingImages = [];
            $autoAttachedCount = 0;

            foreach ($questions as $question) {
                $options = $question['options'] ?? [];

                $rawAnswer = $question['correct_answer'] ?: ($answerKey[$question['number']] ?? '');
                $correctAnswer = $this->resolveCorrectAnswer($rawAnswer, $options);

                if ($correctAnswer === '') {
                    $skippedNoAnswer++;
                }

                $imagePath = null;
                if (!empty($question['image_filename'])) {
                    $key = mb_strtolower(trim($question['image_filename']));
                    if (isset($imageMap[$key])) {
                        $imagePath = $imageMap[$key];
                    } else {
                        $missingImages[] = $question['image_filename'];
                    }
                }

                if ($imagePath === null && isset($autoImageMap[$question['number']])) {
                    $imagePath = $autoImageMap[$question['number']];
                    $autoAttachedCount++;
                }

                $questionType = $question['type'] ?? 'pilihan_ganda';
                $newQuestions[] = [
                    'id' => (string) Str::uuid(),
                    'card_id' => $request->card_id,
                    'question' => $question['question'],
                    'type' => $questionType,
                    'options' => $options,
                    'correct_answer' => $correctAnswer,
                    'explanation' => $question['explanation'] ?? '',
                    'image' => $imagePath,
                    'created_at' => now()->toDateTimeString(),
                    'imported_from_pdf' => true,
                ];
            }

            $package->update([
                'questions' => array_merge($existingQuestions, $newQuestions)
            ]);

            $message = 'Berhasil mengimport ' . count($newQuestions) . ' soal dari PDF!';

            if ($autoAttachedCount > 0) {
                $message .= ' ' . $autoAttachedCount . ' gambar otomatis terdeteksi & terpasang.';
            }

            if ($skippedNoAnswer > 0) {
                $message .= ' ' . $skippedNoAnswer . ' soal belum punya jawaban benar (tidak ditemukan di PDF kunci jawaban) — cek & lengkapi manual di daftar soal.';
            }

            if (!empty($missingImages)) {
                $message .= ' Gambar tidak ditemukan di ZIP untuk: ' . implode(', ', array_unique($missingImages)) . '.';
            }

            return redirect()->route('admin.packages.edit.questions', $package)->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimport PDF. Silakan coba lagi.');
        }
    }

    // ===================== HELPER METHODS =====================

    private function extractImagesZip($zipFile, $packageId)
    {
        $map = [];
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        $zip = new ZipArchive();
        $opened = $zip->open($zipFile->getPathname());

        if ($opened !== true) {
            return $map;
        }

        $targetDir = 'question_images/' . $packageId;
        Storage::disk('public')->makeDirectory($targetDir);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);

            if ($entryName === false || Str::endsWith($entryName, '/') || Str::contains($entryName, '__MACOSX')) {
                continue;
            }

            $baseName = basename($entryName);
            $ext = strtolower(pathinfo($baseName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt, true)) {
                continue;
            }

            $contents = $zip->getFromIndex($i);
            if ($contents === false) {
                continue;
            }

            $safeName = Str::uuid() . '.' . $ext;
            $storedPath = $targetDir . '/' . $safeName;
            Storage::disk('public')->put($storedPath, $contents);

            $map[mb_strtolower($baseName)] = $storedPath;
        }

        $zip->close();

        return $map;
    }

    private function ocrPdfText($pdfPath)
    {
        $tesseractPath = trim((string) exec('which tesseract'));
        if ($tesseractPath === '' || !function_exists('shell_exec')) {
            return '';
        }

        $tmpDir = sys_get_temp_dir() . '/kpm_ocr_' . Str::random(8);
        @mkdir($tmpDir, 0755, true);

        $gsPath = trim((string) exec('which gs'));
        if ($gsPath === '' || !file_exists($gsPath)) {
            return '';
        }

        $cmd = sprintf(
            'gs -dNOPAUSE -dBATCH -sDEVICE=png16m -r200 -sOutputFile=%s/%%d.png %s 2>&1',
            escapeshellarg($tmpDir),
            escapeshellarg($pdfPath)
        );
        exec($cmd, $gsOutput, $gsReturn);

        $allText = [];

        $pages = glob($tmpDir . '/*.png');
        sort($pages);

        foreach ($pages as $pageImage) {
            try {
                $ocr = new TesseractOCR($pageImage);
                $pageText = $ocr->run();
                if (!empty(trim($pageText))) {
                    $allText[] = $pageText;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        foreach ($pages as $pageImage) {
            @unlink($pageImage);
        }
        @rmdir($tmpDir);

        return implode("\n\n", $allText);
    }

    private function extractEmbeddedImagesFromPdf($pdfPath, $packageId)
    {
        $images = [];
        $targetDir = 'question_images/' . $packageId . '/auto';
        Storage::disk('public')->makeDirectory($targetDir);

        $parser = new Parser();
        $pdf = $parser->parseFile($pdfPath);
        $pages = $pdf->getPages();

        foreach ($pages as $pageIndex => $page) {
            $xobjects = $page->getXObjects();

            if (empty($xobjects)) {
                continue;
            }

            foreach ($xobjects as $xobject) {
                if (!$this->isImageXObject($xobject)) {
                    continue;
                }

                $imageData = $this->extractImageFromXObject($xobject);

                if ($imageData === null) {
                    continue;
                }

                if (strlen($imageData['content']) < 200) {
                    continue;
                }

                $filename = 'auto_' . Str::uuid() . '.' . $imageData['ext'];
                $storedPath = $targetDir . '/' . $filename;
                Storage::disk('public')->put($storedPath, $imageData['content']);

                $images[] = [
                    'page' => $pageIndex,
                    'path' => $storedPath,
                    'ext' => $imageData['ext'],
                    'size' => strlen($imageData['content']),
                ];
            }
        }

        return $images;
    }

    private function renderPdfPagesAsImages($pdfPath, $packageId, array $questionPageMap)
    {
        $images = [];
        $targetDir = 'question_images/' . $packageId . '/pages';
        Storage::disk('public')->makeDirectory($targetDir);

        $gsPath = trim((string) exec('which gs'));
        if ($gsPath === '' || !file_exists($gsPath)) {
            return $images;
        }

        $pageNumbers = array_unique(array_values($questionPageMap));
        sort($pageNumbers);

        if (empty($pageNumbers)) {
            return $images;
        }

        foreach ($pageNumbers as $pageIndex) {
            $pageNum = $pageIndex + 1;
            $outputFile = $targetDir . '/page_' . $pageNum . '_' . Str::uuid() . '.jpg';
            $fullPath = Storage::disk('public')->path($outputFile);

            $cmd = sprintf(
                'gs -dNOPAUSE -dBATCH -sDEVICE=jpeg -dJPEGQ=85 -r200 -dFirstPage=%d -dLastPage=%d -sOutputFile=%s %s 2>&1',
                $pageNum,
                $pageNum,
                escapeshellarg($fullPath),
                escapeshellarg($pdfPath)
            );

            exec($cmd, $output, $returnCode);

            if ($returnCode === 0 && file_exists($fullPath) && filesize($fullPath) > 500) {
                $images[] = [
                    'page' => $pageIndex,
                    'path' => $outputFile,
                    'ext' => 'jpg',
                    'size' => filesize($fullPath),
                ];
            }
        }

        return $images;
    }

    private function isImageXObject($xobject)
    {
        if (!method_exists($xobject, 'getDetails')) {
            return false;
        }

        $details = $xobject->getDetails();

        if (isset($details['Subtype']) && $details['Subtype'] === 'Image') {
            return true;
        }

        if (isset($details['Filter'])) {
            $filters = is_array($details['Filter']) ? $details['Filter'] : [$details['Filter']];
            foreach ($filters as $filter) {
                if (in_array($filter, ['DCTDecode', 'JPXDecode', 'FlateDecode'])) {
                    return true;
                }
            }
        }

        return false;
    }

    private function extractImageFromXObject($xobject)
    {
        try {
            $details = $xobject->getDetails();

            if (method_exists($xobject, 'getContent')) {
                $content = $xobject->getContent();
            } else {
                $content = $details['_content'] ?? null;
            }

            if ($content === null) {
                return null;
            }

            $filter = $details['Filter'] ?? null;
            $filters = is_array($filter) ? $filter : [$filter];

            if (in_array('DCTDecode', $filters)) {
                return ['content' => $content, 'ext' => 'jpg'];
            }

            if (in_array('FlateDecode', $filters)) {
                $width = $details['Width'] ?? 0;
                $height = $details['Height'] ?? 0;
                $bitsPerComponent = $details['BitsPerComponent'] ?? 8;
                $colorSpace = $details['ColorSpace'] ?? null;

                if (isset($details['DecodeParms']['Predictor']) && $details['DecodeParms']['Predictor'] > 1) {
                    $content = $this->decodeFlateWithPredictor($content, $width, $height, $bitsPerComponent, $colorSpace);
                }

                $pngContent = $this->convertRawToPng($content, $width, $height, $bitsPerComponent, $colorSpace);
                if ($pngContent !== null) {
                    return ['content' => $pngContent, 'ext' => 'png'];
                }

                return ['content' => $content, 'ext' => 'png'];
            }

            if (in_array('JPXDecode', $filters)) {
                return null;
            }

            if (substr($content, 0, 2) === "\xFF\xD8") {
                return ['content' => $content, 'ext' => 'jpg'];
            }
            if (substr($content, 0, 8) === "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") {
                return ['content' => $content, 'ext' => 'png'];
            }

            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    private function decodeFlateWithPredictor($data, $width, $height, $bitsPerComponent, $colorSpace)
    {
        return $data;
    }

    private function convertRawToPng($data, $width, $height, $bitsPerComponent, $colorSpace)
    {
        if ($width <= 0 || $height <= 0 || $bitsPerComponent != 8) {
            return null;
        }

        $channels = 1;
        $type = 'grayscale';

        if (is_string($colorSpace)) {
            if (strpos($colorSpace, 'RGB') !== false) {
                $channels = 3;
                $type = 'rgb';
            } elseif (strpos($colorSpace, 'CMYK') !== false) {
                return null;
            } elseif (strpos($colorSpace, 'Indexed') !== false) {
                return null;
            }
        } elseif (is_array($colorSpace) && isset($colorSpace[0]) && $colorSpace[0] === 'Indexed') {
            return null;
        }

        $image = imagecreatetruecolor($width, $height);
        if (!$image) {
            return null;
        }

        $expectedSize = $width * $height * $channels;
        if (strlen($data) < $expectedSize) {
            imagedestroy($image);
            return null;
        }

        if ($type === 'grayscale') {
            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $pos = ($y * $width + $x);
                    if ($pos < strlen($data)) {
                        $gray = ord($data[$pos]);
                        $color = imagecolorallocate($image, $gray, $gray, $gray);
                        imagesetpixel($image, $x, $y, $color);
                    }
                }
            }
        } elseif ($type === 'rgb') {
            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $pos = ($y * $width + $x) * 3;
                    if ($pos + 2 < strlen($data)) {
                        $r = ord($data[$pos]);
                        $g = ord($data[$pos + 1]);
                        $b = ord($data[$pos + 2]);
                        $color = imagecolorallocate($image, $r, $g, $b);
                        imagesetpixel($image, $x, $y, $color);
                    }
                }
            }
        } else {
            imagedestroy($image);
            return null;
        }

        ob_start();
        imagepng($image, null, 6);
        $pngData = ob_get_clean();
        imagedestroy($image);

        return $pngData;
    }

    private function mapQuestionNumbersToPages($pdfPath, $questions)
    {
        $map = [];

        if (empty($questions)) {
            return $map;
        }

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);
            $pages = $pdf->getPages();

            $questionNumbers = array_column($questions, 'number');
            sort($questionNumbers);

            foreach ($pages as $pageIndex => $page) {
                $pageText = $page->getText();

                preg_match_all('/^(\d{1,4})[\.\)]\s+/m', $pageText, $matches);
                $numbersFound = array_map('intval', $matches[1] ?? []);

                foreach ($questionNumbers as $qNum) {
                    if (in_array($qNum, $numbersFound) && !isset($map[$qNum])) {
                        $map[$qNum] = $pageIndex;
                    }
                }
            }

            $lastFoundPage = 0;
            foreach ($questionNumbers as $qNum) {
                if (isset($map[$qNum])) {
                    $lastFoundPage = $map[$qNum];
                } elseif ($lastFoundPage > 0) {
                    $map[$qNum] = $lastFoundPage;
                }
            }

        } catch (\Exception $e) {
            $questionNumbers = array_column($questions, 'number');
            sort($questionNumbers);
            $totalQuestions = count($questionNumbers);

            if ($totalQuestions > 0) {
                foreach ($questionNumbers as $index => $qNum) {
                    $map[$qNum] = 0;
                }
            }
        }

        return $map;
    }

    private function assignAutoImagesToQuestions($embeddedImages, $questionPageMap, $questions)
    {
        $result = [];
        $usedImages = [];

        if (empty($embeddedImages) || empty($questionPageMap)) {
            return $result;
        }

        $imagesByPage = [];
        foreach ($embeddedImages as $idx => $img) {
            $page = $img['page'];
            if (!isset($imagesByPage[$page])) {
                $imagesByPage[$page] = [];
            }
            $imagesByPage[$page][] = $img;
        }

        foreach ($questions as $question) {
            $qNum = $question['number'];

            if (!isset($questionPageMap[$qNum])) {
                continue;
            }

            $page = $questionPageMap[$qNum];

            if (isset($imagesByPage[$page]) && !empty($imagesByPage[$page])) {
                foreach ($imagesByPage[$page] as $idx => $img) {
                    $imageKey = $img['path'];
                    if (!in_array($imageKey, $usedImages)) {
                        $result[$qNum] = $img['path'];
                        $usedImages[] = $imageKey;
                        break;
                    }
                }
            }
        }

        $questionsWithoutImage = array_diff(
            array_column($questions, 'number'),
            array_keys($result)
        );

        $remainingImages = [];
        foreach ($embeddedImages as $img) {
            if (!in_array($img['path'], $usedImages)) {
                $remainingImages[] = $img;
            }
        }

        foreach ($questionsWithoutImage as $qNum) {
            if (!empty($remainingImages)) {
                $img = array_shift($remainingImages);
                $result[$qNum] = $img['path'];
            }
        }

        return $result;
    }

    private function resolveCorrectAnswer($rawAnswer, array $options)
    {
        $rawAnswer = trim((string) $rawAnswer);

        if ($rawAnswer === '') {
            return '';
        }

        if (empty($options)) {
            return $rawAnswer;
        }

        if (preg_match('/^[A-Za-z]$/', $rawAnswer)) {
            $index = ord(strtoupper($rawAnswer)) - ord('A');

            if ($index >= 0 && isset($options[$index])) {
                return $options[$index];
            }
            return strtoupper($rawAnswer);
        }

        foreach ($options as $option) {
            if (mb_strtolower(trim($option)) === mb_strtolower($rawAnswer)) {
                return $option;
            }
        }

        return $rawAnswer;
    }

    private function extractQuestionsFromText($text)
    {
        $questions = [];
        $lines = explode("\n", $text);

        $currentQuestion = null;
        $currentOptions = [];
        $currentNumber = null;
        $currentAnswer = '';
        $currentExplanations = [];
        $currentImageFilename = null;

        $phase = null;
        $expectedNumber = null;
        $lastLineWasTable = false;
        $foundAnyOption = false;

        $flush = function () use (
            &$questions, &$currentQuestion, &$currentOptions, &$currentNumber,
            &$currentAnswer, &$currentExplanations, &$currentImageFilename
        ) {
            if ($currentQuestion === null) {
                return;
            }
            $type = empty($currentOptions) ? 'isian_singkat' : 'pilihan_ganda';
            $questions[] = [
                'number' => $currentNumber,
                'question' => trim($currentQuestion),
                'type' => $type,
                'options' => $currentOptions,
                'correct_answer' => $currentAnswer,
                'explanation' => trim(implode("\n", $currentExplanations)),
                'image_filename' => $currentImageFilename,
            ];
        };

        foreach ($lines as $line) {
            $line = rtrim($line);
            $trimmed = trim($line);
            if ($trimmed === '') {
                if ($phase === 'options' && $foundAnyOption) {
                    $phase = 'post_options';
                }
                continue;
            }

            $lineImageFilename = null;
            if (preg_match('/\[GAMBAR\s*:\s*([^\]]+)\]/i', $trimmed, $imgMatch)) {
                $lineImageFilename = trim($imgMatch[1]);
                $trimmed = trim(preg_replace('/\[GAMBAR\s*:\s*([^\]]+)\]/i', '', $trimmed));
                if ($trimmed === '') {
                    if ($lineImageFilename !== null) {
                        $currentImageFilename = $lineImageFilename;
                    }
                    continue;
                }
            }

            $isTableRow = (bool) preg_match('/^\|.*\|$/', $trimmed) || preg_match('/\t/', $trimmed);

            $isNewQuestion = false;
            $newNumber = null;
            if (!$isTableRow && preg_match('/^(\d{1,4})\.\s+(?=\S)/', $trimmed, $matches)) {
                $newNumber = (int) $matches[1];
                if ($expectedNumber === null || $newNumber === $expectedNumber) {
                    $isNewQuestion = true;
                } elseif ($newNumber > $expectedNumber && $currentQuestion !== null) {
                    $isNewQuestion = true;
                }
            }

            if ($isNewQuestion) {
                $flush();

                $currentNumber = $newNumber;
                $expectedNumber = $newNumber + 1;
                $currentQuestion = preg_replace('/^(\d{1,4})[\.\)]\s+/', '', $trimmed);
                $currentOptions = [];
                $currentAnswer = '';
                $currentExplanations = [];
                $currentImageFilename = $lineImageFilename;
                $phase = 'question';
                $lastLineWasTable = false;
                $foundAnyOption = false;
                continue;
            }

            if ($currentQuestion === null) {
                continue;
            }

            if (!$isTableRow && preg_match('/^(jawaban|kunci\s*jawaban|kunci|solusi)\s*[:\-]?\s*(.+)/i', $trimmed, $matches)) {
                $answerValue = trim($matches[2]);
                $answerValue = preg_replace('/^pilihan\s*/i', '', $answerValue);
                $answerValue = trim($answerValue);
                if (preg_match('/^[A-Za-z]$/', $answerValue)) {
                    $currentAnswer = strtoupper($answerValue);
                } else {
                    $currentAnswer = $answerValue;
                }
                if ($lineImageFilename !== null) {
                    $currentImageFilename = $lineImageFilename;
                }
                $lastLineWasTable = false;
                continue;
            }

            if (!$isTableRow && $phase !== 'explanation' && $phase !== 'post_options'
                && preg_match('/^([A-Za-z])[\.\)]\s*(.+)/', $trimmed, $matches)) {
                $letterOrd = ord(strtoupper($matches[1]));
                $optionText = trim($matches[2]);
                if ($optionText === '') {
                    $optionText = '—';
                }
                $currentOptions[] = $optionText;
                $phase = 'options';
                $foundAnyOption = true;
                if ($lineImageFilename !== null) {
                    $currentImageFilename = $lineImageFilename;
                }
                $lastLineWasTable = false;
                continue;
            }

            if (!$isTableRow && $phase !== 'explanation' && stripos($trimmed, 'pembahasan') === 0) {
                $currentExplanations[] = preg_replace('/^pembahasan\s*[:\-]?\s*/i', '', $trimmed);
                if ($lineImageFilename !== null) {
                    $currentImageFilename = $lineImageFilename;
                }
                $phase = 'explanation';
                $lastLineWasTable = false;
                continue;
            }

            if ($isTableRow) {
                if ($phase === 'explanation') {
                    $currentExplanations[] = $trimmed;
                } elseif ($phase === 'options' && !empty($currentOptions)) {
                    $lastIndex = count($currentOptions) - 1;
                    $currentOptions[$lastIndex] .= "\n" . $trimmed;
                } else {
                    $currentQuestion .= "\n" . $trimmed;
                }
                if ($lineImageFilename !== null) {
                    $currentImageFilename = $lineImageFilename;
                }
                $lastLineWasTable = true;
                continue;
            }

            $glue = $lastLineWasTable ? "\n" : ' ';
            if ($phase === 'explanation') {
                if (empty($currentExplanations)) {
                    $currentExplanations[] = $trimmed;
                } else {
                    $lastIndex = count($currentExplanations) - 1;
                    $currentExplanations[$lastIndex] .= $glue . $trimmed;
                }
            } elseif ($phase === 'options' && !empty($currentOptions)) {
                $lastIndex = count($currentOptions) - 1;
                $currentOptions[$lastIndex] .= $glue . $trimmed;
            } else {
                $currentQuestion .= $glue . $trimmed;
            }
            if ($lineImageFilename !== null) {
                $currentImageFilename = $lineImageFilename;
            }
            $lastLineWasTable = false;
        }

        $flush();

        return $questions;
    }

    private function extractAnswerKey($text)
    {
        $answerKey = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^(\d{1,4})[\.\)]\s+(.+)/', $line, $matches)) {
                $number = (int) $matches[1];
                $answer = trim($matches[2]);
                if (preg_match('/^[A-Za-z]$/', $answer)) {
                    $answerKey[$number] = strtoupper($answer);
                } else {
                    $answerKey[$number] = $answer;
                }
            }
            elseif (preg_match('/^(\d{1,4})\s*[=:]\s*(.+)/', $line, $matches)) {
                $number = (int) $matches[1];
                $answer = trim($matches[2]);
                if (preg_match('/^[A-Za-z]$/', $answer)) {
                    $answerKey[$number] = strtoupper($answer);
                } else {
                    $answerKey[$number] = $answer;
                }
            }
        }

        return $answerKey;
    }
}
