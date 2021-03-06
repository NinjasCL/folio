<?php
// The MIT License (MIT)
// 
// Copyright (c) 2016 Camilo Castro - ninjas.cl
// 
// Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
// 
// The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
?>
<?php if($_SERVER['REQUEST_METHOD'] != 'POST'): ?>
<!DOCTYPE html>
<head>
	<meta charset="utf-8">
	<title>Ninja Folio</title>
	
	<style type="text/css">

		footer, header, #folioInfo {
			margin:2em;
			text-align: justify;
		}

		#folioInfo {
			max-width: 400px;
		}

		#folioInfo input {
			margin: 10px;
		}

		#folioInfo button {
			padding: 10px;
			margin-top: 10px;
			font-weight: bold;
			text-transform: uppercase;
			width: 100%;
		}

		#folioInfo fieldset {
			margin-bottom: 5px;
		}

		#folioInfo label {
			margin-right: 10px;
		}

		#folioInfo legend {
			font-weight:bold;
		}

	</style>
</head>
<body>
	<header>
		<h1>Ninja Folio Maker</h1>
	</header>

	<section id="folioInfo">
		<form action="./" method="post">
			
			<fieldset>
			 	<legend>Información de Folio:</legend>
			 	<p>Permite definir los números que aparecerán en cada folio.</p>

			 	<label for="fromNumber">Número Inicial</label>
				<input type="number" name="fromNumber" placeholder="1" required="" tabindex="1" value="1" autofocus="">

				<br>

				<label for="toNumber">Número Final&nbsp;&nbsp;</label>
				<input type="number" name="toNumber" placeholder="10" required="" tabindex="2" value="10">
			</fieldset>

			<fieldset>
				<legend>Información de Posición:</legend>
				<p>En que posición dentro del PDF aparecerá el número de folio</p>

				<label for="posX">Posición X</label>
				<input type="number" name="posX" placeholder="200" required="" tabindex="3">

				<br>

				<label for="posY">Posición Y</label>
				<input type="number" name="posY" placeholder="200" required="" tabindex="4">
			</fieldset>

			<fieldset>
				<legend>Información de Fuente:</legend>
				<p>Define el diseño de la letra y el tamaño que tendrá cada número de folio</p>

				<label for="fontFamily">Nombre de Fuente</label>
				<input type="text" name="fontFamily" placeholder="Arial" required="" tabindex="5" value="Arial">

				<br>

				<label for="fontSize">Tamaño de Fuente</label>
				<input type="number" name="fontSize" placeholder="14" required="" tabindex="6" value="14">
			</fieldset>

			<fieldset>
				<legend>Opciones:</legend>
				<input type="checkbox" name="oneFile" value="1" checked tabindex="7"> Crear un solo archivo con todos los folios
			</fieldset>

			<button type="submit" tabindex="8">Crear Folios</button>
		</form>
	</section>

	<footer>
		<p>2016 - Camilo Castro - <a href="//ninjas.cl" tabindex="9">Ninjas.cl</a></p>
	</footer>
</body>
</html>
<?php else: ?>

<?php
	
$from = (int)(array_key_exists('fromNumber', $_POST) ? $_POST['fromNumber'] : null);
$to = (int)(array_key_exists('toNumber', $_POST) ? $_POST['toNumber'] : null);

$posX = (double)(array_key_exists('posX', $_POST) ? $_POST['posX'] : null);
$posY = (double)(array_key_exists('posY', $_POST) ? $_POST['posY'] : null);

$fontFamily = (string)(array_key_exists('fontFamily', $_POST) ? $_POST['fontFamily'] : null);
$fontSize = (int)(array_key_exists('fontSize', $_POST) ? $_POST['fontSize'] : null);

$oneFile = (bool)(array_key_exists('oneFile', $_POST) ? $_POST['oneFile'] : null);

// Modificar si es necesario
$template = 'folio_template.pdf';
$folioDir = 'folios';
$oneFilePath = "$folioDir/folios.pdf";

if (!isset($from) && !isset($to)) {
	$from = 1;
	$to = 10;
}

