<?php

namespace AdventOdCode;

$rawInput = '1,0,0,3,1,1,2,3,1,3,4,3,1,5,0,3,2,9,1,19,1,19,6,23,2,6,23,27,2,27,9,31,1,5,31,35,1,35,10,39,2,39,9,43,1,5,43,47,2,47,10,51,1,51,6,55,1,5,55,59,2,6,59,63,2,63,6,67,1,5,67,71,1,71,9,75,2,75,10,79,1,79,5,83,1,10,83,87,1,5,87,91,2,13,91,95,1,95,10,99,2,99,13,103,1,103,5,107,1,107,13,111,2,111,9,115,1,6,115,119,2,119,6,123,1,123,6,127,1,127,9,131,1,6,131,135,1,135,2,139,1,139,10,0,99,2,0,14,0';

$tests = [
    '1,0,0,0,99' => '2,0,0,0,99',
    '2,3,0,3,99' => '2,3,0,6,99',
    '2,4,4,5,99,0' => '2,4,4,5,99,9801',
    '1,1,1,4,99,5,6,0,99' => '30,1,1,4,2,5,6,0,99',
];

function runTests(array $tests): void
{
    $errors = [];
    foreach ($tests as $input => $expectedDump) {
        $computer = new Computer($input);
        $computer->execute();
        $dumpString = implode(',', $computer->dump());

        if ($expectedDump !== $dumpString) {
            $errors[] = [
                'input' => $input,
                'expected' => $expectedDump,
                'actual' => $dumpString,
            ];
        }
    }

    if ($errors) {
        var_dump($errors);

        echo PHP_EOL , ' TESTS WERE NOT PASSED' , PHP_EOL , PHP_EOL;

        die;
    }
}

class Computer
{
    protected const OPERATOR_SUM = 1;
    protected const OPERATOR_MULTIPLE = 2;
    protected const OPERATOR_FINISH = 99;

    protected const OPERATOR_MAP = [
        self::OPERATOR_SUM => 'sum',
        self::OPERATOR_MULTIPLE => 'multiple',
    ];

    protected const STEP = 4;

    /**
     * @var array
     */
    protected $originInput;

    /**
     * @var array
     */
    protected $inputParameters = [];

    /**
     * @var array
     */
    protected $program;

    public function __construct(string $rawInput)
    {
        $this->originInput = explode(',', $rawInput);
    }

    public function execute(): void
    {
        $this->flushMemory();

        $elementsCount = count($this->program);
        for ($index = 0; $index < $elementsCount; $index += self::STEP) {
            $operator = $this->getValue($index);

            if ($operator === self::OPERATOR_FINISH) {
                break;
            }

            if (!array_key_exists($operator, self::OPERATOR_MAP)) {
                throw new \Exception('Invalid operator: ' . $operator . ', index : ' . $index);
            }

            $method = self::OPERATOR_MAP[$operator];
            $this->$method($index);
        }
    }

    public function dump(): array
    {
        return $this->program;
    }

    public function getValue(int $index): int
    {
        return $this->program[$index];
    }

    public function setInputs(array $inputParameters): void
    {
        $this->inputParameters = $inputParameters;
    }

    protected function sum(int $index): void
    {
        $index1 = $this->program[$index + 1];
        $index2 = $this->program[$index + 2];
        $position = $this->program[$index + 3];

        $this->program[$position] = $this->program[$index1] + $this->program[$index2];
    }

    protected function multiple($index): void
    {
        $index1 = $this->program[$index + 1];
        $index2 = $this->program[$index + 2];
        $position = $this->program[$index + 3];

        $this->program[$position] = $this->program[$index1] * $this->program[$index2];
    }

    protected function flushMemory(): void
    {
        $this->program = $this->originInput;

        foreach ($this->inputParameters as $index => $replacement) {
            $this->program[$index] = $replacement;
        }
    }
}

class Variator
{
    protected const MIN_VALUE = 0;
    protected const MAX_VALUE = 99;

    protected const FINISH_CONDITION = 19690720;

    /**
     * @var Computer
     */
    protected $computer;

    /**
     * @var array
     */
    protected $inputs;

    public function __construct(Computer $computer){
        $this->computer = $computer;
    }

    public function run(): void
    {
        for ($i = self::MIN_VALUE; $i <= self::MAX_VALUE; ++$i) {
            for ($j = self::MIN_VALUE; $j <= self::MAX_VALUE; ++$j) {
                $this->computer->setInputs([1 => $i, 2 => $j]);
                $this->computer->execute();

                if ($this->computer->getValue(0) === self::FINISH_CONDITION) {
                    $this->inputs = [
                        $i,
                        $j,
                    ];

                    break;
                }
            }
        }

        if (!$this->inputs) {
            throw new \Exception('Inputs not found');
        }
    }

    public function getFormattedInputs(): string
    {
        return $this->inputs[0] . $this->inputs[1];
    }
}

runTests($tests);

$computer = new Computer($rawInput);
$variator = new Variator($computer);
$variator->run();

var_dump($variator->getFormattedInputs());
