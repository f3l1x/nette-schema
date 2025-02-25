<?php

declare(strict_types=1);

use Nette\Schema\Context;
use Nette\Schema\Expect;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class DynamicParameter implements Nette\Schema\DynamicParameter
{
	/** @var string */
	private $value;


	public function __construct(string $value)
	{
		$this->value = $value;
	}
}


test('', function () {
	$schema = Expect::structure([
		'a' => Expect::string()->dynamic(),
		'b' => Expect::string('def')->dynamic(),
		'c' => Expect::int()->dynamic(),
		'arr' => Expect::arrayOf(Expect::int()->dynamic()),
		'anyOf' => Expect::anyOf(Expect::int(), Expect::string())->dynamic(),
	]);

	$context = new Context;
	Assert::equal(
		(object) [
			'a' => new DynamicParameter("\$this->parameters['foo']"),
			'b' => new DynamicParameter("\$this->parameters['bar']"),
			'c' => 123,
			'arr' => ['x' => new DynamicParameter("\$this->parameters['baz']")],
			'anyOf' => new DynamicParameter("\$this->parameters['anyOf']"),
		],
		$schema->complete([
			'a' => new DynamicParameter("\$this->parameters['foo']"),
			'b' => new DynamicParameter("\$this->parameters['bar']"),
			'c' => 123,
			'arr' => ['x' => new DynamicParameter("\$this->parameters['baz']")],
			'anyOf' => new DynamicParameter("\$this->parameters['anyOf']"),
		], $context)
	);

	Assert::equal(
		[
			[
				new DynamicParameter("\$this->parameters['foo']"),
				'string',
			],
			[
				new DynamicParameter("\$this->parameters['bar']"),
				'string',
			],
			[
				new DynamicParameter("\$this->parameters['baz']"),
				'int',
			],
		],
		$context->dynamics
	);
});
