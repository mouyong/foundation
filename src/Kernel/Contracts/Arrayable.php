<?php

namespace Yan\Foundation\Kernel\Contracts;

use ArrayAccess;

interface Arrayable extends ArrayAccess
{
    public function toArray();
}