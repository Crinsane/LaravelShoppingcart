<?php

class TestProduct {
	public $description = 'This is the description of the test model';
	public function find($id) { return $this; }
}