<?php
function pre_print($m){
	echo "<pre>";
	print_r($m);
	echo "</pre>";
}

$dir    = './statnice';

$otazky = scandir($dir);
array_shift($otazky);
array_shift($otazky);
// $otazky = array_slice($otazky,6,2);

$tmp = array();
$ook = true;
$err=array();
foreach($otazky as $one){
		$pics = scandir($dir."/".$one);
		array_shift($pics);
		array_shift($pics);
		
		$tmp[$one] = array();
		foreach($pics as $pix) {
			$fileName = $dir."/".$one."/".$pix;
			$fileSize = filesize($fileName);
			if($fileSize < 10){
				// // echo $fileName . " > ".$fileSize."<br>";
				// // $ook=false;
				$err[] = $fileName . " > " . $fileSize;
			}
			$ext = pathinfo($fileName, PATHINFO_EXTENSION);
			if(substr($ext,0,1) === "j" || substr($ext,0,1) === "J"){
				$tmp[$one][] = $pix;
			}	
		}
}
error_reporting(E_ERROR | E_WARNING | E_PARSE);
// pre_print($tmp);
// exit();
require_once('tcpdf_include.php');
$pdf = new TCPDF('Portrait', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->setAutoPageBreak(FALSE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setFont('dejavusans', '', 12, '', true);

if(count($err)>0){
	$pdf->AddPage();
	$pdf->Write(5,"Chybne soubory");
	$pdf->Ln();
	foreach($err as $e){
				$pdf->Write(5,$e);	
				$pdf->Ln();
	}
	
}

// $pdf->AddPage();
// foreach($tmp as $folder=>$otazka){	
	// $pdf->Write(5,$folder." > ".count($otazka));	
	// $pdf->Ln();
// }

$v_space = 15; 
$cols = 3;

$h_space = 5;
$rows = 4;

$pix_w = (190 - ($cols-1)*$h_space) / $cols; 
$pix_h = (260 - ($rows-1)*$v_space) / $rows; 

$startx = 10;
$starty = 15;

$smallFontSize = 9;

foreach($tmp as $folder=>$otazka){	
	$pages = ceil(count($otazka) / ($cols*$rows));
	$page = 1;
	$pdf->AddPage();
	$pdf->setFont('dejavusans', 'B', 14, '', true);
	$pdf->Text(10,5,$folder. "     str. $page / $pages");
	$pdf->setFont('dejavusans', '', $smallFontSize, '', true);
	$x = 0;
	$y = 0;
	$cnt=0;

	foreach($otazka as $index=>$pix){
		$fileName = $dir."/".$folder."/".$pix;
		$fileSize = filesize($fileName);
		
		$posx = $startx + $x*($pix_w + $h_space);
		$posy =  $starty + $y*($pix_h + $v_space);
		
		if($fileSize !=0) {
			$dim = getimagesize($fileName);
			
			// pic dimensions
			$pw = $dim[0];
			$ph = $dim[1];
			
			// pic ratios
			$xratio = $pix_w / $pw;
			$yratio = $pix_h / $ph;
			$ratio = min($xratio, $yratio);
			
			$pixw = $pw*$ratio;
			$pixh = $ph*$ratio;
						
			// pic shifts to center it within given container
			$sx = ($pix_w - $pixw) / 2;
			$sy = ($pix_h - $pixh) / 2;
			
			$pdf->Image($fileName, $posx + $sx, $posy + $sy, $pixw, $pixh, 'JPG', '', '', true, 300, '', false, false, 0,true);
		}
		$cnt++;
		// $pdf->Text($posx,$posy+$pix_h ,$pix);
		
		$pdf->setXY($posx, $posy + $pix_h + 1);
		//public Cell(float $w, float $h[, string $txt = '' ], mixed $border, int $ln[, string $align = '' ][, bool $fill = false ][, mixed $link = '' ], int $stretch[, bool $ignore_min_height = false ][, string $calign = 'T' ][, string $valign = 'M' ]) : mixed
		$pdf->MultiCell($pix_w, 10 ,  $pix, 0, "L"); // $pix .
		
		$x++;
		if($x === $cols){
			$x=0;
			$y++;
			if($y === $rows){
				$y=0;
				if($cnt<count($otazka)){
					$page++;
					$pdf->AddPage();
					$pdf->setFont('dejavusans', 'B', 14, '', true);
					$pdf->Text(10,5,$folder. "     str. $page / $pages");
					$pdf->setFont('dejavusans', '', $smallFontSize, '', true);
				}
			}
		}
	}
} 
 // Image method signature:
// Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)


$pdf->Output('otazky.pdf', 'i');

