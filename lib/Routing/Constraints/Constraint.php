<?php

namespace Enlighten\Routing\Constraints;

use Enlighten\Http\Request;

/**
 * A routing constraint.
 */
abstract class Constraint
{
    /**
     * Returns whether or not this constraint was satisfied for a given $request.
     *
     * @param Request $request
     * @return bool
     */
    public abstract function isSatisfied(Request $request);
}