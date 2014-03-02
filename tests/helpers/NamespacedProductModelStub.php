<?php namespace Acme\Test\Models;
class TestProduct {
	public $description = 'This is the description of the namespaced test model';
	public function find($id) { return $this; }
}