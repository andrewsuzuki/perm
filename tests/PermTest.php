<?php namespace Andrewsuzuki\Perm\Test;

use Andrewsuzuki\Perm\Perm;
use Illuminate\Filesystem\Filesystem;

class PermTest extends \PHPUnit_Framework_TestCase {

	public $testConfigArray = array();

	public function setup()
	{
		$this->testConfigArray = array(
			'url' => 'http://andrewsuzuki.com',
			'gender' => 'male',
			'name' => array(
				'first' => 'Andrew',
				'last' => 'Suzuki'
			),
		);
	}

	public function mockFilesystem()
	{
		return $this->getMock('Illuminate\Filesystem\Filesystem');
	}

	public function loadPerm($existing = true)
	{
		$filesystem = $this->mockFilesystem();
		$filesystem->expects($this->any())->method('exists')->will($this->returnValue($existing));
		$filesystem->expects($this->any())->method('getRequire')->will($this->returnValue($this->testConfigArray));
		$perm = new Perm($filesystem);

		return $perm->load('some_dir/some_file');
	}

	public function testLoadsExisting()
	{
		$perm = $this->loadPerm(true);
		$this->assertInstanceOf('Andrewsuzuki\\Perm\\Perm', $perm);
		$this->assertEquals($this->testConfigArray, $perm->getAll());
	}

	/**
	 * @expectedException		Exception
	 * @expectedExceptionMessage Existing configuration file could not be loaded (not valid array).
	 */
	public function testThrowsExceptionIfLoadsInvalid()
	{
		$filesystem = $this->mockFilesystem();
		$filesystem->expects($this->once())->method('exists')->will($this->returnValue(true));
		$filesystem->expects($this->once())->method('getRequire')->will($this->returnValue(null));
		$perm = new Perm($filesystem);

		$perm->load('does_not_exist/does_not_exist');
	}

	public function testLoadsNonExisting()
	{
		$perm = $this->loadPerm(false);
		$this->assertInstanceOf('Andrewsuzuki\\Perm\\Perm', $perm);
		$this->assertEquals(array(), $perm->getAll());
	}

	public function testGetAll()
	{
		$perm = $this->loadPerm(true);

		$this->assertEquals($this->testConfigArray, $perm->getAll());
	}

	public function testGet()
	{
		$perm = $this->loadPerm(true);

		$this->assertEquals($this->testConfigArray['url'], $perm->get('url'));
		$this->assertEquals($this->testConfigArray['name'], $perm->get('name'));
		$this->assertEquals($this->testConfigArray['name']['first'], $perm->get('name.first'));
		$this->assertEquals($this->testConfigArray['name']['last'], $perm->get('name.last'));

		$this->assertEquals(
			array('url' => $this->testConfigArray['url'], 'gender' => $this->testConfigArray['gender']),
			$perm->get(array('url', 'gender'))
		);
	}

	public function testSet()
	{
		$perm = $this->loadPerm(true);

		$this->assertInstanceOf('Andrewsuzuki\\Perm\\Perm', $perm->set('url', 'http://andrewsuzuki.com/actualUrl'));
		$this->assertInstanceOf('Andrewsuzuki\\Perm\\Perm', $perm->set('name.last', 'Brown'));

		$this->assertInstanceOf('Andrewsuzuki\\Perm\\Perm', $perm->set(array(
			'location' => 'Earth',
			'parents.dad' => 'John'
		)));

		$this->assertEquals('http://andrewsuzuki.com/actualUrl', $perm->get('url'));
		$this->assertEquals('Brown', $perm->get('name.last'));
		$this->assertEquals('Earth', $perm->get('location'));
		$this->assertEquals('John', $perm->get('parents.dad'));
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage Config value cannot be a closure.
	 */
	public function testSetClosureException()
	{
		$perm = $this->loadPerm(true);

		$perm->set('test', function() {
			return 'cool';
		});
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage Config value cannot be an object.
	 */
	public function testSetObjectException()
	{
		$perm = $this->loadPerm(true);

		$perm->set('test', new \StdClass);
	}

	public function testSetAndGetFilename()
	{
		$perm = $this->loadPerm(true);

		$new_filename = '/test/.a_new/profile';

		$this->assertInstanceOf('Andrewsuzuki\\Perm\\Perm', $perm->setFilename($new_filename));

		$this->assertEquals($new_filename, $perm->getFilename());
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage Filename basename cannot contain dots.
	 */
	public function testSetFilenameWithDotsException()
	{
		$perm = $this->loadPerm(true);

		$perm->setFilename('/test/.a_new/profile.blarga');
	}

	public function testForget()
	{
		$perm = $this->loadPerm(true);

		$this->assertInstanceOf('Andrewsuzuki\\Perm\\Perm', $perm->forget('name'));
		$this->assertInstanceOf('Andrewsuzuki\\Perm\\Perm', $perm->forget('gender'));

		$this->assertEquals(array('url' => $this->testConfigArray['url']), $perm->getAll());
	}

	public function testSave()
	{
		$filesystem = $this->mockFilesystem();
		$filesystem->expects($this->any())->method('makeDirectory');
		$filesystem->expects($this->any())->method('put')->with($this->anything(), $this->callback(function($contents) {
			return substr($contents, 0, 18) == '<?php return array' && substr($contents, -3) == ' ?>';
		}));

		$perm = new Perm($filesystem);

		$perm->load('some_dir/some_file');

		$save = $perm->save();

		$this->assertInstanceOf('Andrewsuzuki\\Perm\\Perm', $save);
	}

	/**
	 * @expectedException		Exception
	 * @expectedExceptionMessage A filename was not loaded/set.
	 */
	public function testSaveNoFilenameException()
	{
		$filesystem = $this->mockFilesystem();
		$filesystem->expects($this->any())->method('makeDirectory');
		$filesystem->expects($this->any())->method('put');
		$perm = new Perm($filesystem);

		$perm->save();
	}

	/**
	 * @expectedException		Exception
	 * @expectedExceptionMessage Can't make directory for some reason
	 */
	public function testSaveCantMakeDirectoryException()
	{
		$filesystem = $this->mockFilesystem();
		$filesystem->expects($this->any())->method('makeDirectory')->will($this->throwException(new \Exception('Can\'t make directory for some reason')));
		$filesystem->expects($this->any())->method('put');
		$perm = new Perm($filesystem);

		$perm->load('some_dir/some_file');

		$perm->save();
	}

	/**
	 * @expectedException		Exception
	 * @expectedExceptionMessage Can't put file for some reason
	 */
	public function testSaveCantPutFileException()
	{
		$filesystem = $this->mockFilesystem();
		$filesystem->expects($this->any())->method('makeDirectory');
		$filesystem->expects($this->any())->method('put')->will($this->throwException(new \Exception('Can\'t put file for some reason')));
		$perm = new Perm($filesystem);

		$perm->load('some_dir/some_file');

		$perm->save();
	}

	public function testMethodChaining()
	{
		$perm = $this->loadPerm(true);

		$result = $perm->set('url', 'http://andrewsuzuki.com/actualUrl')->forget('name')->forget('gender')->getAll();
		$this->assertEquals(array('url' => 'http://andrewsuzuki.com/actualUrl'), $result);
	}
}