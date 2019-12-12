<?php

class Criteria
{
    public static function isValid(int $password): bool
    {
        return static::notRaisingCriteria($password)
            && static::duplicatingCriteria($password);
    }

    protected static function notRaisingCriteria(int $password): bool
    {
        $str = str_split($password);

        $prev = null;
        foreach ($str as $number) {
            if (
                $prev !== null
                && $prev > $number
            ) {
                return false;
            }

            $prev = $number;
        }

        return true;
    }

    protected static function duplicatingCriteria(int $password): bool
    {
        $str = str_split($password);

        foreach ($str as $index => $number) {
            $prevPrev = $str[$index - 2] ?? null;
            $prev = $str[$index - 1] ?? null;
            $next = $str[$index + 1] ?? null;

            if (
                $number === $prev
                && $number !== $prevPrev
                && $number !== $next
            ) {
                return true;
            }
        }

        return false;
    }
}

$tests = [
    111122 => true,
    111111 => false,
    12345 => false,
    123455 => true,
    1234554 => false,
    112345 => true,
    123444 => false,
    11223344 => true,
    11223344555 => true,
];

foreach ($tests as $password => $result) {
    if (Criteria::isValid($password) !== $result) {
        var_dump('ERROR', $password); die;
    }
}

$counter = 0;

$from = 240298;
$to = 784956;

for ($password = $from; $password <= $to; ++$password) {
    if (Criteria::isValid($password)) {
        $counter++;
    }
}

var_dump($counter);die();
