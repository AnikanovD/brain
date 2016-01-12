<?php

trait Singleton
{
    public static function getInstance() {
        static $_instance = NULL;
        $class = __CLASS__;
        return $_instance ?: $_instance = new $class;
    }

    public function __clone() {}

    public function __wakeup() {}
}

class Brain
{
    use Singleton;

    public $neurons = [];
    public $inputNeurons = [];
    public $outputNeurons = [];
    public $synapses = [];

    public $tickCount = 0;
    public $generationCount = 0;

    public function addNeuron($neuron)
    {
        $this->neurons[$neuron->getId()] = $neuron;

        return $neuron->getId();
    }

    public function updateNeuron($neuron)
    {
        $this->addNeuron($neuron);
    }

    public function getNeuron($neuronId)
    {
        return $this->neurons[$neuronId];
    }

    public function addInputNeuron($neuronId)
    {
        $this->inputNeurons[] = $neuronId;
    }

    public function addOutputNeuron($neuronId)
    {
        $this->outputNeurons[] = $neuronId;
    }

    public function addSynapse($synapse)
    {
        $configuration = $synapse->buildConfiguration();
        $this->synapses[$configuration['id']] = $configuration;

        return $configuration['id'];
    }

    public function updateSynapse($synapse)
    {
        $this->addSynapse($synapse);
    }

    public function getSynapse($synapseId)
    {
        return new Synapse($this->synapses[$synapseId]);
    }

    public function loadSynapses()
    {
        $net = file_get_contents('net.txt');
        //echo 'Loaded net hash:' . md5($net) . PHP_EOL;
        $this->synapses = unserialize($net);
    }

    public function dumpSynapses()
    {
        $synapses = serialize($this->synapses);
        //echo  PHP_EOL . 'Saved net hash:' . md5($synapses);
        file_put_contents('net.txt', $synapses);

        // monitor for test
        $monitor = '';
        foreach ($this->synapses as $key => $value) {
            $synapse = Brain::getInstance()->getSynapse($key);
            $monitor .= $key . ' '
                . $synapse->getSenderNeuron()->getPreId()
                . ' > '
                . $synapse->getReceiverNeuron()->getPreId()
                . ': '
                . $value['weight']
                . ' at ('
                . $value['spikeAt']
                . ')'
                . PHP_EOL;
        }
        file_put_contents('monitor.txt', $monitor);
    }

    public function tick()
    {
        $this->tickCount++;

        foreach ($this->neurons as $neuron) {
            $neuron->beforeTick();
        }

        foreach ($this->synapses as $synapse) {
            $synapse = $this->getSynapse($synapse['id']);
            $synapse->tick();
        }

        foreach ($this->neurons as $neuron) {
            $neuron->tick();
        }

        Projection::dumpLayerMap();
    }

    static public function getTickCount()
    {
        return self::getInstance()->tickCount;
    }

    public function activateInputNeuron($inputNeuronNum)
    {
        $neuronId = $this->inputNeurons[$inputNeuronNum - 1];
        $neuron = $this->getNeuron($neuronId);
        $neuron->activate();
    }

    public function isSpikeOutputNeuron($outputNeuronNum)
    {
        $neuronId = $this->outputNeurons[$outputNeuronNum - 1];
        $neuron = $this->getNeuron($neuronId);
        return $neuron->isSpike();
    }

    public function isWasOutput()
    {
        foreach ($this->outputNeurons as $neuronId) {
            $neuron = $this->getNeuron($neuronId);
            if ($neuron->isSpike()) {
                return true;
            }
        }

        return false;
    }

    public function isDead()
    {
        foreach ($this->neurons as $neuron) {
            if ($neuron->curState->state != NeuronState::POLARIZATION) {
                return false;
            }
        }

        return true;
    }
}

class BrainTeacher
{
    const FAIL = 2;
    const HALF_FAIL = 1;

    static public $teachFunction;
    static public $initFunction;
    static public $unsuccessfulAttempts;
    static public $maxGeneration;
    static public $maxTickPerGeneration;
    static public $maxAttempts;
    static public $resetOnLimit;

    static public function staggerSynapse($strong)
    {
        $brain = Brain::getInstance();

        foreach ($brain->synapses as $synapse) {
            $l = $strong;
            $t = rand(-$strong, $strong);
            $func = round(($t * $t * $t) / ($l * $l));
            $synapse['weight'] += $func;
            $brain->synapses[$synapse['id']] = $synapse;
        }
    }

