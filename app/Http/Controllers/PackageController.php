<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;
use ZipArchive;

class PackageController extends Controller
{
    public function index()
    {
        $query = Package::where('is_active', true);

        if (request('kelas')) {
            $query->where('kelas', request('kelas'));
        }
        if (request('jenjang')) {
            $query->where('jenjang', request('jenjang'));
        }

        $packages = $query->paginate(12);
        $allKelas = Package::where('is_active', true)->whereNotNull('kelas')->where('kelas', '!=', '')->distinct()->pluck('kelas')->sort()->values();
        $allJenjang = Package::where('is_active', true)->whereNotNull('jenjang')->where('jenjang', '!=', '')->distinct()->pluck('jenjang')->sort()->values();

        return view('packages.index', compact('packages', 'allKelas', 'allJenjang'));
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

        if ($request->filled('jenjang')) {
            $query->where('jenjang', $request->jenjang);
        }

        $packages = $query->latest()->paginate(10)->withQueryString();
        $allKelas = Package::whereNotNull('kelas')->where('kelas', '!=', '')->distinct()->pluck('kelas')->sort()->values();
        $allJenjang = Package::whereNotNull('jenjang')->where('jenjang', '!=', '')->distinct()->pluck('jenjang')->sort()->values();

        return view('admin.packages.index', compact('packages', 'allKelas', 'allJenjang'));
    }

    public function adminShow(Package $package)
    {
        $cards = $package->cards ?? [];
        $allQuestions = $package->questions ?? [];
        $questionsByCard = collect($allQuestions)->groupBy('card_id');
        $totalCards = count($cards);
        $totalQuestions = count($allQuestions);
        $totalOrders = $package->orders()->count();
        $paidOrders = $package->orders()->where('payment_status', 'paid')->count();
        $totalRevenue = $package->orders()->where('payment_status', 'paid')->sum('total_price');
        $totalPracticeSessions = $package->practiceSessions()->count();

        return view('admin.packages.show', compact(
            'package', 'cards', 'allQuestions', 'questionsByCard',
            'totalCards', 'totalQuestions', 'totalOrders', 'paidOrders',
            'totalRevenue', 'totalPracticeSessions'
        ));
    }

