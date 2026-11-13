<?php

namespace Tests\Unit;

use App\Support\AnswerNormalizer;
use PHPUnit\Framework\TestCase;

class AnswerNormalizerTest extends TestCase
{
    public function test_matches_exact_numbers(): void
    {
        $this->assertTrue(AnswerNormalizer::isMatch('2500', '2500'));
        $this->assertTrue(AnswerNormalizer::isMatch(' 2500 ', '2500'));
        $this->assertFalse(AnswerNormalizer::isMatch('25000', '2500'));
        $this->assertFalse(AnswerNormalizer::isMatch('250', '2500'));
        $this->assertFalse(AnswerNormalizer::isMatch(null, '2500'));
        $this->assertFalse(AnswerNormalizer::isMatch('', '2500'));
    }

    public function test_matches_currency_and_indonesian_formatting(): void
    {
        $key = '2500';

        // Berbagai variasi penulisan uang yang harus benar
        $variations = [
            '2500',
            'Rp 2.500',
            'Rp 2500',
            'Rp. 2.500',
            'Rp. 2500',
            'Rp 2.500,-',
            'Rp. 2.500,-',
            '2.500,-',
            '2500,-',
            '2.500 Rupiah',
            '2500 Rupiah',
            '2.500 rupiah',
            '2500 rupiah',
            '2.500',
            '2,500',
            '2.500,00',
            'Rp 2.500,00',
            'IDR 2,500',
            'IDR 2.500',
            'Rp2500',
            'Rp2.500',
            'dua ribu lima ratus',
            'dua ribu lima ratus rupiah',
        ];

        foreach ($variations as $variation) {
            $this->assertTrue(
                AnswerNormalizer::isMatch($variation, $key),
                "Gagal mencocokkan variasi '{$variation}' dengan kunci '{$key}'"
            );
        }
    }

    public function test_matches_when_key_itself_is_formatted(): void
    {
        $key = 'Rp 2.500';

        $this->assertTrue(AnswerNormalizer::isMatch('2500', $key));
        $this->assertTrue(AnswerNormalizer::isMatch('2.500 Rupiah', $key));
        $this->assertTrue(AnswerNormalizer::isMatch('2.500,-', $key));
        $this->assertTrue(AnswerNormalizer::isMatch('dua ribu lima ratus', $key));
    }

    public function test_matches_with_accepted_answers_array(): void
    {
        $key = '2500';
        $accepted = ['dua ribu lima ratus', 'Rp 2.500,-', 'dua koma lima ribu'];

        $this->assertTrue(AnswerNormalizer::isMatch('2500', $key, $accepted));
        $this->assertTrue(AnswerNormalizer::isMatch('Rp 2.500', $key, $accepted));
        $this->assertTrue(AnswerNormalizer::isMatch('dua ribu lima ratus', $key, $accepted));
    }

    public function test_matches_with_accepted_answers_text(): void
    {
        $key = 'Ir. Soekarno';
        $accepted = ['Soekarno', 'Bung Karno', 'Presiden Soekarno'];

        $this->assertTrue(AnswerNormalizer::isMatch('soekarno', $key, $accepted));
        $this->assertTrue(AnswerNormalizer::isMatch('Bung Karno', $key, $accepted));
        $this->assertTrue(AnswerNormalizer::isMatch('ir. soekarno', $key, $accepted));
        $this->assertTrue(AnswerNormalizer::isMatch('Ir Soekarno', $key, $accepted));
        $this->assertFalse(AnswerNormalizer::isMatch('Mohammad Hatta', $key, $accepted));
    }

    public function test_matches_decimals_both_dot_and_comma(): void
    {
        $this->assertTrue(AnswerNormalizer::isMatch('2,5', '2.5'));
        $this->assertTrue(AnswerNormalizer::isMatch('2.5', '2,5'));
        $this->assertTrue(AnswerNormalizer::isMatch('2,5 kg', '2.5'));
        $this->assertTrue(AnswerNormalizer::isMatch('2.5 kg', '2,5 kg'));
        $this->assertTrue(AnswerNormalizer::isMatch('0,75', '0.75'));
    }
}