    static public function amplifySynapses($tick, $inc, $dec)
    {
        $brain = Brain::getInstance();

        foreach ($brain->synapses as $synapse) {
            // todo: customizable threshold
            if ($brain->getTickCount() - $synapse['spikeAt'] < $tick) {
                $synapse['weight'] += $inc;
            } else {
                $synapse['weight'] -= $dec;
            }
            $brain->synapses[$synapse['id']] = $synapse;
        }
    }

    static public function addAttempt($result)
    {
        if (self::HALF_FAIL == $result) {
            static::$unsuccessfulAttempts += 1;
        }

        if (self::FAIL == $result) {
            static::$unsuccessfulAttempts += 2;
        }
    }

    static public function checkAttemptsCount($threshold)
    {
        return (static::$unsuccessfulAttempts >= $threshold);
    }

    static public function setTeachFunction($function)
    {
        static::$teachFunction = $function;
    }

    static public function setInitFunction($function)
    {
        static::$initFunction = $function;
    }

    static public function isLimit()
    {
        return (
            isset(static::$maxGeneration)
            && (Brain::getInstance()->generationCount > static::$maxGeneration)
        ) || (
            isset(static::$maxTickPerGeneration)
            && (Brain::getInstance()->getTickCount() / Brain::getInstance()->generationCount > static::$maxTickPerGeneration)
        );
    }

    static public function isExpected()
    {
        return call_user_func(static::$teachFunction);
    }

    static public function initBrain()
    {
        return call_user_func(static::$initFunction);
    }
}

class Projection
{
    static public $mapEnabled;
    static public $layerNeuronsCount;

    static public function dumpLayerMap()
    {
        $brain = Brain::getInstance();
        $neuronPotentials = [];
        $wasSpike = false;
        $layer = 0;

        if ($brain->getTickCount() % 20 == 1) {
            return;
        }

        if (!static::$mapEnabled) {
            echo "\r   #" . $brain->getTickCount() . ' (' . $brain->generationCount . ')';
        } else {
            echo str_repeat(PHP_EOL, 60);
            echo '#' . $brain->getTickCount() . ' - ' . memory_get_usage() . PHP_EOL;

            foreach ($brain->neurons as $neuron) {
                if ($neuron->isSpike()) {
                    $icon = ' ! ';
                    $wasSpike = true;
                } else {
                    $icon = ' ' . $neuron->curState->getStateIcon() . ' ';
                }

                if ($layer == static::$layerNeuronsCount) {
                    echo PHP_EOL;
                    $layer = 0;
                }
                $layer++;
                echo $icon;
            }

            usleep(10000);

            if ($wasSpike) {
                //usleep(1000000);
            }
        }
    }

}

//--------

class Structure
{

}

class LayeredStructure extends Structure
{

    public function build($layersCount, $layerNeuronsCount, $additionalSynapses)
    {
        $brain = Brain::getInstance();
        $layerNeurons = [];

        // Build layers
        for ($layerNum = 0; $layerNum < $layersCount; $layerNum++) {
            for ($neuronNum = 0; $neuronNum < $layerNeuronsCount; $neuronNum++) {
                $neuron = new Neuron($layerNum . '-' . $neuronNum);
                // setting custom params
                $layerNeurons[$layerNum][$neuronNum] = $neuron;
            }
        }

        // Build synapses
        for ($layerNum = 0; $layerNum < ($layersCount - 1); $layerNum++) {
            for ($neuronNum = 0; $neuronNum < $layerNeuronsCount; $neuronNum++) {
                for ($receiverNeuronNum = 0; $receiverNeuronNum < $layerNeuronsCount; $receiverNeuronNum++) {
                    $synapse = new Synapse();
                    $synapse->setSenderNeuron($layerNeurons[$layerNum][$neuronNum]);
                    $synapse->setReceiverNeuron($layerNeurons[$layerNum + 1][$receiverNeuronNum]);

                    $synapseId = $brain->addSynapse($synapse);
                    $layerNeurons[$layerNum][$neuronNum]->addSynapse($synapseId); // save synapse id to neuron
                }
            }
        }

        // Additional synapses
        foreach ($additionalSynapses as $synapseConf) {
            $snp = explode(',', $synapseConf[0]);
            $rnp = explode(',', $synapseConf[1]);

            $synapse = new Synapse();
            $synapse->setSenderNeuron($layerNeurons[$snp[0]][$snp[1]]);
            $synapse->setReceiverNeuron($layerNeurons[$rnp[0]][$rnp[1]]);
            $synapse->setWeight($synapseConf[2]);

            $synapseId = $brain->addSynapse($synapse);
            $layerNeurons[$snp[0]][$snp[1]]->addSynapse($synapseId);
        }

        // Save structure to brain
        for ($layerNum = 0; $layerNum < $layersCount; $layerNum++) {
            for ($neuronNum = 0; $neuronNum < $layerNeuronsCount; $neuronNum++) {
                $neuronId = $brain->addNeuron($layerNeurons[$layerNum][$neuronNum]);

                if ($layerNum == 0) {
                    $brain->addInputNeuron($neuronId);
                }

                if ($layerNum == ($layersCount - 1)) {
                    $brain->addOutputNeuron($neuronId);
                }

                unset($layerNeurons[$layerNum][$neuronNum]);
            }
        }
    }

}

