<?php

class World
{
    public static $neurons;
    public static $neuronCount = 0;

    public static function newNeuron($neuron)
    {
        self::$neurons[++self::$neuronCount] = $neuron;

        return self::$neuronCount;
    }
}

class Neuron
{
    const STATE_BLISS    = 'bliss';
    const STATE_WARMED   = 'warmed';
    const STATE_INFLAMED = 'inflamed';
    const STATE_BURNED   = 'burned';

    public $id;

    public $potential;

    public $curState;
    public $prevState;

    public $sparkThreshold;
    public $burnoutThreshold;

    public $sparkPower;

    public $burningRate;
    public $coolingRate;

    public function __construct()
    {
        $this->id = World::newNeuron($this);

        $this->init();
    }

    public function init()
    {
        $this->setDefaultPreset();
    }

    public function setDefaultPreset()
    {
        $this->potential = 0;

        $this->curState = self::STATE_BLISS;
        $this->prevState = self::STATE_BLISS;

        $this->sparkThreshold = 20;
        $this->burnoutThreshold = 60;

        $this->sparkPower = 40;

        $this->burningRate = 4;
        $this->coolingRate = 5;
    }

    public function rememberState()
    {
        $this->prevState = $this->curState;
    }

    public function fire($potential)
    {
        $this->potential += $potential;
    }

    public function resolve()
    {
        $this->rememberState();

        // recover
        if ($this->curState == self::STATE_BLISS) {
            if ($this->potential < 0) {
                $this->potential = 0;
            }
        }

        // fire
        if ($this->curState == self::STATE_BLISS) {
            if ($this->potential > 0) {
                $this->curState = self::STATE_WARMED;
            }
        }

        // spark
        if ($this->curState == self::STATE_BLISS || $this->curState == self::STATE_WARMED) {
            if ($this->potential >= $this->sparkThreshold) {
                $this->curState = self::STATE_INFLAMED;
                $this->potential += $this->sparkPower;
            }
        }

        // burnout
        if ($this->prevState == self::STATE_INFLAMED && $this->curState == self::STATE_INFLAMED) {
            if ($this->potential <= $this->burnoutThreshold) {
                $this->curState = self::STATE_BURNED;
            } else {
                $this->potential += rand(0, $this->burningRate);
            }
        }

        // calm
        if ($this->prevState == self::STATE_BURNED && $this->curState == self::STATE_BURNED) {
            if ($this->potential <= 0) {
                $this->curState = self::STATE_BLISS;
            }
        }

        // permanent cooldown
        if ($this->potential > 0) {
            $this->potential -= $this->coolingRate;
        }
    }
}

trait NeuronStateChain
{
    public $stateChain;

    public function init()
    {
        parent::init();

        $this->stateChain = [
            self::STATE_BLISS,
            self::STATE_BLISS,
            self::STATE_BLISS,
            self::STATE_BLISS,
        ];
    }

    public function rememberState()
    {
        if ($this->prevState != $this->curState) {
            array_shift($this->stateChain);
            array_push($this->stateChain, $this->curState);
        }

        parent::rememberState();
    }

    public function getStateChain()
    {
        return implode(' => ', $this->stateChain);
    }

    public function defStatesChain()
    {
        $statesChains = [
            'associated' => [Neuron::STATE_BLISS, Neuron::STATE_WARMED],
            'detected'   => [Neuron::STATE_BLISS, Neuron::STATE_INFLAMED, Neuron::STATE_BURNED],
            'predicted'  => [Neuron::STATE_BLISS, Neuron::STATE_WARMED, Neuron::STATE_INFLAMED, Neuron::STATE_BURNED],
        ];

        return $statesChain;
    }
}

trait NeuronInputs
{
    public $inputs = [];

    public function in($potential)
    {
        $this->inputs[] = $potential;
    }

    public function integrate()
    {
        $potential = array_sum($this->inputs);
        $this->inputs = [];
        $this->fire($potential);
    }
}

trait NeuronOutput
{
    public function out()
    {
        if ($this->prevState == Neuron::STATE_INFLAMED) {
            return true;
        }

        return false;
    }
}

class SimpleNeuron extends Neuron
{
    use NeuronInputs;
    use NeuronOutput;
    //use NeuronStateChain;
}





$fires = [0,0,34,14,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
$neuron = new SimpleNeuron;

foreach ($fires as $i => $potential) {
    echo str_pad($i, 4, ' ', STR_PAD_BOTH) . ' | ';
    echo str_pad($neuron->curState, 12, ' ', STR_PAD_LEFT) . ' | ';
    echo str_pad($neuron->potential, 7, ' ', STR_PAD_BOTH) . ' | ';

    $neuron->integrate();

    echo str_pad($neuron->potential, 7, ' ', STR_PAD_BOTH) . ' | ';

    $neuron->resolve();

    echo str_pad($neuron->potential, 7, ' ', STR_PAD_BOTH) . ' | ';
    echo str_pad($neuron->curState, 12, ' ', STR_PAD_RIGHT) . ' | ';

    echo $neuron->out();

    $neuron->in($potential);
    //echo $neuron->getStateChain();

    echo PHP_EOL;
}