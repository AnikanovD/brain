<?php

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