//--------

abstract class Cell
{
    public $id;
    public $preId;
    public $prevState;
    public $curState;

    public function __construct($preId)
    {
        $this->id = md5($preId . 'cell');
        $this->preId = $preId;

        $this->prevState = $this->createState();
        $this->curState = $this->createState();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPreId()
    {
        return $this->preId;
    }

    public function beforeTick()
    {
        $this->prevState = clone $this->curState;
    }

    abstract public function createState();
    abstract public function tick();
}

class Neuron extends Cell
{
    public $synapses = [];

    public $potentialSpike = 40;
    public $potentialActivate = -55;
    public $potentialBliss = -70;

    public $powerPolarization = 2;
    public $powerDepolarization = 10;
    public $powerRepolarization = 10;
    public $powerFactorBliss = 3;

    public function __construct($preId)
    {
        parent::__construct($preId);

        $this->prevState->potential = $this->potentialBliss;
        $this->curState->potential = $this->potentialBliss;
    }

    public function createState()
    {
        return new NeuronState;
    }

    public function addSynapse($synapseId)
    {
        $this->synapses[] = $synapseId;
    }

    public function getSynapses()
    {
        return $this->synapses;
    }

    public function isSpike()
    {
        return ($this->prevState->potential >= $this->potentialSpike);
    }

    public function activate()
    {
        $this->curState->setState(NeuronState::DEPOLARIZATION);
        $this->curState->potential = $this->potentialActivate;
    }

    public function integrate($influence)
    {
        $this->curState->potential += $influence;
    }

    public function updateState()
    {
        $state = $this->prevState;

        if ($state->state == NeuronState::POLARIZATION && $state->potential >= $this->potentialActivate) {
            $this->curState->setState(NeuronState::DEPOLARIZATION);
        }

        if ($state->state == NeuronState::DEPOLARIZATION && $state->potential >= $this->potentialSpike) {
            $this->curState->setState(NeuronState::REPOLARIZATION);
        }

        if ($state->state == NeuronState::REPOLARIZATION && $state->potential < $this->potentialBliss) {
            $this->curState->setState(NeuronState::BLISS);
        }

        if ($state->state == NeuronState::BLISS && $state->potential == $this->potentialBliss) {
            $this->curState->setState(NeuronState::POLARIZATION);
        }
    }

    public function tick()
    {
        $this->updateState();

        if ($this->curState->state == NeuronState::POLARIZATION && ($this->curState->potential - $this->powerPolarization) < $this->potentialBliss) {
            $this->curState->potential = $this->potentialBliss;
        }

        if ($this->curState->state == NeuronState::POLARIZATION && $this->curState->potential > $this->potentialBliss) {
            $this->curState->potential -= $this->powerPolarization;
        }

        if ($this->curState->state == NeuronState::DEPOLARIZATION) {
            $this->curState->potential += $this->powerDepolarization;
        }

        if ($this->curState->state == NeuronState::REPOLARIZATION) {
            $this->curState->potential -= $this->powerRepolarization;
        }

        if ($this->curState->state == NeuronState::BLISS) {
            $this->curState->potential = $this->potentialBliss + ceil(($this->potentialBliss - $this->curState->potential) / $this->powerFactorBliss);
        }
    }
}

class NeuronState
{
    const BLISS = 1;
    const POLARIZATION = 2;
    const DEPOLARIZATION = 3;
    const REPOLARIZATION = 4;

    public $state;
    public $potential;
    public $refTickNumber;
    public $stateTickCount = 0;

