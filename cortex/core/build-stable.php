<?php

require_once './Core.php';

/**
 * Build cortex
 */

Env::stage('Build cortex');

// todo

$width = 100;
$height = 100;

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
			'activateThreshold' => 2,
			//'activateResist' => 1,
			'fading' => 0.6,
			'burstDuration' => 2,
			'burstThreshold' => 120,
		]);

		$neuron->createInputs([
			'count' => 25,
			'radius' => 50,
			'weight' => 1,
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
			'radius' => 20,
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

$cortexDataFile = $cortex->resultPath . '/data.json';

unset($cortex);

// todo: save cortex state
file_put_contents($cortexDataFile, json_encode($neurons));

Env::trace('cortex was dumped');
sleep(1);

/**
 * Run cortex
 */

Env::stage('Run cortex');


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


$outputs = array_merge(
	$neurons['cc'.$cc1r.'n0']->exportOutputs(),
	$neurons['cc'.$cc1r.'n1']->exportOutputs(),
	$neurons['cc'.$cc1r.'n2']->exportOutputs(),
	$neurons['cc'.$cc1r.'n3']->exportOutputs()
	// $neurons['cc'.$cc1r.'n4']->exportOutputs(),
	// $neurons['cc'.$cc1r.'n5']->exportOutputs(),
	// $neurons['cc'.$cc1r.'n6']->exportOutputs()
);


for ($hop = 0; $hop < $hopMaxCount; $hop++) {
	Env::stage("hop: $hop");

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
