<?php

namespace App\Services;

class WidalService
{
    protected array $map;
    protected array $vowelShort;
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
            'NG' => 'NY',
            'NY' => 'NG',
            'É'   => 'NYÉ',
            'EU'  => 'NYEU',
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
    }

    /** ---- Transformasi ---- */

    /**
     * Encode string ke Bahasa Widal
     */
    public function encode(string $input): string
    {
        $orig = mb_strtoupper($input, 'UTF-8');

        // Tokenisasi huruf
        $tokens = $this->tokenizeDigraphsAndAccents($orig);
        $countTokens = count($tokens);

        $outParts = [];
        $prefixNY = false;

        // Looping token untuk mapping huruf
        for ($i = 0; $i < $countTokens; $i++) {
            // Token
            $token = $tokens[$i];

            // Jika token tidak ada di mapping huruf
            if (!isset($this->map[$token])) {
                $outParts[] = $token;
                continue;
            }

            // Mapping huruf
            $mapped = $this->map[$token];

            // Jika token adalah huruf vokal
            if ($this->isVowelToken($token)) {
                // Jika token adalah huruf vokal pertama
                if ($i === 0) {
                    $prefixNY = true;
                    $outParts[] = $this->vowelShort[$mapped] ?? $mapped;
                    continue;
                }

                // Jika token adalah huruf vokal kedua
                $prev = $tokens[$i - 1];
                if ($this->isVowelToken($prev)) {
                    $outParts[] = $mapped;
                    continue;
                }

                // Jika token adalah huruf vokal terakhir
                if ($i === $countTokens - 1) {
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
        $out = str_replace(['NYNY', 'ONYÉ', 'ONYE'], ['NY', 'OÉ', 'OE'], $out);

        return mb_strtolower($out, 'UTF-8');
    }

    /**
     * Decrypt string dari Bahasa Widal ke Bahasa Normal
     */
    public function decrypt(string $input): string
    {
        // 
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

    // Cek apakah token adalah huruf vokal
    protected function isVowelToken(string $t): bool
    {
        return in_array($t, $this->vowels, true);
    }
}