    public function __construct()
    {
        $this->tick();
        $this->state = self::POLARIZATION;
    }

    public function setState($state)
    {
        $this->state = $state;
        $this->stateTickCount = 1;
    }

    public function getStateSymbol()
    {
        $symbols = [
            self::BLISS => 'B',
            self::POLARIZATION => 'P',
            self::DEPOLARIZATION => 'D',
            self::REPOLARIZATION => 'R'
        ];

        return $symbols[$this->state];
    }

    public function getStateIcon()
    {
        $symbols = [
            self::BLISS => ' ',
            self::POLARIZATION => '*',
            self::DEPOLARIZATION => '+',
            self::REPOLARIZATION => '-'
        ];

        return $symbols[$this->state];
    }

    public function tick()
    {
        $this->refTickNumber = Brain::getTickCount();
        $this->stateTickCount++;
    }

}

class Synapse
{
    public $senderNeuronId;
    public $receiverNeuronId;
    public $weight = 7; // sign show stimulating or inhibitory influence
    public $spikeAt = -1000;

    public function __construct($configuration = null)
    {
        if (isset($configuration)) {
            $brain = Brain::getInstance();
            $this->senderNeuronId = $configuration['senderNeuronId'];
            $this->receiverNeuronId = $configuration['receiverNeuronId'];
            $this->weight = $configuration['weight'];
            $this->spikeAt = $configuration['spikeAt'];
        }
    }

    public function setSenderNeuron($neuron)
    {
        $this->senderNeuronId = $neuron->getId();
    }

    public function getSenderNeuron()
    {
        return Brain::getInstance()->getNeuron($this->senderNeuronId);
    }

    public function setReceiverNeuron($neuron)
    {
        $this->receiverNeuronId = $neuron->getId();
    }

