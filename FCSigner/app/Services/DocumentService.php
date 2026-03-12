<?php
namespace App\Services;

class DocumentService
{
    private function decodeTxt($str) {
        return @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str) ?: $str;
    }

    private function drawSignatureBlock($pdf, $name, $cpf, $assinatura_base64 = null) {
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
        $pdf->Ln(8);
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

    public function assinarDocumento($caminhoOriginal, $doc_hash, $contratante, $cpf_contratante, $contratado, $cpf, $celular, $assinatura_base64 = null) {
        $urlValidacao = "meuprazojus.com.br/validar/" . $doc_hash;
        
        $pdf = new \setasign\Fpdi\Fpdi();
        $pageCount = $pdf->setSourceFile($caminhoOriginal);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            $pdf->SetFont('Helvetica', '', 8);
            $pdf->SetTextColor(80, 80, 80);
            $textoFooter = $this->decodeTxt("Assinado por: $contratante e $contratado | Validar em $urlValidacao");
            // Centralizando certinho na base da página respeitando 15mm de margem inferior para não cortar na impressão.
            $pdf->SetXY(10, $size['height'] - 15);
            $pdf->Cell($size['width'] - 20, 10, $textoFooter, 0, 0, 'C');
        }

        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 20);
        
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
        
        // Colocar o QR code já no cabeçalho superior direito da Página de Assinaturas
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
        // Garante que o cursor do PDF desça a partir da nova linha antes de começar o título
        $pdf->SetY(32);
        $pdf->Ln(10);
        
        $pdf->SetFont('Helvetica', 'B', 16);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(0, 15, $this->decodeTxt('Página de assinaturas'), 0, 1, 'C');
        $pdf->Ln(10);
        $ip_con = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Desconhecido';
        
        $cpfStr = empty($cpf_contratante) ? 'CPF Vinculado à Conta' : ("CPF: " . $cpf_contratante);
        $this->drawSignatureBlock($pdf, $contratante, $cpfStr); 
        $this->drawSignatureBlock($pdf, $contratado, "CPF: " . $cpf, $assinatura_base64);
        
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
        $nomeArquivoFinal = "documento_" . $doc_hash . ".pdf";
        $pdf->Output('F', __DIR__ . '/../../uploads/' . $nomeArquivoFinal);
        
        return $nomeArquivoFinal;
    }
}
