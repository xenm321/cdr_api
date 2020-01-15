<?php

namespace App\Services;

class CallRecordsService
{
    private $mysql;
    private $recordDir;

    public function __construct(Mysql $mysql, $recordDir)
    {
        $this->mysql = $mysql;
        $this->recordDir = rtrim($recordDir, '/');
    }

    public function getFile($callUniqueId)
    {
        $fileName = $this->getFileName($callUniqueId);

        return $this->getFilePath($fileName);
    }

    private function getFileName($callUniqueId)
    {
        $query = 'SELECT recordingfile FROM cdr WHERE uniqueid=:uniqueid';

        $fileName = $this->mysql->fetchOne($query, array('uniqueid' => $callUniqueId));

        if (!$fileName) {
            throw new \DomainException(sprintf('Файл с id %s не найден в БД', $callUniqueId));
        }

        return $fileName;
    }

    private function getFilePath($fileName)
    {
        $parts = explode('-', $fileName);

        if (!isset($parts[3])) {
            throw new \DomainException(sprintf('Некорректное имя файла %s', $fileName));
        }

        $year = substr($parts[3], 0, 4);
        $month = substr($parts[3], 4, 2);
        $day = substr($parts[3], 6, 2);

        $filePath = sprintf('%s/%s/%s/%s/%s', $this->recordDir, $year, $month, $day, $fileName);

        if (!file_exists($filePath)) {
            throw new \DomainException(sprintf('Файл записи %s не найден', $fileName));
        }

        return $filePath;
    }
}
