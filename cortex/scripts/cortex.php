<?php

class Env
{
	public static $showTrace = false;

	public static function stage($message)
	{
		echo PHP_EOL . ' # ' . $message . PHP_EOL;
	}

	public static function info($message)
	{
		echo '    ' . $message . PHP_EOL;
	}

	public static function warning($message)
	{
		echo PHP_EOL . ' ! >  ' .  $message . PHP_EOL . PHP_EOL;
	}

	public static function trace($message)
	{
		if (self::$showTrace) {
			echo ' i >  ' . $message . PHP_EOL;
		}
	}
}

class Cortex
{
	public $size;
	public $layers;

	public $resultPath;

	public function __construct($size)
	{
		$this->size = $size;

		// todo: move to class
		$this->resultPath = realpath(__DIR__ . '/../results') . '/last';

		if (@mkdir($this->resultPath)) {
			Env::trace("create path '" . $this->resultPath);
		} else {
			//Env::warning("can't create path '" . $this->resultPath);
		}
	}

	public function addLayer($name, $layer)
	{
		$this->layers[$name] = $layer;
	}

	public function appendLayer($name, $layer)
	{
		if (!isset($this->layers[$name])) {
			$this->layers[$name] = [];
		}

		$this->layers[$name] = array_merge($this->layers[$name], $layer);
	}

	public function dump($title = null)
	{
		list($width, $height) = $this->size;

		$im = imagecreate($width, $height);
		$bgColor = imagecolorallocate($im, 0, 0, 0);
		$spColor = imagecolorallocate($im, 230, 50, 50); // special

		foreach ($this->layers as $name => $layer) {
			$nameHash = crc32($name);
			$rColor = $gColor = $bColor = 255;
			$rColor -= $nameHash % 255;
			$nameHash = floor($nameHash / 255);
			$gColor -= $nameHash % 255;
			$nameHash = floor($nameHash / 255);
			$bColor -= $nameHash % 255;

			$fgColor = imagecolorallocate($im, $rColor, $gColor, $bColor);
			//$fgColor = imagecolorallocate($im, 50 + rand(0,8)*25, 50 + rand(0,8)*25, 50 + rand(0,8)*25);

			foreach ($layer as list($x, $y)) {
				if ($name != 'neurons') {
					imagesetpixel($im, $x, $y, $fgColor);
				} else {
					imagefilledellipse($im, $x, $y, 3, 3, $fgColor);
				}
			}
		}

		//$im = imagescale($im, 1800, -1, IMG_BICUBIC);

		if (isset($title)) {
			$title = 'dump-layer-' . $title;
		} else {
			$title = 'dump-layer';
		}

		$imagePath = $this->resultPath . '/' . $title . '.png';

		imagepng($im, $imagePath);
		imagedestroy($im);
	}
}

/**
 * Neuron
 * Creates its own inputs and outputs.
 * Receives input signals.
 * Integrates input signals.
 * Activates on hop.
 */
class Neuron
{
	public $id;
	public $coord;

	public $ccId; // cortical column id

	public $inputs;
	public $outputs;

	public $potential;

	public $activateResist;
	public $activateThreshold;

	public $burst;
	public $burstDuration;
	public $burstThreshold;

	public $fading;
	public $fadingRate;
	public $fadingThreshold;

	public function __construct($id, $coord, $inputs = [], $outputs = [])
	{
		Env::trace("generates neuron '$id'");

		$this->id = $id;
		$this->coord = $coord;

		$this->inputs = $inputs;
		$this->outputs = $outputs;
	}

	public function setCorticalColumnId($id)
	{
		$this->ccId = $id;
	}

	public function configure($params)
	{
		$this->potential = 0;

		$this->activateResist = $params['activateResist'] ?? 0;
		$this->activateThreshold = $params['activateThreshold'] ?? 1;

		$this->burst = 0;
		$this->burstDuration = $params['burstDuration'] ?? 0;
		$this->burstThreshold = $params['burstThreshold'] ?? 0;

		$this->fading = $params['fading'] ?? 0.5;
		$this->fadingRate = $params['fadingRate'] ?? 1;
		$this->fadingThreshold = $params['fadingThreshold'] ?? 0.5;
	}

	public function createInputs($params)
	{
		$inputCount = $params['count'];
		$inputRadius = $params['radius'];
		$inputWeight = $params['weight'];
		$inputOffset = $params['offset'] ?? 0;

		// Random offset
		$ox = rand(-$inputOffset, $inputOffset);
		$oy = rand(-$inputOffset, $inputOffset);

		// Center
		list($cx, $cy) = $this->coord;

		for ($i = 0; $i < $inputCount; $i++) {
			// Calculates random point around neuron
			$distance = 1;
			$distance += rand(0, $inputRadius * 0.2);
			$distance += rand(0, $inputRadius * 0.3);
			$distance += rand(0, $inputRadius * 0.5);

			$angle = (rand() / getrandmax()) * (M_PI * 2);

			$x = ceil(cos($angle) * $distance) + $cx + $ox;
			$y = ceil(sin($angle) * $distance) + $cy + $oy;

			// Add input
			$this->inputs[] = [$x, $y, $inputWeight];
		}
	}

