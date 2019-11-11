<?php

// Copyright 2019 The Bitum developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Bitum;

class Pow {

	private static $zero = '0';

	public static function hash($message, $algo = 'sha256') {
		return hash($algo, $message);
	}

	public static function findNonce($message) {
		$nonce = 0;
		while (!self::isValidNonce($message, $nonce)) {
			++$nonce;
		}

		return $nonce;
	}

	public static function isValidNonce($message, $nonce) {
		return 0 === strpos(self::hash($message . $nonce), self::$zero);
	}
}
