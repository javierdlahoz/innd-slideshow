<?php

namespace Slideshow;

abstract class AbstractSingleton{
	
	/**
	 * @var self
	 */
	private static $service;
	
	/**
	 * Get the singleton instance.
	 *
	 * @return self
	 */
	public static function getSingleton() {
		$class = get_called_class();
		if (!self::$service instanceof $class) {
			self::$service = new $class();
		}
		return self::$service;
	}


	// Evita que el objeto se pueda clonar
    public function __clone()
    {
        trigger_error('Clonation of this objetc is not allowed', E_USER_ERROR);
    }
	
}