	public function createOutputs($params)
	{
		$outputCount = $params['count'];
		$outputRadius = $params['radius'];
		$outputOffset = $params['offset'] ?? 0;

		// Random offset
		$ox = rand(-$outputOffset, $outputOffset);
		$oy = rand(-$outputOffset, $outputOffset);

		// Center
		list($cx, $cy) = $this->coord;

		for ($i = 0; $i < $outputCount; $i++) {
			// Calculates random point around neuron
			$distance = rand(0, $outputRadius * 1);

			$angle = (rand() / getrandmax()) * (M_PI * 2);

			$x = ceil(cos($angle) * $distance) + $cx + $ox;
			$y = ceil(sin($angle) * $distance) + $cy + $oy;

			// Add output
			$this->outputs[] = [$x, $y];
		}
	}

	public function destroyOutput($coord)
	{
		list($ox, $oy) = $coord;

		foreach ($this->outputs as $key => $output) {
			if (($output[0] == $ox) && ($output[1] == $oy)) {
				unset($this->outputs[$key]);
			}
		}
	}

	public function hop()
	{
		//$activateResist = rand(0, $this->activateResist);
		$activateResist = $this->activateResist;
		$prevPotential = $this->potential;

		if ($this->burstThreshold && ($this->potential >= $this->burstThreshold)) {
			$this->burst = $this->burstDuration;
			Env::info($this->id . ' in da BURST!!1 ');
		}

		if ($this->potential >= ($this->activateThreshold + $activateResist)) {
			//$this->potential = 0;
			$this->fadingRate += 2;
			$this->activateResist += abs($prevPotential - $this->activateThreshold);

			return $prevPotential;
		}

		if ($this->potential >= $this->fadingThreshold) {
			$this->potential *= $this->fading;
		} else {
			$this->potential = 0;
		}

		if ($this->activateResist > 1) {
			$this->activateResist *= ($this->fadingRate / ($this->fadingRate + 1));
		} else {
			$this->activateResist = 0;
		}

		if ($this->fadingRate > 1) {
			$this->fadingRate--;
		}

		if ($this->burst > 0) {
			Env::info($this->id . ' burst left: ' . $this->burst);
			$this->burst--;

			return $prevPotential;
		}

		return false;
	}

	public function hitInput($coord, $modificator = 1)
	{
		list($ix, $iy) = $coord;

		foreach ($this->inputs as $key => $input) {
			list($x, $y, $w) = $input;
			if (($ix == $x) && ($iy == $y)) {
				//$this->inputs[$key][2] = $w * ($modificator * 0.01);
				$this->potential += $w * $modificator;
				return true;
			}
		}

		return false;
	}

	public function exportInputs()
	{
		$coords = [];

		foreach ($this->inputs as list($x, $y, $w)) {
			$coords[] = [$x, $y];
		}

		return $coords;
	}

	public function exportOutputs()
	{
		return $this->outputs;
	}
}

/**
 * Build cortex
 */

Env::stage('Build cortex');

// todo

$width = 80;
$height = 40;

$hopMaxCount = 3000;

$neuronCount = 4;
$columnSize = 15;
$columnCount = $width * $height;

$cw = $width * $columnSize;
$ch = $height * $columnSize;

$cortex = new Cortex([$cw, $ch]);

Env::info("neurons: " . ($columnCount * $neuronCount));
Env::info("cortical columns: " . $columnCount);

// todo: move to class Cortex
$neurons = [];
$neuronMap = [];
$inputMap = [];
$outputMap = [];

// Builds cortical columns
echo "\n";
for ($cci = 0; $cci < $columnCount; $cci++) {
	$ccx = $columnSize * ($cci % $width);
	$ccy = $columnSize * floor($cci / $width);

	// todo: move to method Cortext::build()
	for ($ni = 0; $ni < $neuronCount; $ni++) {

		$id = 'cc' . $cci . 'n' . $ni;
		$ncr = 0.5;
		$coord = [
			round($ccx + $columnSize * 0.5 + rand($columnSize * -$ncr, $columnSize * $ncr)),
			round($ccy + $columnSize * 0.5 + rand($columnSize * -$ncr, $columnSize * $ncr)),
		];

		$neuron = new Neuron($id, $coord);
		$neuron->setCorticalColumnId($cci);
		$neuron->configure([
			'activateThreshold' => 1,
			//'activateResist' => 1,
			'fading' => 0.5,
			//'burstDuration' => 10,
			//'burstThreshold' => 40,
		]);

		$neuron->createInputs([
			'count' => 30,
			'radius' => 20,
			'weight' => 2,
		]);

		// Lateral break
		/*
		$neuron->createInputs([
			'count' => 4,
			'radius' => 4,
			'weight' => -30,
			'offset' => 200,
		]);
		*/

		// Long axon
		/*
		if ($ni == 0) {
			$neuron->createOutputs([
				'count' => 10,
				'radius' => 5,
				'offset' => 30,
			]);
		}
		*/

		$neuron->createOutputs([
			'count' => 15,
			'radius' => 10,
			//'offset' => 0,
		]);

		// Add to the global list of neurons
		$neurons[$id] = $neuron;

		// Build map of neuron coordinate
		$neuronMap[$id] = $coord;

		//$cortex->addLayer($id . 'in', $inputs);
		//$cortex->appendLayer('inputs', $inputs);
		//unset($inputs);

		//$outputs = $neuron->exportOutputs();
		//$cortex->appendLayer('outputs', $outputs);
		//unset($outputs);
	}
	echo '.';
}
echo "\n";

