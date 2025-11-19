<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WidalService
{
    protected array $map;
    protected array $vowelShort;
    protected array $ambiguousMap;
    // Digraf
    protected array $digraphs = ['NG','NY','EU'];
    // Huruf Vokal Basa Sunda
    protected array $vowels = ['A','I','U','E','É','EU','O'];

    public function __construct()
    {
        // Mapping huruf
        $this->map = [
            'A'   => 'NYA',
            'B'  => 'H',
            'C'  => 'J',
            'D'  => 'P',
            'E'   => 'NYE',
            'F'  => 'D',
            'G'  => 'S',
            'H'  => 'B',
            'I'   => 'NYI',
            'J'  => 'C',
            'K'  => 'N',
            'L'  => 'R',
            'M'  => 'Y',
            'N'  => 'K',
            'O'   => 'NYO',
            'P'  => 'D',
            'Q'  => 'N',
            'R'  => 'L',
            'S'  => 'G',
            'T'  => 'W',
            'U'   => 'NYU',
            'V'  => 'D',
            'W'  => 'T',
            'X'  => 'N',
            'Y'  => 'M',
            'Z'  => 'C',
            'NG' => "NY",
            'NY' => "NG",
            'É'   => 'NYÉ',
            'EU'  => 'NYEU',
        ];

        $this->mapReverse = [
            'A'   => 'NYA',
            'B'  => 'H',
            'C'  => 'J',
            'D'  => 'P',
            'E'   => 'NYE',
            'F'  => 'D',
            'G'  => 'S',
            'H'  => 'B',
            'I'   => 'NYI',
            'J'  => 'C',
            'K'  => 'N',
            'L'  => 'R',
            'M'  => 'Y',
            'N'  => 'K',
            'O'   => 'NYO',
            'P'  => 'D',
            'Q'  => 'N',
            'R'  => 'L',
            'S'  => 'G',
            'T'  => 'W',
            'U'   => 'NYU',
            'V'  => 'D',
            'W'  => 'T',
            'X'  => 'N',
            'Y'  => 'M',
            'Z'  => 'C',
            'NG' => "(NY)",
            'NY' => "(NG)",
            'É'   => 'NYÉ',
            'EU'  => 'NYEU',
        ];

        // Mapping huruf ambigu
        $this->ambiguousMap = [
            // J/Z -> C
            'J' => 'C', 'Z' => 'C', 'C' => 'J',
            // P/V/F -> D
            'P' => 'D', 'V' => 'D', 'F' => 'D', 'D' => 'P',
            // K/X/Q -> N
            'K' => 'N', 'X' => 'N', 'Q' => 'N', 'N' => 'K',
        ];

        // Mapping huruf vokal
        $this->vowelShort = [
            'NYA'  => 'A',
            'NYI'  => 'I',
            'NYU'  => 'U',
            'NYE'  => 'E',
            'NYÉ'  => 'É',
            'NYO'  => 'O',
            'NYEU' => 'EU',
        ];

        $this->vowelShortReverse = [
            "(NYA)"  => 'NGA',
            "(NYI)"  => 'NGI',
            "(NYU)"  => 'NGU',
            "(NYE)"  => 'NGE',
            "(NYÉ)"  => 'NGÉ',
            "(NYO)"  => 'NGO',
            "(NYEU)" => 'NGEU',
            '(NY)' => "NG",
            '(NG)' => "NY",
            'NGA'  => 'NYA',
            'NGI'  => 'NYI',
            'NGU'  => 'NYU',
            'NGE'  => 'NYE',
            'NGÉ'  => 'NYÉ',
            'NGO'  => 'NYO',
            'NGEU' => 'NYEU',
        ];
    }

    /** ---- Transformasi ---- */

    /**
     * Encode string ke Bahasa Widal
     */
    public function encode(string $input, bool $reversal = false): string
    {
        // Normalisasi input
        if (function_exists('Normalizer::normalize')) {
            $input = \Normalizer::normalize($input, \Normalizer::FORM_C);
        }

        // Ubah input ke huruf besar
        $original = mb_strtoupper($input, 'UTF-8');

        // Tokenisasi huruf
        $tokens = $this->tokenizeDigraphsAndAccents($original);
        $countTokens = count($tokens);

        $outParts = [];
        $prefixNY = false;

        // Looping token untuk mapping huruf
        for ($index = 0; $index < $countTokens; $index++) {
            // Token
            $token = $tokens[$index];

            if ($reversal) {
                if (!isset($this->mapReverse[$token])) {
                    $outParts[] = $token;
                    continue;
                }

                // Mapping huruf
                $mapped = $this->mapReverse[$token];
            } else {
                if (!isset($this->map[$token])) {
                    $outParts[] = $token;
                    continue;
                }

                // Mapping huruf
                $mapped = $this->map[$token];
            }

            // Jika token adalah huruf vokal
            if ($this->isVowelToken($token)) {
                // Jika token adalah huruf vokal pertama
                // if ($index === 0) {
                //     $prefixNY = true;
                //     $outParts[] = $this->vowelShort[$mapped] ?? $mapped;
                //     continue;
                // }
                if ($this->isWordStart($tokens, $index)) {
                    $outParts[] = 'NY';
                    $outParts[] = $this->vowelShort[$mapped] ?? $mapped;
                    continue;
                }

                // Jika token adalah huruf vokal kedua
                $prev = $tokens[$index - 1];
                if ($this->isVowelToken($prev)) {
                    $outParts[] = $mapped;
                    continue;
                }

                // Jika token adalah huruf vokal terakhir
                if ($index === $countTokens - 1) {
                    // previous token already checked above: if prev is vowel we would have taken mapped
                    // so reaching here means prev is NOT vowel -> emit short letter
                    $outParts[] = $this->vowelShort[$mapped] ?? $mapped;
                    continue;
                }

                // Jika token adalah huruf vokal di antara konsonan
                $outParts[] = $this->vowelShort[$mapped] ?? $mapped;
                continue;
            }

            // Jika token adalah konsonan / digraf
            $outParts[] = $mapped;
        }

        $out = implode('', $outParts);

        // Jika string dimulai dengan huruf vokal, tambahkan 'NY' (menghindari double NY di awal)
        if ($prefixNY) {
            if (mb_substr($out, 0, 2, 'UTF-8') === 'NY') {
                $out = 'NY' . mb_substr($out, 2, null, 'UTF-8');
            } else {
                $out = 'NY' . $out;
            }
        }

        // Hapus 'NYNY' -> 'NY'
        $out = str_replace([
            'NYNY',
            // 'ONYÉ',
            // 'ONYE',
        ], [
            'NY',
            // 'OÉ',
            // 'OE',
        ], $out);

        return mb_strtolower($out, 'UTF-8');
    }

    /**
     * Decode string dari Bahasa Widal ke Bahasa Normal
     */
    public function decode(string $input): string
    {
        // Normalisasi input
        if (function_exists('Normalizer::normalize')) {
            $input = \Normalizer::normalize($input, \Normalizer::FORM_C);
        }

        // Ubah input ke huruf besar
        $string = mb_strtoupper($input, 'UTF-8');

        // Tokenisasi huruf
        $tokens = $this->tokenizeForDecode($string);

        // Jika token awal adalah 'NY' dan token kedua adalah huruf vokal
        if (isset($tokens[0]) && $tokens[0] === 'NY' && isset($tokens[1])) {
            // Jika token kedua adalah huruf vokal
            $shortVowels = array_keys($this->vowelShortReverse);
            if (in_array($tokens[1], $shortVowels, true)) {
                // Hapus token awal 'NY'
                array_shift($tokens);
                $startedWithVowel = true;
            } else {
                $startedWithVowel = false;
            }
        } else {
            $startedWithVowel = false;
        }

        $reverse = [];

        // Membuat reverse map
        foreach ($this->map as $orig => $out) {
            // Jika output ada di reverse map
            $reverse[$out][] = $orig;
        }

        // Menghilangkan ambigu
        foreach($reverse as $key => $value) {
            if (in_array($key, $this->ambiguousMap, true)) {
                $reverse[$key] = $this->ambiguousMap[$key];
            }
        }

        // Menambahkan reverse entries untuk huruf vokal pendek
        foreach ($this->vowelShortReverse as $nyv => $short) {
            // Jika output ada di reverse map
            $reverse[$nyv][] = $short;
        }

        // Membuat decoded
        $decoded = [];

        foreach ($tokens as $pos => $tok) {
            if (isset($reverse[$tok])) {
                $cands = $reverse[$tok];
                $decoded[] = $cands[0];
            } else {
                // Jika token tidak ada di reverse map (punctuation, digits, unknown) - pass through
                $decoded[] = $tok;
            }
        }

        // Membuat string
        $result = implode('', $decoded);

        return mb_strtolower($result, 'UTF-8');
    }

    /** ---- Helpers ---- */

    // Tokenisasi huruf
    protected function tokenizeDigraphsAndAccents(string $string): array
    {
        $tokens = [];
        $index = 0;
        $length = mb_strlen($string, 'UTF-8');

        while ($index < $length) {
            $two = mb_substr($string, $index, 2, 'UTF-8');

            // Jika token adalah digraf
            if (in_array($two, $this->digraphs, true)) {
                $tokens[] = $two;
                $index += 2;
                continue;
            }

            // Jika token adalah huruf
            $one = mb_substr($string, $index, 1, 'UTF-8');
            $tokens[] = $one;
            $index += 1;
        }

        return $tokens;
    }

    // Tokenisasi huruf untuk decode
    protected function tokenizeForDecode(string $string): array
    {
        $tokens = [];
        $index = 0;
        $length = mb_strlen($string, 'UTF-8');

        $nyPatterns = [
            '(NYEU)', '(NYÉ)', '(NYA)', '(NYE)', '(NYI)', '(NYO)', '(NYU)','(NY)', '(NG)',
            'NYEU', 'NYÉ', 'NYA', 'NYE', 'NYI', 'NYO', 'NYU',
            'NGEU', 'NGÉ', 'NGA', 'NGE', 'NGI', 'NGO', 'NGU'
        ];

        while ($index < $length) {
            $matched = false;
            
            // Cek pattern
            foreach ($nyPatterns as $pattern) {
                $patternLength = mb_strlen($pattern, 'UTF-8');

                // Jika pattern ditemukan
                if (mb_substr($string, $index, $patternLength, 'UTF-8') === $pattern) {
                    $tokens[] = $pattern;
                    $index += $patternLength;
                    $matched = true;
                    break;
                }
            }
            // Jika pattern tidak ditemukan
            if ($matched) continue;

            // Jika token adalah 'NY'
            if (mb_substr($string, $index, 2, 'UTF-8') === 'NY') {
                $tokens[] = 'NY';
                $index += 2;
                continue;
            } elseif (mb_substr($string, $index, 2, 'UTF-8') === 'NG') {
                $tokens[] = 'NG';
                $index += 2;
                continue;
            }

            // Jika token adalah huruf
            $tokens[] = mb_substr($string, $index, 1, 'UTF-8');
            $index += 1;
        }

        return $tokens;
    }

    // Cek apakah token adalah huruf vokal
    protected function isVowelToken(string $t): bool
    {
        return in_array($t, $this->vowels, true);
    }

    protected function isWordStart(array $tokens, int $i): bool
    {
        if ($i === 0) return true;
        $prev = $tokens[$i - 1];
        if (preg_match('/\p{L}/u', $prev) === 1) {
            return false;
        }
        return true;
    }
}
