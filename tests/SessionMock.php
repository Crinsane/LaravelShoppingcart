<?php

class SessionMock {
	protected $session;
	public function has($key)
	{
		return isset($this->session[$key]);
	}
	public function get($key)
	{
		return $this->session[$key];
	}
	public function put($key, $value)
	{
		$this->session[$key] = $value;
	}
}