Env::info('build map of inputs');

// Build map of inputs
foreach ($neurons as $id => $neuron) {
	foreach ($neuron->exportInputs() as list($x, $y)) {
		if (!isset($inputMap[$x][$y])) {
			$inputMap[$x][$y] = [];
		}
		$inputMap[$x][$y][] = $id;
	}
}

// Cleanup unused outputs
$destroyedOutputs = 0;
foreach ($neurons as $id => $neuron) {
	foreach ($neuron->exportOutputs() as list($x, $y)) {
		if (!isset($inputMap[$x][$y])) {
			$neuron->destroyOutput([$x, $y]);
			$destroyedOutputs++;
		}
	}
}

Env::info("destroyed outputs: $destroyedOutputs");

// Create dump of neurons
$cortex->addLayer('neurons', $neuronMap);
$cortex->dump();

unset($cortex);

// todo: save cortex state
file_put_contents(__DIR__ . '/../results/last/data.json', json_encode($neurons));

Env::trace('cortex was dumped');
sleep(1);

/**
 * Run cortext
 */

Env::stage('Run cortext');


// Prepare initial inputs
$cc1r = rand($columnCount * 0.1, $columnCount * 0.9);
$cc2r = rand($columnCount * 0.1, $columnCount * 0.9);
$cc3r = rand($columnCount * 0.1, $columnCount * 0.9);

$neurons['cc'.$cc1r.'n1']->potential = 100;
$neurons['cc'.$cc1r.'n2']->potential = 100;
$neurons['cc'.$cc1r.'n3']->potential = 100;

$neurons['cc'.$cc2r.'n1']->potential = 100;
$neurons['cc'.$cc2r.'n2']->potential = 100;
$neurons['cc'.$cc2r.'n3']->potential = 100;

$neurons['cc'.$cc3r.'n0']->potential = 100;
$neurons['cc'.$cc3r.'n1']->potential = 100;
$neurons['cc'.$cc3r.'n2']->potential = 100;

/*
$outputs = array_merge(
	$neurons['cc'.$cc1r.'n0']->exportOutputs(),
	$neurons['cc'.$cc1r.'n1']->exportOutputs(),
	$neurons['cc'.$cc1r.'n2']->exportOutputs(),
	$neurons['cc'.$cc1r.'n3']->exportOutputs(),
	$neurons['cc'.$cc1r.'n4']->exportOutputs(),
	$neurons['cc'.$cc1r.'n5']->exportOutputs(),
	$neurons['cc'.$cc1r.'n6']->exportOutputs()
);
*/

for ($hop = 0; $hop < $hopMaxCount; $hop++) {
	Env::info("hop: $hop");

	$interrupt = true;
	$hits = 0;
	$maxPotential = 0;
	$neuronCoords = [];
	$outputCount = count($outputs);

	$cortex = new Cortex([$cw, $ch]);

	// Activate inputs
	foreach ($outputs as list($x, $y)) {
		if (($x < 1) || ($y < 1)) continue;
		foreach ($inputMap[$x][$y] as $id) {
			//echo "  activate input ($x;$y) $id \n";
			//$hits += (int) $neurons[$id]->hitInput([$x, $y], ($outputCount > 2000) ? (2000 / $outputCount) : (($outputCount > 100) && ($outputCount < 1000) ? 1000 / $outputCount : 1));
			$hits += (int) $neurons[$id]->hitInput([$x, $y]);
			$neuronCoords[] = $neurons[$id]->coord;
		}
	}

	$cortex->addLayer('neurons', $neuronCoords);


	// Flush outputs
	$outputs = [];

	// Iterate hops
	foreach ($neurons as $id => $neuron) {
		$potential = $neuron->hop();

		if ($potential) {
			//Env::trace("activated $id ($activated)");
			$interrupt = false;

			if ($potential > $maxPotential) {
				$maxPotential = $potential;
			}

			//if ($hop % 3 == 1)
			    //$cortex->addLayer('inputs-' . $neuron->ccId, $neuron->exportInputs());

			$outputs = array_merge($outputs, $neuron->exportOutputs());
		}
	}


	//if ($hop % 2 == 1)
		//$cortex->addLayer('outputs-' . $neuron->ccId, $outputs);

	$cortex->dump(str_pad($hop, 4, '0', STR_PAD_LEFT));

	Env::info(" > outputs: $outputCount");
	Env::info(" > hits: $hits");
	Env::info(" > max potential: $maxPotential");

	if ($interrupt) {
		break;
	}
}

Env::stage("Done");
