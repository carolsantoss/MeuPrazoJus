<?php

$jurisdictionsPath = __DIR__ . '/../data/jurisdictions.json';
$localDataPath = __DIR__ . '/../data/ibge_cities.json';

echo "Reading data from local file: $localDataPath\n";

if (!file_exists($localDataPath)) {
    die("Local data file not found!\n");
}

$json = file_get_contents($localDataPath);
$municipios = json_decode($json, true);

if (!$municipios) {
    die("Error decoding JSON: " . json_last_error_msg() . "\n");
}

echo "Found " . count($municipios) . " municipalities.\n";

function normalizeId($string) {
    $string = trim($string);
    $string = mb_strtoupper($string, 'UTF-8');
    
    $string = preg_replace('/[ÁÀÃÂÄ]/u', 'A', $string);
    $string = preg_replace('/[ÉÈÊË]/u', 'E', $string);
    $string = preg_replace('/[ÍÌÎÏ]/u', 'I', $string);
    $string = preg_replace('/[ÓÒÕÔÖ]/u', 'O', $string);
    $string = preg_replace('/[ÚÙÛÜ]/u', 'U', $string);
    $string = preg_replace('/[Ç]/u', 'C', $string);
    $string = preg_replace('/[Ñ]/u', 'N', $string);
    
    $string = str_replace(' ', '_', $string);
    $string = preg_replace('/[^A-Z0-9_]/', '', $string);
    
    $string = preg_replace('/_+/', '_', $string);
    
    return $string;
}

$citiesByState = [];

foreach ($municipios as $mun) {
    $uf = $mun['microrregiao']['mesorregiao']['UF']['sigla'] ?? null;
    
    if (!$uf) {
        continue;
    }

    $name = $mun['nome'];
    $id = normalizeId($name);
    
    if (!isset($citiesByState[$uf])) {
        $citiesByState[$uf] = [];
    }
    
    $citiesByState[$uf][] = [
        'id' => $id,
        'name' => $name
    ];
}

foreach ($citiesByState as $uf => &$cities) {
    usort($cities, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
}

if (!file_exists($jurisdictionsPath)) {
    die("jurisdictions.json not found at $jurisdictionsPath\n");
}

$currentData = json_decode(file_get_contents($jurisdictionsPath), true);

$currentData['cities'] = $citiesByState;

echo "Saving updated jurisdictions.json with " . count($citiesByState) . " states...\n";
file_put_contents($jurisdictionsPath, json_encode($currentData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Done!\n";
