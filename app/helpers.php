<?php

use Carbon\Carbon;
use Illuminate\Support\Str;

function bool2int($value)
{
    return !is_null($value) ? intval($value) : null;
}

function databr2en($data)
{
    if (!$data) {
        return $data;
    }

    return Carbon::createFromFormat('d/m/Y', substr($data, 0, 10))->format('Y-m-d');
}

function dataen2br($data)
{
    if (!$data) {
        return $data;
    }

    return Carbon::createFromFormat('Y-m-d', substr($data, 0, 10))->format('d/m/Y');
}

function moedabr2en($valor)
{
    return str_replace(',', '.', str_replace('.', '', $valor));
}

function moedaen2br($valor)
{
    return number_format($valor, 2, ',', '.');
}

function timestamp_br($timestamp, $show_seconds = false)
{
    if (!$timestamp) {
        return $timestamp;
    }

    return Carbon::createFromFormat('Y-m-d H:i:s' . (Str::contains($timestamp, '.') ? '.u' : ''), $timestamp)->format('d/m/Y H:i' . ($show_seconds ? ':s' : ''));
}

function mascara_string($string, $mascara)
{
    for ($i = 0; $i < strlen($string); $i++) {
        $mascara[strpos($mascara, '#')] = $string[$i];
    }

    return $mascara;
}

function so_numero($string)
{
    return preg_replace('/[^0-9]/', '', $string);
}

function formata_bool($value)
{
    return $value ? 'Sim' : 'Não';
}

function formata_cep($cep)
{
    $cep = so_numero($cep);

    if (strlen($cep) != 8) {
        return '';
    }

    return substr($cep, 0, 5) . '-' . substr($cep, -3);
}

function formata_cpf_cnpj($cpf_cnpj)
{
    $cpf_cnpj = so_numero($cpf_cnpj);
    $tamanho = strlen($cpf_cnpj);

    if ($tamanho != 11 && $tamanho != 14) {
        return '';
    }

    return mascara_string($cpf_cnpj, $tamanho == 11 ? '###.###.###-##' : '##.###.###/####-##');
}

function formata_telefone($telefone)
{
    $telefone = so_numero($telefone);

    // É prefixo não geográfico (Ex.: 0800 123 1234)
    if (strlen($telefone) == 11 && in_array(substr($telefone, 0, 4), ['0300', '0500', '0800', '0900'])) {
        return mascara_string($telefone, '#### ### ####');
    }

    // Elimina zeros à esquerda
    $telefone = ltrim($telefone, '0');
    $digitos = strlen($telefone);

    // É um número inválido
    if ($digitos < 8 || $digitos > 11) {
        return '';
    }

    if ($digitos == 9) {
        $mascara = '# ####-####'; // 9 1234-1234
    } else if ($digitos == 10) {
        $mascara = '(##) ####-####'; // 53 1234-1234
    } else if ($digitos == 11) {
        $mascara = '(##) # ####-####'; // 53 9 1234-1234
    } else {
        $mascara = '####-####'; // 1234-1234
    }

    return mascara_string($telefone, $mascara);
}

function is_active_path($path)
{
    return request()->is($path . '*');
}

function is_cpf($cpf): bool
{
    $cpf = so_numero($cpf);

    if (strlen($cpf) != 11) {
        return false;
    }

    // Verifica se foi informado uma sequência de digitos repetidos. Ex.: 111.111.111-11
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    // Faz o cálculo para validar o CPF
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }

        $d = ((10 * $d) % 11) % 10;

        if ($cpf[$c] != $d) {
            return false;
        }
    }

    return true;
}

function is_cnpj(string $cnpj): bool
{
    $cnpj = so_numero($cnpj);

    if (strlen($cnpj) != 14) {
        return false;
    }

    // Verifica sequência de digitos repetidos. Ex: 11.111.111/111-11
    if (preg_match('/(\d)\1{13}/', $cnpj)) {
        return false;
    }

    // Valida dígitos verificadores
    for ($t = 12; $t < 14; $t++) {
        for ($d = 0, $m = ($t - 7), $i = 0; $i < $t; $i++) {
            $d += $cnpj[$i] * $m;
            $m = ($m == 2 ? 9 : --$m);
        }

        $d = ((10 * $d) % 11) % 10;

        if ($cnpj[$i] != $d) {
            return false;
        }
    }

    return true;
}

function humanize_seconds($seconds)
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;

    $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
    $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
    $seconds = str_pad($seconds, 2, '0', STR_PAD_LEFT);

    return "$hours:$minutes:$seconds";
}
