<?php

/**
 * Мозг полиморфичный интерпретатор
 *
 * Понятие обладает важностью относительно других. Может быть отрицательной, или положительной.
 * Контекст рождается из совокупности других понятий и суммы их важности. Это кластер синапсов. Дендрит.
 * Понятие формируется из более базовых понятий, важность которых зависит от контекста.
 *
 * Мозг в сущности состоит из проводников тока.
 *
 * Феномен сознания состоит из структур:
 * рецептивного поля(афферентные нейроны)
 * понятий(интернейроны и синапсы)
 * - эмоций(лимбическая система)(алгоритм настройки синапсов)
 * - консолидация памяти(гиппокамп)(система управления синапсами)
 * реакций(эфферентные нейроны)
 *
 * Отрицательные эмоции, гасят положительные, т.к. намного важнее.
 * Стратегия выбора реакции
 * - в первую очередь убрать отрицательные
 * - по возможности добавить положительные, не добавляя при это косвенные отрицательные
 *
 * Синхронизация нейронов. Определение взаимосвязи контекстов.
 * Гиппокамп генерирует тета-ритм при удержании внимания.
 *
 * Серия дендритных спайков.
 *
 * Импульс индуцируется(генерируется) синапсом.
 * !!! Возможно надо добавить скорость прохождения импульса !!!
 * Импульс модулируется(изменяется) расстоянием(S;l), сопротивлением(R), весомостью(U), важностью(I).
 * !Закон ома: U = I * R
 * Импульс сгенерированный синапсом, в любом случае доходит до сомы, но с пройденным расстоянием потенциал становиться ничтожно малым (1/x^2).
 * !Потеря мощности: P(l) = (P^2 * l) / U^2
 *
 * У синапса, у дендрита, у сомы, различные механизмы изменения параметров.
 * Механизмы это разные комбинации условий взаимодействий белков клетки нейрона и экспресии их генов.
 */

abstract class BasicConductor
{

}

abstract class BasicCapacitor
{

}

/**
 * Импульс
 * Передаёт информацию
 * Совокупность импульсов, индуцированных в один момент времени, является информацией о моменте времени
 */
class Impulse
{
    public $time;
    public $potential;

    public function __construct($time, $potential = 1)
    {
        $this->time = $time;
        $this->potential = $potential;
    }
}

/**
 * Проводящий, но только полупровдниковый, потому что нам похуй откуда зашел импульс :)
 * Основная сущность всех частей нейрона
 */
abstract class Conductive
{
    /**
     * Название
     */
    public $name;

    /**
     * Подключение проводника
     */
    public $output;

    /**
     * Импульсы движущиеся к выходу
     */
    public $impulses = [];

    /**
     * Длина проводника
     */
    public $distance;

    /**
     * Создаем проводник
     * Указываем подключение к другому проводнику
     */
    public function __construct($output = null)
    {
        $this->output = $output;
    }

    /**
     * Индуцируем импульс
     */
    public function induce($link)
    {
        $this->impulses[] = new Impulse($this->distance);
    }

    abstract public function tick();

    /**
     * Импульс достиг подключения проводника
     */
    public function produce()
    {
        if (isset($this->output)) {
            $this->output->induce($this);
        }
    }

}

/**
 * Синапсы
 * Короткий проводник
 * Индуцируют и модулируют импульсы
 */
class Link extends Conductive
{
    /**
     * Удаленность (механический параметр, расстояние)
     * Определяет задержку импульсов до кластера синапсов (ветки дендритов)
     * Основа синхронизированности разных отделов мозга
     * Синхронизирует разноудаленные рецепции понятия.
     */
    public $distance = 1;

    /**
     * Коэффицент затухания потенциала (электрический параметр, сопротивление)
     * Оказывает пропорциональное воздействие на потенциал распространяющегося импульса
     * Кластеризует синапсы в ветки дендритов
     * Определяет контекст
     * Участвует в успешности генерации спайка кластера
     */
    public $fading = 1;

    /**
     * Относительная весомость(сила) импульса в кластере (семантический параметр, значимость)
     * Оказывает воздействие на потенциал импульса относительно других в кластере
     * Определяет вклад в контекст для понятия
     */
    public $effect = 1;

    /**
     * Следующий момент времени
     * Дискретно модулируем импульс или индуцируем импульс в подключении
     */
    public function tick()
    {
        foreach ($this->impulses as $index => $impulse) {
            if ($impulse->time == 0) {
                $this->produce();
                unset($this->impulses[$index]);
            } else {
                $impulse->time--;
                $this->modulate($impulse);
            }
        }
    }

    /**
     * Модулируем импульс
     * Зависит от высших свойств проводника
     */
    public function modulate($impulse)
    {
        $impulse->potential *= $this->fading;
    }
}

/**
 * Дендриты, ветка дендрита
 * Подмножество проводников
 * Модулируют импульсы
 */
class LinkCluster extends Link
{
    /**
     * Синапсы
     */
    public $links = [];

    /**
     * Локальный потенциал кластера дендритов
     */
    public $potential;
    public $potentialInit = 0;

    /**
     * Спайк
     * Синергия одновременно активированных понятий
     * Порог активации
     */
    public $spike = 3;

    public function induce($link)
    {
        $this->impulses[] = new Impulse(1, $link->effect);
    }

    /**
     * Интегрируем поступающие импульсы
     * Поляризуем кластер
     */
    public function tick()
    {
        // обрабатываем очередь импульсов
        foreach ($this->impulses as $key => $impulse) {
            if ($impulse->time == 0) {
                $this->potential += $impulse->potential;
            }

            $impulse->time--;

            // забываем про очень старые импульсы
            // надо сделать порог и коэффицент забываемости
            if ($impulse->time < -100) {
                unset($this->impulses[$key]);
            }
        }

        $this->modulate();
    }

    /**
     * Модулируем импульсы на кластере
     */
    public function modulate($impulse)
    {
        // если уже достаточно заряда то генерируем спайк
        if ($this->potential > $this->spike) {
            $this->produce();
            $this->potential = $this->potentialInit;
        } elseif ($this->potential > $this->potentialInit) {
            $this->potential -= 1;
        } elseif ($this->potential < $this->potentialInit) {
            $this->potential += 1;
        }
    }

}

/**
 * Сома
 * Объединяет дендриты и аксон
 * Модулирует импульсы
 */
class Entity extends LinkCluster
{
    /**
     * Дендриты
     */
    public $linkClusters = []; // дендриты

    /**
     * Аксон
     */
    public $links = [];

    /**
     * Потенциал нейрона
     */
    public $potential;

    /**
     * Спайк
     * Порог активации нейрона (синапсов аксона)
     */
    public $spike;
}