<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WidalService
{
    protected array $map;
    protected array $mapReverse;
    protected array $mapDecode;
    protected array $vowelShort;
    protected array $digraphs = ['NG','NY','EU'];
    protected array $vowels = ['A','I','U','E','É','EU','O'];

    // Ubah ke true jika ingin menyimpan log ke storage
    protected bool $enableLog = false;

    public function __construct()
    {
        // Mapping huruf non-reversal
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

        // Mapping huruf reversal
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

        $this->mapDecode = [
            'NYA' => 'A',
            'H' => 'B',
            'J' => 'C',
            'P' => 'D',
            'NYE' => 'E',
            'D' => 'P',
            'S' => 'G',
            'B' => 'H',
            'NYI' => 'I',
            'C' => 'J',
            'N' => 'K',
            'R' => 'L',
            'Y' => 'M',
            'K' => 'N',
            'NYO' => 'O',
            'L' => 'R',
            'G' => 'S',
            'W' => 'T',
            'NYU' => 'U',
            'T' => 'W',
            'M' => 'Y',
            'NY' => 'NG',
            'NG' => 'NY',
            'NYÉ' => 'É',
            'NYEU' => 'EU',
            '(NYA)' => 'NGA',
            '(NYI)' => 'NGI',
            '(NYU)' => 'NGU',
            '(NYE)' => 'NGE',
            '(NYÉ)' => 'NGÉ',
            '(NYO)' => 'NGO',
            '(NYEU)' => 'NGEU',
            '(NY)' => 'NG',
            '(NG)' => 'NY',
            'NGA' => 'NYA',
            'NGI' => 'NYI',
            'NGU' => 'NYU',
            'NGE' => 'NYE',
            'NGÉ' => 'NYÉ',
            'NGO' => 'NYO',
            'NGEU' => 'NYEU',
        ];

        // Mapping huruf vokal non-reversal
        $this->vowelShort = [
            'NYA'  => 'A',
            'NYI'  => 'I',
            'NYU'  => 'U',
            'NYE'  => 'E',
            'NYÉ'  => 'É',
            'NYO'  => 'O',
            'NYEU' => 'EU',
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
        if ($this->enableLog) Log::info("-==========-");
        if ($this->enableLog) Log::info("This string has these tokens [".implode(',', $tokens)."]");
        if ($this->enableLog) Log::info("-==========-");

        $outputPart = [];

        // Looping token untuk mapping huruf
        foreach ($tokens as $key => $value) {
            $token = $value;

            // Skip jika huruf tidak ada di map
            if (!isset($this->map[$token]) || !isset($this->mapReverse[$token])) {
                $outputPart[] = $token;
                if ($this->enableLog) Log::info("[{$key}] Token '{$token}' isn't in this dictionary.");
                continue;
            }

            if ($reversal) {
                // Mapping huruf jika menggunakan reversal
                $mapped = $this->mapReverse[$token];
            } else {
                // Mapping huruf tanpa menggunakan reversal
                $mapped = $this->map[$token];
            }
            if ($this->enableLog) Log::info("[{$key}] First try mapping for token '{$token}' is '{$mapped}'.");

            // Jika token adalah huruf vokal
            if ($this->isVowelToken($token)) {
                if ($this->enableLog) Log::info("[{$key}] Because '{$token}' is Vowels, so it's remapped.");

                // Jika token adalah huruf vokal pertama maka gunakan "NY"
                if ($this->isWordStart($tokens, $key)) {
                    $outputPart[] = 'NY';
                    $outputPart[] = $this->vowelShort[$mapped] ?? $mapped;
                    if ($this->enableLog) Log::info("[{$key}] Token '{$token}' is the first vowel in this word, so it's remapped into '".$this->vowelShort[$mapped] ?? $mapped."'");
                    continue;
                }

                // Jika token sebelumnya huruf vokal maka gunakan "NY"
                $prev = $tokens[$key - 1];
                if ($this->isVowelToken($prev)) {
                    $outputPart[] = $mapped;
                    if ($this->enableLog) Log::info("[{$key}] Because previous token '{$prev}' is a vowels, so it's remapped into {$mapped}");
                    continue;
                }

                // Handler jika huruf terakhir tidak termapping
                if ($key === count($tokens) - 1) {
                    $outputPart[] = $this->vowelShort[$mapped] ?? $mapped;
                    if ($this->enableLog) Log::info("[{$key}][EX] Because previous token '{$prev}' is not a vowels, so it's not remapped");
                    continue;
                }

                // Jika token adalah huruf vokal di antara konsonan
                $outputPart[] = $this->vowelShort[$mapped] ?? $mapped;
                if ($this->enableLog) Log::info("[{$key}] Because previous token is not a vowels, so it's not remapped");
                continue;
            }

            // Jika token adalah konsonan / digraf
            $outputPart[] = $mapped;
        }

        // Gabungkan semua bagian hasil mapping
        $output = implode('', $outputPart);

        // Hapus 'NYNY' -> 'NY'
        $output = str_replace([
            'NYNY',
        ], [
            'NY',
        ], $output);

        if ($this->enableLog) Log::info("The result of this string remapping is '{$output}'");
        if ($this->enableLog) Log::info("-==========-");
        return mb_strtolower($output, 'UTF-8');
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
        if ($this->enableLog) Log::info("-==========-");
        if ($this->enableLog) Log::info("This string has these tokens [".implode(',', $tokens)."]");
        if ($this->enableLog) Log::info("-==========-");

        // Membuat decoded
        $decoded = [];

        // Looping token untuk mapping huruf
        foreach ($tokens as $key => $value) {
            $token = $value;

            // Skip jika huruf tidak ada di map
            if (!isset($this->mapDecode[$token])) {
                $decoded[] = $token;
                if ($this->enableLog) Log::info("[{$key}] Token '{$token}' isn't in this dictionary.");
                continue;
            }

            $mapped = $this->mapDecode[$token];

            $decoded[] = $mapped;
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