    public function create()
    {
        return view('admin.packages.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'kelas' => 'nullable|string|max:255',
            'jenjang' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'hide_explanation' => 'nullable|boolean',
            'time_limit_minutes' => 'nullable|integer|min:0|max:480',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_discount_active' => 'nullable|boolean',
            'discount_price' => 'nullable|required_if:is_discount_active,1|numeric|min:0|lt:price',
            'is_pay_what_you_want' => 'nullable|boolean',
            'min_pay_amount' => 'nullable|numeric|min:0',
            'membership_duration_days' => 'required|integer|min:1|max:3650',
        ], [
            'discount_price.lt' => 'Harga diskon harus lebih murah dari harga normal.',
            'discount_price.required_if' => 'Harga diskon wajib diisi jika diskon diaktifkan.',
            'membership_duration_days.required' => 'Durasi membership wajib diisi.',
            'membership_duration_days.min' => 'Durasi membership minimal 1 hari.',
            'time_limit_minutes.max' => 'Batas waktu pengerjaan maksimal 480 menit (8 jam).',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $package = Package::create([
            'title' => $request->title,
            'description' => $request->description,
            'kelas' => $request->kelas,
            'jenjang' => $request->jenjang,
            'price' => $request->price,
            'discount_price' => $request->boolean('is_discount_active') ? $request->discount_price : null,
            'is_discount_active' => $request->boolean('is_discount_active'),
            'is_pay_what_you_want' => $request->boolean('is_pay_what_you_want'),
            'min_pay_amount' => $request->min_pay_amount ?? 0,
            'membership_duration_days' => $request->membership_duration_days,
            'is_active' => $request->is_active ?? true,
            'hide_explanation' => $request->boolean('hide_explanation'),
            'time_limit_minutes' => $request->time_limit_minutes ?: null,
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
        return view('admin.packages.edit.informasi', compact('package'));
    }

    public function editCards(Package $package)
    {
        $cardsMap = collect($package->cards ?? [])->keyBy('id');
        $allQuestions = array_values($package->questions ?? []);
        $questionsByCard = collect($allQuestions)->groupBy('card_id');

        return view('admin.packages.edit.cards', compact('package', 'cardsMap', 'questionsByCard'));
    }

    public function editQuestions(Package $package)
    {
        $cardsMap = collect($package->cards ?? [])->keyBy('id');
        $allQuestions = array_values($package->questions ?? []);
        $questionsByCard = collect($allQuestions)->groupBy('card_id');
        $totalCards = count($package->cards ?? []);
        $totalQuestions = count($allQuestions);

        return view('admin.packages.edit.questions', compact('package', 'cardsMap', 'allQuestions', 'questionsByCard', 'totalCards', 'totalQuestions'));
    }

    // ===================== UPDATE METHODS =====================

    public function update(Request $request, Package $package)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'kelas' => 'nullable|string|max:255',
            'jenjang' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'hide_explanation' => 'nullable|boolean',
            'time_limit_minutes' => 'nullable|integer|min:0|max:480',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_discount_active' => 'nullable|boolean',
            'discount_price' => 'nullable|required_if:is_discount_active,1|numeric|min:0|lt:price',
            'is_pay_what_you_want' => 'nullable|boolean',
            'min_pay_amount' => 'nullable|numeric|min:0',
            'membership_duration_days' => 'required|integer|min:1|max:3650',
        ], [
            'discount_price.lt' => 'Harga diskon harus lebih murah dari harga normal.',
            'discount_price.required_if' => 'Harga diskon wajib diisi jika diskon diaktifkan.',
            'membership_duration_days.required' => 'Durasi membership wajib diisi.',
            'membership_duration_days.min' => 'Durasi membership minimal 1 hari.',
            'time_limit_minutes.max' => 'Batas waktu pengerjaan maksimal 480 menit (8 jam).',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $package->update([
            'title' => $request->title,
            'description' => $request->description,
            'kelas' => $request->kelas,
            'jenjang' => $request->jenjang,
            'price' => $request->price,
            'discount_price' => $request->boolean('is_discount_active') ? $request->discount_price : null,
            'is_discount_active' => $request->boolean('is_discount_active'),
            'is_pay_what_you_want' => $request->boolean('is_pay_what_you_want'),
            'min_pay_amount' => $request->min_pay_amount ?? 0,
            'membership_duration_days' => $request->membership_duration_days,
            'is_active' => $request->is_active ?? true,
            'hide_explanation' => $request->boolean('hide_explanation'),
            'time_limit_minutes' => $request->time_limit_minutes ?: null,
        ]);

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $package->update(['thumbnail' => $path]);
        }

        return redirect()->route('admin.packages.edit.informasi', $package)->with('success', 'Informasi paket berhasil diperbarui!');
    }

    public function confirmDelete(Package $package)
    {
        $totalCards = count($package->cards ?? []);
        $totalQuestions = count($package->questions ?? []);
        $totalOrders = $package->orders()->count();
        $totalPracticeSessions = $package->practiceSessions()->count();

        return view('admin.packages.confirm-delete', compact(
            'package', 'totalCards', 'totalQuestions', 'totalOrders', 'totalPracticeSessions'
        ));
    }

    public function destroy(Package $package)
    {
        $hasActiveMembership = $package->orders()
            ->where('payment_status', 'paid')
            ->where('membership_end', '>=', now())
            ->exists();

        if ($hasActiveMembership) {
            return redirect()->route('admin.packages.show', $package)
                ->with('error', 'Tidak dapat menghapus paket karena masih ada user dengan membership aktif!');
        }

        $package->delete();
        return redirect()->route('admin.packages.index')->with('success', 'Paket berhasil dihapus!');
    }

    public function show(Package $package)
    {
        if (!$package->is_active) {
            return redirect()->route('packages.index')->with('error', 'Paket tidak tersedia!');
        }

        $order = null;
        $enrollmentReady = false;
        $hasAccess = false;
        $membershipActive = false;
        $membershipExpired = false;

        if (auth()->check()) {
            $order = Order::latestPaidFor(auth()->id(), $package->id);

            if ($order) {
                $enrollmentReady = $order->enrollmentIsReady();
                $membershipActive = $order->isMembershipActive();
                $membershipExpired = $order->membershipStatus() === 'expired';
                $hasAccess = $order->enrollmentIsUnlocked() && $membershipActive;
            }
        }

        $totalCards = count($package->cards ?? []);
        $totalQuestions = count($package->questions ?? []);
        $videos = $package->videos()->where('is_active', true)->get();

        return view('packages.show', [
            'package' => $package,
            'order' => $order,
            'enrollmentReady' => $enrollmentReady,
            'hasAccess' => $hasAccess,
            'membershipActive' => $membershipActive,
            'membershipExpired' => $membershipExpired,
            'totalCards' => $totalCards,
            'totalQuestions' => $totalQuestions,
            'videos' => $videos,
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

        $package->update(['cards' => array_values($cards)]);

        return redirect()->route('admin.packages.edit.cards', $package)->with('success', 'Card berhasil dihapus!');
    }

    // ===================== QUESTION METHODS =====================

    public function createQuestion(Package $package)
    {
        $cards = $package->cards ?? [];
        return view('admin.packages.edit.question-form', compact('package', 'cards'));
    }

    public function editQuestion(Package $package, $questionId)
    {
        $cards = $package->cards ?? [];
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

        return view('admin.packages.edit.question-form', compact('package', 'cards', 'question'));
    }

    public function addQuestion(Request $request, Package $package)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
            'options' => 'required|array|min:2',
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

        $questions = $package->questions ?? [];
        $questions[] = [
            'id' => (string) Str::uuid(),
            'card_id' => $request->card_id,
            'question' => $request->question,
            'options' => $request->options,
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
            'options' => 'required|array|min:2',
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

            $questions[$i]['card_id'] = $request->card_id;
            $questions[$i]['question'] = $request->question;
            $questions[$i]['options'] = $request->options;
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
        $cards = $package->cards ?? [];
        return view('admin.packages.edit.import-pdf', compact('package', 'cards'));
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
            $pdf = $parser->parseFile($request->file('pdf_file')->getPathname());
            $text = $pdf->getText();

            if (trim($text) === '') {
                return back()->with('error', 'PDF tidak mengandung teks yang bisa dibaca. Jika PDF ini hasil scan/foto, convert dulu ke PDF berbasis teks sebelum di-import (fitur ini sengaja tidak memakai OCR).');
            }

            $questions = $this->extractQuestionsFromText($text);

            if (empty($questions)) {
                return back()->with('error', 'Tidak dapat mengekstrak soal dari PDF. Pastikan format PDF sesuai template: setiap soal diawali nomor + titik (contoh "1. Ibu kota Indonesia adalah...."), lalu pilihan jawaban di baris terpisah diawali huruf A-E + titik (contoh "A. Jakarta"), dan nomor soal berurutan (1, 2, 3, dst).');
            }

            $answerKey = [];
            if ($request->hasFile('answer_key_pdf')) {
                $answerPdf = $parser->parseFile($request->file('answer_key_pdf')->getPathname());
                $answerText = $answerPdf->getText();
                $answerKey = $this->extractAnswerKey($answerText);
            }

            $imageMap = [];
            if ($request->hasFile('images_zip')) {
                $imageMap = $this->extractImagesZip($request->file('images_zip'), $package->id);
            }

            $embeddedImages = $this->extractEmbeddedImagesFromPdf($request->file('pdf_file')->getPathname(), $package->id);
            $questionPageMap = $this->mapQuestionNumbersToPages($request->file('pdf_file')->getPathname(), $questions);
            $autoImageMap = $this->assignAutoImagesToQuestions($embeddedImages, $questionPageMap, $questions);

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

                $newQuestions[] = [
                    'id' => (string) Str::uuid(),
                    'card_id' => $request->card_id,
                    'question' => $question['question'],
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
                $message .= ' 📸 ' . $autoAttachedCount . ' gambar otomatis terdeteksi & terpasang.';
            }

            if ($skippedNoAnswer > 0) {
                $message .= ' ⚠️ ' . $skippedNoAnswer . ' soal belum punya jawaban benar (tidak ditemukan di PDF kunci jawaban) — cek & lengkapi manual di daftar soal.';
            }

            if (!empty($missingImages)) {
                $message .= ' ⚠️ Gambar tidak ditemukan di ZIP untuk: ' . implode(', ', array_unique($missingImages)) . '.';
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
        $expectedOptionOrd = null;
        $lastLineWasTable = false;

        $flush = function () use (
            &$questions, &$currentQuestion, &$currentOptions, &$currentNumber,
            &$currentAnswer, &$currentExplanations, &$currentImageFilename
        ) {
            if ($currentQuestion === null) {
                return;
            }
            $questions[] = [
                'number' => $currentNumber,
                'question' => trim($currentQuestion),
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

            $isTableRow = (bool) preg_match('/^\|.*\|$/', $trimmed);

            $isNewQuestion = false;
            $newNumber = null;
            if (!$isTableRow && preg_match('/^(\d{1,4})[\.\)]\s+(?=\S)/', $trimmed, $matches)) {
                $newNumber = (int) $matches[1];
                if ($expectedNumber === null || $newNumber === $expectedNumber) {
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
                $expectedOptionOrd = ord('A');
                $lastLineWasTable = false;
                continue;
            }

            if ($currentQuestion === null) {
                continue;
            }

            if (!$isTableRow && preg_match('/^(jawaban|kunci\s*jawaban|kunci)\s*[:\-]?\s*([A-Za-z])\b/i', $trimmed, $matches)) {
                $currentAnswer = strtoupper($matches[2]);
                $currentExplanations[] = $trimmed;
                if ($lineImageFilename !== null) {
                    $currentImageFilename = $lineImageFilename;
                }
                $phase = 'explanation';
                $lastLineWasTable = false;
                continue;
            }

            if (!$isTableRow && $phase !== 'explanation'
                && preg_match('/^([A-Za-z])[\.\)]\s+(?=\S)/', $trimmed, $matches)) {
                $letterOrd = ord(strtoupper($matches[1]));
                if ($letterOrd === $expectedOptionOrd) {
                    $optionText = preg_replace('/^([A-Za-z])[\.\)]\s+/', '', $trimmed);
                    $currentOptions[] = $optionText;
                    $expectedOptionOrd = $letterOrd + 1;
                    $phase = 'options';
                    if ($lineImageFilename !== null) {
                        $currentImageFilename = $lineImageFilename;
                    }
                    $lastLineWasTable = false;
                    continue;
                }
            }

            if (!$isTableRow && $phase !== 'explanation' && stripos($trimmed, 'pembahasan') === 0) {
                $currentExplanations[] = $trimmed;
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
            if (preg_match('/^(\d{1,4})[\.\)]\s+([A-Za-z])\b/', $line, $matches)) {
                $number = (int) $matches[1];
                $answer = strtoupper($matches[2]);
                $answerKey[$number] = $answer;
            }
            elseif (preg_match('/^(\d{1,4})\s*[=:]\s*([A-Za-z])\b/', $line, $matches)) {
                $number = (int) $matches[1];
                $answer = strtoupper($matches[2]);
                $answerKey[$number] = $answer;
            }
        }

        return $answerKey;
    }
}
