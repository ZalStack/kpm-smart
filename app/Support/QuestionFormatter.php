<?php

namespace App\Support;

class QuestionFormatter
{
    /**
     * Render konten dengan format (LaTeX, tabel, dll)
     */
    public static function render(string $content): string
    {
        if (empty($content)) {
            return '';
        }

        // Proses tabel Markdown-style
        $content = self::renderTable($content);

        // Proses LaTeX (akan di-render oleh KaTeX di frontend)
        // Biarkan tanda $ tetap ada untuk KaTeX

        // Convert newlines to <br> but preserve table structure
        $lines = explode("\n", $content);
        $result = [];
        $inTable = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            // Skip if it's a table line (already rendered)
            if (strpos($line, '<div class="table-wrapper') !== false) {
                $result[] = $line;
                $inTable = true;
                continue;
            }
            if ($inTable && strpos($line, '</div>') !== false) {
                $result[] = $line;
                $inTable = false;
                continue;
            }
            if ($inTable) {
                $result[] = $line;
                continue;
            }
            // Regular text - escape and convert newlines
            if (!empty(trim($line))) {
                $result[] = nl2br(e($line));
            } else {
                $result[] = '<br>';
            }
        }

        return implode("\n", $result);
    }

    /**
     * Render tabel dari format Markdown
     */
    private static function renderTable(string $content): string
    {
        $lines = explode("\n", $content);
        $inTable = false;
        $tableRows = [];
        $result = [];
        $skipNext = false;

        foreach ($lines as $line) {
            if ($skipNext) {
                $skipNext = false;
                continue;
            }

            $trimmed = trim($line);

            // Deteksi baris tabel (diawali dan diakhiri |)
            if (preg_match('/^\|.*\|$/', $trimmed)) {
                if (!$inTable) {
                    $inTable = true;
                    $tableRows = [];
                }
                $tableRows[] = $trimmed;
                continue;
            }

            // Jika keluar dari tabel
            if ($inTable) {
                if (!empty($tableRows)) {
                    $result[] = self::buildTableHtml($tableRows);
                    $tableRows = [];
                }
                $inTable = false;
            }

            $result[] = $line;
        }

        // Jika tabel di akhir teks
        if ($inTable && !empty($tableRows)) {
            $result[] = self::buildTableHtml($tableRows);
        }

        return implode("\n", $result);
    }

    /**
     * Build HTML table dari baris Markdown
     */
    private static function buildTableHtml(array $rows): string
    {
        if (empty($rows)) {
            return '';
        }

        $html = '<div class="table-wrapper my-3.5 max-w-[480px] w-full overflow-x-auto rounded-lg shadow-sm border border-slate-800"><table class="w-full border-collapse text-center" style="width: 100%; max-width: 480px; border-collapse: collapse; margin: 0;">';
        $isHeader = true;

        foreach ($rows as $row) {
            // Remove leading/trailing |
            $row = trim($row, '|');
            $cells = array_map('trim', explode('|', $row));

            // Skip separator row (|---|)
            if (preg_match('/^[-:\s]+$/', implode('', $cells))) {
                $isHeader = false;
                continue;
            }

            $tag = $isHeader ? 'th' : 'td';
            $bg = $isHeader ? ' bg-[#0f2b48] text-white font-bold' : ' bg-white text-slate-800';
            $align = ' text-center';

            $html .= '<tr>';
            foreach ($cells as $cell) {
                $style = $isHeader
                    ? 'background-color: #0f2b48; color: #ffffff; font-weight: bold; text-align: center; padding: 10px 18px; border: 1px solid #1e293b; font-size: 16px; letter-spacing: 0.025em;'
                    : 'background-color: #ffffff; color: #1e293b; text-align: center; padding: 9px 18px; border: 1px solid #334155; font-size: 15px; font-weight: 500;';

                $html .= sprintf(
                    '<%s class="border border-slate-700 px-4 py-2%s%s" style="%s">%s</%s>',
                    $tag,
                    $bg,
                    $align,
                    $style,
                    htmlspecialchars($cell, ENT_QUOTES, 'UTF-8'),
                    $tag
                );
            }
            $html .= '</tr>';
            $isHeader = false;
        }

        $html .= '</table></div>';
        return $html;
    }

    /**
     * Format teks soal mentah (dari import PDF) menjadi HTML yang siap tampil.
     * Mengkonversi tabel Markdown, tag [GAMBAR:...], dan baris baru.
     * NOTE: Teks dari PDF dianggap trusted (admin upload), jadi tidak perlu escape.
     */
    public static function formatImportedText(string $text, array $imageMap = []): string
    {
        if (empty($text)) {
            return '';
        }

        // 1. Konversi tag [GAMBAR:filename] menjadi <img> HTML (sebelum escape)
        $text = self::convertImageTags($text, $imageMap);

        // 2. Render tabel Markdown-style menjadi HTML
        $text = self::renderTable($text);

        // 3. Convert newlines ke <br>, tapi jangan di dalam tabel atau tag HTML
        $lines = explode("\n", $text);
        $result = [];
        $inTable = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (strpos($line, '<div class="table-wrapper') !== false) {
                $result[] = $line;
                $inTable = true;
                continue;
            }
            if ($inTable && strpos($line, '</div>') !== false) {
                $result[] = $line;
                $inTable = false;
                continue;
            }
            if ($inTable) {
                $result[] = $line;
                continue;
            }
            // Skip baris yang sudah berisi tag <img> atau <table>
            if (preg_match('/^<(img|table|div)/i', $trimmed)) {
                $result[] = $line;
                continue;
            }
            if (!empty(trim($line))) {
                $result[] = nl2br(e($line));
            } else {
                $result[] = '<br>';
            }
        }

        return implode("\n", $result);
    }

    /**
     * Konversi tag [GAMBAR:filename] menjadi tag <img> HTML
     */
    public static function convertImageTags(string $content, array $imageMap = []): string
    {
        return preg_replace_callback('/\[GAMBAR\s*:\s*([^\]]+)\]/i', function ($matches) use ($imageMap) {
            $filename = trim($matches[1]);
            $key = mb_strtolower($filename);

            // Cari di imageMap yang sudah di-import
            if (isset($imageMap[$key])) {
                $path = $imageMap[$key];
                if (filter_var($path, FILTER_VALIDATE_URL)) {
                    return '<img src="' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8') . '" alt="Gambar soal" class="max-w-full h-auto rounded-md my-2" />';
                }
                return '<img src="/storage/' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8') . '" alt="Gambar soal" class="max-w-full h-auto rounded-md my-2" />';
            }

            // Fallback: coba path dengan subfolder auto/
            $autoPath = 'question_images/auto/' . $filename;
            $autoFullPath = public_path('storage/' . $autoPath);
            if (file_exists($autoFullPath)) {
                return '<img src="/storage/' . htmlspecialchars($autoPath, ENT_QUOTES, 'UTF-8') . '" alt="Gambar soal" class="max-w-full h-auto rounded-md my-2" />';
            }

            // Tidak ditemukan - hapus tag daripada tampilkan gambar 404/403
            return '<span class="text-xs text-muted-foreground italic">[gambar tidak tersedia]</span>';
        }, $content);
    }

    /**
     * Get URL untuk gambar soal
     */
    public static function imageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // Jika path sudah berupa URL lengkap
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return asset('storage/' . $path);
    }
}
