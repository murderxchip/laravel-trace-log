<?php

namespace Cactus\Trace;


class TraceIndex
{
    private $index;
    private $level = 1;

    /**
     * TraceIndex constructor.
     *
     * @param $index 0 |
     */
    public function __construct($parentIndex = NULL)
    {
        if (!$parentIndex || $parentIndex == '') {
            $this->index = [1, 1];
        } else {
            $a = explode('.', $parentIndex);
            array_push($a, 1);
            $this->index = $a;
        }
        $this->level = count($this->index);
    }

    public function incr()
    {
        $this->index[$this->level - 1]++;
    }

    public function getIndex()
    {
        return join('.', $this->index);
    }
}