if (!isset($from) || !is_numeric($from)) {
	die("Número Inicial No Válido");
}

if (!isset($to) || !is_numeric($to)) {
	die("Número Final No Válido");
}

if ($to < $from) {
	die("Número Final Menor a Inicial");
}

if (empty($posX) || !is_numeric($posX)) {
	die("Posición X No Válida");
}

if (empty($posY) || !is_numeric($posY)) {
	die("Posición Y No Válida");
}

if (empty($fontFamily)) {
	die("Nombre de Letra Vacía");
}

if (empty($fontSize) || !is_numeric($fontSize)) {
	die("Tamaño de Letra No Válida");
}

if (!file_exists($template)) {
	die("Archivo $template no encontrado");
}

if (!is_dir($folioDir)) {
	die("Directorio $folioDir no encontrado");
}

require_once('./vendor/fpdf/fpdf.php'); 
require_once('./vendor/fpdf/fpdi.php'); 

try {

	$files = [];

	for($i = $from; $i <= $to; $i++) {

		$pdf = new FPDI();

		$pdf->setSourceFile($template); 

		$tplIdx = $pdf->importPage(1); 
 
		$specs = $pdf->getTemplateSize($tplidx);

		// P = Vertical Layout
		// L = Horizontal Layout
   		$pdf->addPage($specs['h'] > $specs['w'] ? 'P' : 'L');

   		// use the imported page and place it at point 0,0; calculate width and height
		// automaticallay and ajust the page size to the size of the imported page
		$pdf->useTemplate($tplIdx, 0, 0, 0, 0, true); 

		// now write some text above the imported page 
		$pdf->SetFont($fontFamily, '', $fontSize);

		$pdf->SetTextColor(0,0,0);

		//set position in pdf document
		$pdf->SetXY($posX, $posY);

		$file = "$folioDir/folio_$i.pdf";

		//first parameter defines the line height
		$pdf->Write(0, $i);

		// output the pdf as a file (http://www.fpdf.org/en/doc/output.htm)
		$pdf->Output($file, 'F');

		$files[] = $file;
	}

	// iterate over array of files and merge
	if (isset($oneFile) && $oneFile) {

		$pdf = new FPDI();

		foreach ($files as $file) {

			$pageCount = $pdf->setSourceFile($file);
			
			for ($i = 0; $i < $pageCount; $i++) {

				// https://www.setasign.com/products/fpdi/about/#code
				$tplidx = $pdf->importPage($i + 1, '/MediaBox');

				$specs = $pdf->getTemplateSize($tplidx);

   				$pdf->addPage($specs['h'] > $specs['w'] ? 'P' : 'L');

				$pdf->useTemplate($tplidx, 0, 0, 0, 0, true); 
			}
	
		}

		$pdf->Output($oneFilePath, 'F');

		// Delete generated files
		foreach ($files as $file) {
			if(file_exists($file)) {
				unlink($file);
			}
		}
	}

} catch(Exception $e){
	die($e->getMessage());
}

$urlToFile = function($file) {

	$url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . 
			'://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
	
	$url .= htmlspecialchars($file);

	return $url;
};


if (isset($oneFile) && $oneFile) {
	$files = [$oneFilePath];
}

$fileList = "<ul>";

foreach ($files as $file) {
	$url = $urlToFile($file);
	$fileList .= '<li><a href="' . $url .'" target="_blank">' . $url . '</a></li>';
}

$fileList .= "</ul>";


?>
<!DOCTYPE html>
<head>
	<meta charset="utf-8">
	<title>Ninja Folio Maker</title>
	<style type="text/css">
		body {
			padding:2em;
		}
		button {
			padding: 1em; 
			width:10em; 
			text-transform: uppercase;
			font-weight: bold;
		}

		li {
			margin-top:10px;
		}
	</style>
</head>
<body>
	<h3>Trabajo Terminado.</h3>
	<p>Archivos Generados</p>

	<?php echo $fileList ?>

	<a href="./">
		<button>
			Reiniciar
		</button>
	</a>
</body>
</html>
<?php endif ?>