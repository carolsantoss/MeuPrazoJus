<?php
namespace App\Services;

class DocumentService
{
    private function decodeTxt($str) {
        return @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str) ?: $str;
    }

    private function parseUA($ua) {
        if (!$ua || $ua == 'Não identificado') return 'Não identificado';
        
        $os = 'Desconhecido';
        if (preg_match('/iPhone/i', $ua)) $os = 'iPhone (iOS)';
        elseif (preg_match('/Android/i', $ua)) $os = 'Android';
        elseif (preg_match('/Windows/i', $ua)) $os = 'Windows';
        elseif (preg_match('/Macintosh|Mac OS X/i', $ua)) $os = 'Mac';
        elseif (preg_match('/Linux/i', $ua)) $os = 'Linux';

        $browser = 'Desconhecido';
        if (preg_match('/Chrome/i', $ua) && !preg_match('/Edge|Edg/i', $ua)) $browser = 'Chrome';
        elseif (preg_match('/Safari/i', $ua) && !preg_match('/Chrome/i', $ua)) $browser = 'Safari';
        elseif (preg_match('/Firefox/i', $ua)) $browser = 'Firefox';
        elseif (preg_match('/Edge|Edg/i', $ua)) $browser = 'Edge';
        
        return "$os - $browser";
    }

    private function drawSignatureBlock($pdf, $name, $cpf, $assinatura_base64 = null, $extraInfo = null) {
        $y = $pdf->GetY();
        if ($assinatura_base64 && strpos($assinatura_base64, 'data:image') === 0) {
            $data = explode(',', $assinatura_base64);
            $imgData = base64_decode(end($data));
            $isJpeg = strpos($assinatura_base64, 'data:image/jpeg') === 0;
            $ext = $isJpeg ? 'jpg' : 'png';
            $type = $isJpeg ? 'JPEG' : 'PNG';
            $tmpImg = sys_get_temp_dir() . '/' . uniqid('sig_') . '.' . $ext;
            file_put_contents($tmpImg, $imgData);
            
            $pdf->Image($tmpImg, 75, $y, 60, 0, $type);
            unlink($tmpImg);
            
            $pdf->SetY($y + 25);
        } else {
            $firstName = explode(' ', trim($name))[0];
            $pdf->SetFont('Times', 'I', 32);
            $pdf->SetTextColor(120, 120, 120);
            $pdf->Cell(0, 12, $this->decodeTxt($firstName), 0, 1, 'C');
            $pdf->SetY($pdf->GetY() + 5);
        }
        
        $yLinha = $pdf->GetY();
        $pdf->SetDrawColor(30, 30, 30);
        $pdf->Line(70, $yLinha, 140, $yLinha);
        $pdf->Ln(2);
        
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(0, 5, $this->decodeTxt($name), 0, 1, 'C');
        
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->Cell(0, 5, $this->decodeTxt($cpf), 0, 1, 'C');
        
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, $this->decodeTxt("Signatário"), 0, 1, 'C');
        
        if ($extraInfo) {
            $pdf->Ln(2);
            $pdf->SetFont('Helvetica', '', 6);
            $pdf->SetTextColor(150, 150, 150);
            $pdf->MultiCell(0, 3, $this->decodeTxt($extraInfo), 0, 'C');
        }
        $pdf->Ln(6);
    }

    private function drawHistoryItem($pdf, $date, $time, $iconType, $name, $actionText) {
        $yStart = $pdf->GetY();
        
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY(10, $yStart);
        $pdf->MultiCell(25, 4, $this->decodeTxt("$date\n$time"), 0, 'R');
        
        $pdf->SetXY(38, $yStart);
        $pdf->SetFont('Helvetica', '', 14);
        if ($iconType == 'create') {
            $pdf->SetTextColor(150, 150, 150);
            $pdf->Cell(10, 8, '+', 0, 0, 'C');
        } elseif ($iconType == 'view') {
            $pdf->SetTextColor(59, 130, 246);
            $pdf->Cell(10, 8, 'O', 0, 0, 'C'); 
        } elseif ($iconType == 'sign') {
            $pdf->SetTextColor(34, 197, 94);
            $pdf->Cell(10, 8, 'V', 0, 0, 'C'); 
        }
        
        $pdf->SetLeftMargin(55);
        $pdf->SetXY(55, $yStart);
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->Write(5, $this->decodeTxt($name . " "));
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->Write(5, $this->decodeTxt($actionText));
        $pdf->Ln(8);
        $pdf->SetLeftMargin(10); 
        $pdf->SetY($pdf->GetY() + 4);
    }

    private function drawRubrica($pdf, $x, $y, $assinatura_base64 = null) {
        if ($assinatura_base64 && strpos($assinatura_base64, 'data:image') === 0) {
            $data = explode(',', $assinatura_base64);
            $imgData = base64_decode(end($data));
            $isJpeg = strpos($assinatura_base64, 'data:image/jpeg') === 0;
            $ext = $isJpeg ? 'jpg' : 'png';
            $type = $isJpeg ? 'JPEG' : 'PNG';
            $tmpImg = sys_get_temp_dir() . '/' . uniqid('rub_') . '.' . $ext;
            file_put_contents($tmpImg, $imgData);
            
            $pdf->Image($tmpImg, $x, $y, 30, 0, $type);
            unlink($tmpImg);
        } else {
            $pdf->SetFont('Times', 'I', 10);
            $pdf->SetTextColor(180, 180, 180);
            $pdf->Text($x + 2, $y + 10, $this->decodeTxt("Assinado"));
        }
        
        $pdf->SetDrawColor(220, 220, 220);
        $pdf->Line($x, $y + 12, $x + 35, $y + 12);
    }

    public function assinarDocumento($caminhoOriginal, $doc_hash, $contratante, $cpf_contratante, $contratado, $cpf, $celular, $assinatura_base64 = null, $titulo = 'Documento', $userAgent = null, $signaturePositions = [], $metadataDocs = []) {
        $urlValidacao = "meuprazojus.com.br/validar/" . $doc_hash;
        $ip_con = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Desconhecido';
        $stampData = date('d/m/Y H:i:s');
        $signatureStamp = "IP: $ip_con | Data: $stampData\nDispositivo: " . $this->parseUA($userAgent);
        
        $tmpSigImg = null;
        $sigType = 'PNG';
        if ($assinatura_base64 && strpos($assinatura_base64, 'data:image') === 0) {
            $data = explode(',', $assinatura_base64);
            $imgData = base64_decode(end($data));
            $isJpeg = strpos($assinatura_base64, 'data:image/jpeg') === 0;
            $ext = $isJpeg ? 'jpg' : 'png';
            $sigType = $isJpeg ? 'JPEG' : 'PNG';
            $tmpSigImg = sys_get_temp_dir() . '/' . uniqid('sig_main_') . '.' . $ext;
            file_put_contents($tmpSigImg, $imgData);
        }

        $pdfSource = new \setasign\Fpdi\Fpdi();
        $totalSourcePages = $pdfSource->setSourceFile($caminhoOriginal);

        if (empty($metadataDocs)) {
            $metadataDocs = [[
                'name' => $titulo,
                'startPage' => 1,
                'pageCount' => $totalSourcePages
            ]];
        }

        $dirDestino = __DIR__ . '/../../uploads/' . $doc_hash;
        if (!is_dir($dirDestino)) {
            mkdir($dirDestino, 0777, true);
        }

        $generatedFiles = [];

        foreach ($metadataDocs as $index => $docMeta) {
            $pdf = new \setasign\Fpdi\Fpdi();
            $pdf->SetAutoPageBreak(false);
            $pdf->setSourceFile($caminhoOriginal);

            $start = $docMeta['startPage'];
            $end = $start + $docMeta['pageCount'] - 1;

            for ($pageNo = $start; $pageNo <= $end; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);

                $pdf->SetFont('Helvetica', '', 7);
                $pdf->SetTextColor(100, 100, 100);
                
                $legalInfo = "Em conformidade com a MP nº 2.200-2/2001 e Lei nº 14.063/2020.";
                $footerText = "Assinado por: $contratante e $contratado | Validar em $urlValidacao";
                
                $pdf->SetXY(10, $size['height'] - 12);
                $pdf->Cell($size['width'] - 20, 4, $this->decodeTxt($footerText), 0, 1, 'C');
                $pdf->SetX(10);
                $pdf->Cell($size['width'] - 20, 4, $this->decodeTxt($legalInfo), 0, 0, 'C');

                $hasDynamicSigs = false;
                if (!empty($signaturePositions)) {
                    foreach ($signaturePositions as $pos) {
                        if ($pos['page'] == $pageNo) {
                            $hasDynamicSigs = true;
                            $x = $pos['x'] * $size['width'];
                            $y = $pos['y'] * $size['height'];
                            $w = $pos['w'] * $size['width'];
                            $h = $pos['h'] * $size['height'];

                            $signerType = $pos['signer'] ?? 'signer_1';

                            if ($signerType === 'owner') {
                                $pdf->SetFont('Times', 'I', 14);
                                $pdf->SetTextColor(30, 30, 30);
                                $pdf->SetXY($x, $y + ($h / 2) - 3);
                                $pdf->Cell($w, 8, $this->decodeTxt($contratante), 0, 0, 'C');
                                
                                if (!empty($cpf_contratante)) {
                                    $pdf->SetFont('Helvetica', '', 8);
                                    $pdf->SetTextColor(100, 100, 100);
                                    $pdf->SetXY($x, $y + ($h / 2) + 5);
                                    $pdf->Cell($w, 6, $this->decodeTxt("CPF: " . $cpf_contratante), 0, 0, 'C');
                                }
                            } else if ($signerType === 'signer_1' || $signerType === 'signer') {
                                if ($tmpSigImg) {
                                    $pdf->Image($tmpSigImg, $x, $y, $w, $h, $sigType);
                                } else {
                                    $pdf->SetFont('Times', 'I', 14);
                                    $pdf->SetTextColor(0, 0, 0);
                                    $pdf->SetXY($x, $y + ($h / 2) - 3);
                                    $pdf->Cell($w, 8, $this->decodeTxt($contratado), 0, 0, 'C');
                                    
                                    if (!empty($cpf)) {
                                        $pdf->SetFont('Helvetica', '', 8);
                                        $pdf->SetTextColor(100, 100, 100);
                                        $pdf->SetXY($x, $y + ($h / 2) + 5);
                                        $pdf->Cell($w, 6, $this->decodeTxt("CPF: " . $cpf), 0, 0, 'C');
                                    }
                                }
                            } else {
                                $label = str_replace('signer_', 'Signatário ', $signerType);
                                if (isset($pos['signer_name']) && trim($pos['signer_name']) !== '') {
                                    $label = $pos['signer_name'];
                                }
                                $pdf->SetFont('Times', 'I', 12);
                                $pdf->SetTextColor(150, 150, 150);
                                $pdf->SetXY($x, $y + ($h / 2));
                                $pdf->Cell($w, 10, $this->decodeTxt($label . " (Pendente)"), 0, 0, 'C');
                                
                                $pdf->SetDrawColor(200, 200, 200);
                                $pdf->Rect($x, $y, $w, $h);
                            }
                        }
                    }
                }

                if (!$hasDynamicSigs && empty($signaturePositions)) {
                    $this->drawRubrica($pdf, $size['width'] - 45, $size['height'] - 28, $assinatura_base64);
                }
            }

            $pdf->SetAutoPageBreak(true, 20);
            $pdf->AddPage();
            
            $pdf->SetFont('Helvetica', 'B', 24);
            $pdf->SetTextColor(15, 23, 42); 
            $pdf->Cell(12, 10, 'FC', 0, 0, 'L');
            $pdf->SetTextColor(59, 130, 246); 
            $pdf->Cell(10, 10, '.', 0, 0, 'L');
            
            $pdf->SetFont('Helvetica', '', 8);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->SetXY(75, 12);
            $dtStr = date('d M Y \à\s H:i');
            $pdf->MultiCell(100, 4, $this->decodeTxt("Data e horários em GMT -3:00\nÚltima atualização em $dtStr\nIdentificador: $doc_hash"), 0, 'R');
            
            $qrPath = __DIR__ . "/../../uploads/qr_$doc_hash.png";
            if(!file_exists($qrPath)){
                $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode("https://meuprazojus.com.br/validar/$doc_hash");
                @file_put_contents($qrPath, @file_get_contents($qrUrl));
            }
            if(file_exists($qrPath) && filesize($qrPath) > 0) {
                $pdf->Image($qrPath, 178, 10, 20, 20, 'PNG');
            }
            
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->Line(10, 32, 200, 32);
            $pdf->SetY(32);
            $pdf->Ln(10);
            
            $pdf->SetFont('Helvetica', 'B', 16);
            $pdf->SetTextColor(15, 23, 42);
            $pdf->Cell(0, 15, $this->decodeTxt('Página de assinaturas'), 0, 1, 'C');
            $pdf->Ln(10);
            $ip_con = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Desconhecido';
            
            $cpfStr = empty($cpf_contratante) ? 'CPF Vinculado à Conta' : ("CPF: " . $cpf_contratante);
            $this->drawSignatureBlock($pdf, $contratante, $cpfStr); 
            $this->drawSignatureBlock($pdf, $contratado, "CPF: " . $cpf, $assinatura_base64, $signatureStamp);
            
            $pdf->Ln(5);
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetTextColor(15, 23, 42);
            $pdf->Cell(0, 8, $this->decodeTxt("HISTÓRICO"), 0, 1, 'L');
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
            $pdf->Ln(5);

            $d = date('d M Y');
            $t = date('H:i:s');
            $this->drawHistoryItem($pdf, $d, $t, 'create', $contratante, "criou este documento.");
            $this->drawHistoryItem($pdf, $d, $t, 'view', $contratante, "(Titular da Conta) visualizou este documento por meio do IP $ip_con.");
            $this->drawHistoryItem($pdf, $d, $t, 'sign', $contratante, "(Titular da Conta) assinou eletronicamente este documento por meio do IP $ip_con.");
            $this->drawHistoryItem($pdf, $d, $t, 'view', $contratado, "(Celular: $celular, CPF: $cpf) visualizou este documento por meio do IP $ip_con.");
            $this->drawHistoryItem($pdf, $d, $t, 'sign', $contratado, "(Celular: $celular, CPF: $cpf) assinou eletronicamente este documento por meio do IP $ip_con.");
            
            $pdf->SetY(-28); 
            $pdf->SetFont('Helvetica', 'I', 7.5);
            $pdf->SetTextColor(120, 120, 120);
            $pdf->MultiCell(0, 3.2, $this->decodeTxt("Este documento foi assinado por meio de assinaturas eletrônicas avançadas e está em plena conformidade com a Medida Provisória nº 2.200-2/2001 e com a Lei nº 14.063/2020, possuindo validade jurídica e integridade garantida por criptografia."), 0, 'C');

            $info = pathinfo($docMeta['name']);
            $nomeBase = preg_replace('/[^A-Za-z0-9_]/', '_', $info['filename']);
            if (!$nomeBase) $nomeBase = 'Documento_' . ($index + 1);
            
            $nomeArquivoFinal = $nomeBase . "_Assinado.pdf";
            $savePath = $dirDestino . '/' . $nomeArquivoFinal;
            
            if (file_exists($savePath)) {
                $nomeArquivoFinal = $nomeBase . '_' . ($index + 1) . "_Assinado.pdf";
                $savePath = $dirDestino . '/' . $nomeArquivoFinal;
            }

            $pdf->Output('F', $savePath);
            $generatedFiles[] = [
                'name' => $nomeArquivoFinal,
                'path' => $savePath
            ];
        }

        if ($tmpSigImg && file_exists($tmpSigImg)) {
            unlink($tmpSigImg);
        }

        if (count($generatedFiles) > 1 && class_exists('ZipArchive')) {
            $zipName = "Documentos_Assinados_" . substr($doc_hash, 0, 8) . ".zip";
            $zipPath = $dirDestino . '/' . $zipName;
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                foreach ($generatedFiles as $f) {
                    $zip->addFile($f['path'], $f['name']);
                }
                $zip->close();
                
                return $doc_hash . '/' . $zipName;
            }
        }
        
        return $doc_hash . '/' . $generatedFiles[0]['name'];
    }
    public function assinarDocumentoMulti($caminhoOriginal, $doc_hash, $contratante, $cpf_contratante, $collectedSignatures, $titulo = 'Documento', $signaturePositions = [], $metadataDocs = []) {
        $urlValidacao = "meuprazojus.com.br/validar/" . $doc_hash;
        
        $tmpFiles = [];
        foreach ($collectedSignatures as $sigId => &$sigData) {
            $sigData['_tmpPath'] = null;
            if (!empty($sigData['sig']) && strpos($sigData['sig'], 'data:image') === 0) {
                $data = explode(',', $sigData['sig']);
                $imgData = base64_decode(end($data));
                $isJpeg = strpos($sigData['sig'], 'data:image/jpeg') === 0;
                $ext = $isJpeg ? 'jpg' : 'png';
                $sigData['_sigType'] = $isJpeg ? 'JPEG' : 'PNG';
                $tmpPath = sys_get_temp_dir() . '/' . uniqid("sig_{$sigId}_") . '.' . $ext;
                file_put_contents($tmpPath, $imgData);
                $sigData['_tmpPath'] = $tmpPath;
                $tmpFiles[] = $tmpPath;
            }
        }
        
        $pdfSource = new \setasign\Fpdi\Fpdi();
        $totalSourcePages = $pdfSource->setSourceFile($caminhoOriginal);

        if (empty($metadataDocs)) {
            $metadataDocs = [[
                'name' => $titulo,
                'startPage' => 1,
                'pageCount' => $totalSourcePages
            ]];
        }

        $dirDestino = __DIR__ . '/../../uploads/' . $doc_hash;
        if (!is_dir($dirDestino)) {
            mkdir($dirDestino, 0777, true);
        }

        $generatedFiles = [];

        foreach ($metadataDocs as $index => $docMeta) {
            $pdf = new \setasign\Fpdi\Fpdi();
            $pdf->SetAutoPageBreak(false);
            $pdf->setSourceFile($caminhoOriginal);

            $start = $docMeta['startPage'];
            $end = $start + $docMeta['pageCount'] - 1;

            for ($pageNo = $start; $pageNo <= $end; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);

                $pdf->SetFont('Helvetica', '', 7);
                $pdf->SetTextColor(100, 100, 100);
                
                $legalInfo = "Em conformidade com a MP nº 2.200-2/2001 e Lei nº 14.063/2020.";
                
                $allNames = array_column($collectedSignatures, 'name');
                if (count($allNames) > 2) {
                    $footerText = "Assinado digitalmente por múltiplas partes | Validar em $urlValidacao";
                } else {
                    $nomesStr = implode(' e ', $allNames);
                    $footerText = "Assinado por: $contratante e $nomesStr | Validar em $urlValidacao";
                }
                
                $pdf->SetXY(10, $size['height'] - 12);
                $pdf->Cell($size['width'] - 20, 4, $this->decodeTxt($footerText), 0, 1, 'C');
                $pdf->SetX(10);
                $pdf->Cell($size['width'] - 20, 4, $this->decodeTxt($legalInfo), 0, 0, 'C');

                $hasDynamicSigs = false;
                if (!empty($signaturePositions)) {
                    foreach ($signaturePositions as $pos) {
                        if ($pos['page'] == $pageNo) {
                            $hasDynamicSigs = true;
                            $x = $pos['x'] * $size['width'];
                            $y = $pos['y'] * $size['height'];
                            $w = $pos['w'] * $size['width'];
                            $h = $pos['h'] * $size['height'];

                            $signerType = $pos['signer'] ?? 'signer_1';

                            if ($signerType === 'owner') {
                                $pdf->SetFont('Times', 'I', 14);
                                $pdf->SetTextColor(30, 30, 30);
                                $pdf->SetXY($x, $y + ($h / 2) - 3);
                                $pdf->Cell($w, 8, $this->decodeTxt($contratante), 0, 0, 'C');
                                
                                if (!empty($cpf_contratante)) {
                                    $pdf->SetFont('Helvetica', '', 8);
                                    $pdf->SetTextColor(100, 100, 100);
                                    $pdf->SetXY($x, $y + ($h / 2) + 5);
                                    $pdf->Cell($w, 6, $this->decodeTxt("CPF: " . $cpf_contratante), 0, 0, 'C');
                                }
                            } else {
                                if (isset($collectedSignatures[$signerType])) {
                                    $sigData = &$collectedSignatures[$signerType];
                                    if (!empty($sigData['_tmpPath'])) {
                                        $pdf->Image($sigData['_tmpPath'], $x, $y, $w, $h, $sigData['_sigType']);
                                    } else {
                                        $pdf->SetFont('Times', 'I', 14);
                                        $pdf->SetTextColor(0, 0, 0);
                                        $pdf->SetXY($x, $y + ($h / 2) - 3);
                                        $pdf->Cell($w, 8, $this->decodeTxt($sigData['name']), 0, 0, 'C');
                                        
                                        if (!empty($sigData['cpf'])) {
                                            $pdf->SetFont('Helvetica', '', 8);
                                            $pdf->SetTextColor(100, 100, 100);
                                            $pdf->SetXY($x, $y + ($h / 2) + 5);
                                            $pdf->Cell($w, 6, $this->decodeTxt("CPF: " . $sigData['cpf']), 0, 0, 'C');
                                        }
                                    }
                                } else {
                                    $label = str_replace('signer_', 'Signatário ', $signerType);
                                    if (isset($pos['signer_name']) && trim($pos['signer_name']) !== '') {
                                        $label = $pos['signer_name'];
                                    }
                                    $pdf->SetFont('Times', 'I', 12);
                                    $pdf->SetTextColor(150, 150, 150);
                                    $pdf->SetXY($x, $y + ($h / 2));
                                    $pdf->Cell($w, 10, $this->decodeTxt($label . " (Pendente)"), 0, 0, 'C');
                                    $pdf->SetDrawColor(200, 200, 200);
                                    $pdf->Rect($x, $y, $w, $h);
                                }
                            }
                        }
                    }
                }

                if (!$hasDynamicSigs && empty($signaturePositions)) {
                    $firstSig = reset($collectedSignatures);
                    $rubImg = $firstSig['sig'] ?? null;
                    $this->drawRubrica($pdf, $size['width'] - 45, $size['height'] - 28, $rubImg);
                }
            }

            $pdf->SetAutoPageBreak(true, 20);
            $pdf->AddPage();
            
            $pdf->SetFont('Helvetica', 'B', 24);
            $pdf->SetTextColor(15, 23, 42); 
            $pdf->Cell(12, 10, 'FC', 0, 0, 'L');
            $pdf->SetTextColor(59, 130, 246); 
            $pdf->Cell(10, 10, '.', 0, 0, 'L');
            
            $pdf->SetFont('Helvetica', '', 8);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->SetXY(75, 12);
            $dtStr = date('d M Y \à\s H:i');
            $pdf->MultiCell(100, 4, $this->decodeTxt("Data e horários em GMT -3:00\nÚltima atualização em $dtStr\nIdentificador: $doc_hash"), 0, 'R');
            
            $qrPath = __DIR__ . "/../../uploads/qr_$doc_hash.png";
            if(!file_exists($qrPath)){
                $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode("https://meuprazojus.com.br/validar/$doc_hash");
                @file_put_contents($qrPath, @file_get_contents($qrUrl));
            }
            if(file_exists($qrPath) && filesize($qrPath) > 0) {
                $pdf->Image($qrPath, 178, 10, 20, 20, 'PNG');
            }
            
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->Line(10, 32, 200, 32);
            $pdf->SetY(32);
            $pdf->Ln(10);
            
            $pdf->SetFont('Helvetica', 'B', 16);
            $pdf->SetTextColor(15, 23, 42);
            $pdf->Cell(0, 15, $this->decodeTxt('Página de assinaturas'), 0, 1, 'C');
            $pdf->Ln(10);
            
            $cpfStr = empty($cpf_contratante) ? 'CPF Vinculado à Conta' : ("CPF: " . $cpf_contratante);
            $this->drawSignatureBlock($pdf, $contratante, $cpfStr); 
            
            foreach ($collectedSignatures as $sigData) {
                $stamp = "IP: {$sigData['ip']} | Data: {$sigData['date']}\nDispositivo: " . $this->parseUA($sigData['ua']);
                $this->drawSignatureBlock($pdf, $sigData['name'], "CPF: " . $sigData['cpf'], $sigData['sig'], $stamp);
            }
            
            $pdf->Ln(5);
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetTextColor(15, 23, 42);
            $pdf->Cell(0, 8, $this->decodeTxt("HISTÓRICO"), 0, 1, 'L');
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
            $pdf->Ln(5);

            $this->drawHistoryItem($pdf, date('d M Y'), date('H:i:s'), 'create', $contratante, "criou este documento.");
            
            foreach ($collectedSignatures as $sigData) {
                $ts = strtotime($sigData['date']);
                $dSig = date('d M Y', $ts);
                $tSig = date('H:i:s', $ts);
                $this->drawHistoryItem($pdf, $dSig, $tSig, 'view', $sigData['name'], "(Celular: {$sigData['phone']}, CPF: {$sigData['cpf']}) acessou este documento por meio do IP {$sigData['ip']}.");
                $this->drawHistoryItem($pdf, $dSig, $tSig, 'sign', $sigData['name'], "(Celular: {$sigData['phone']}, CPF: {$sigData['cpf']}) assinou eletronicamente este documento por meio do IP {$sigData['ip']}.");
            }
            
            $pdf->SetY(-28); 
            $pdf->SetFont('Helvetica', 'I', 7.5);
            $pdf->SetTextColor(120, 120, 120);
            $pdf->MultiCell(0, 3.2, $this->decodeTxt("Este documento foi assinado por meio de assinaturas eletrônicas avançadas e está em plena conformidade com a Medida Provisória nº 2.200-2/2001 e com a Lei nº 14.063/2020, possuindo validade jurídica e integridade garantida por criptografia."), 0, 'C');

            $info = pathinfo($docMeta['name']);
            $nomeBase = preg_replace('/[^A-Za-z0-9_]/', '_', $info['filename']);
            if (!$nomeBase) $nomeBase = 'Documento_' . ($index + 1);
            
            $nomeArquivoFinal = $nomeBase . "_Assinado.pdf";
            $savePath = $dirDestino . '/' . $nomeArquivoFinal;
            
            if (file_exists($savePath)) {
                $nomeArquivoFinal = $nomeBase . '_' . ($index + 1) . "_Assinado.pdf";
                $savePath = $dirDestino . '/' . $nomeArquivoFinal;
            }

            $pdf->Output('F', $savePath);
            $generatedFiles[] = [
                'name' => $nomeArquivoFinal,
                'path' => $savePath
            ];
        }

        foreach ($tmpFiles as $t) {
            if (file_exists($t)) unlink($t);
        }

        if (count($generatedFiles) > 1 && class_exists('ZipArchive')) {
            $zipName = "Documentos_Assinados_" . substr($doc_hash, 0, 8) . ".zip";
            $zipPath = $dirDestino . '/' . $zipName;
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                foreach ($generatedFiles as $f) {
                    $zip->addFile($f['path'], $f['name']);
                }
                $zip->close();
                return $doc_hash . '/' . $zipName;
            }
        }
        
        return $doc_hash . '/' . $generatedFiles[0]['name'];
    }
}
