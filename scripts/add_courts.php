<?php

$jurisdictionsPath = __DIR__ . '/../data/jurisdictions.json';

if (!file_exists($jurisdictionsPath)) {
    die("jurisdictions.json not found!\n");
}

$data = json_decode(file_get_contents($jurisdictionsPath), true);

// TRFs
$trfs = [
    'TRF1' => ['AC', 'AM', 'AP', 'BA', 'DF', 'GO', 'MA', 'MT', 'PA', 'PI', 'RO', 'RR', 'TO'],
    'TRF2' => ['ES', 'RJ'],
    'TRF3' => ['MS', 'SP'],
    'TRF4' => ['PR', 'RS', 'SC'],
    'TRF5' => ['AL', 'CE', 'PB', 'PE', 'RN', 'SE'],
    'TRF6' => ['MG']
];

// TRTs
$trts = [
    'TRT1' => ['RJ'],
    'TRT2' => ['SP'], // Capital
    'TRT3' => ['MG'],
    'TRT4' => ['RS'],
    'TRT5' => ['BA'],
    'TRT6' => ['PE'],
    'TRT7' => ['CE'],
    'TRT8' => ['PA', 'AP'],
    'TRT9' => ['PR'],
    'TRT10' => ['DF', 'TO'],
    'TRT11' => ['AM', 'RR'],
    'TRT12' => ['SC'],
    'TRT13' => ['PB'],
    'TRT14' => ['RO', 'AC'],
    'TRT15' => ['SP'], // Interior
    'TRT16' => ['MA'],
    'TRT17' => ['ES'],
    'TRT18' => ['GO'],
    'TRT19' => ['AL'],
    'TRT20' => ['SE'],
    'TRT21' => ['RN'],
    'TRT22' => ['PI'],
    'TRT23' => ['MT'],
    'TRT24' => ['MS']
];

$courtsByState = [];

$stateNames = [];
foreach ($data['states'] as $s) {
    $stateNames[$s['id']] = $s['name'];
}

foreach ($stateNames as $uf => $name) {
    $courts = [];
    
    // 1. TJ (Estadual)
    $courts[] = [
        'id' => "TJ{$uf}",
        'name' => "TJ - {$uf} (Tribunal de Justiça do Estado)",
        'type' => 'TJ'
    ];
    
    // 2. TRF (Federal)
    foreach ($trfs as $trf => $ufs) {
        if (in_array($uf, $ufs)) {
            $region = substr($trf, 3);
            $courts[] = [
                'id' => $trf,
                'name' => "{$trf} - {$region}ª Região (Federal)",
                'type' => 'TRF'
            ];
            break;
        }
    }
    
    // 3. TRT (Trabalho)
    foreach ($trts as $trt => $ufs) {
        if (in_array($uf, $ufs)) {
            $region = substr($trt, 3);
            
            if ($uf === 'SP') {
               if ($trt === 'TRT2') {
                   $courts[] = [
                       'id' => 'TRT2',
                       'name' => "TRT - 2ª Região (SP Capital/Litoral)",
                       'type' => 'TRT'
                   ];
               } else if ($trt === 'TRT15') {
                   $courts[] = [
                       'id' => 'TRT15',
                       'name' => "TRT - 15ª Região (SP Interior)",
                       'type' => 'TRT'
                   ];
               }
            } else {
                $courts[] = [
                    'id' => $trt,
                    'name' => "TRT - {$region}ª Região (Trabalho)",
                    'type' => 'TRT'
                ];
            }
        }
    }
    
    $courtsByState[$uf] = $courts;
}

$data['courts'] = $courtsByState;

echo "Saving courts data...\n";
file_put_contents($jurisdictionsPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Done! Courts added for " . count($courtsByState) . " states.\n";