    public function getReceiverNeuron()
    {
        return Brain::getInstance()->getNeuron($this->receiverNeuronId);
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    public function getId()
    {
        return md5($this->senderNeuronId . $this->receiverNeuronId);
    }

    public function buildConfiguration()
    {
        return [
            'id' => $this->getId(),
            'senderNeuronId' => $this->senderNeuronId,
            'receiverNeuronId' => $this->receiverNeuronId,
            'weight' => $this->weight,
            'spikeAt' => $this->spikeAt
        ];
    }

    public function tick()
    {
        $brain = Brain::getInstance();
        if ($this->getSenderNeuron()->isSpike()) {
            $this->getReceiverNeuron()->integrate($this->weight);
            $this->spikeAt = $brain->getTickCount();
            $brain->updateSynapse($this);
        }
    }
}

//--------

function buildBrain()
{
    $layersCount = 3;
    $layerNeuronsCount = 2;
    $additionalSynapses = [
        //['0,0', '2,0', '5'],
        //['0,1', '2,1', '5'],
        //['0,2', '2,2', '5'],
        //['0,3', '2,3', '5'],
    ];

    $structure = new LayeredStructure;
    $structure->build($layersCount, $layerNeuronsCount, $additionalSynapses);
}

function startBrain($attempt = 1)
{
    BrainTeacher::initBrain();
    Projection::$layerNeuronsCount = 2;
    $brain = Brain::getInstance();
    $brain->generationCount = 1;
    $brain->tickCount = 1;

    // Simulate brain
    for (;;) {
        if ($attempt > BrainTeacher::$maxAttempts) {
            return;
        }

        if (BrainTeacher::isLimit()) {
            echo '   attempt ' . $attempt . PHP_EOL;

            if (BrainTeacher::$resetOnLimit) {
                $brain->loadSynapses();
            }

            startBrain($attempt + 1);
            return;
        }

        $brain->tick();

        $successResolve = false;
        $fullSuccessResolve = false;

        if ($brain->isWasOutput()) {
            $successResolve = BrainTeacher::isExpected();
            if ($successResolve) {
                $fullSuccessResolve = true;
                if (rand(0,10) == 5) {
                    BrainTeacher::amplifySynapses(50, 1, 1);
                    $brain->dumpSynapses();
                }
            }

            while (!$brain->isDead()) {
                $brain->tick();

                if ($brain->isWasOutput()) {
                    $fullSuccessResolve = BrainTeacher::isExpected() && $fullSuccessResolve;
                }
            }

            // improve function
            //$fullSuccessResolve = $fullSuccessResolve && (BrainTeacher::$maxTickPerGeneration > $brain->getTickCount());

            if ($fullSuccessResolve) {
                $brain->dumpSynapses();
                BrainTeacher::$maxTickPerGeneration = $brain->getTickCount();
                echo PHP_EOL . '   Ok' . PHP_EOL . PHP_EOL;
                return;
            }
        }

        if ($brain->isDead()) {
            if ($successResolve && !$fullSuccessResolve) {
                //BrainTeacher::$unsuccessfulAttempts += 1;
                //BrainTeacher::amplifySynapses(40, 2, 2);
            } else {
                //BrainTeacher::$unsuccessfulAttempts += 2;
                BrainTeacher::staggerSynapse(10);
            }

            /*
            if (BrainTeacher::checkAttemptsCount(50)) {
                BrainTeacher::staggerSynapse(3 * $attempt);
                BrainTeacher::$unsuccessfulAttempts = 0;
            }
            */

            $brain->generationCount++;
            BrainTeacher::initBrain();
        }
    }

    echo PHP_EOL;
}


function buildNet()
{
    $brain = Brain::getInstance();

    BrainTeacher::setInitFunction(function() use ($brain) {
        $brain->activateInputNeuron(1);
    });

    BrainTeacher::setTeachFunction(function() use ($brain) {
        return true;
    });

    startBrain();
}

function teachCaseOne()
{
    $brain = Brain::getInstance();
    $brain->loadSynapses();

    BrainTeacher::setInitFunction(function() use ($brain) {
        $brain->activateInputNeuron(1);
        $brain->activateInputNeuron(2);
    });

    BrainTeacher::setTeachFunction(function() use ($brain) {
        return ($brain->isSpikeOutputNeuron(1) && !$brain->isSpikeOutputNeuron(2) && !$brain->isSpikeOutputNeuron(3) && !$brain->isSpikeOutputNeuron(4));
    });

    startBrain();
}

function teachCaseTwo()
{
    $brain = Brain::getInstance();
    $brain->loadSynapses();

    BrainTeacher::setInitFunction(function() use ($brain) {
        $brain->activateInputNeuron(3);
        $brain->activateInputNeuron(4);
    });

    BrainTeacher::setTeachFunction(function() use ($brain) {
        return (!$brain->isSpikeOutputNeuron(1) && !$brain->isSpikeOutputNeuron(2) && $brain->isSpikeOutputNeuron(3) && !$brain->isSpikeOutputNeuron(4));
    });

    startBrain();
}

function teachCaseThree()
{
    $brain = Brain::getInstance();
    $brain->loadSynapses();

    BrainTeacher::setInitFunction(function() use ($brain) {
        $brain->activateInputNeuron(1);
        $brain->activateInputNeuron(2);
    });

    BrainTeacher::setTeachFunction(function() use ($brain) {
        return ($brain->isSpikeOutputNeuron(1));
    });

    startBrain();
}

function teachCaseFour()
{
    $brain = Brain::getInstance();
    $brain->loadSynapses();

    BrainTeacher::setInitFunction(function() use ($brain) {
        //$brain->activateInputNeuron(1);
        $brain->activateInputNeuron(2);
    });

    BrainTeacher::setTeachFunction(function() use ($brain) {
        return (!$brain->isSpikeOutputNeuron(1));
    });

    startBrain();
}

function teachCaseFive()
{
    $brain = Brain::getInstance();
    $brain->loadSynapses();

    BrainTeacher::setInitFunction(function() use ($brain) {
        $brain->activateInputNeuron(1);
        //$brain->activateInputNeuron(2);
    });

    BrainTeacher::setTeachFunction(function() use ($brain) {
        return (!$brain->isSpikeOutputNeuron(1));
    });

    startBrain();
}


// Start
set_time_limit(3600);

buildBrain();
Projection::$mapEnabled = false;
BrainTeacher::$maxGeneration = 300;
BrainTeacher::$maxAttempts = 10;
BrainTeacher::$maxTickPerGeneration = 50;
BrainTeacher::$resetOnLimit = true;

if (isset($argv[1]) && ($argv[1] == 'new')) {
    buildNet();
    die();
}

for (;;) {
    //echo 'One' . PHP_EOL;
    //teachCaseOne();
    //echo 'Two' . PHP_EOL;
    //teachCaseTwo();
    //usleep(500000);
    echo 'Other' . PHP_EOL;
    teachCaseThree();
    teachCaseFour();
    teachCaseFive();

    echo PHP_EOL;
}