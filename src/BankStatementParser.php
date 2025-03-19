<?php

include 'src/Platezhka.php';
include 'src/Kontragent.php';

class BankStatementParser
{
    private string $filePath;
    private array $fileContent;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function parse(): array
    {
        $this->readFile();
        $platezhki = [];
        $currentPlatezhka = null;

        foreach ($this->fileContent as $line) {
            $line = rtrim($line);
            $line = mb_convert_encoding($line, "utf-8", "windows-1251"); // Конвертируем значение в utf-8 так как изначальная кодировка файла windows-1251

            if (str_starts_with($line, 'СекцияДокумент=Платежное поручение')) {
                $currentPlatezhka = new Platezhka();
            } elseif (str_starts_with($line, 'КонецДокумента')) {
                if ($currentPlatezhka !== null) {
                    $platezhki[] = $currentPlatezhka;
                }
                $currentPlatezhka = null;
            } elseif ($currentPlatezhka !== null) {
                $this->parseLine($line, $currentPlatezhka);
            }

        }

        return $platezhki;

    }

    private function parseLine(string $line, Platezhka $platezhka): void
    {
        if (strpos($line, '=') === false) {
            return;
        }

        [$key, $value] = explode("=", $line, 2);
        $key = trim($key);
        $value = trim($value, "\"");

        switch ($key) {
            case 'Дата':
                $platezhka->date = $value;
                break;
            case 'Сумма':
                $platezhka->sum = (float) $value;
                break;
            case 'НазначениеПлатежа':
                $platezhka->paydirection = $value;
                break;
            case 'ПлательщикИНН':
                if (!isset($platezhka->payer)) {
                    $platezhka->payer = new Kontragent();
                }
                $platezhka->payer->INN = $value;
                break;
            case 'Плательщик1':
                if (!isset($platezhka->payer)) {
                    $platezhka->payer = new Kontragent();
                }
                $platezhka->payer->payerName = $value;
                break;
            case 'ПолучательИНН':
                if (!isset($platezhka->receiver)) {
                    $platezhka->receiver = new Kontragent();
                }
                $platezhka->receiver->INN = $value;
                break;
            case 'Получатель1':
                if (!isset($platezhka->receiver)) {
                    $platezhka->receiver = new Kontragent();
                }
                $platezhka->receiver->receiverName = $value;
                break;
        }

    }

    private function readFile(): void
    {
        try {
            $this->validateFile();
            $this->fileContent = file($this->filePath);
            if ($this->fileContent === false) {
                throw new Exception("Не удалось прочитать файл: $this->filePath");
            }

            $this->fileContent;
        } catch (Exception $e) {
            echo "Ошибка: " . $e->getMessage() . PHP_EOL;
            $this->fileContent = [];
        }
    }

    private function validateFile(): void
    {
        if (!file_exists($this->filePath)) {
            throw new Exception("Файл не найден: " . $this->filePath);
        }

        if (!is_readable($this->filePath)) {
            throw new Exception("Нет прав на чтение файла: " . $this->filePath);
        }
    }
}

?>