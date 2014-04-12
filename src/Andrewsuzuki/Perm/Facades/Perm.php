<?php namespace Andrewsuzuki\Perm\Facades;

use Illuminate\Support\Facades\Facade;

class Perm extends Facade {

	protected static function getFacadeAccessor()
	{
		return 'perm';
	}
}