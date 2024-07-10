<?php

namespace Jotahdavid\Cpf;

class Cpf {
    public const FISCAL_REGIONS = [
        ['RS'],
        ['DF', 'GO', 'MS', 'MT', 'TO'],
        ['AC', 'AM', 'AP', 'PA', 'RO', 'RR'],
        ['CE', 'MA', 'PI'],
        ['AL', 'PB', 'PE', 'RN'],
        ['BA', 'SE'],
        ['MG'],
        ['ES', 'RJ'],
        ['SP'],
        ['PR', 'SC'],
    ];

    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getFiscalRegion(): array
    {
        if (!$this->isValid()) {
            return [];
        }

        $cpf = $this->format();
        $digit = intval($cpf[10]);

        return self::FISCAL_REGIONS[$digit];
    }

    public function format(): ?string
    {
        $numbers = $this->getNumbers();

        if (strlen($numbers) < 11) {
            return null;
        }

        $formattedCpf = preg_replace_callback('/\d{3}/', fn ($match) => $match[0] . '.', $numbers);
        $formattedCpf = preg_replace_callback('/\.\d{2}$/', fn ($match) => '-' . substr($match[0], 1), $formattedCpf);

        return $formattedCpf;
    }

    public function isValid(): bool
    {
        $numbersList = str_split($this->getNumbers());

        [$firstDigit, $secondDigit] = array_splice($numbersList, -2);

        $firstVerifyingDigit = $this->calculateVerifyingDigit($numbersList);

        $numbersList[] = (string) $firstVerifyingDigit;

        $secondVerifyingDigit = $this->calculateVerifyingDigit(array_slice($numbersList, 1));

        return intval($firstDigit) === $firstVerifyingDigit && intval($secondDigit) === $secondVerifyingDigit;
    }

    private function getNumbers(): string
    {
        return substr(preg_replace('/[^\d]/', '', $this->value), 0, 11);
    }

    private function calculateVerifyingDigit(array $digits = []): int
    {
        $acc = 0;

        foreach ($digits as $index => $digit) {
            $acc += intval($digit) * ($index + 1);
        }

        return ($acc % 11) % 10;
    }
}
