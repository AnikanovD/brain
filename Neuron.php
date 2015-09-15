<?php

namespace brain;

class Neuron {

	public $path;
	public $axons = [];

	public $potential;
	public $potentialFactor;
	public $threshold;

	public function receptor($potential)
	{
		$this->potential += $potential;

		if ($potential >= $threshold) {
			$this->spike();
		}
	}

	public function spike()
	{
		$outPotential = $this->potential * $potentialFactor;

		foreach ($axons as $axon) {
			$axon->retranslate($outPotential);
		}
	}

	public function scoring($score)
	{
		if ($score > 0) {
			$this->threshold -= 1;
		}

		if ($score < 0) {
			$this->threshold += 1;
		}
	}

	public function addAxon($to)
	{
		$axon = new Axon;
		$axon->connectedTo = World::$brain->getNeuronByPath($to);

		$this->axons[] = $axon;
	}

}