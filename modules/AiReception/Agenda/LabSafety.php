<?php

namespace Modules\AiReception\Agenda;

final class LabSafety
{
    public static function settings() : array
    {
        $root = dirname(__DIR__, 3);
        $path = $root . '/.agenda-runtime/settings.json';
        if (!is_file($path)) {
            throw new \RuntimeException('Laboratorio no inicializado.');
        }
        $s = json_decode(file_get_contents($path), true);
        if (!is_array($s) || ($s['purpose'] ?? '') !== 'disposable-aireception-lab-v1' || ($s['root'] ?? '') !== str_replace('\\', '/', realpath($root))) {
            throw new \RuntimeException('Configuracion de laboratorio invalida.');
        }
        return $s;
    }
    public static function check($db) : void
    {
        $s = self::settings();
        $row = $db->selectOne('SELECT @@port AS port, @@datadir AS datadir, DATABASE() AS db');
        $expected = strtolower(rtrim($s['root'], '/') . '/.agenda-runtime/mysql-data/');
        if ((int) $row->port !== 13317 || $row->db !== 'aireception_lab_v1' || strtolower(str_replace('\\', '/', $row->datadir)) !== $expected) {
            throw new \RuntimeException('Servidor fuera del laboratorio: operacion bloqueada.');
        }
    }
}
