<?php

class Actor {}

	class PullActor extends Actor {}
	class MotorActor extends Actor {}
	class ChemicalActor extends Actor {}
		class ExcretoryActor extends ChemicalActor {}
		class SecretoryActor extends ChemicalActor {}

class Sensor {}

	class ExternalSensor extends Sensor {}
		class TouchSensor extends ExternalSensor {}
		class SoundSensor extends ExternalSensor {}
		class LightSensor extends ExternalSensor {}
		class ChemicalSensor extends ExternalSensor {}
		class TemperatureSensor extends ExternalSensor {}

	class InternalSensor extends Sensor {}
		class VitalitySensor extends InternalSensor {}
		class SaturationSensor extends InternalSensor {}

class Nucleus {} // plural: nuclei

class Cortex {} // plural: cortices
	class CorticalSpace {}
		class CorticalModule {} // include ~50~100 cortical columns 
			class CorticalColumn {} // include ~80~120 neurons
