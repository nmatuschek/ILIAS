<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

/**
 * Factory to build predicates for the fluent interface.
 *
 * A predicate is some abstract function from some record (like a dictionary,
 * a row in a table) to true or false.
 */
class ilFluentPredicateAtomFactory {
	/**
	 * @var	\Closure
	 */
	protected $continue;

	/**
	 * @var	ilPredicateFactory
	 */
	protected $factory;

	public function __construct(\Closure $continue, ilPredicateFactory $factory) {
		$this->continue = $continue;
		$this->factory = $factory;
	}

	public function int($value) {
		$c = $this->continue;
		return $c($this->factory->int($value));
	}

	public function str($value) {
		$c = $this->continue;
		return $c($this->factory->str($value));
	}
}