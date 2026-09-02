<?php

namespace App\Support;

class QuestionFormatter
{
    /**
     * Render konten dengan format (LaTeX, tabel, dll)
     */
    public static function render($content)
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
    private static function renderTable($content)
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
    private static function buildTableHtml($rows)
    {
        if (empty($rows)) {
            return '';
        }

        $html = '<div class="table-wrapper my-3"><table class="min-w-full border-collapse border border-gray-300 text-sm">';
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
            $align = $isHeader ? ' text-left' : '';
            $bg = $isHeader ? ' bg-gray-50' : '';

            $html .= '<tr>';
            foreach ($cells as $cell) {
                $html .= sprintf(
                    '<%s class="border border-gray-300 px-3 py-1.5%s%s">%s</%s>',
                    $tag,
                    $bg,
                    $align,
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
     * Get URL untuk gambar soal
     */
    public static function imageUrl($path): ?string
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
